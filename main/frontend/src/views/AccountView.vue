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
                <p><strong>{{ t('account.submittedAt') }}</strong> {{ formatDate(adoption.submittedAt) }}</p>
                <button class="btn-release" @click="releaseAdoption(adoption.id)">{{ t('account.withdraw') }}</button>
              </div>
            </div>
          </div>

          <p v-else class="no-adoption">{{ t('account.noAdoption') }}</p>
        </div>

        <div class="notifications-section">
          <h2>{{ t('account.notificationsTitle') }}</h2>
          <p v-if="!notifications.length" class="no-adoption">{{ t('account.noNotifications') }}</p>
          <div v-else class="notification-account-list">
            <div
              v-for="note in notifications"
              :key="note.id"
              class="notification-account-card"
            >
              <div class="notification-account-inner">
                <div class="notification-account-body">
                  <p class="notification-account-type">{{ notificationTitle(note) }}</p>
                  <p class="notification-account-text">{{ note.message }}</p>
                  <p class="notification-account-date">
                    {{ formatDate(note.moderatedAt || note.approvedAt || note.sentAt) }}
                  </p>
                </div>
                <button
                  type="button"
                  class="notification-delete-btn"
                  :title="t('account.notifDelete')"
                  :aria-label="t('account.notifDeleteAria')"
                  @click="deleteNotification(note.id)"
                >
                  ×
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { getStoredUser, logoutUser } from '@/api/authApi'
import {
  deleteMyApplication,
  fetchMyApplications,
  fetchMyNotifications,
  markMyNotificationsRead,
} from '@/api/restApi'
import { translate } from '@/i18n/siteMessages'
import { useLocaleStore } from '@/stores/locale'
import { clearLoggedInUserIfBlocked, isUserLoggedIn } from '@/utils/authStorage'
import { removeNotificationForUser } from '@/utils/contactMessages'
import { mapState } from 'pinia'

export default {
  name: 'AccountView',
  data() {
    return {
      name: '',
      email: '',
      adoptions: [],
      notifications: [],
      showNotifications: false,
    }
  },
  mounted() {
    void this.boot()
    window.addEventListener('storage', this.onStorageChange)
    window.addEventListener('contactMessagesUpdated', this.loadNotifications)
    window.addEventListener('authUpdated', this.onAuthUpdated)
  },
  beforeUnmount() {
    window.removeEventListener('storage', this.onStorageChange)
    window.removeEventListener('contactMessagesUpdated', this.loadNotifications)
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
    unreadNotifications() {
      return this.notifications.filter((note) => !note.read).length
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
      await this.loadNotifications()
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
    async loadNotifications() {
      try {
        const messages = await fetchMyNotifications()
        this.notifications = messages
          .filter((message) => message.status === 'approved')
          .sort(
            (a, b) =>
              new Date(b.moderatedAt || b.approvedAt || b.sentAt) -
              new Date(a.moderatedAt || a.approvedAt || a.sentAt),
          )
      } catch {
        this.notifications = []
      }
    },
    notificationTitle(note) {
      if (note.source === 'admin_role_grant') {
        return translate(this.lang, 'account.notifAdminRole')
      }
      return translate(this.lang, 'account.notifFromAdmin')
    },
    onStorageChange(event) {
      if (event.key === 'spa_auth_token' || event.key === 'spa_auth_user') {
        void this.boot()
      }
    },
    toggleNotifications() {
      this.showNotifications = !this.showNotifications
      if (this.showNotifications) {
        void this.markNotificationsRead()
      }
    },
    async deleteNotification(id) {
      await removeNotificationForUser(id)
      await this.loadNotifications()
    },
    async markNotificationsRead() {
      try {
        await markMyNotificationsRead()
        await this.loadNotifications()
      } catch {
        /* ignore */
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
    async releaseAdoption(adoptionId) {
      try {
        await deleteMyApplication(adoptionId)
        await this.loadAdoptions()
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

.adoption-section,
.notifications-section {
  margin-top: 2rem;
}

.adoption-section h2,
.notifications-section h2 {
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

.no-adoption {
  color: var(--hp-muted, rgba(255, 255, 255, 0.7));
}

.notification-account-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.notification-account-card {
  background: var(--hp-surface, rgba(255, 255, 255, 0.055));
  border: 1px solid var(--hp-line, rgba(255, 255, 255, 0.1));
  border-radius: 14px;
  padding: 0.85rem;
}

.notification-account-inner {
  display: flex;
  justify-content: space-between;
  gap: 12px;
}

.notification-account-type {
  color: var(--hp-gold, #f5cc4c);
  font-weight: 700;
  font-size: 0.85rem;
}

.notification-account-text {
  color: var(--hp-text, #f4f4f5);
  margin: 0.35rem 0;
}

.notification-account-date {
  color: var(--hp-muted, rgba(255, 255, 255, 0.7));
  font-size: 0.82rem;
}

.notification-delete-btn {
  background: transparent;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  line-height: 1;
  color: var(--hp-muted, rgba(255, 255, 255, 0.7));
  padding: 0.15rem;
}

.notification-delete-btn:hover {
  color: #fecaca;
}
</style>
