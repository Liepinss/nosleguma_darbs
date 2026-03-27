<template>
  <div class="signup-container">
    <div class="signup-box">
      <h1>Reģistrējieties</h1>
      <form @submit.prevent="handleSignup">
        <div class="form-group">
          <label for="name">Vārds</label>
          <input 
            type="text" 
            id="name" 
            v-model="name" 
            placeholder="Ievadiet vārdu"
            required
            class="vards"
          />
        </div>
        <div class="form-group">
          <label for="email">E-pasts</label>
          <input 
            type="email" 
            id="email" 
            v-model="email" 
            placeholder="Ievadiet e-pastu"
            required
          />
        </div>
        <div class="form-group">
          <label for="password">Parole</label>
          <input 
            type="password" 
            id="password" 
            v-model="password" 
            placeholder="Ievadiet paroli"
            required
          />
        </div>
        <div class="form-group">
          <label for="confirm-password">Apstiprinājuma parole</label>
          <input 
            type="password" 
            id="confirm-password" 
            v-model="confirmPassword" 
            placeholder="Atkārtojiet paroli"
            required
          />
        </div>
        <button type="submit" class="btn-submit">Reģistrējieties</button>
      </form>
      <p class="switch-form">
        Jau ir konts? <router-link to="/login">Pierakstieties</router-link>
      </p>
    </div>
  </div>
</template>

<script>
export default {
  name: 'SignupView',
  data() {
    return {
      name: '',
      email: '',
      password: '',
      confirmPassword: ''
    }
  },
  methods: {
    handleSignup() {
      if (this.password !== this.confirmPassword) {
        alert('Paroles nesakrīt!')
        return
      }
      // Handle signup logic here
      console.log('Signup:', { name: this.name, email: this.email, password: this.password })
      // You can add API call here
    }
  }
}
</script>

<style scoped>
.signup-container {
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  background: linear-gradient(135deg, #1A1A2E, #16213E);
  padding: 20px;
}

.signup-box {
  background: linear-gradient(135deg, #FF6B35, #FFD23F);
  padding: 40px;
  border-radius: 30px;
  box-shadow: 0 10px 30px rgba(255, 107, 53, 0.5);
  max-width: 400px;
  width: 100%;
}
.vards {
  color: white;
  text-align: flex-start;
  background-color: white;
}

.signup-box h1 {
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
  transition: box-shadow 0.4s ease;
  box-shadow: 0 0 5px rgba(247, 245, 245, 0.878);
}

.form-group input:focus {
  outline: none;
  box-shadow: 0 0 10px rgba(255, 255, 255, 0.97);
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

.switch-form {
  text-align: center;
  color: white;
  margin-top: 20px;
}

.switch-form a {
  color: white;
  text-decoration: underline;
  font-weight: bold;
  transition: color 0.3s ease;
}

.switch-form a:hover {
  color: #FFD23F;
}

.error-message {
  color: #ff4444;
  text-align: center;
  margin-top: 15px;
  font-weight: bold;
}

.success-message {
  color: #44ff44;
  text-align: center;
  margin-top: 15px;
  font-weight: bold;
}
</style>