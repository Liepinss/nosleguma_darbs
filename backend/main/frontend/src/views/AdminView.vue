<template>
  <div class="admin-container">
    <div class="admin-header">
      <h1>{{ t('admin.title') }}</h1>
      <button @click="logout" class="btn-logout">{{ t('admin.logout') }}</button>
    </div>

    <div class="admin-content">
      <div class="admin-tabs">
        <button
          :class="{ active: activeTab === 'messages' }"
          @click="activeTab = 'messages'"
          class="tab-btn"
        >
          {{ t('admin.tab.messages') }}
        </button>
        <button
          :class="{ active: activeTab === 'supportChat' }"
          @click="openSupportChatTab"
          class="tab-btn"
        >
          {{ t('admin.tab.support') }}
        </button>
        <button
          :class="{ active: activeTab === 'adoptions' }"
          @click="activeTab = 'adoptions'"
          class="tab-btn"
        >
          {{ t('admin.tab.adoptions') }}
        </button>
        <button
          :class="{ active: activeTab === 'animals' }"
          @click="activeTab = 'animals'"
          class="tab-btn"
        >
          {{ t('admin.tab.animals') }}
        </button>
        <button
          :class="{ active: activeTab === 'users' }"
          @click="activeTab = 'users'"
          class="tab-btn"
        >
          {{ t('admin.tab.users') }}
        </button>
        <button
          :class="{ active: activeTab === 'logs' }"
          type="button"
          @click="openLogsTab"
          class="tab-btn"
        >
          {{ t('admin.tab.logs') }}
        </button>
      </div>

      <!-- MESSAGES TAB -->
      <div v-if="activeTab === 'messages'" class="tab-content">
        <div class="tab-header-row">
          <h2>{{ t('admin.msg.title') }}</h2>
          <button @click="loadData" class="btn-refresh">{{ t('admin.refresh') }}</button>
        </div>
        <div v-if="messages.length === 0" class="no-data">
          {{ t('admin.msg.none') }}
        </div>
        <div v-else class="messages-list">
          <div v-for="message in messages" :key="message.id" class="message-item">
            <div class="message-header">
              <strong>{{ message.name }}</strong>
              <span class="message-date">{{ formatDate(message.sentAt) }}</span>
            </div>
            <p><strong>{{ t('admin.label.email') }}</strong> {{ message.email }}</p>
            <p v-if="message.selectedAnimals"><strong>{{ t('admin.label.animals') }}</strong> {{ message.selectedAnimals }}</p>
            <p><strong>{{ t('admin.label.message') }}</strong></p>
            <p class="message-text">{{ message.message }}</p>
            <p><strong>{{ t('admin.label.status') }}</strong> {{ messageStatusLabel(message.status) }}</p>
            <div class="message-actions">
              <button v-if="message.status === 'pending'" @click="approveMessage(message.id)" class="btn-approve">{{ t('admin.btn.approve') }}</button>
              <button v-if="message.status === 'pending'" @click="declineMessage(message.id)" class="btn-decline">{{ t('admin.btn.decline') }}</button>
              <span
                v-else-if="message.status === 'approved' && message.source !== 'admin_role_grant'"
                class="message-approved"
              >{{ t('admin.status.approved') }}</span>
              <span v-else-if="message.status === 'declined'" class="message-declined">{{ t('admin.status.declined') }}</span>
              <button
                v-if="message.source === 'admin_role_grant'"
                type="button"
                @click="cancelAdminRoleOffer(message)"
                class="btn-decline-admin-offer-admin"
              >
                {{ t('admin.btn.declineOffer') }}
              </button>
              <button @click="deleteMessage(message.id)" class="btn-delete">{{ t('admin.btn.delete') }}</button>
            </div>
          </div>
        </div>
      </div>

      <!-- SUPPORT CHAT TAB -->
      <div v-if="activeTab === 'supportChat'" class="tab-content">
        <div class="tab-header-row">
          <h2>{{ t('admin.support.title') }}</h2>
          <button type="button" @click="loadSupportChatThreads" class="btn-refresh">{{ t('admin.refresh') }}</button>
        </div>
        <div class="support-chat-layout">
          <div class="support-chat-threads">
            <div v-if="!supportThreads.length" class="no-data">{{ t('admin.support.none') }}</div>
            <button
              v-for="thread in supportThreads"
              :key="thread.userId"
              type="button"
              :class="['support-thread-row', { active: supportChatUserId === thread.userId }]"
              @click="selectSupportThread(thread.userId)"
            >
              <span class="support-thread-row__top">
                <strong>{{ thread.name }}</strong>
                <span v-if="thread.unreadFromUser" class="support-thread-badge">{{ thread.unreadFromUser }}</span>
              </span>
              <span class="support-thread-row__email">{{ thread.email }}</span>
              <span class="support-thread-row__preview">{{ thread.lastPreview }}</span>
            </button>
          </div>
          <div v-if="supportChatUserId" class="support-chat-pane">
            <header v-if="supportChatUser" class="support-chat-pane__head">
              <div class="support-chat-pane__head-main">
                <p class="support-chat-pane__identity">
                  <strong>{{ supportChatUser.name }}</strong>
                  <span class="support-chat-pane__email">{{ supportChatUser.email }}</span>
                </p>
                <div
                  v-if="supportPendingApplications.length"
                  class="support-chat-pane__approvals"
                  role="region"
                  :aria-label="t('admin.support.pendingRegion')"
                >
                  <p class="support-chat-pane__approvals-title">{{ t('admin.support.pendingTitle') }}</p>
                  <ul class="support-chat-pane__approvals-list">
                    <li v-for="row in supportPendingApplications" :key="row.id" class="support-chat-pane__approval-row">
                      <span class="support-chat-pane__approval-animal">{{ row.animalName }}</span>
                      <button
                        type="button"
                        class="btn-approve-adoption"
                        :disabled="supportApproveBusyId === row.id"
                        @click="approveAdoptionFromChat(row.id)"
                      >
                        {{ supportApproveBusyId === row.id ? t('admin.support.approving') : t('admin.support.approve') }}
                      </button>
                    </li>
                  </ul>
                </div>
              </div>
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
                :placeholder="t('admin.support.placeholder')"
                maxlength="5000"
              />
              <button type="button" class="btn-save support-chat-reply__btn" @click="sendSupportReply">
                {{ t('admin.support.send') }}
              </button>
            </div>
          </div>
          <div v-else class="no-data support-chat-placeholder">
            {{ t('admin.support.pick') }}
          </div>
        </div>
      </div>

      <!-- ADOPTIONS TAB -->
      <div v-if="activeTab === 'adoptions'" class="tab-content">
        <h2>{{ t('admin.adopt.title') }}</h2>
        <div v-if="adoptions.length === 0" class="no-data">
          {{ t('admin.adopt.none') }}
        </div>
        <div v-else class="adoptions-list">
          <div v-for="adoption in adoptions" :key="adoption.id" class="adoption-item">
            <div class="adoption-header">
              <strong>🐾 {{ adoption.animalName }}</strong>
              <span class="adoption-date">{{ formatDate(adoption.submittedAt) }}</span>
            </div>
            <p><strong>{{ t('admin.adopt.name') }}</strong> {{ adoption.name }}</p>
            <p><strong>{{ t('admin.adopt.status') }}</strong> {{ adoption.status }}</p>
            <p><strong>{{ t('admin.adopt.email') }}</strong> {{ adoption.email }}</p>
            <p><strong>{{ t('admin.adopt.phone') }}</strong> {{ adoption.phone }}</p>
            <p><strong>{{ t('admin.adopt.address') }}</strong> {{ adoption.address }}</p>
            <p><strong>{{ t('admin.adopt.exp') }}</strong></p>
            <p class="experience-text">{{ adoption.experience }}</p>
            <button @click="deleteAdoption(adoption.id)" class="btn-delete">{{ t('admin.btn.delete') }}</button>
          </div>
        </div>
      </div>
      <div v-if="activeTab === 'animals'" class="tab-content">
        <h2>{{ animalEditingId != null ? t('admin.an.editTitle') : t('admin.an.addTitle') }}</h2>
        <p v-if="animalEditingId != null" class="animal-edit-banner" role="status">
          {{ t('admin.an.editingBanner').replace('{name}', animalForm.name || '—') }}
        </p>
        <div class="animal-form">
          <div class="form-row">
            <label for="admin-animal-name">{{ t('admin.an.name') }}</label>
            <input
              id="admin-animal-name"
              v-model="animalForm.name"
              type="text"
              :placeholder="t('admin.an.phName')"
              autocomplete="off"
            />
          </div>
          <div class="form-row">
            <label for="admin-animal-gender">{{ t('admin.an.gender') }}</label>
            <input
              id="admin-animal-gender"
              v-model="animalForm.gender"
              type="text"
              :placeholder="t('admin.an.phGender')"
              autocomplete="off"
            />
          </div>
          <div class="form-row">
            <label for="admin-animal-species">{{ t('admin.an.category') }}</label>
            <input
              id="admin-animal-species"
              v-model="animalForm.species"
              type="text"
              :placeholder="t('admin.an.phCat')"
              autocomplete="off"
            />
          </div>
          <div class="form-row">
            <label for="admin-animal-desc">{{ t('admin.an.desc') }}</label>
            <textarea
              id="admin-animal-desc"
              v-model="animalForm.description"
              :placeholder="t('admin.an.phDesc')"
            ></textarea>
          </div>
          <div class="form-row">
            <label for="admin-animal-image">{{ t('admin.an.image') }}</label>
            <input
              id="admin-animal-image"
              v-model="animalForm.image"
              type="text"
              :placeholder="t('admin.an.phUrl')"
              autocomplete="off"
            />
          </div>
          <div class="animal-form-actions">
            <button type="button" class="btn-save" @click="submitAnimal">
              {{ animalEditingId != null ? t('admin.an.update') : t('admin.an.save') }}
            </button>
            <button
              v-if="animalEditingId != null"
              type="button"
              class="btn-cancel-edit"
              @click="cancelAnimalEdit"
            >
              {{ t('admin.an.cancelEdit') }}
            </button>
          </div>
        </div>

        <h2>{{ t('admin.an.listTitle') }}</h2>
        <div v-if="storedAnimals.length === 0" class="no-data">
          {{ t('admin.an.none') }}
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
                <strong>{{ animal.name }}</strong> — {{ speciesDisplay(animal.species || 'Cits') }}, {{ animal.gender }}
                <p>{{ animal.description }}</p>
              </div>
            </div>
            <div class="animal-item-actions">
              <button type="button" class="btn-edit-animal" @click="startEditAnimal(animal)">
                {{ t('admin.an.edit') }}
              </button>
              <button type="button" @click="deleteAnimal(animal.id)" class="btn-delete">{{ t('admin.btn.delete') }}</button>
            </div>
          </div>
        </div>
      </div>

      <!-- USERS TAB -->
      <div v-if="activeTab === 'users'" class="tab-content">
        <div class="users-accounts-toolbar">
          <h2 class="users-section-title">{{ t('admin.users.all') }}</h2>
          <div class="users-toolbar-actions">
            <label class="users-filter-label" for="admin-users-filter">
              <span class="users-filter-label-text">{{ t('admin.users.filterLabel') }}</span>
              <select
                id="admin-users-filter"
                v-model="usersAccountFilter"
                class="users-filter-select"
              >
                <option value="all">{{ t('admin.users.filterAll') }}</option>
                <option value="active">{{ t('admin.users.filterActive') }}</option>
                <option value="blocked">{{ t('admin.users.filterBlocked') }}</option>
              </select>
            </label>
            <button type="button" @click="loadUsersAdmin" class="btn-refresh">{{ t('admin.refresh') }}</button>
          </div>
        </div>
        <div v-if="registeredUsers.length === 0" class="no-data">
          {{ t('admin.users.none') }}
        </div>
        <div v-else-if="filteredRegisteredUsers.length === 0" class="no-data">
          {{ t('admin.users.filterEmpty') }}
        </div>
        <div v-else class="users-list">
          <div v-for="user in filteredRegisteredUsers" :key="user.id" class="user-admin-item">
            <div class="user-admin-main">
              <strong>{{ user.name }}</strong>
              <span v-if="user.isOwner" class="badge-owner">{{ t('admin.badge.owner') }}</span>
              <span v-if="user.blocked" class="badge-blocked">{{ t('admin.badge.blocked') }}</span>
              <span v-if="user.isAdmin" class="badge-admin">{{ t('admin.badge.admin') }}</span>
              <p><strong>{{ t('admin.label.email') }}</strong> {{ user.email }}</p>
              <div class="user-activity">
                <p v-if="sessionsByEmail[user.email]" class="user-activity-row">
                  <span class="badge-online">{{ t('admin.badge.online') }}</span>
                  <span class="user-activity-detail">
                    {{ t('admin.users.since') }} {{ formatDate(sessionsByEmail[user.email].loginAt) }}
                  </span>
                </p>
                <p v-else class="user-activity-row">
                  <span class="badge-offline">{{ t('admin.badge.offline') }}</span>
                  <span
                    v-if="user.lastLogoutAt"
                    class="user-activity-detail"
                  >
                    {{ t('admin.users.lastOut') }} {{ formatDate(user.lastLogoutAt) }}
                  </span>
                </p>
                <p class="user-activity-row muted">
                  <strong>{{ t('admin.users.lastIn') }}</strong>
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
                {{ t('admin.users.block') }}
              </button>
              <button
                v-else
                type="button"
                @click="unblockUser(user)"
                class="btn-unblock-user"
              >
                {{ t('admin.users.unblock') }}
              </button>
              <button
                v-if="!user.blocked"
                type="button"
                @click="grantAdminRole(user)"
                class="btn-grant-admin"
              >
                {{ t('admin.users.grantAdmin') }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- ACTIVITY LOG TAB -->
      <div v-if="activeTab === 'logs'" class="tab-content">
        <div class="tab-header-row">
          <h2>{{ t('admin.logs.title') }}</h2>
          <button type="button" @click="loadActivityLogs" class="btn-refresh">{{ t('admin.refresh') }}</button>
        </div>
        <div v-if="activityLogs.length === 0" class="no-data">
          {{ t('admin.logs.none') }}
        </div>
        <div v-else class="activity-log-scroll">
          <table class="activity-log-table">
            <thead>
              <tr>
                <th>{{ t('admin.logs.colTime') }}</th>
                <th>{{ t('admin.logs.colUser') }}</th>
                <th>{{ t('admin.logs.colAction') }}</th>
                <th>{{ t('admin.logs.colDetails') }}</th>
                <th>{{ t('admin.logs.colIp') }}</th>
                <th>{{ t('admin.logs.colDevice') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="row in activityLogs" :key="row.id">
                <td>{{ formatDate(row.at) }}</td>
                <td>
                  <template v-if="row.user">
                    <strong>{{ row.user.name }}</strong>
                    <span class="activity-log-email">{{ row.user.email }}</span>
                  </template>
                  <span v-else class="muted">{{ t('admin.logs.guest') }}</span>
                </td>
                <td>{{ logActionLabel(row.action) }}</td>
                <td class="activity-details-cell">
                  <p v-if="activityLogDetailLine(row)" class="activity-detail-text">{{ activityLogDetailLine(row) }}</p>
                  <pre v-if="activityLogSecondaryMeta(row)" class="activity-meta activity-meta--extra">{{
                    activityLogSecondaryMeta(row)
                  }}</pre>
                  <pre v-else-if="!activityLogDetailLine(row)" class="activity-meta">{{ formatLogMeta(row.meta) }}</pre>
                </td>
                <td class="muted activity-log-ip">{{ row.ip || '—' }}</td>
                <td class="muted activity-log-ua" :title="row.userAgent || ''">{{ shortUa(row.userAgent) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import {
  adminCancelAdminRoleOffer,
  adminCreateAnimal,
  adminUpdateAnimal,
  adminDeleteAnimal,
  adminDeleteApplication,
  adminDeleteContactMessage,
  adminFetchSupportChat,
  adminListActivityLogs,
  adminListApplications,
  adminListContactMessages,
  adminApproveApplication,
  adminListSupportThreads,
  adminListUsers,
  adminPostSupportChat,
  adminUpdateContactMessage,
  adminUpdateUser,
  fetchAnimalsCatalog,
} from '@/api/restApi'
import { translate } from '@/i18n/siteMessages'
import { useLocaleStore } from '@/stores/locale'
import { deriveActiveSessionsFromUsers, setUserBlocked } from '@/utils/authStorage'
import { mapState } from 'pinia'

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
      animalEditingId: null,
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
      supportPendingApplications: [],
      supportApproveBusyId: null,
      supportMessages: [],
      supportReply: '',
      activityLogs: [],
      usersAccountFilter: 'all',
    }
  },
  computed: {
    ...mapState(useLocaleStore, ['lang']),
    t() {
      return (key) => translate(this.lang, key)
    },
    sessionsByEmail() {
      const map = {}
      for (const s of this.activeSessions) {
        map[s.email] = s
      }
      return map
    },
    filteredRegisteredUsers() {
      const list = this.registeredUsers
      if (this.usersAccountFilter === 'blocked') {
        return list.filter((u) => u.blocked)
      }
      if (this.usersAccountFilter === 'active') {
        return list.filter((u) => !u.blocked)
      }
      return list
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
    openLogsTab() {
      this.activeTab = 'logs'
      void this.loadActivityLogs()
    },
    async loadActivityLogs() {
      try {
        this.activityLogs = await adminListActivityLogs()
      } catch {
        this.activityLogs = []
      }
    },
    logActionLabel(action) {
      const key = `log.${action}`
      const s = translate(this.lang, key)
      return s === key ? action : s
    },
    formatLogMeta(meta) {
      if (meta == null || (typeof meta === 'object' && Object.keys(meta).length === 0)) return '—'
      try {
        return JSON.stringify(meta, null, 2)
      } catch {
        return '—'
      }
    },
    logMetaHasAnimal(row) {
      const m = row.meta
      if (!m || typeof m !== 'object') return false
      const a = String(row.action || '')
      if (a.startsWith('animal.') || a.startsWith('adoption.')) return true
      return m.animal_id != null
    },
    logMetaAnimalLabel(m) {
      if (!m) return this.t('admin.logs.detail.unknownAnimal')
      if (m.animal_name) return m.animal_name
      if (m.name && (m.animal_id != null || m.animal_image || m.image)) return m.name
      if (m.animal_id != null) {
        return this.t('admin.logs.detail.animalIdOnly').replace('{id}', String(m.animal_id))
      }
      return this.t('admin.logs.detail.unknownAnimal')
    },
    activityLogDetailLine(row) {
      if (!this.logMetaHasAnimal(row)) return null
      const m = row.meta || {}
      const animal = this.logMetaAnimalLabel(m)
      const id = m.animal_id != null ? String(m.animal_id) : '—'
      const fill = (tpl) => tpl.replace('{animal}', animal).replace('{id}', id)
      switch (row.action) {
        case 'adoption.submitted':
          return fill(this.t('admin.logs.detail.adoptionSubmitted'))
        case 'adoption.withdrawn':
          return fill(this.t('admin.logs.detail.adoptionWithdrawn'))
        case 'adoption.deleted_by_admin':
          return fill(this.t('admin.logs.detail.adoptionDeletedAdmin'))
        case 'adoption.approved_by_admin':
          return fill(this.t('admin.logs.detail.adoptionApprovedAdmin'))
        case 'animal.created':
          return fill(this.t('admin.logs.detail.animalCreated'))
        case 'animal.updated':
          return fill(this.t('admin.logs.detail.animalUpdated'))
        case 'animal.deleted':
          return fill(this.t('admin.logs.detail.animalDeleted'))
        default:
          return fill(this.t('admin.logs.detail.animalGeneric'))
      }
    },
    activityLogSecondaryMeta(row) {
      const m = row.meta
      if (!m || typeof m !== 'object') return null
      const skip = new Set(['animal_id', 'animal_name', 'animal_image', 'name', 'image'])
      const rest = {}
      for (const [k, v] of Object.entries(m)) {
        if (!skip.has(k)) rest[k] = v
      }
      if (Object.keys(rest).length === 0) return null
      try {
        return JSON.stringify(rest, null, 2)
      } catch {
        return null
      }
    },
    shortUa(ua) {
      if (!ua) return '—'
      return ua.length > 80 ? `${ua.slice(0, 77)}…` : ua
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
      this.supportApproveBusyId = null
      try {
        const d = await adminFetchSupportChat(userId)
        this.supportChatUser = d.user || null
        this.supportMessages = Array.isArray(d.messages) ? d.messages : []
        const raw = d.pending_applications || d.pendingApplications || []
        this.supportPendingApplications = Array.isArray(raw)
          ? raw.map((p) => ({
              id: p.id,
              animalId: p.animal_id ?? p.animalId,
              animalName: p.animal_name ?? p.animalName ?? '—',
              submittedAt: p.submitted_at ?? p.submittedAt,
            }))
          : []
        this.$nextTick(() => this.scrollSupportChat())
      } catch {
        this.supportChatUser = null
        this.supportMessages = []
        this.supportPendingApplications = []
      }
      await this.loadSupportChatThreads()
    },
    async approveAdoptionFromChat(applicationId) {
      if (!applicationId || this.supportApproveBusyId != null) return
      this.supportApproveBusyId = applicationId
      try {
        await adminApproveApplication(applicationId)
        window.dispatchEvent(new Event('contactMessagesUpdated'))
        await this.selectSupportThread(this.supportChatUserId)
        await this.loadData()
      } catch {
        /* ignore */
      } finally {
        this.supportApproveBusyId = null
      }
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
    resetAnimalForm() {
      this.animalEditingId = null
      this.animalForm = {
        name: '',
        gender: '',
        species: '',
        description: '',
        image: '',
      }
    },
    startEditAnimal(animal) {
      this.animalEditingId = animal.id
      this.animalForm = {
        name: animal.name || '',
        gender: animal.gender || '',
        species: animal.species || '',
        description: animal.description || '',
        image: animal.image || '',
      }
    },
    cancelAnimalEdit() {
      this.resetAnimalForm()
    },
    async submitAnimal() {
      if (!this.animalForm.name || !this.animalForm.image) {
        return
      }
      const payload = {
        name: this.animalForm.name,
        species: this.animalForm.species || translate(this.lang, 'species.other'),
        gender: this.animalForm.gender || translate(this.lang, 'admin.an.defaultGender'),
        description: this.animalForm.description || translate(this.lang, 'admin.an.defaultDesc'),
        image: this.animalForm.image,
      }
      try {
        if (this.animalEditingId != null) {
          await adminUpdateAnimal(this.animalEditingId, payload)
        } else {
          await adminCreateAnimal(payload)
        }
        await this.loadAnimals()
        window.dispatchEvent(new Event('animalsUpdated'))
        this.resetAnimalForm()
      } catch {
        /* ignore */
      }
    },
    async deleteAnimal(id) {
      try {
        if (this.animalEditingId === id) {
          this.resetAnimalForm()
        }
        await adminDeleteAnimal(id)
        await this.loadAnimals()
        window.dispatchEvent(new Event('animalsUpdated'))
      } catch {
        /* ignore */
      }
    },
    formatDate(dateString) {
      if (!dateString) return '—'
      const loc = this.lang === 'en' ? 'en-GB' : 'lv-LV'
      return new Date(dateString).toLocaleString(loc, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
      })
    },
    messageStatusLabel(status) {
      const s = status || 'pending'
      if (s === 'pending') return translate(this.lang, 'admin.status.pending')
      if (s === 'approved') return translate(this.lang, 'admin.status.approved')
      if (s === 'declined') return translate(this.lang, 'admin.status.declined')
      return s
    },
    speciesDisplay(species) {
      return species === 'Cits' ? translate(this.lang, 'species.other') : species
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
  flex-wrap: wrap;
  gap: 10px;
  margin-bottom: 30px;
}

.activity-log-scroll {
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
  border-radius: 12px;
  border: 1px solid var(--hp-line-strong, rgba(255, 255, 255, 0.12));
}

.activity-log-table {
  width: 100%;
  min-width: 720px;
  border-collapse: collapse;
  font-size: 0.85rem;
}

.activity-log-table th,
.activity-log-table td {
  padding: 0.65rem 0.75rem;
  text-align: left;
  vertical-align: top;
  border-bottom: 1px solid var(--hp-line-strong, rgba(255, 255, 255, 0.08));
}

.activity-log-table th {
  font-weight: 700;
  color: var(--hp-muted, rgba(244, 244, 245, 0.75));
  white-space: nowrap;
}

.activity-log-email {
  display: block;
  font-size: 0.8rem;
  opacity: 0.85;
  margin-top: 0.15rem;
}

.activity-details-cell {
  min-width: 200px;
  max-width: 320px;
}

.activity-detail-text {
  margin: 0 0 0.35rem;
  font-size: 0.9rem;
  line-height: 1.45;
}

.activity-details-cell .activity-detail-text:last-child {
  margin-bottom: 0;
}

.activity-meta {
  margin: 0;
  font-family: ui-monospace, monospace;
  font-size: 0.72rem;
  white-space: pre-wrap;
  word-break: break-word;
  max-width: 280px;
  max-height: 120px;
  overflow: auto;
}

.activity-meta--extra {
  margin-top: 0.35rem;
  max-height: 96px;
}

.activity-log-ip {
  white-space: nowrap;
}

.activity-log-ua {
  max-width: 160px;
  overflow: hidden;
  text-overflow: ellipsis;
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

.animal-edit-banner {
  margin: -0.5rem 0 1rem;
  font-size: 0.92rem;
  color: var(--hp-muted, rgba(255, 255, 255, 0.72));
}

.animal-form-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 0.65rem;
  margin-top: 0.35rem;
}

.animal-form-actions .btn-save {
  width: auto;
  flex: 1 1 160px;
  margin: 0;
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

.btn-cancel-edit {
  padding: 14px 18px;
  border-radius: 999px;
  border: 1.5px solid var(--hp-line-strong, rgba(255, 255, 255, 0.18));
  background: transparent;
  color: var(--hp-text, #f4f4f5);
  font-size: 1rem;
  font-weight: 700;
  cursor: pointer;
  transition:
    border-color 0.2s ease,
    color 0.2s ease;
}

.btn-cancel-edit:hover {
  border-color: rgba(245, 204, 76, 0.45);
  color: var(--hp-gold, #f5cc4c);
}

.animal-item-actions {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  align-items: stretch;
  flex-shrink: 0;
}

.btn-edit-animal {
  padding: 0.55rem 0.85rem;
  border-radius: 12px;
  border: 1.5px solid rgba(245, 204, 76, 0.45);
  background: rgba(245, 204, 76, 0.1);
  color: var(--hp-gold, #f5cc4c);
  font-weight: 700;
  font-size: 0.85rem;
  cursor: pointer;
  transition:
    background 0.2s ease,
    border-color 0.2s ease;
}

.btn-edit-animal:hover {
  background: rgba(245, 204, 76, 0.18);
  border-color: rgba(245, 204, 76, 0.65);
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

.users-section-title {
  margin-top: 36px;
}

.users-accounts-toolbar {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 12px 20px;
  margin-bottom: 20px;
}

.users-accounts-toolbar .users-section-title {
  margin: 0;
}

.users-toolbar-actions {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 12px 16px;
}

.users-filter-label {
  display: flex;
  align-items: center;
  gap: 10px;
  cursor: pointer;
}

.users-filter-label-text {
  font-size: 0.9rem;
  font-weight: 700;
  color: var(--hp-muted, rgba(244, 244, 245, 0.75));
  white-space: nowrap;
}

.users-filter-select {
  min-width: 11rem;
  padding: 0.5rem 2rem 0.5rem 0.9rem;
  border-radius: 999px;
  border: 1.5px solid var(--hp-line-strong, rgba(255, 255, 255, 0.18));
  background: rgba(0, 0, 0, 0.28);
  color: var(--hp-text, #f4f4f5);
  font-weight: 700;
  font-size: 0.88rem;
  cursor: pointer;
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23f4f4f5' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 0.75rem center;
}

.users-filter-select:focus {
  outline: 2px solid var(--hp-orange, #ff5722);
  outline-offset: 2px;
}

.users-filter-select option {
  color: #1a1a1a;
  background: #fff;
}

.users-list {
  display: grid;
  gap: 16px;
}

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

.support-chat-pane__head-main {
  display: flex;
  flex-direction: column;
  gap: 0.85rem;
}

.support-chat-pane__identity {
  margin: 0;
  color: var(--hp-text, #f4f4f5);
}

.support-chat-pane__approvals {
  padding: 0.65rem 0.75rem;
  border-radius: 12px;
  border: 1px solid rgba(245, 204, 76, 0.28);
  background: rgba(245, 204, 76, 0.06);
}

.support-chat-pane__approvals-title {
  margin: 0 0 0.5rem;
  font-size: 0.72rem;
  font-weight: 800;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: var(--hp-gold, #f5cc4c);
}

.support-chat-pane__approvals-list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.support-chat-pane__approval-row {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 0.5rem;
}

.support-chat-pane__approval-animal {
  font-weight: 700;
  color: var(--hp-text, #f4f4f5);
}

.btn-approve-adoption {
  padding: 0.45rem 0.85rem;
  border-radius: 999px;
  border: none;
  font-weight: 800;
  font-size: 0.78rem;
  cursor: pointer;
  color: #0a0a0c;
  background: linear-gradient(180deg, var(--hp-gold, #f5cc4c) 0%, #e6b73a 100%);
  transition:
    opacity 0.2s ease,
    transform 0.2s ease;
}

.btn-approve-adoption:hover:not(:disabled) {
  transform: translateY(-1px);
}

.btn-approve-adoption:disabled {
  opacity: 0.55;
  cursor: not-allowed;
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
