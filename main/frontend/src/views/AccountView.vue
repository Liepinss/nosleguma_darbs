<template>
  <div class="account-container">
    <div class="account-box">
      <div class="account-header">
        <div>
          <h1>{{ t('account.title') }}</h1>
        </div>
        <div class="header-actions">
          <button type="button" @click="logout" class="btn-account-logout">{{ t('account.logout') }}</button>
        </div>
      </div>

      <div class="account-body">
        <div class="account-info">
          <div class="info-row">
            <span class="info-label">{{ t('account.nameLabel') }}</span>
            <span class="info-value">{{ name || t('account.noName') }}</span>
          </div>
          <div class="info-row">
            <span class="info-label">{{ t('account.emailLabel') }}</span>
            <span class="info-value">{{ email }}</span>
          </div>
          <p class="account-chat-hint">
            {{ t('account.chatHint') }}
          </p>
          <p v-if="$route.query.noadmin === '1'" class="account-noadmin-note">
            {{ t('account.noadminNote') }}
          </p>
        </div>

        <div class="adoption-section">
          <h2>{{ t('account.adoptionTitle') }}</h2>

          <div v-if="adoptions.length" class="adoption-list">
            <div class="adoption-card" v-for="adoption in adoptions" :key="adoption.id">
              <div class="animal-image">
                <img :src="adoption.animalImage || fallbackAnimalImg" :alt="adoption.animalName" />
              </div>
              <div class="animal-details">
                <h3>{{ adoption.animalName }}</h3>
                <p class="adoption-status-line">
                  <span
                    class="adoption-status-badge"
                    :class="{
                      'adoption-status-badge--pending': adoption.status === 'pending',
                      'adoption-status-badge--approved': adoption.status === 'approved',
                    }"
                  >
                    {{ adoptionStatusLabel(adoption.status) }}
                  </span>
                </p>
                <p><strong>{{ t('account.submittedAt') }}</strong> {{ formatDate(adoption.submittedAt) }}</p>
                <button
                  v-if="adoption.status === 'pending'"
                  type="button"
                  class="btn-release"
                  @click="releaseAdoption(adoption)"
                >
                  {{ t('account.withdraw') }}
                </button>
                <button
                  v-else-if="adoption.status === 'approved'"
                  type="button"
                  class="btn-release btn-release--approved"
                  @click="releaseAdoption(adoption)"
                >
                  {{ t('account.withdrawApproved') }}
                </button>
              </div>
            </div>
          </div>

          <p v-else class="no-adoption">{{ t('account.noAdoption') }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { getStoredUser, logoutUser } from '@/api/authApi'
import { deleteMyApplication, fetchMyApplications } from '@/api/restApi'
import { translate } from '@/i18n/siteMessages'
import { useLocaleStore } from '@/stores/locale'
import { clearLoggedInUserIfBlocked, isUserLoggedIn } from '@/utils/authStorage'
import { mapState } from 'pinia'

export default {
  name: 'AccountView',
  data() {
    return {
      name: '',
      email: '',
      adoptions: [],
    }
  },
  mounted() {
    void this.boot()
    window.addEventListener('storage', this.onStorageChange)
    window.addEventListener('authUpdated', this.onAuthUpdated)
  },
  beforeUnmount() {
    window.removeEventListener('storage', this.onStorageChange)
    window.removeEventListener('authUpdated', this.onAuthUpdated)
  },
  computed: {
    ...mapState(useLocaleStore, ['lang']),
    t() {
      return (key) => translate(this.lang, key)
    },
    fallbackAnimalImg() {
      const label = encodeURIComponent(translate(this.lang, 'account.imgFallback'))
      return `https://via.placeholder.com/180?text=${label}`
    },
  },
  methods: {
    async boot() {
      if (!isUserLoggedIn()) {
        this.$router.push('/login')
        return
      }
      if (await clearLoggedInUserIfBlocked()) {
        this.$router.push({ path: '/login', query: { blocked: '1' } })
        return
      }
      this.syncProfileFromStore()
      await this.loadAdoptions()
    },
    onAuthUpdated() {
      this.syncProfileFromStore()
    },
    syncProfileFromStore() {
      const u = getStoredUser()
      this.email = u?.email || ''
      this.name = u?.name || ''
    },
    async loadAdoptions() {
      try {
        const rows = await fetchMyApplications()
        this.adoptions = rows.map((adoption) => ({
          ...adoption,
          animalImage: adoption.animalImage || null,
        }))
      } catch {
        this.adoptions = []
      }
    },
    adoptionStatusLabel(status) {
      const s = status === 'approved' ? 'approved' : 'pending'
      return translate(this.lang, `account.adoptionStatus.${s}`)
    },
    onStorageChange(event) {
      if (event.key === 'spa_auth_token' || event.key === 'spa_auth_user') {
        void this.boot()
      }
    },
    formatDate(value) {
      if (!value) return ''
      const loc = this.lang === 'en' ? 'en-GB' : 'lv-LV'
      return new Date(value).toLocaleString(loc, {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
      })
    },
    async releaseAdoption(adoption) {
      if (adoption.status === 'approved') {
        if (!window.confirm(this.t('account.withdrawApprovedConfirm'))) {
          return
        }
      }
      try {
        await deleteMyApplication(adoption.id)
        await this.loadAdoptions()
        window.dispatchEvent(new Event('contactMessagesUpdated'))
      } catch {
        /* ignore */
      }
    },
    async logout() {
      await logoutUser()
      window.dispatchEvent(new Event('authUpdated'))
      this.$router.push('/login')
    },
  },
}
</script>

<style scoped>
.account-container {
  flex: 1;
  display: flex;
  justify-content: center;
  align-items: flex-start;
  padding: clamp(1.25rem, 3vw, 2rem) 20px 3rem;
  background:
    radial-gradient(ellipse 70% 45% at 50% 0%, rgba(120, 45, 30, 0.35), transparent 55%),
    linear-gradient(168deg, #0c0d12 0%, #0a0a0c 100%);
}

.account-box {
  width: 100%;
  max-width: 920px;
  padding: clamp(1.5rem, 3vw, 2.25rem);
  border-radius: 24px;
  background: linear-gradient(165deg, #161a22 0%, #0e1118 100%);
  border: 1px solid var(--hp-line-strong, rgba(255, 255, 255, 0.18));
  box-shadow: 0 24px 64px rgba(0, 0, 0, 0.45);
}

.account-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
}

.account-header h1 {
  font-family: 'Playfair Display', Georgia, serif;
  color: var(--hp-text, #f4f4f5);
  font-size: clamp(1.5rem, 3vw, 2rem);
  font-weight: 600;
}

.btn-account-logout {
  padding: 0.5rem 1.15rem;
  border-radius: 999px;
  font-weight: 700;
  font-size: 0.85rem;
  cursor: pointer;
  border: 1.5px solid rgba(248, 113, 113, 0.45);
  background: transparent;
  color: #fecaca;
  transition:
    background 0.2s ease,
    border-color 0.2s ease;
}

.btn-account-logout:hover {
  background: rgba(239, 68, 68, 0.12);
  border-color: #f87171;
}

.account-body {
  color: var(--hp-muted, rgba(255, 255, 255, 0.7));
}

.info-row {
  margin-bottom: 0.65rem;
  font-size: 1rem;
  color: var(--hp-text, #f4f4f5);
}

.info-label {
  color: var(--hp-muted, rgba(255, 255, 255, 0.7));
}

.account-chat-hint {
  margin-top: 1.25rem;
  font-size: 0.9rem;
  line-height: 1.45;
  color: var(--hp-muted, rgba(255, 255, 255, 0.7));
}

.account-noadmin-note {
  margin-top: 0.85rem;
  padding: 0.75rem 1rem;
  border-radius: 12px;
  background: rgba(245, 204, 76, 0.1);
  border: 1px solid rgba(245, 204, 76, 0.25);
  color: var(--hp-gold, #f5cc4c);
  font-size: 0.88rem;
  line-height: 1.45;
}

.adoption-section {
  margin-top: 2rem;
}

.adoption-section h2 {
  font-family: 'Playfair Display', Georgia, serif;
  color: var(--hp-gold, #f5cc4c);
  margin-bottom: 1rem;
  font-size: 1.25rem;
  font-weight: 600;
}

.adoption-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.adoption-card {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
  background: var(--hp-surface, rgba(255, 255, 255, 0.055));
  border: 1px solid var(--hp-line, rgba(255, 255, 255, 0.1));
  padding: 1rem;
  border-radius: 16px;
}

.animal-details h3 {
  color: var(--hp-text, #f4f4f5);
  margin-bottom: 0.35rem;
}

.adoption-status-line {
  margin: 0 0 0.35rem;
}

.adoption-status-badge {
  display: inline-block;
  padding: 0.2rem 0.65rem;
  border-radius: 999px;
  font-size: 0.72rem;
  font-weight: 800;
  letter-spacing: 0.04em;
  text-transform: uppercase;
}

.adoption-status-badge--pending {
  background: rgba(245, 204, 76, 0.12);
  border: 1px solid rgba(245, 204, 76, 0.35);
  color: var(--hp-gold, #f5cc4c);
}

.adoption-status-badge--approved {
  background: rgba(34, 197, 94, 0.15);
  border: 1px solid rgba(74, 222, 128, 0.4);
  color: #86efac;
}

.animal-details p {
  color: var(--hp-muted, rgba(255, 255, 255, 0.7));
  font-size: 0.92rem;
}

.animal-image img {
  width: 140px;
  height: 140px;
  object-fit: cover;
  border-radius: 12px;
  border: 1px solid var(--hp-line, rgba(255, 255, 255, 0.1));
}

.btn-release {
  margin-top: 0.65rem;
  padding: 0.45rem 1rem;
  background: rgba(239, 68, 68, 0.15);
  color: #fecaca;
  border: 1px solid rgba(248, 113, 113, 0.35);
  border-radius: 999px;
  cursor: pointer;
  font-weight: 700;
  font-size: 0.85rem;
}

.btn-release--approved {
  background: rgba(255, 255, 255, 0.06);
  color: var(--hp-text, #f4f4f5);
  border-color: rgba(255, 255, 255, 0.28);
}

.btn-release--approved:hover {
  background: rgba(248, 113, 113, 0.12);
  border-color: rgba(248, 113, 113, 0.45);
  color: #fecaca;
}

.no-adoption {
  color: var(--hp-muted, rgba(255, 255, 255, 0.7));
}
</style>
