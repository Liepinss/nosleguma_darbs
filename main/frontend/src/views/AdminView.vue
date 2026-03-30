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
          :class="{ active: activeTab === 'adoptions' }"
          @click="activeTab = 'adoptions'"
          class="tab-btn"
        >
          Adopcijas pieteikumi
        </button>
      </div>

      <!-- MESSAGES TAB -->
      <div v-if="activeTab === 'messages'" class="tab-content">
        <h2>Kontaktu ziņojumi</h2>
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
            <p><strong>Ziņojums:</strong></p>
            <p class="message-text">{{ message.message }}</p>
            <button @click="deleteMessage(message.id)" class="btn-delete">Dzēst</button>
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
    </div>
  </div>
</template>

<script>
export default {
  name: 'AdminView',
  data() {
    return {
      activeTab: 'messages',
      messages: [],
      adoptions: []
    }
  },
  mounted() {
    this.loadData()
  },
  methods: {
    loadData() {
      this.messages = JSON.parse(localStorage.getItem('contactMessages')) || []
      this.adoptions = JSON.parse(localStorage.getItem('adoptions')) || []
    },
    formatDate(dateString) {
      const date = new Date(dateString)
      return date.toLocaleString('lv-LV')
    },
    deleteMessage(id) {
      this.messages = this.messages.filter(m => m.id !== id)
      localStorage.setItem('contactMessages', JSON.stringify(this.messages))
    },
    deleteAdoption(id) {
      this.adoptions = this.adoptions.filter(a => a.id !== id)
      localStorage.setItem('adoptions', JSON.stringify(this.adoptions))
    },
    logout() {
      localStorage.removeItem('adminLoggedIn')
      this.$router.push('/admin-login')
    }
  }
}
</script>

<style scoped>
.admin-container {
  min-height: 100vh;
  background: linear-gradient(135deg, #1A1A2E, #16213E);
  padding: 20px;
  color: red;
}

.admin-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
  padding-bottom: 20px;
  border-bottom: 3px solid #FF6B35;
}

.admin-header h1 {
  color: #FF6B35;
  font-size: 2rem;
}

.btn-logout {
  padding: 9px 20px;
  background: #f60b0b;
  color: white;
  border: none;
  border-radius: 20px;
  cursor: pointer;
  font-weight: bold;
  transition: background 0.3s ease;
}

.btn-logout:hover {
  background: #d63a52;
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
  padding: 12px 24px;
  background: transparent;
  color: white;
  border: 2px solid #FF6B35;
  border-radius: 20px;
  cursor: pointer;
  font-weight: bold;
  transition: all 0.3s ease;
}

.tab-btn.active {
  background: #FF6B35;
  color: white;
}

.tab-btn:hover {
  background: rgba(255, 107, 53, 0.2);
}

.tab-content h2 {
  color: #FFD23F;
  margin-bottom: 20px;
  font-size: 1.5rem;
}

.no-data {
  text-align: center;
  padding: 40px;
  background: rgba(255, 107, 53, 0.1);
  border-radius: 10px;
  color: rgba(255, 255, 255, 0.6);
}

.messages-list,
.adoptions-list {
  display: grid;
  gap: 20px;
}

.message-item,
.adoption-item {
  background: linear-gradient(135deg, #FF6B35, #FFD23F);
  padding: 20px;
  border-radius: 15px;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
}

.message-header,
.adoption-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
  padding-bottom: 10px;
  border-bottom: 2px solid rgba(0, 0, 0, 0.2);
  color: white;
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
  background: rgba(0, 0, 0, 0.1);
  padding: 10px;
  border-radius: 8px;
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