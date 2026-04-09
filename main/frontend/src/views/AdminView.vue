<template>
  <div class="admin-container">
    <div class="admin-header">
      <h1>Admin panelis</h1>
      <button @click="logout" class="btn-logout">Iziet</button>
    </div>

    <div class="admin-content">
      <div class="admin-tabs">
        <button
          :class="{ active: activeTab === 'messages' }"
          @click="activeTab = 'messages'"
          class="tab-btn"
        >
          Ziņojumi
        </button>
        <button
          :class="{ active: activeTab === 'supportChat' }"
          @click="openSupportChatTab"
          class="tab-btn"
        >
          Support čats
        </button>
        <button
          :class="{ active: activeTab === 'adoptions' }"
          @click="activeTab = 'adoptions'"
          class="tab-btn"
        >
          Adopcijas pieteikumi
        </button>
        <button
          :class="{ active: activeTab === 'animals' }"
          @click="activeTab = 'animals'"
          class="tab-btn"
        >
          Dzīvnieki
        </button>
        <button
          :class="{ active: activeTab === 'users' }"
          @click="activeTab = 'users'"
          class="tab-btn"
        >
          Lietotāji
        </button>
      </div>

      <!-- MESSAGES TAB -->
      <div v-if="activeTab === 'messages'" class="tab-content">
        <div class="tab-header-row">
          <h2>Kontaktu ziņojumi</h2>
          <button @click="loadData" class="btn-refresh">Atsvaidzināt</button>
        </div>
        <div v-if="messages.length === 0" class="no-data">
          Nav ziņojumu
        </div>
        <div v-else class="messages-list">
          <div v-for="message in messages" :key="message.id" class="message-item">
            <div class="message-header">
              <strong>{{ message.name }}</strong>
              <span class="message-date">{{ formatDate(message.sentAt) }}</span>
            </div>
            <p><strong>E-pasts:</strong> {{ message.email }}</p>
            <p v-if="message.selectedAnimals"><strong>Izvēlētie dzīvnieki:</strong> {{ message.selectedAnimals }}</p>
            <p><strong>Ziņojums:</strong></p>
            <p class="message-text">{{ message.message }}</p>
            <p><strong>Status:</strong> {{ message.status || 'pending' }}</p>
            <div class="message-actions">
              <button v-if="message.status === 'pending'" @click="approveMessage(message.id)" class="btn-approve">Apstiprināt</button>
              <button v-if="message.status === 'pending'" @click="declineMessage(message.id)" class="btn-decline">Noraidīt</button>
              <span
                v-else-if="message.status === 'approved' && message.source !== 'admin_role_grant'"
                class="message-approved"
              >Apstiprināts</span>
              <span v-else-if="message.status === 'declined'" class="message-declined">Noraidīts</span>
              <button
                v-if="message.source === 'admin_role_grant'"
                type="button"
                @click="cancelAdminRoleOffer(message)"
                class="btn-decline-admin-offer-admin"
              >
                Noraidīt admin piedāvājumu
              </button>
              <button @click="deleteMessage(message.id)" class="btn-delete">Dzēst</button>
            </div>
          </div>
        </div>
      </div>

      <!-- SUPPORT CHAT TAB -->
      <div v-if="activeTab === 'supportChat'" class="tab-content">
        <div class="tab-header-row">
          <h2>Support čats</h2>
          <button type="button" @click="loadSupportChatThreads" class="btn-refresh">Atsvaidzināt</button>
        </div>
        <div class="support-chat-layout">
          <div class="support-chat-threads">
            <div v-if="!supportThreads.length" class="no-data">Nav sarunu</div>
            <button
              v-for="t in supportThreads"
              :key="t.userId"
              type="button"
              :class="['support-thread-row', { active: supportChatUserId === t.userId }]"
              @click="selectSupportThread(t.userId)"
            >
              <span class="support-thread-row__top">
                <strong>{{ t.name }}</strong>
                <span v-if="t.unreadFromUser" class="support-thread-badge">{{ t.unreadFromUser }}</span>
              </span>
              <span class="support-thread-row__email">{{ t.email }}</span>
              <span class="support-thread-row__preview">{{ t.lastPreview }}</span>
            </button>
          </div>
          <div v-if="supportChatUserId" class="support-chat-pane">
            <header v-if="supportChatUser" class="support-chat-pane__head">
              <p>
                <strong>{{ supportChatUser.name }}</strong>
                <span class="support-chat-pane__email">{{ supportChatUser.email }}</span>
              </p>
            </header>
            <div ref="supportScroll" class="support-chat-msgs">
              <div
                v-for="m in supportMessages"
                :key="m.id"
                :class="['support-chat-bubble', m.isFromAdmin ? 'support-chat-bubble--admin' : 'support-chat-bubble--user']"
              >
                <p class="support-chat-bubble__text">{{ m.body }}</p>
                <time class="support-chat-bubble__time">{{ formatDate(m.createdAt) }}</time>
              </div>
            </div>
            <div class="support-chat-reply">
              <textarea
                v-model="supportReply"
                class="support-chat-reply__input"
                rows="3"
                placeholder="Atbilde lietotājam…"
                maxlength="5000"
              />
              <button type="button" class="btn-save support-chat-reply__btn" @click="sendSupportReply">
                Sūtīt atbildi
              </button>
            </div>
          </div>
          <div v-else class="no-data support-chat-placeholder">
            Izvēlieties sarunu no saraksta
          </div>
        </div>
      </div>

      <!-- ADOPTIONS TAB -->
      <div v-if="activeTab === 'adoptions'" class="tab-content">
        <h2>Adopcijas pieteikumi</h2>
        <div v-if="adoptions.length === 0" class="no-data">
          Nav pieteikumu
        </div>
        <div v-else class="adoptions-list">
          <div v-for="adoption in adoptions" :key="adoption.id" class="adoption-item">
            <div class="adoption-header">
              <strong>🐾 {{ adoption.animalName }}</strong>
              <span class="adoption-date">{{ formatDate(adoption.submittedAt) }}</span>
            </div>
            <p><strong>Vārds:</strong> {{ adoption.name }}</p>
            <p><strong>E-pasts:</strong> {{ adoption.email }}</p>
            <p><strong>Telefons:</strong> {{ adoption.phone }}</p>
            <p><strong>Dzīvojamais vieta:</strong> {{ adoption.address }}</p>
            <p><strong>Pieredze:</strong></p>
            <p class="experience-text">{{ adoption.experience }}</p>
            <button @click="deleteAdoption(adoption.id)" class="btn-delete">Dzēst</button>
          </div>
        </div>
      </div>
      <div v-if="activeTab === 'animals'" class="tab-content">
        <h2>Pievienot jaunu dzīvnieku</h2>
        <div class="animal-form">
          <div class="form-row">
            <label>Vārds</label>
            <input v-model="animalForm.name" type="text" placeholder="Dzīvnieka vārds" />
          </div>
          <div class="form-row">
            <label>Dzimums</label>
            <input v-model="animalForm.gender" type="text" placeholder="Dzimums" />
          </div>
          <div class="form-row">
            <label>Kategorija</label>
            <input v-model="animalForm.species" type="text" placeholder="Piemēram Suns, Kaķis" />
          </div>
          <div class="form-row">
            <label>Apraksts</label>
            <textarea v-model="animalForm.description" placeholder="Apraksts"></textarea>
          </div>
          <div class="form-row">
            <label>Attēla URL</label>
            <input v-model="animalForm.image" type="text" placeholder="https://..." />
          </div>
          <button @click="addAnimal" class="btn-save">Saglabāt dzīvnieku</button>
        </div>

        <h2>Esošie dzīvnieki</h2>
        <div v-if="storedAnimals.length === 0" class="no-data">
          Nav pievienotu dzīvnieku
        </div>
        <div v-else class="animals-list">
          <div v-for="animal in storedAnimals" :key="animal.id" class="animal-item">
            <div class="animal-item-info">
              <img
                v-if="animal.image"
                :src="animal.image"
                :alt="animal.name"
                class="animal-thumb"
                loading="lazy"
                decoding="async"
                referrerpolicy="no-referrer"
              />
              <div>
                <strong>{{ animal.name }}</strong> — {{ animal.species || 'Cits' }}, {{ animal.gender }}
                <p>{{ animal.description }}</p>
              </div>
            </div>
            <button type="button" @click="deleteAnimal(animal.id)" class="btn-delete">Dzēst</button>
          </div>
        </div>
      </div>

      <!-- USERS TAB -->
      <div v-if="activeTab === 'users'" class="tab-content">
        <div class="tab-header-row">
          <h2>Pierakstījušies lietotāji (pēdējā pierakstīšanās &gt; iziešana)</h2>
          <button type="button" @click="loadUsersAdmin" class="btn-refresh">Atsvaidzināt</button>
        </div>
        <p class="users-hint">
          Dati no servera. Admin poga galvenē redzama tikai kontiem ar admin tiesībām (sākumā Rudolfs; pārējie — pēc „Piešķirt admin lomu”).
        </p>
        <div v-if="activeSessions.length === 0" class="no-data">
          Neviens lietotājs šobrīd nav atzīmēts kā pierakstījies
        </div>
        <div v-else class="sessions-list">
          <div v-for="session in activeSessions" :key="session.email" class="session-item">
            <div class="session-header">
              <strong>{{ session.name || 'Nav vārda' }}</strong>
              <span class="session-date">{{ formatDate(session.loginAt) }}</span>
            </div>
            <p><strong>E-pasts:</strong> {{ session.email }}</p>
          </div>
        </div>

        <h2 class="users-section-title">Visi reģistrētie konti</h2>
        <div v-if="registeredUsers.length === 0" class="no-data">
          Nav lietotāju
        </div>
        <div v-else class="users-list">
          <div v-for="user in registeredUsers" :key="user.id" class="user-admin-item">
            <div class="user-admin-main">
              <strong>{{ user.name }}</strong>
              <span v-if="user.isOwner" class="badge-owner">Īpašnieks</span>
              <span v-if="user.blocked" class="badge-blocked">Bloķēts</span>
              <span v-if="user.isAdmin" class="badge-admin">Admin loma</span>
              <p><strong>E-pasts:</strong> {{ user.email }}</p>
              <div class="user-activity">
                <p v-if="sessionsByEmail[user.email]" class="user-activity-row">
                  <span class="badge-online">Šobrīd tiešsaistē</span>
                  <span class="user-activity-detail">
                    šajā pārlūkā kopš {{ formatDate(sessionsByEmail[user.email].loginAt) }}
                  </span>
                </p>
                <p v-else class="user-activity-row">
                  <span class="badge-offline">Nav tiešsaistē</span>
                  <span
                    v-if="user.lastLogoutAt"
                    class="user-activity-detail"
                  >
                    pēdējā iziešana {{ formatDate(user.lastLogoutAt) }}
                  </span>
                </p>
                <p class="user-activity-row muted">
                  <strong>Pēdējā pierakstīšanās:</strong>
                  {{ user.lastLoginAt ? formatDate(user.lastLoginAt) : '—' }}
                </p>
              </div>
            </div>
            <div v-if="!user.isOwner" class="user-admin-actions">
              <button
                v-if="!user.blocked"
                type="button"
                @click="blockUser(user)"
                class="btn-block-user"
              >
                Bloķēt piekļuvi
              </button>
              <button
                v-else
                type="button"
                @click="unblockUser(user)"
                class="btn-unblock-user"
              >
                Atbloķēt
              </button>
              <button
                v-if="!user.blocked"
                type="button"
                @click="grantAdminRole(user)"
                class="btn-grant-admin"
              >
                Piešķirt admin lomu
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import {
  adminCancelAdminRoleOffer,
  adminCreateAnimal,
  adminDeleteAnimal,
  adminDeleteApplication,
  adminDeleteContactMessage,
  adminFetchSupportChat,
  adminListApplications,
  adminListContactMessages,
  adminListSupportThreads,
  adminListUsers,
  adminPostSupportChat,
  adminUpdateContactMessage,
  adminUpdateUser,
  fetchAnimalsCatalog,
} from '@/api/restApi'
import { deriveActiveSessionsFromUsers, setUserBlocked } from '@/utils/authStorage'

export default {
  name: 'AdminView',
  data() {
    return {
      activeTab: 'messages',
      messages: [],
      adoptions: [],
      storedAnimals: [],
      activeSessions: [],
      registeredUsers: [],
      animalForm: {
        name: '',
        gender: '',
        species: '',
        description: '',
        image: '',
      },
      supportThreads: [],
      supportChatUserId: null,
      supportChatUser: null,
      supportMessages: [],
      supportReply: '',
    }
  },
  computed: {
    sessionsByEmail() {
      const map = {}
      for (const s of this.activeSessions) {
        map[s.email] = s
      }
      return map
    },
  },
  mounted() {
    void this.loadData()
    void this.loadAnimals()
    void this.loadUsersAdmin()
    window.addEventListener('animalsUpdated', this.loadAnimals)
  },
  beforeUnmount() {
    window.removeEventListener('animalsUpdated', this.loadAnimals)
  },
  methods: {
    async loadData() {
      try {
        this.messages = await adminListContactMessages()
        this.adoptions = await adminListApplications()
      } catch {
        this.messages = []
        this.adoptions = []
      }
    },
    openSupportChatTab() {
      this.activeTab = 'supportChat'
      void this.loadSupportChatThreads()
    },
    async loadSupportChatThreads() {
      try {
        this.supportThreads = await adminListSupportThreads()
      } catch {
        this.supportThreads = []
      }
    },
    async selectSupportThread(userId) {
      this.supportChatUserId = userId
      try {
        const d = await adminFetchSupportChat(userId)
        this.supportChatUser = d.user || null
        this.supportMessages = Array.isArray(d.messages) ? d.messages : []
        this.$nextTick(() => this.scrollSupportChat())
      } catch {
        this.supportChatUser = null
        this.supportMessages = []
      }
      await this.loadSupportChatThreads()
    },
    scrollSupportChat() {
      const el = this.$refs.supportScroll
      if (el) el.scrollTop = el.scrollHeight
    },
    async sendSupportReply() {
      const text = this.supportReply.trim()
      if (!text || !this.supportChatUserId) return
      try {
        await adminPostSupportChat(this.supportChatUserId, { body: text })
        this.supportReply = ''
        await this.selectSupportThread(this.supportChatUserId)
      } catch {
        /* ignore */
      }
    },
    async loadAnimals() {
      try {
        const { animals } = await fetchAnimalsCatalog()
        this.storedAnimals = animals
      } catch {
        this.storedAnimals = []
      }
    },
    async loadUsersAdmin() {
      try {
        const users = await adminListUsers()
        this.registeredUsers = users
        this.activeSessions = deriveActiveSessionsFromUsers(users)
      } catch {
        this.registeredUsers = []
        this.activeSessions = []
      }
    },
    async blockUser(user) {
      await setUserBlocked(user.id, true)
      await this.loadUsersAdmin()
    },
    async unblockUser(user) {
      await setUserBlocked(user.id, false)
      await this.loadUsersAdmin()
    },
    async grantAdminRole(user) {
      await adminUpdateUser(user.id, { is_admin: true })
      await this.loadUsersAdmin()
      window.dispatchEvent(new Event('contactMessagesUpdated'))
    },
    async addAnimal() {
      if (!this.animalForm.name || !this.animalForm.image) {
        return
      }
      try {
        await adminCreateAnimal({
          name: this.animalForm.name,
          species: this.animalForm.species || 'Cits',
          gender: this.animalForm.gender || 'Nav norādīts',
          description: this.animalForm.description || 'Nav apraksta',
          image: this.animalForm.image,
        })
        await this.loadAnimals()
        window.dispatchEvent(new Event('animalsUpdated'))
        this.animalForm = {
          name: '',
          gender: '',
          species: '',
          description: '',
          image: '',
        }
      } catch {
        /* ignore */
      }
    },
    async deleteAnimal(id) {
      try {
        await adminDeleteAnimal(id)
        await this.loadAnimals()
        window.dispatchEvent(new Event('animalsUpdated'))
      } catch {
        /* ignore */
      }
    },
    formatDate(dateString) {
      if (!dateString) return '—'
      return new Date(dateString).toLocaleString('lv-LV')
    },
    async deleteMessage(id) {
      try {
        await adminDeleteContactMessage(id)
        await this.loadData()
        window.dispatchEvent(new Event('contactMessagesUpdated'))
      } catch {
        /* ignore */
      }
    },
    async cancelAdminRoleOffer(message) {
      try {
        await adminCancelAdminRoleOffer(message.id)
        await this.loadData()
        await this.loadUsersAdmin()
      } catch {
        /* ignore */
      }
    },
    async approveMessage(id) {
      try {
        await adminUpdateContactMessage(id, 'approved')
        await this.loadData()
        window.dispatchEvent(new Event('contactMessagesUpdated'))
      } catch {
        /* ignore */
      }
    },
    async declineMessage(id) {
      try {
        await adminUpdateContactMessage(id, 'declined')
        await this.loadData()
        window.dispatchEvent(new Event('contactMessagesUpdated'))
      } catch {
        /* ignore */
      }
    },
    async deleteAdoption(id) {
      try {
        await adminDeleteApplication(id)
        await this.loadData()
      } catch {
        /* ignore */
      }
    },
    logout() {
      this.$router.push('/')
    },
  },
}
</script>

<style scoped>
.admin-container {
  flex: 1;
  background:
    radial-gradient(ellipse 70% 45% at 50% 0%, rgba(120, 45, 30, 0.35), transparent 55%),
    linear-gradient(168deg, #0c0d12 0%, #0a0a0c 100%);
  padding: clamp(1rem, 2vw, 1.5rem) 20px 2.5rem;
  color: var(--hp-text, #f4f4f5);
}

.admin-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
  padding-bottom: 1.25rem;
  border-bottom: 1px solid var(--hp-line, rgba(255, 255, 255, 0.1));
  box-shadow: 0 1px 0 0 rgba(245, 204, 76, 0.15);
}

.admin-header h1 {
  font-family: 'Playfair Display', Georgia, serif;
  color: var(--hp-gold, #f5cc4c);
  font-size: clamp(1.5rem, 3vw, 2rem);
  font-weight: 600;
}

.btn-logout {
  padding: 0.5rem 1.15rem;
  background: rgba(239, 68, 68, 0.12);
  color: #fecaca;
  border: 1.5px solid rgba(248, 113, 113, 0.4);
  border-radius: 999px;
  cursor: pointer;
  font-weight: 700;
  font-size: 0.85rem;
  transition:
    background 0.2s ease,
    border-color 0.2s ease;
}

.btn-logout:hover {
  background: rgba(239, 68, 68, 0.2);
  border-color: #f87171;
}

.admin-content {
  max-width: 1200px;
  margin: 0 auto;
}

.admin-tabs {
  display: flex;
  gap: 10px;
  margin-bottom: 30px;
}

.tab-btn {
  padding: 0.55rem 1.15rem;
  background: transparent;
  color: var(--hp-text, #f4f4f5);
  border: 1.5px solid var(--hp-line-strong, rgba(255, 255, 255, 0.18));
  border-radius: 999px;
  cursor: pointer;
  font-weight: 700;
  font-size: 0.88rem;
  transition:
    background 0.2s ease,
    border-color 0.2s ease,
    color 0.2s ease;
}

.tab-btn.active {
  background: linear-gradient(180deg, var(--hp-orange, #ff5722) 0%, var(--hp-orange-deep, #e64a19) 100%);
  border-color: transparent;
  color: #fff;
  box-shadow: 0 4px 16px rgba(255, 87, 34, 0.3);
}

.btn-refresh {
  padding: 10px 18px;
  background: rgba(255, 255, 255, 0.12);
  color: white;
  border: 1px solid rgba(255, 255, 255, 0.25);
  border-radius: 16px;
  cursor: pointer;
  font-weight: 700;
  transition: background 0.2s ease;
}

.btn-refresh:hover {
  background: rgba(255, 255, 255, 0.2);
}

.tab-header-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
  margin-bottom: 20px;
}

.tab-btn:hover:not(.active) {
  border-color: rgba(245, 204, 76, 0.35);
  color: var(--hp-gold, #f5cc4c);
}

.btn-save {
  width: 100%;
  padding: 14px 18px;
  background: linear-gradient(180deg, var(--hp-orange, #ff5722) 0%, var(--hp-orange-deep, #e64a19) 100%);
  border: none;
  border-radius: 999px;
  margin: 7px;
  color: #fff;
  font-size: 1rem;
  font-weight: 700;
  cursor: pointer;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  box-shadow: 0 8px 24px rgba(255, 87, 34, 0.32);
}

.btn-save:hover {
  transform: translateY(-2px);
  box-shadow: 0 12px 32px rgba(255, 87, 34, 0.4);
}

.admin-content input,
.admin-content textarea {
  width: 100%;
  padding: 12px 16px;
  border: 1px solid rgba(255,255,255,0.35);
  border-radius: 12px;
  background: rgba(255,255,255,0.08);
  color: white;
  font-size: 1rem;
}

.admin-content input::placeholder,
.admin-content textarea::placeholder {
  color: rgba(255,255,255,0.6);
}

.admin-content textarea {
  min-height: 120px;
  resize: vertical;
}

.tab-content h2 {
  font-family: 'Playfair Display', Georgia, serif;
  color: var(--hp-gold, #f5cc4c);
  margin-bottom: 20px;
  font-size: 1.35rem;
  font-weight: 600;
}

.no-data {
  text-align: center;
  padding: 2.5rem 1.5rem;
  background: var(--hp-surface, rgba(255, 255, 255, 0.055));
  border: 1px solid var(--hp-line, rgba(255, 255, 255, 0.1));
  border-radius: 16px;
  color: var(--hp-muted, rgba(255, 255, 255, 0.7));
}

.messages-list,
.adoptions-list,
.animals-list {
  display: grid;
  gap: 20px;
}

.message-item,
.adoption-item {
  background: var(--hp-surface, rgba(255, 255, 255, 0.055));
  border: 1px solid var(--hp-line, rgba(255, 255, 255, 0.1));
  padding: 20px;
  border-radius: 18px;
  box-shadow: 0 12px 40px rgba(0, 0, 0, 0.25);
}

.animal-item {
  background: var(--hp-surface, rgba(255, 255, 255, 0.055));
  border: 1px solid var(--hp-line, rgba(255, 255, 255, 0.1));
  padding: 20px;
  border-radius: 18px;
  box-shadow: 0 12px 40px rgba(0, 0, 0, 0.25);
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  align-items: flex-start;
  gap: 12px;
}

.animal-item-info {
  display: flex;
  gap: 14px;
  align-items: flex-start;
  flex: 1;
  min-width: 0;
}

.animal-thumb {
  width: 72px;
  height: 72px;
  object-fit: cover;
  border-radius: 12px;
  flex-shrink: 0;
  border: 2px solid rgba(255, 255, 255, 0.35);
}

.animal-item p {
  color: white;
  margin: 8px 0 0;
}

.message-header,
.adoption-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
  padding-bottom: 10px;
  border-bottom: 1px solid var(--hp-line, rgba(255, 255, 255, 0.1));
  color: var(--hp-text, #f4f4f5);
}

.message-date,
.adoption-date {
  font-size: 0.8rem;
  color: rgba(255, 255, 255, 0.7);
}

.message-item p,
.adoption-item p {
  color: white;
  margin: 8px 0;
}

.message-text,
.experience-text {
  background: rgba(0, 0, 0, 0.25);
  border: 1px solid var(--hp-line, rgba(255, 255, 255, 0.08));
  padding: 10px;
  border-radius: 10px;
  line-height: 1.6;
  margin-top: 10px;
}

.btn-delete {
  padding: 8px 16px;
  background: rgba(0, 0, 0, 0.3);
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  margin-top: 10px;
  transition: background 0.3s ease;
}

.btn-delete:hover {
  background: rgba(0, 0, 0, 0.5);
}

.btn-approve {
  padding: 8px 16px;
  background: rgba(34, 197, 94, 0.18);
  color: #86efac;
  border: 1px solid rgba(74, 222, 128, 0.35);
  border-radius: 999px;
  cursor: pointer;
  margin-top: 10px;
  margin-right: 10px;
  font-weight: 700;
}

.btn-approve:hover {
  background: rgba(34, 197, 94, 0.28);
}

.btn-decline {
  padding: 8px 16px;
  background: rgba(255, 0, 0, 0.18);
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  margin-top: 10px;
  margin-right: 10px;
  font-weight: bold;
}

.btn-decline:hover {
  background: rgba(255, 0, 0, 0.3);
}

.btn-decline-admin-offer-admin {
  padding: 8px 16px;
  background: rgba(139, 0, 0, 0.45);
  color: white;
  border: 2px solid rgba(255, 200, 200, 0.35);
  border-radius: 8px;
  cursor: pointer;
  font-weight: bold;
}

.btn-decline-admin-offer-admin:hover {
  background: rgba(160, 20, 20, 0.55);
}

.message-declined {
  display: inline-flex;
  align-items: center;
  padding: 8px 14px;
  background: rgba(255, 0, 0, 0.2);
  border-radius: 8px;
  color: white;
  font-weight: bold;
}

.message-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  align-items: center;
  margin-top: 12px;
}

.message-approved {
  display: inline-flex;
  align-items: center;
  padding: 8px 14px;
  background: rgba(0, 0, 0, 0.2);
  border-radius: 8px;
  color: white;
  font-weight: bold;
}

.users-hint {
  color: rgba(255, 255, 255, 0.65);
  font-size: 0.9rem;
  margin-bottom: 16px;
  line-height: 1.45;
}

.users-section-title {
  margin-top: 36px;
}

.sessions-list,
.users-list {
  display: grid;
  gap: 16px;
}

.session-item,
.user-admin-item {
  background: var(--hp-surface, rgba(255, 255, 255, 0.055));
  border: 1px solid var(--hp-line, rgba(255, 255, 255, 0.1));
  padding: 18px 20px;
  border-radius: 18px;
  box-shadow: 0 12px 40px rgba(0, 0, 0, 0.25);
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  align-items: flex-start;
  gap: 12px;
}

.user-admin-main {
  flex: 1;
  min-width: 0;
}

.user-activity {
  margin-top: 10px;
  padding-top: 10px;
  border-top: 1px solid var(--hp-line, rgba(255, 255, 255, 0.1));
}

.user-activity-row {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 8px;
  margin: 6px 0 0;
  font-size: 0.9rem;
}

.user-activity-row.muted {
  margin-top: 8px;
}

.user-activity-detail {
  color: rgba(255, 255, 255, 0.9);
}

.badge-online {
  display: inline-block;
  padding: 3px 10px;
  border-radius: 999px;
  background: rgba(0, 140, 70, 0.5);
  color: white;
  font-size: 0.75rem;
  font-weight: 800;
}

.badge-offline {
  display: inline-block;
  padding: 3px 10px;
  border-radius: 999px;
  background: rgba(0, 0, 0, 0.22);
  color: rgba(255, 255, 255, 0.95);
  font-size: 0.75rem;
  font-weight: 700;
}

.session-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 100%;
  margin-bottom: 8px;
  padding-bottom: 8px;
  border-bottom: 1px solid var(--hp-line, rgba(255, 255, 255, 0.1));
  color: var(--hp-text, #f4f4f5);
}

.session-date {
  font-size: 0.85rem;
  color: rgba(255, 255, 255, 0.75);
}

.session-item p,
.user-admin-item p {
  color: white;
  margin: 4px 0 0;
  width: 100%;
}

.badge-owner {
  display: inline-block;
  margin-left: 10px;
  padding: 4px 10px;
  background: rgba(180, 120, 0, 0.55);
  border-radius: 8px;
  font-size: 0.8rem;
  color: white;
  font-weight: bold;
  vertical-align: middle;
}

.badge-blocked {
  display: inline-block;
  margin-left: 10px;
  padding: 4px 10px;
  background: rgba(180, 0, 0, 0.35);
  border-radius: 8px;
  font-size: 0.8rem;
  color: white;
  font-weight: bold;
  vertical-align: middle;
}

.badge-admin {
  display: inline-block;
  margin-left: 10px;
  padding: 4px 10px;
  background: rgba(0, 100, 180, 0.45);
  border-radius: 8px;
  font-size: 0.8rem;
  color: white;
  font-weight: bold;
  vertical-align: middle;
}

.user-admin-actions {
  flex-shrink: 0;
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  justify-content: flex-end;
  max-width: 100%;
}

.btn-block-user {
  padding: 10px 16px;
  background: rgba(180, 0, 0, 0.35);
  color: white;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  font-weight: bold;
}

.btn-block-user:hover {
  background: rgba(180, 0, 0, 0.5);
}

.btn-unblock-user {
  padding: 10px 16px;
  background: rgba(34, 197, 94, 0.15);
  color: #86efac;
  border: 1px solid rgba(74, 222, 128, 0.3);
  border-radius: 999px;
  cursor: pointer;
  font-weight: 700;
}

.btn-unblock-user:hover {
  background: rgba(34, 197, 94, 0.25);
}

.btn-grant-admin {
  padding: 10px 16px;
  background: transparent;
  color: var(--hp-gold, #f5cc4c);
  border: 1.5px solid rgba(245, 204, 76, 0.45);
  border-radius: 999px;
  cursor: pointer;
  font-weight: 700;
}

.btn-grant-admin:hover {
  background: rgba(245, 204, 76, 0.1);
  border-color: var(--hp-gold, #f5cc4c);
}

.support-chat-layout {
  display: grid;
  grid-template-columns: minmax(0, 280px) minmax(0, 1fr);
  gap: 1.25rem;
  align-items: stretch;
  min-height: 420px;
}

.support-chat-threads {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  max-height: 520px;
  overflow-y: auto;
}

.support-thread-row {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 0.25rem;
  width: 100%;
  text-align: left;
  padding: 0.75rem 0.85rem;
  border-radius: 14px;
  border: 1px solid var(--hp-line, rgba(255, 255, 255, 0.1));
  background: var(--hp-surface, rgba(255, 255, 255, 0.055));
  color: var(--hp-text, #f4f4f5);
  cursor: pointer;
  font: inherit;
  transition:
    border-color 0.2s ease,
    background 0.2s ease;
}

.support-thread-row:hover {
  border-color: rgba(245, 204, 76, 0.35);
}

.support-thread-row.active {
  border-color: rgba(255, 87, 34, 0.55);
  background: rgba(255, 87, 34, 0.12);
}

.support-thread-row__top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.5rem;
  width: 100%;
}

.support-thread-row__email {
  font-size: 0.78rem;
  color: var(--hp-muted, rgba(255, 255, 255, 0.7));
  word-break: break-all;
}

.support-thread-row__preview {
  font-size: 0.78rem;
  color: rgba(255, 255, 255, 0.55);
  display: -webkit-box;
  line-clamp: 2;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.support-thread-badge {
  flex-shrink: 0;
  min-width: 1.25rem;
  padding: 2px 7px;
  border-radius: 999px;
  background: #dc2626;
  color: #fff;
  font-size: 0.68rem;
  font-weight: 800;
}

.support-chat-placeholder {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 200px;
}

.support-chat-pane {
  display: flex;
  flex-direction: column;
  min-height: 0;
  border: 1px solid var(--hp-line, rgba(255, 255, 255, 0.1));
  border-radius: 16px;
  background: var(--hp-surface, rgba(255, 255, 255, 0.055));
  overflow: hidden;
}

.support-chat-pane__head {
  padding: 0.75rem 1rem;
  border-bottom: 1px solid var(--hp-line, rgba(255, 255, 255, 0.1));
  background: rgba(0, 0, 0, 0.2);
}

.support-chat-pane__head p {
  margin: 0;
  color: var(--hp-text, #f4f4f5);
}

.support-chat-pane__email {
  display: block;
  margin-top: 0.25rem;
  font-size: 0.82rem;
  font-weight: 400;
  color: var(--hp-muted, rgba(255, 255, 255, 0.7));
}

.support-chat-msgs {
  flex: 1;
  min-height: 220px;
  max-height: 340px;
  overflow-y: auto;
  padding: 0.75rem 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.6rem;
}

.support-chat-bubble {
  max-width: 92%;
  padding: 0.55rem 0.75rem;
  border-radius: 12px;
  font-size: 0.88rem;
  line-height: 1.45;
}

.support-chat-bubble--user {
  align-self: flex-start;
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid var(--hp-line, rgba(255, 255, 255, 0.12));
}

.support-chat-bubble--admin {
  align-self: flex-end;
  background: rgba(255, 87, 34, 0.2);
  border: 1px solid rgba(255, 87, 34, 0.35);
}

.support-chat-bubble__text {
  margin: 0;
  white-space: pre-wrap;
  word-break: break-word;
}

.support-chat-bubble__time {
  display: block;
  margin-top: 0.35rem;
  font-size: 0.68rem;
  color: rgba(255, 255, 255, 0.5);
}

.support-chat-reply {
  padding: 0.75rem 1rem 1rem;
  border-top: 1px solid var(--hp-line, rgba(255, 255, 255, 0.1));
  background: rgba(0, 0, 0, 0.18);
}

.support-chat-reply__input {
  width: 100%;
  box-sizing: border-box;
  margin-bottom: 0.65rem;
}

.support-chat-reply__btn {
  width: auto;
  margin: 0;
}

@media (max-width: 900px) {
  .support-chat-layout {
    grid-template-columns: 1fr;
  }

  .support-chat-threads {
    max-height: 220px;
  }
}

@media (max-width: 768px) {
  .admin-header {
    flex-direction: column;
    gap: 15px;
    text-align: center;
  }

  .admin-tabs {
    flex-direction: column;
  }

  .tab-btn {
    width: 100%;
  }
}
</style>
