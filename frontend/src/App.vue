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
            <li><a href="#animals" @click="closeMenu">Dzīvnieki</a></li>
            <li><a href="#about" @click="closeMenu">Par mums</a></li>
            <li><a href="#contact" @click="closeMenu">Kontakti</a></li>
          
          </ul>
        </nav>

        <div class="auth-buttons">
          <template v-if="userLoggedIn">
            <button @click="toggleNotificationPanel" class="btn-notification">
              Paziņojumi
              <span v-if="unreadNotifications" class="notification-badge">{{ unreadNotifications }}</span>
            </button>
            <router-link to="/account" class="btn-account">Mans konts</router-link>
          </template>
          <template v-else>
            <router-link to="/login" class="btn-login">Pierakstīties</router-link>
            <router-link to="/signup" class="btn-signup">Reģistrējieties</router-link>
          </template>
          <router-link to="/admin-login" class="btn-admin">Admin</router-link>
        </div>
      </div>

      <div v-if="showNotificationPanel" class="notification-dropdown">
        <div class="notification-dropdown-header">
          <strong>Administratora paziņojumi</strong>
          <button @click="closeNotificationPanel" class="close-dropdown">×</button>
        </div>
        <div v-if="notificationList.length === 0" class="dropdown-empty">
          Nav jaunu paziņojumu
        </div>
        <div v-else class="dropdown-items">
          <div v-for="notification in notificationList" :key="notification.id" class="dropdown-item">
            <div class="dropdown-item-row">
              <div>
                <p class="dropdown-message">{{ notification.message }}</p>
                <p class="dropdown-meta">
                  <span>{{ notification.status === 'approved' ? 'Apstiprināts' : 'Noraidīts' }}</span>
                  •
                  <span>{{ formatDropdownDate(notification.moderatedAt || notification.approvedAt || notification.sentAt) }}</span>
                </p>
              </div>
              <button class="dropdown-delete" @click.stop="deleteNotification(notification.id)">Dzēst</button>
            </div>
          </div>
        </div>
      </div>
    </header>

    <router-view />

    <footer>
      <div class="container footer-wrap">
        <div>© {{ currentYear }} Dzīvnieku adoptācijas centrs</div>
      </div>
    </footer>
  </div>
</template>

<script>

export default {
  name: 'App',

  data() {
    return {
      menuOpen: false,
      currentYear: new Date().getFullYear(),
      userLoggedIn: false,
      unreadNotifications: 0,
      showNotificationPanel: false
    }
  },
  mounted() {
    this.refreshAuthState()
    this.refreshNotifications()
    window.addEventListener('storage', this.onStorageChange)
    window.addEventListener('contactMessagesUpdated', this.refreshNotifications)
  },
  beforeUnmount() {
    window.removeEventListener('storage', this.onStorageChange)
    window.removeEventListener('contactMessagesUpdated', this.refreshNotifications)
  },
  watch: {
    $route() {
      this.refreshAuthState()
      this.refreshNotifications()
    }
  },
  computed: {
    notificationList() {
      if (!this.userLoggedIn) return []
      const email = this.getCurrentUserEmail()
      const messages = JSON.parse(localStorage.getItem('contactMessages') || '[]')
      return messages
        .filter(message => message.email === email && ['approved', 'declined'].includes(message.status))
        .sort((a, b) => new Date(b.moderatedAt || b.approvedAt || b.sentAt) - new Date(a.moderatedAt || a.approvedAt || a.sentAt))
    }
  },
  methods: {
    toggleMenu() {
      this.menuOpen = !this.menuOpen
    },
    closeMenu() {
      this.menuOpen = false
    },
    refreshAuthState() {
      this.userLoggedIn = !!localStorage.getItem('userLoggedIn')
    },
    getCurrentUserEmail() {
      return localStorage.getItem('userEmail') || ''
    },
    refreshNotifications() {
      if (!this.userLoggedIn) {
        this.unreadNotifications = 0
        return
      }
      const email = this.getCurrentUserEmail()
      const messages = JSON.parse(localStorage.getItem('contactMessages') || '[]')
      this.unreadNotifications = messages.filter(message =>
        message.email === email &&
        ['approved', 'declined'].includes(message.status) &&
        !message.read
      ).length
    },
    onStorageChange(event) {
      if (event.key === 'contactMessages') {
        this.refreshNotifications()
      }
      if (event.key === 'userLoggedIn' || event.key === 'userEmail') {
        this.refreshAuthState()
      }
    },
    toggleNotificationPanel() {
      this.showNotificationPanel = !this.showNotificationPanel
      if (this.showNotificationPanel) {
        this.refreshNotifications()
      }
    },
    deleteNotification(id) {
      const messages = JSON.parse(localStorage.getItem('contactMessages') || '[]')
      const updated = messages.filter(message => message.id !== id)
      localStorage.setItem('contactMessages', JSON.stringify(updated))
      this.refreshNotifications()
    },
    closeNotificationPanel() {
      this.showNotificationPanel = false
    },
    logout() {
      localStorage.removeItem('userLoggedIn')
      localStorage.removeItem('userEmail')
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

:root {
  --primary: #db602b; /* Burnt clay for a warmer, deeper accent */
  --secondary: #B57A48; /* Bronze warmth for richer highlights */
  --accent: #E7B07F; /* Honey-peach glow for softer contrast */
  --dark-bg: #1A1A2E; /* Deep blue-black for background depth */
  --light-bg: #16213E; /* Lighter blue for subtle variations */
  --white: #FFFFFF; /* White for text and contrast */
}

body {
  font-family: 'Segoe UI', Tahoma, "Geneva", Verdana, sans-serif;
  background-color: var(--dark-bg);
  color: var(--white);
  line-height: 1.6;
}

#app {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
  width: 100%;
}

header {
  background: linear-gradient(135deg, var(--dark-bg), var(--light-bg));
  padding: 1rem 0;
  position: sticky;
  top: 0;
  z-index: 1000;
  border-bottom: 3px solid var(--primary);
}

.nav {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 2rem;
}

.btn-notification {
  padding: 10px 18px;
  background: rgba(255, 255, 255, 0.12);
  color: var(--white);
  border: 1px solid rgba(255, 255, 255, 0.3);
  border-radius: 20px;
  text-decoration: none;
  font-weight: 700;
  display: inline-flex;
  align-items: center;
  gap: 10px;
}

.notification-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 24px;
  height: 24px;
  border-radius: 999px;
  background: var(--white);
  color: var(--dark-bg);
  font-size: 0.85rem;
  padding: 0 8px;
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
  background-color: var(--primary);
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

.menu a, .menu router-link {
  color: var(--white);
  text-decoration: none;
  font-weight: 500;
  transition: color 0.3s;
  padding: 0.5rem 1rem;
  border-radius: 20px;
  cursor: pointer;
  white-space: nowrap; /* keep each menu link on one line */
}

.menu a:hover, .menu router-link:hover {
  color: var(--secondary);
  background-color: rgba(255, 210, 63, 0.1);
}

.auth-buttons {
  display: flex;
  gap: 0.8rem;
  margin-left: 0;
  flex-shrink: 0;
}

.btn-login, .btn-signup, .btn-admin {
  padding: 0.4rem 0.8rem;
  border-radius: 20px;
  text-decoration: none;
  font-weight: bold;
  transition: all 0.3s ease;
  border: 2px solid var(--primary);
  font-size: 0.8rem;
  white-space: nowrap;
}

.btn-admin {
  color: var(--white);
  background-color: transparent;
}

.btn-admin:hover {
  background-color: rgba(255, 210, 63, 0.3);
  border-color: var(--secondary);
}

.btn-account {
  padding: 0.4rem 0.8rem;
  border-radius: 20px;
  text-decoration: none;
  font-weight: bold;
  transition: all 0.3s ease;
  border: 2px solid var(--primary);
  font-size: 0.8rem;
  color: var(--primary);
  background-color: transparent;
}

.btn-account:hover {
  background-color: rgba(255, 255, 255, 0.1);
}

.btn-notification {
  padding: 0.4rem 0.8rem;
  border-radius: 20px;
  text-decoration: none;
  font-weight: bold;
  transition: all 0.3s ease;
  border: 2px solid var(--primary);
  font-size: 0.8rem;
  color: var(--white);
  background-color: transparent;
  display: inline-flex;
  align-items: center;
  gap: 10px;
}

.btn-notification:hover {
  background-color: rgba(255, 255, 255, 0.1);
}

.notification-dropdown {
  background: rgba(24, 24, 44, 0.95);
  border: 1px solid rgba(255, 255, 255, 0.12);
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.25);
  border-radius: 18px;
  max-width: 420px;
  width: calc(100% - 40px);
  margin: 0 auto;
  margin-top: 1rem;
  padding: 1rem;
  color: var(--white);
}

.notification-dropdown-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.8rem;
}

.notification-dropdown-header strong {
  font-size: 1rem;
}

.close-dropdown {
  background: transparent;
  border: none;
  color: var(--white);
  font-size: 1.4rem;
  cursor: pointer;
}

.dropdown-empty {
  padding: 1rem;
  color: rgba(255, 255, 255, 0.7);
}

.dropdown-items {
  display: grid;
  gap: 0.8rem;
}

.dropdown-item {
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 14px;
  padding: 0.9rem;
}

.dropdown-item-row {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 1rem;
}

.dropdown-delete {
  background: transparent;
  border: 1px solid rgba(255, 255, 255, 0.35);
  color: rgba(255, 255, 255, 0.9);
  border-radius: 12px;
  padding: 0.35rem 0.75rem;
  cursor: pointer;
  font-size: 0.85rem;
}

.dropdown-delete:hover {
  background: rgba(255, 255, 255, 0.08);
}

.dropdown-message {
  margin-bottom: 0.6rem;
}

.dropdown-meta {
  color: rgba(255, 255, 255, 0.7);
  font-size: 0.85rem;
}

@media (max-width: 768px) {
  .notification-dropdown {
    width: 100%;
    margin-top: 0.8rem;
  }
}

.btn-login {
  color: var(--primary);
  background-color: transparent;
}

.btn-login:hover {
  background-color: var(--primary);
  color: var(--white);
}

.btn-signup {
  color: var(--white);
  background-color: var(--primary);
}

.btn-signup:hover {
  background-color: var(--secondary);
  border-color: var(--secondary);
}

footer {
  background: linear-gradient(135deg, var(--dark-bg), var(--light-bg));
  padding: 2rem 0;
  margin-top: auto;
  border-top: 3px solid var(--primary);
  text-align: center;
}

.footer-wrap {
  color: var(--white);
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
    background: linear-gradient(135deg, var(--dark-bg), var(--light-bg));
    transition: right 0.3s ease;
    padding-top: 80px;
    border-left: 3px solid var(--primary);
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
}
</style>