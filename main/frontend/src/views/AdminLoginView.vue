<template>
  <div class="admin-login-container">
    <div class="admin-login-box">
      <h1>🔐 Admin pierakstīšanās</h1>
      <form @submit.prevent="handleAdminLogin">
        <div class="form-group">
          <label for="password">Admin parole</label>
          <input 
            type="password" 
            id="password" 
            v-model="password" 
            placeholder="Ievadiet admin paroli"
            required
          />
        </div>
        <button type="submit" class="btn-submit">Pierakstīties</button>
        <p v-if="message" :class="messageType">{{ message }}</p>
      </form>
      <p class="back-link">
        <router-link to="/">← Atpakaļ uz sākumu</router-link>
      </p>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AdminLoginView',
  data() {
    return {
      password: '',
      message: '',
      messageType: '',
      adminPassword: 'adminRVT' 
    }
  },
  methods: {
    handleAdminLogin() {
      if (this.password === this.adminPassword) {
        localStorage.setItem('adminLoggedIn', 'true')
        this.message = 'Pierakstīšanās veiksmīga!'
        this.messageType = 'success'
        setTimeout(() => {
          this.$router.push('/admin')
        }, 1500)
      } else {
        this.message = 'Nepareiza parole!'
        this.messageType = 'error'
      }
    }
  }
}
</script>

<style scoped>
.admin-login-container {
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  background: linear-gradient(135deg, #1A1A2E, #16213E);
  padding: 20px;
}

.admin-login-box {
  background: linear-gradient(135deg, #FF6B35, #FFD23F);
  padding: 40px;
  border-radius: 30px;
  box-shadow: 0 10px 30px rgba(255, 107, 53, 0.5);
  max-width: 400px;
  width: 100%;
}

.admin-login-box h1 {
  text-align: center;
  color: white;
  margin-bottom: 30px;
  font-size: 2rem;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  color: white;
  font-weight: bold;
  margin-bottom: 8px;
}

.form-group input {
  width: 100%;
  padding: 12px;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  box-sizing: border-box;
}

.form-group input:focus {
  outline: none;
  box-shadow: 0 0 10px rgba(255, 255, 255, 0.3);
}

.btn-submit {
  width: 100%;
  padding: 12px;
  background: rgba(0, 0, 0, 0.3);
  color: white;
  font-weight: bold;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: background 0.3s ease;
  margin-top: 10px;
}

.btn-submit:hover {
  background: rgba(0, 0, 0, 0.5);
}

.message {
  margin-top: 15px;
  padding: 10px;
  border-radius: 8px;
  text-align: center;
  color: white;
}

.success {
  background-color: rgba(76, 175, 80, 0.3);
}

.error {
  background-color: rgba(244, 67, 54, 0.3);
}

.back-link {
  text-align: center;
  color: white;
  margin-top: 20px;
}

.back-link a {
  color: white;
  text-decoration: underline;
  font-weight: bold;
  transition: color 0.3s ease;
}

.back-link a:hover {
  color: #FFD23F;
}
</style>