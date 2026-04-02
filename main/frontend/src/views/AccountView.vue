<template>
  <div class="account-container">
    <div class="account-box">
      <div class="account-header">
        <div>
          <h1>Mans konts</h1>
        </div>
        <div class="header-actions">
          
          <button @click="logout" class="btn-account">Iziet</button>
        </div>
      </div>

      <div class="account-body">
        <div class="account-info">
          <div class="info-row">
            <span class="info-label">Vārds un uzvārds: </span>
            <span class="info-value">{{ name || 'Nav pieejams' }}</span>
          </div>
          <div class="info-row">
            <span class="info-label">E-pasts: </span>
            <span class="info-value">{{ email }}</span>
          </div>
          <div class="support-box">
            <label class="support-label">Rakstiet support</label>
            <textarea v-model="supportText" class="support-input" placeholder="Uzrakstiet savu jautājumu vai ziņu šeit..."></textarea>
            <button class="btn-support" @click="sendSupportMessage">Sūtīt ziņu</button>
            <p v-if="supportStatus" class="support-status">{{ supportStatus }}</p>
          </div>
        </div>

        <div class="adoption-section">
          <h2>Izvēlētais dzīvnieks</h2>

          <div v-if="adoptions.length" class="adoption-list">
            <div class="adoption-card" v-for="adoption in adoptions" :key="adoption.id">
              <div class="animal-image">
                <img :src="adoption.animalImage" :alt="adoption.animalName" />
              </div>
              <div class="animal-details">
                <h3>{{ adoption.animalName }}</h3>
                <p><strong>Pieteikuma datums:</strong> {{ formatDate(adoption.submittedAt) }}</p>
                <button class="btn-release" @click="releaseAdoption(adoption.id)">Atteikties</button>
              </div>
            </div>
          </div>

          <p v-else class="no-adoption">Jums vēl nav izvēlēts dzīvnieks.</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AccountView',
  data() {
    return {
      name: '',
      email: '',
      adoptions: [],
      supportText: '',
      supportStatus: '',
      notifications: [],
      showNotifications: false,
      animalImages: {
        1: 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTglMbr5eE2rhr-f1qPDLurT4p5eT_f5OvlQQ&s',
        2: 'https://upload.wikimedia.org/wikipedia/commons/6/6e/Golde33443.jpg',
        3: 'https://upload.wikimedia.org/wikipedia/commons/7/74/A-Cat.jpg',
        4: 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTglMbr5eE2rhr-f1qPDLurT4p5eT_f5OvlQQ&s'
      }
    }
  },
  mounted() {
    const isLoggedIn = localStorage.getItem('userLoggedIn')
    if (!isLoggedIn) {
      this.$router.push('/login')
      return
    }
    this.email = localStorage.getItem('userEmail') || ''
    this.name = localStorage.getItem('userName') || ''

    if (!this.name && this.email) {
      const users = JSON.parse(localStorage.getItem('users') || '[]')
      const user = users.find(u => u.email === this.email)
      if (user && user.name) {
        this.name = user.name
        localStorage.setItem('userName', user.name)
      }
    }

    this.loadAdoptions()
    this.loadNotifications()
    window.addEventListener('storage', this.onStorageChange)
  },
  beforeUnmount() {
    window.removeEventListener('storage', this.onStorageChange)
  },
  computed: {
    unreadNotifications() {
      return this.notifications.filter(note => !note.read).length
    }
  },
  methods: {
    loadAdoptions() {
      const adoptions = JSON.parse(localStorage.getItem('adoptions') || '[]')
      this.adoptions = adoptions
        .filter(adoption => adoption.email === this.email)
        .map(adoption => ({
          ...adoption,
          animalImage: adoption.animalImage || this.getAnimalImageById(adoption.animalId)
        }))
    },
    loadNotifications() {
      const messages = JSON.parse(localStorage.getItem('contactMessages') || '[]')
      this.notifications = messages.filter(
        message => message.email === this.email && message.status === 'approved'
      )
    },
    onStorageChange(event) {
      if (event.key === 'contactMessages') {
        this.loadNotifications()
      }
    },
    toggleNotifications() {
      this.showNotifications = !this.showNotifications
      if (this.showNotifications) {
        this.markNotificationsRead()
      }
    },
    deleteNotification(id) {
      const messages = JSON.parse(localStorage.getItem('contactMessages') || '[]')
      const updated = messages.filter(message => message.id !== id)
      localStorage.setItem('contactMessages', JSON.stringify(updated))
      this.loadNotifications()
    },
    markNotificationsRead() {
      const messages = JSON.parse(localStorage.getItem('contactMessages') || '[]')
      const updated = messages.map(message => {
        if (message.email === this.email && message.status === 'approved' && !message.read) {
          return { ...message, read: true }
        }
        return message
      })
      localStorage.setItem('contactMessages', JSON.stringify(updated))
      this.loadNotifications()
    },
    formatDate(value) {
      if (!value) return ''
      return new Date(value).toLocaleString('lv-LV', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
    },
    getAnimalImageById(animalId) {
      return this.animalImages[animalId] || 'https://via.placeholder.com/180?text=Dzīvnieks'
    },
    sendSupportMessage() {
      if (!this.supportText.trim()) {
        this.supportStatus = 'Lūdzu, ierakstiet savu ziņu.'
        return
      }

      const messages = JSON.parse(localStorage.getItem('contactMessages') || '[]')
      messages.push({
        id: Date.now(),
        name: this.name || 'Nezināms',
        email: this.email || 'Nav e-pasta',
        message: this.supportText,
        selectedAnimals: this.adoptions.map(a => a.animalName).join(', ') || 'Nav izvēlēts dzīvnieks',
        sentAt: new Date().toISOString(),
        source: 'support',
        status: 'pending',
        read: false
      })
      localStorage.setItem('contactMessages', JSON.stringify(messages))
      this.supportText = ''
      this.supportStatus = 'Ziņa nosūtīta veiksmīgi. Admins to redzēs.'
      setTimeout(() => {
        this.supportStatus = ''
      }, 4000)
    },
    releaseAdoption(adoptionId) {
      const adoptions = JSON.parse(localStorage.getItem('adoptions') || '[]')
      const updated = adoptions.filter(adoption => adoption.id !== adoptionId)
      localStorage.setItem('adoptions', JSON.stringify(updated))
      this.adoptions = this.adoptions.filter(adoption => adoption.id !== adoptionId)
    },
    logout() {
      localStorage.removeItem('userLoggedIn')
      localStorage.removeItem('userEmail')
      localStorage.removeItem('userName')
      this.$router.push('/login')
    }
  }
}
</script>

<style scoped>
.account-container {
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  background: linear-gradient(135deg, #1A1A2E, #16213E);
  padding: 20px;
}

.account-box {
  background: linear-gradient(135deg, #FF6B35, #FFD23F);
  padding: 40px;
  border-radius: 30px;
  box-shadow: 0 10px 30px rgba(255, 107, 53, 0.5);
  max-width: 900px;
  width: 100%;
}

.account-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 20px;
  margin-bottom: 30px;
}

.account-header h1 {
  color: white;
  margin: 0;
  font-size: 2.2rem;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 12px;
}

.btn-notification {
  padding: 10px 18px;
  background: rgba(255, 255, 255, 0.12);
  color: white;
  border: 1px solid rgba(255, 255, 255, 0.3);
  border-radius: 16px;
  cursor: pointer;
  font-weight: 700;
}

.notification-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 24px;
  height: 24px;
  border-radius: 999px;
  background: #1A1A2E;
  color: #FFD23F;
  margin-left: 10px;
  font-size: 0.85rem;
}

.notification-panel {
  margin-top: 25px;
  padding: 20px;
  background: rgba(255, 255, 255, 0.14);
  border-radius: 20px;
}

.notification-item {
  padding: 14px 16px;
  background: rgba(255, 255, 255, 0.08);
  border-radius: 16px;
  margin-bottom: 12px;
}

.notification-meta {
  color: rgba(255, 255, 255, 0.75);
  font-size: 0.9rem;
  margin-top: 8px;
}

.user-email {
  color: rgba(255, 255, 255, 0.85);
  margin-top: 8px;
  display: block;
}

.user-name {
  color: rgba(255, 255, 255, 0.95);
  margin-top: 8px;
  display: block;
  font-size: 1.05rem;
  font-weight: 600;
}

.account-body {
  display: grid;
  gap: 30px;
}

@media (min-width: 920px) {
  .account-body {
    grid-template-columns: 1fr 1.1fr;
    align-items: start;
  }
}

.account-info {
  display: grid;
  gap: 15px;
  padding: 25px;
  background: rgba(255, 255, 255, 0.12);
  border-radius: 20px;
}

.support-box {
  display: grid;
  gap: 12px;
  margin-top: 10px;
}

.support-label {
  color: rgba(255, 255, 255, 0.8);
  font-size: 0.95rem;
}

.support-input {
  width: 100%;
  min-height: 120px;
  padding: 16px;
  border-radius: 16px;
  border: 1px solid rgba(255, 255, 255, 0.25);
  background: rgba(255, 255, 255, 0.12);
  color: white;
  resize: vertical;
}

.support-note {
  color: rgba(255, 255, 255, 0.7);
  font-size: 0.9rem;
  line-height: 1.4;
}

.info-value {
  color: white;
  font-size: 1.1rem;
  margin-top: 6px;
}

.adoption-section h2 {
  color: white;
  margin-bottom: 20px;
}

.adoption-list {
  display: grid;
  gap: 20px;
}

.adoption-card {
  display: flex;
  gap: 20px;
  align-items: center;
  background: rgba(255, 255, 255, 0.12);
  border-radius: 20px;
  overflow: hidden;
}

.animal-image {
  flex: 0 0 180px;
  min-width: 180px;
  height: 180px;
}

.animal-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.animal-details {
  padding: 20px;
  color: white;
  flex: 1;
}

.animal-details h3 {
  margin: 0 0 12px;
}

.animal-details p {
  margin: 8px 0;
}

.btn-release {
  padding: 12px 16px;
  background: rgba(255, 255, 255, 0.15);
  color: white;
  font-weight: bold;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-radius: 12px;
  cursor: pointer;
  transition: background 0.3s ease, border-color 0.3s ease;
  margin-top: 16px;
}

.btn-release:hover {
  background: rgba(255, 255, 255, 0.25);
  border-color: white;
}

.btn-support {
  width: 100%;
  padding: 14px 18px;
  margin-top: 25px;
  background: white;
  color: #1A1A2E;
  font-weight: 700;
  border: none;
  border-radius: 16px;
  cursor: pointer;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.btn-support:hover {
  transform: translateY(-2px);
  box-shadow: 0 14px 28px rgba(0, 0, 0, 0.18);
}
</style>
