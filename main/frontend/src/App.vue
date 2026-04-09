<template>
  <div id="app">
    <header id="home">
      <div class="container nav">
        <div class="brand">LAIMĪGĀS ĶEPAS</div>

        <button 
          class="hamburger" 
          @click="toggleMenu"
          :class="{ active: menuOpen }"
        >
          <span></span>
          <span></span>
          <span></span>
        </button>

        <nav :class="{ open: menuOpen }">
          <ul class="menu">
            <li><a href="/" @click.prevent="goHome">Sākums</a></li>
            <li><a href="#how" @click="closeMenu">Kā notiek</a></li>
            <li><a href="#animals" @click="closeMenu">Dzīvnieki</a></li>
            <li><a href="#about" @click="closeMenu">Par mums</a></li>
            <li><a href="#contact" @click="closeMenu">Kontakti</a></li>
          </ul>
          <div class="menu-auth" aria-label="Konta darbības">
            <template v-if="userLoggedIn">
              <button type="button" @click="openNotificationsFromMenu" class="btn-notification">
                Paziņojumi
                <span v-if="unreadNotifications" class="notification-badge">{{ unreadNotifications }}</span>
              </button>
              <router-link to="/account" class="btn-account" @click="closeMenu">Mans konts</router-link>
              <router-link
                v-if="userIsAdmin"
                to="/admin"
                class="btn-admin"
                @click="closeMenu"
              >Admin</router-link>
            </template>
            <template v-else>
              <router-link to="/login" class="btn-login" @click="closeMenu">Pierakstīties</router-link>
              <router-link to="/signup" class="btn-signup" @click="closeMenu">Reģistrējieties</router-link>
            </template>
          </div>
        </nav>

        <div class="auth-buttons">
          <template v-if="userLoggedIn">
            <button @click="toggleNotificationPanel" class="btn-notification">
              Paziņojumi
              <span v-if="unreadNotifications" class="notification-badge">{{ unreadNotifications }}</span>
            </button>
            <router-link to="/account" class="btn-account">Mans konts</router-link>
            <router-link v-if="userIsAdmin" to="/admin" class="btn-admin">Admin</router-link>
          </template>
          <template v-else>
            <router-link to="/login" class="btn-login">Pierakstīties</router-link>
            <router-link to="/signup" class="btn-signup">Reģistrējieties</router-link>
          </template>
        </div>
      </div>

      <div
        v-if="showNotificationPanel"
        class="notification-panel"
        role="dialog"
        aria-labelledby="notif-panel-title"
        aria-modal="true"
      >
        <div class="notification-panel__inner">
          <div class="notification-panel__head">
            <div class="notification-panel__title-row">
              <span class="notification-panel__icon" aria-hidden="true">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path
                    d="M12 22a2.5 2.5 0 0 0 2.45-2h-4.9A2.5 2.5 0 0 0 12 22z"
                    fill="currentColor"
                    opacity="0.9"
                  />
                  <path
                    d="M18 16v-5a6 6 0 1 0-12 0v5l-2 2v1h16v-1l-2-2z"
                    stroke="currentColor"
                    stroke-width="1.75"
                    stroke-linejoin="round"
                  />
                </svg>
              </span>
              <div>
                <h2 id="notif-panel-title" class="notification-panel__title">Paziņojumi</h2>
                <p class="notification-panel__subtitle">No administrācijas</p>
              </div>
            </div>
            <button type="button" @click="closeNotificationPanel" class="notification-panel__close" aria-label="Aizvērt">
              <span aria-hidden="true">×</span>
            </button>
          </div>
          <div v-if="notificationList.length === 0" class="notification-panel__empty">
            <p class="notification-panel__empty-title">Viss skaidrs</p>
            <p class="notification-panel__empty-text">Šobrīd nav jaunu ziņu.</p>
          </div>
          <ul v-else class="notification-panel__list" role="list">
            <li
              v-for="notification in notificationList"
              :key="notification.id"
              class="notification-panel__card"
              :class="{
                'notification-panel__card--approved': notification.status === 'approved',
                'notification-panel__card--declined': notification.status === 'declined',
              }"
              role="listitem"
            >
              <div class="notification-panel__card-main">
                <span
                  class="notification-panel__tag"
                  :class="{
                    'notification-panel__tag--ok': notification.status === 'approved',
                    'notification-panel__tag--no': notification.status === 'declined',
                  }"
                >
                  {{ notificationTypeLabel(notification) }}
                </span>
                <p class="notification-panel__message">{{ notification.message }}</p>
                <time class="notification-panel__time" :datetime="notification.moderatedAt || notification.approvedAt || notification.sentAt">
                  {{ formatDropdownDate(notification.moderatedAt || notification.approvedAt || notification.sentAt) }}
                </time>
              </div>
              <button
                type="button"
                class="notification-panel__dismiss"
                title="Dzēst paziņojumu"
                aria-label="Dzēst paziņojumu"
                @click.stop="deleteUserNotification(notification.id)"
              >
                <span aria-hidden="true">×</span>
              </button>
            </li>
          </ul>
        </div>
      </div>
    </header>

    <main class="app-main">
      <router-view />
    </main>

    <SupportChatWidget v-if="showSupportChat" />

    <footer>
      <div class="container footer-wrap">
        <div>© {{ currentYear }} LAIMĪGĀS ĶEPAS</div>
      </div>
    </footer>
  </div>
</template>

<script>
import SupportChatWidget from '@/components/SupportChatWidget.vue'
import { getStoredUser, refreshSessionUser } from '@/api/authApi'
import { fetchMyNotifications } from '@/api/restApi'
import { clearLoggedInUserIfBlocked, isUserLoggedIn } from '@/utils/authStorage'
import { removeNotificationForUser } from '@/utils/contactMessages'

export default {
  name: 'App',

  components: { SupportChatWidget },

  data() {
    return {
      menuOpen: false,
      currentYear: new Date().getFullYear(),
      userLoggedIn: false,
      userIsAdmin: false,
      unreadNotifications: 0,
      showNotificationPanel: false,
      /** Bumped when notifications refetch so notificationList recomputes. */
      contactMessagesTick: 0,
      headerNotifications: [],
    }
  },
  mounted() {
    void this.enforceBlockState()
    void this.refreshNotifications()
    window.addEventListener('storage', this.onStorageChange)
    window.addEventListener('contactMessagesUpdated', this.onContactMessagesUpdated)
    window.addEventListener('authUpdated', this.onAuthUpdated)
  },
  beforeUnmount() {
    window.removeEventListener('storage', this.onStorageChange)
    window.removeEventListener('contactMessagesUpdated', this.onContactMessagesUpdated)
    window.removeEventListener('authUpdated', this.onAuthUpdated)
  },
  watch: {
    $route() {
      void this.enforceBlockState()
      void this.refreshNotifications()
    },
  },
  computed: {
    showSupportChat() {
      return this.userLoggedIn && !this.$route.path.startsWith('/admin')
    },
    notificationList() {
      void this.contactMessagesTick
      if (!this.userLoggedIn) return []
      return [...this.headerNotifications].sort(
        (a, b) =>
          new Date(b.moderatedAt || b.approvedAt || b.sentAt) -
          new Date(a.moderatedAt || a.approvedAt || a.sentAt),
      )
    },
  },
  methods: {
    async onContactMessagesUpdated() {
      if (isUserLoggedIn()) {
        try {
          await refreshSessionUser()
        } catch {
          /* ignore */
        }
      }
      this.refreshAuthState()
      void this.refreshNotifications()
    },
    onAuthUpdated() {
      this.refreshAuthState()
      void this.refreshNotifications()
    },
    toggleMenu() {
      this.menuOpen = !this.menuOpen
    },
    closeMenu() {
      this.menuOpen = false
    },
    refreshAuthState() {
      this.userLoggedIn = isUserLoggedIn()
      this.userIsAdmin = !!getStoredUser()?.isAdmin
    },
    async enforceBlockState() {
      const cleared = await clearLoggedInUserIfBlocked()
      if (cleared) {
        this.closeNotificationPanel()
        if (this.$route.matched.some((r) => r.meta.requiresAuth)) {
          this.$router.push({ path: '/login', query: { blocked: '1' } })
        }
      }
      this.refreshAuthState()
      await this.refreshNotifications()
    },
    getCurrentUserEmail() {
      return getStoredUser()?.email || ''
    },
    async refreshNotifications() {
      this.contactMessagesTick++
      if (!isUserLoggedIn()) {
        this.unreadNotifications = 0
        this.headerNotifications = []
        return
      }
      try {
        const list = await fetchMyNotifications()
        this.headerNotifications = list
        this.unreadNotifications = list.filter(
          (message) =>
            ['approved', 'declined'].includes(message.status) && !message.read,
        ).length
      } catch {
        this.headerNotifications = []
        this.unreadNotifications = 0
      }
    },
    onStorageChange(event) {
      if (event.key === 'spa_auth_token' || event.key === 'spa_auth_user') {
        void this.enforceBlockState()
      }
    },
    toggleNotificationPanel() {
      this.showNotificationPanel = !this.showNotificationPanel
      if (this.showNotificationPanel) {
        this.refreshNotifications()
      }
    },
    openNotificationsFromMenu() {
      this.toggleNotificationPanel()
      this.closeMenu()
    },
    notificationTypeLabel(notification) {
      if (notification.source === 'admin_role_grant') {
        return 'Administratora piekļuve'
      }
      return notification.status === 'approved' ? 'Apstiprināts' : 'Noraidīts'
    },
    closeNotificationPanel() {
      this.showNotificationPanel = false
    },
    async deleteUserNotification(id) {
      await removeNotificationForUser(id)
      await this.refreshNotifications()
    },
    logout() {
      this.userLoggedIn = false
      this.closeMenu()
      this.$router.push('/login')
    },
    formatDropdownDate(value) {
      if (!value) return ''
      return new Date(value).toLocaleString('lv-LV', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
    },
    goHome() {
      this.closeMenu()
      if (this.$route.path === '/') {
        window.scrollTo({ top: 0, behavior: 'smooth' })
      } else {
        this.$router.push('/').then(() => {
          window.scrollTo({ top: 0, behavior: 'smooth' })
        })
      }
    }
  }
}
</script>

<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
  background-color: var(--hp-bg);
  color: var(--hp-text);
  line-height: 1.6;
}

#app {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.app-main {
  flex: 1;
  width: 100%;
  display: flex;
  flex-direction: column;
  min-height: 0;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
  width: 100%;
}

header {
  position: sticky;
  top: 0;
  z-index: 1000;
  padding: 0.85rem 0;
  /* Tā pati bāze kā mājas lapai (#0a0a0c), ne zilpelēcīgs gradients */
  background-color: var(--hp-bg);
  background-image: radial-gradient(
    ellipse 110% 180% at 50% -70%,
    rgba(120, 45, 30, 0.14),
    transparent 50%
  );
  border-bottom: 1px solid var(--hp-line);
  box-shadow: 0 1px 0 rgba(245, 204, 76, 0.12);
}

.brand {
  font-weight: 800;
  font-size: 0.72rem;
  letter-spacing: 0.22em;
  text-transform: uppercase;
  color: var(--hp-gold);
  user-select: none;
}

.nav {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 2rem;
}

.hamburger {
  display: none;
  flex-direction: column;
  background: none;
  border: none;
  cursor: pointer;
  padding: 0;
  z-index: 1;
}

.hamburger span {
  width: 25px;
  height: 3px;
  background-color: var(--hp-gold);
  margin: 3px 0;
  transition: 0.3s;
  border-radius: 3px;
}

.hamburger.active span:nth-child(1) {
  transform: rotate(-45deg) translate(-6px, 6px);
}

.hamburger.active span:nth-child(2) {
  opacity: 0;
}

.hamburger.active span:nth-child(3) {
  transform: rotate(45deg) translate(-6px, -6px);
}

.menu {
  display: flex;
  list-style: none;
  gap: 2rem;
  flex: 1;
  white-space: nowrap; /* ensure line does not wrap */
}

.menu li {
  flex: none;
}

.menu a,
.menu router-link {
  color: rgba(255, 255, 255, 0.88);
  text-decoration: none;
  font-weight: 600;
  font-size: 0.9rem;
  transition:
    color 0.2s ease,
    background-color 0.2s ease;
  padding: 0.5rem 1rem;
  border-radius: 999px;
  cursor: pointer;
  white-space: nowrap;
}

.menu a:hover,
.menu router-link:hover {
  color: var(--hp-gold);
  background-color: rgba(255, 255, 255, 0.06);
}

.menu-auth {
  display: none;
}

.auth-buttons {
  display: flex;
  gap: 0.55rem;
  margin-left: 0;
  flex-shrink: 0;
  align-items: center;
}

.btn-login,
.btn-signup,
.btn-admin,
.btn-account {
  padding: 0.5rem 1.15rem;
  border-radius: 999px;
  text-decoration: none;
  font-weight: 700;
  font-size: 0.8rem;
  white-space: nowrap;
  transition:
    transform 0.2s ease,
    box-shadow 0.2s ease,
    background 0.2s ease,
    border-color 0.2s ease,
    color 0.2s ease;
  border: 1.5px solid transparent;
}

.btn-login {
  color: var(--hp-text);
  background: transparent;
  border-color: rgba(255, 255, 255, 0.42);
}

.btn-login:hover {
  border-color: var(--hp-gold);
  color: var(--hp-gold);
  background: rgba(255, 255, 255, 0.06);
}

.btn-signup {
  color: #fff;
  background: linear-gradient(180deg, var(--hp-orange) 0%, var(--hp-orange-deep) 100%);
  border-color: transparent;
  box-shadow: 0 4px 18px rgba(255, 87, 34, 0.35);
}

.btn-signup:hover {
  transform: translateY(-1px);
  box-shadow: 0 6px 22px rgba(255, 87, 34, 0.45);
}

.btn-admin {
  color: var(--hp-gold);
  background: transparent;
  border-color: rgba(245, 204, 76, 0.45);
}

.btn-admin:hover {
  background: rgba(245, 204, 76, 0.1);
  border-color: var(--hp-gold);
}

.btn-account {
  color: var(--hp-text);
  background: var(--hp-surface);
  border-color: var(--hp-line-strong);
}

.btn-account:hover {
  background: var(--hp-surface-hover);
  border-color: rgba(245, 204, 76, 0.35);
  color: var(--hp-gold);
}

.btn-notification {
  padding: 0.5rem 1.05rem;
  border-radius: 999px;
  font-weight: 700;
  font-size: 0.8rem;
  color: var(--hp-text);
  background: var(--hp-surface);
  border: 1.5px solid var(--hp-line-strong);
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  transition:
    background 0.2s ease,
    border-color 0.2s ease;
}

.btn-notification:hover {
  background: var(--hp-surface-hover);
  border-color: rgba(245, 204, 76, 0.35);
}

.notification-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 22px;
  height: 22px;
  border-radius: 999px;
  background: var(--hp-gold);
  color: var(--hp-bg);
  font-size: 0.75rem;
  font-weight: 800;
  padding: 0 6px;
}

/* Peldošs paziņojumu panelis */
.notification-panel {
  position: absolute;
  top: 100%;
  right: clamp(12px, 3vw, 24px);
  left: auto;
  margin-top: 0.5rem;
  width: min(400px, calc(100vw - 24px));
  z-index: 1001;
  padding: 0;
  border: none;
  background: transparent;
  pointer-events: none;
}

.notification-panel__inner {
  pointer-events: auto;
  border-radius: 20px;
  padding: 0;
  overflow: hidden;
  background: rgba(14, 17, 24, 0.92);
  backdrop-filter: blur(18px);
  -webkit-backdrop-filter: blur(18px);
  border: 1px solid var(--hp-line-strong);
  box-shadow:
    0 0 0 1px rgba(245, 204, 76, 0.06) inset,
    0 24px 56px rgba(0, 0, 0, 0.55),
    0 0 80px rgba(255, 87, 34, 0.06);
  animation: notif-inner-in 0.24s ease-out;
}

@keyframes notif-inner-in {
  from {
    opacity: 0;
    transform: translateY(-8px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@media (prefers-reduced-motion: reduce) {
  .notification-panel__inner {
    animation: none;
  }
}

.notification-panel__head {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 0.75rem;
  padding: 1rem 1rem 0.85rem;
  border-bottom: 1px solid var(--hp-line);
}

.notification-panel__title-row {
  display: flex;
  align-items: flex-start;
  gap: 0.65rem;
}

.notification-panel__icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  border-radius: 12px;
  background: rgba(245, 204, 76, 0.1);
  color: var(--hp-gold);
  flex-shrink: 0;
}

.notification-panel__title {
  margin: 0;
  font-family: 'Playfair Display', Georgia, serif;
  font-size: 1.15rem;
  font-weight: 600;
  color: var(--hp-text);
  line-height: 1.2;
}

.notification-panel__subtitle {
  margin: 0.2rem 0 0;
  font-size: 0.72rem;
  font-weight: 600;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: var(--hp-muted);
}

.notification-panel__close {
  flex-shrink: 0;
  width: 38px;
  height: 38px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px solid var(--hp-line);
  border-radius: 12px;
  background: var(--hp-surface);
  color: var(--hp-text);
  font-size: 1.5rem;
  line-height: 1;
  cursor: pointer;
  transition:
    background 0.2s ease,
    border-color 0.2s ease,
    color 0.2s ease;
}

.notification-panel__close:hover {
  border-color: rgba(245, 204, 76, 0.4);
  background: var(--hp-surface-hover);
  color: var(--hp-gold);
}

.notification-panel__empty {
  padding: 2rem 1.25rem 1.75rem;
  text-align: center;
}

.notification-panel__empty-title {
  margin: 0 0 0.35rem;
  font-weight: 700;
  font-size: 1rem;
  color: var(--hp-text);
}

.notification-panel__empty-text {
  margin: 0;
  font-size: 0.9rem;
  color: var(--hp-muted);
  line-height: 1.5;
}

.notification-panel__list {
  list-style: none;
  margin: 0;
  padding: 0.65rem;
  max-height: min(60vh, 420px);
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.notification-panel__list::-webkit-scrollbar {
  width: 6px;
}

.notification-panel__list::-webkit-scrollbar-thumb {
  background: var(--hp-line-strong);
  border-radius: 999px;
}

.notification-panel__card {
  display: flex;
  gap: 0.5rem;
  align-items: flex-start;
  padding: 0.85rem 0.75rem;
  border-radius: 14px;
  background: var(--hp-surface);
  border: 1px solid var(--hp-line);
  position: relative;
  transition:
    border-color 0.2s ease,
    background 0.2s ease;
}

.notification-panel__card::before {
  content: '';
  position: absolute;
  left: 0;
  top: 10px;
  bottom: 10px;
  width: 3px;
  border-radius: 0 3px 3px 0;
  background: var(--hp-line-strong);
  opacity: 0.8;
}

.notification-panel__card--approved::before {
  background: linear-gradient(180deg, #4ade80, #22c55e);
  opacity: 1;
}

.notification-panel__card--declined::before {
  background: linear-gradient(180deg, #f87171, #ef4444);
  opacity: 1;
}

.notification-panel__card:hover {
  border-color: rgba(245, 204, 76, 0.2);
  background: var(--hp-surface-hover);
}

.notification-panel__card-main {
  flex: 1;
  min-width: 0;
  padding-left: 0.35rem;
}

.notification-panel__tag {
  display: inline-block;
  font-size: 0.65rem;
  font-weight: 800;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  padding: 0.2rem 0.5rem;
  border-radius: 999px;
  margin-bottom: 0.45rem;
  background: rgba(255, 255, 255, 0.08);
  color: var(--hp-muted);
}

.notification-panel__tag--ok {
  background: rgba(34, 197, 94, 0.18);
  color: #86efac;
}

.notification-panel__tag--no {
  background: rgba(239, 68, 68, 0.18);
  color: #fecaca;
}

.notification-panel__message {
  margin: 0 0 0.5rem;
  font-size: 0.92rem;
  line-height: 1.5;
  color: rgba(255, 255, 255, 0.93);
}

.notification-panel__time {
  display: block;
  font-size: 0.78rem;
  color: var(--hp-muted);
  font-style: normal;
}

.notification-panel__dismiss {
  flex-shrink: 0;
  width: 34px;
  height: 34px;
  padding: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px solid transparent;
  border-radius: 10px;
  background: rgba(239, 68, 68, 0.08);
  color: #fca5a5;
  font-size: 1.25rem;
  line-height: 1;
  cursor: pointer;
  transition:
    background 0.2s ease,
    border-color 0.2s ease;
}

.notification-panel__dismiss:hover {
  background: rgba(239, 68, 68, 0.2);
  border-color: rgba(248, 113, 113, 0.35);
}

@media (max-width: 768px) {
  .notification-panel {
    left: 50%;
    right: auto;
    transform: translateX(-50%);
    width: min(400px, calc(100vw - 20px));
  }
}

footer {
  background-color: var(--hp-bg);
  background-image: radial-gradient(
    ellipse 110% 180% at 50% 170%,
    rgba(120, 45, 30, 0.1),
    transparent 50%
  );
  padding: 2rem 0;
  margin-top: auto;
  border-top: 1px solid var(--hp-line);
  box-shadow: inset 0 1px 0 rgba(245, 204, 76, 0.12);
  text-align: center;
}

.footer-wrap {
  color: var(--hp-muted);
  font-size: 0.85rem;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  font-weight: 600;
}

@media (max-width: 768px) {
  .hamburger {
    display: flex;
  }

  .auth-buttons {
    display: none;
  }

  nav {
    position: fixed;
    top: 0;
    right: -100%;
    height: 100vh;
    width: 70%;
    max-width: 300px;
    background-color: var(--hp-bg);
    background-image: radial-gradient(
      ellipse 90% 60% at 100% 0%,
      rgba(120, 45, 30, 0.12),
      transparent 55%
    );
    transition: right 0.3s ease;
    padding-top: 80px;
    border-left: 1px solid var(--hp-line-strong);
    box-shadow: -12px 0 40px rgba(0, 0, 0, 0.45);
  }

  nav.open {
    right: 0;
  }

  .menu {
    flex-direction: column;
    gap: 0;
    padding: 2rem;
    flex: none;
  }

  .menu li {
    margin: 0.5rem 0;
  }

  .menu a, .menu router-link {
    display: block;
    padding: 1rem;
  }

  .menu-auth {
    display: flex;
    flex-direction: column;
    gap: 0.65rem;
    padding: 1rem 2rem 2rem;
    margin-top: 0.25rem;
    border-top: 1px solid var(--hp-line);
  }

  .menu-auth .btn-login,
  .menu-auth .btn-signup,
  .menu-auth .btn-admin,
  .menu-auth .btn-account {
    display: flex;
    justify-content: center;
    align-items: center;
    width: 100%;
    text-align: center;
    padding: 0.85rem 1rem;
    box-sizing: border-box;
    border-radius: 999px;
  }

  .menu-auth .btn-notification {
    display: inline-flex;
    justify-content: center;
    align-items: center;
    width: 100%;
    padding: 0.85rem 1rem;
    box-sizing: border-box;
    border-radius: 999px;
  }
}
</style>