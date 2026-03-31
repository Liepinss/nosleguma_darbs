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
        <button 
          :class="{ active: activeTab === 'animals' }"
          @click="activeTab = 'animals'"
          class="tab-btn"
        >
          Dzīvnieki
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
              <span v-else-if="message.status === 'approved'" class="message-approved">Apstiprināts</span>
              <span v-else-if="message.status === 'declined'" class="message-declined">Noraidīts</span>
              <button @click="deleteMessage(message.id)" class="btn-delete">Dzēst</button>
            </div>
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
            <div>
              <strong>{{ animal.name }}</strong> — {{ animal.gender }}
              <p>{{ animal.description }}</p>
            </div>
            <button @click="deleteAnimal(animal.id)" class="btn-delete">Dzēst</button>
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
      adoptions: [],
      storedAnimals: [],
      animalForm: {
        name: '',
        gender: '',
        description: '',
        image: ''
      }
    }
  },
  mounted() {
    this.loadData()
    this.loadAnimals()
    window.addEventListener('storage', this.loadData)
  },
  beforeUnmount() {
    window.removeEventListener('storage', this.loadData)
  },
  methods: {
    loadData() {
      this.messages = JSON.parse(localStorage.getItem('contactMessages')) || []
      this.adoptions = JSON.parse(localStorage.getItem('adoptions')) || []
    },
    loadAnimals() {
      this.storedAnimals = JSON.parse(localStorage.getItem('animals')) || []
    },
    addAnimal() {
      if (!this.animalForm.name || !this.animalForm.image) {
        return
      }
      const animals = JSON.parse(localStorage.getItem('animals')) || []
      const newAnimal = {
        id: Date.now(),
        name: this.animalForm.name,
        species: this.animalForm.species || 'Cits',
        gender: this.animalForm.gender || 'Nav norādīts',
        description: this.animalForm.description || 'Nav apraksta',
        image: this.animalForm.image
      }
      animals.push(newAnimal)
      localStorage.setItem('animals', JSON.stringify(animals))
      this.loadAnimals()
      this.animalForm = {
        name: '',
        gender: '',
        description: '',
        image: ''
      }
    },
    deleteAnimal(id) {
      const animals = JSON.parse(localStorage.getItem('animals')) || []
      const updated = animals.filter(animal => animal.id !== id)
      localStorage.setItem('animals', JSON.stringify(updated))
      this.loadAnimals()
    },
    formatDate(dateString) {
      const date = new Date(dateString)
      return date.toLocaleString('lv-LV')
    },
    deleteMessage(id) {
      this.messages = this.messages.filter(m => m.id !== id)
      localStorage.setItem('contactMessages', JSON.stringify(this.messages))
      window.dispatchEvent(new Event('contactMessagesUpdated'))
    },
    approveMessage(id) {
      const messages = JSON.parse(localStorage.getItem('contactMessages') || '[]')
      const updated = messages.map(message => {
        if (message.id === id) {
          return {
            ...message,
            status: 'approved',
            approvedAt: new Date().toISOString(),
            moderatedAt: new Date().toISOString(),
            read: false
          }
        }
        return message
      })
      localStorage.setItem('contactMessages', JSON.stringify(updated))
      window.dispatchEvent(new Event('contactMessagesUpdated'))
      this.loadData()
    },
    declineMessage(id) {
      const messages = JSON.parse(localStorage.getItem('contactMessages') || '[]')
      const updated = messages.map(message => {
        if (message.id === id) {
          return {
            ...message,
            status: 'declined',
            moderatedAt: new Date().toISOString(),
            read: false
          }
        }
        return message
      })
      localStorage.setItem('contactMessages', JSON.stringify(updated))
      window.dispatchEvent(new Event('contactMessagesUpdated'))
      this.loadData()
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

.tab-btn:hover {
  background: rgba(255, 107, 53, 0.2);
}

.btn-save {
  width: 100%;
  padding: 14px 18px;
  background: linear-gradient(135deg, #FF6B35, #FFD23F);
  border: none;
  border-radius: 999px;
  margin: 7px;
  color: #08121f;
  font-size: 1rem;
  font-weight: 700;
  cursor: pointer;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  box-shadow: 0 12px 24px rgba(255, 107, 53, 0.24);
}

.btn-save:hover {
  transform: translateY(-2px);
  box-shadow: 0 16px 28px rgba(255, 107, 53, 0.3);
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

.btn-approve {
  padding: 8px 16px;
  background: rgba(255, 255, 255, 0.15);
  color: #1A1A2E;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  margin-top: 10px;
  margin-right: 10px;
  font-weight: bold;
}

.btn-approve:hover {
  background: rgba(255, 255, 255, 0.3);
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