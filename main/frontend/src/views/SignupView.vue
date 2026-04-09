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
      <p v-if="message" :class="messageType === 'success' ? 'success-message' : 'error-message'">{{ message }}</p>
      <p class="switch-form">
        Jau ir konts? <router-link to="/login">Pierakstieties</router-link>
      </p>
    </div>
  </div>
</template>

<script>
import { firstValidationMessage } from '@/api/http'
import { register } from '@/api/authApi'

export default {
  name: 'SignupView',
  data() {
    return {
      name: '',
      email: '',
      password: '',
      confirmPassword: '',
      message: '',
      messageType: ''
    }
  },
  methods: {
    async handleSignup() {
      if (this.password !== this.confirmPassword) {
        this.message = 'Paroles nesakrīt.'
        this.messageType = 'error'
        return
      }

      this.message = ''
      try {
        await register({
          name: this.name.trim(),
          email: this.email.trim(),
          password: this.password,
        })
        this.message = 'Reģistrācija veiksmīga! Lūdzu, pieslēdzieties.'
        this.messageType = 'success'
        setTimeout(() => {
          this.$router.push('/login')
        }, 1000)
      } catch (e) {
        this.message = firstValidationMessage(e)
        this.messageType = 'error'
      }
    },
  }
}
</script>

<style scoped>
.signup-container {
  flex: 1;
  display: flex;
  justify-content: center;
  align-items: center;
  padding: clamp(1.5rem, 4vw, 2.5rem) 20px;
  background:
    radial-gradient(ellipse 75% 55% at 50% 0%, rgba(120, 45, 30, 0.38), transparent 55%),
    linear-gradient(168deg, #0c0d12 0%, #0a0a0c 100%);
}

.signup-box {
  width: 100%;
  max-width: 420px;
  padding: clamp(1.75rem, 4vw, 2.5rem);
  border-radius: 24px;
  background: linear-gradient(165deg, #161a22 0%, #0e1118 100%);
  border: 1px solid var(--hp-line-strong, rgba(255, 255, 255, 0.18));
  box-shadow: 0 24px 64px rgba(0, 0, 0, 0.45);
}

.signup-box h1 {
  font-family: 'Playfair Display', Georgia, serif;
  text-align: center;
  color: var(--hp-text, #f4f4f5);
  margin-bottom: 1.5rem;
  font-size: clamp(1.65rem, 4vw, 2rem);
  font-weight: 600;
}

.form-group {
  margin-bottom: 1.1rem;
}

.form-group label {
  display: block;
  color: rgba(255, 255, 255, 0.88);
  font-weight: 600;
  font-size: 0.88rem;
  margin-bottom: 0.45rem;
}

.form-group input {
  width: 100%;
  padding: 0.8rem 1rem;
  border: 1.5px solid var(--hp-line-strong, rgba(255, 255, 255, 0.18));
  border-radius: 12px;
  font-size: 1rem;
  background: rgba(0, 0, 0, 0.28);
  color: var(--hp-text, #f4f4f5);
  transition:
    border-color 0.2s ease,
    box-shadow 0.2s ease;
}

.form-group input::placeholder {
  color: rgba(255, 255, 255, 0.35);
}

.form-group input:focus {
  outline: none;
  border-color: var(--hp-gold, #f5cc4c);
  box-shadow: 0 0 0 3px rgba(245, 204, 76, 0.12);
}

.btn-submit {
  width: 100%;
  padding: 0.85rem 1rem;
  margin-top: 0.5rem;
  border: none;
  border-radius: 999px;
  font-weight: 700;
  font-size: 1rem;
  color: #fff;
  cursor: pointer;
  background: linear-gradient(180deg, var(--hp-orange, #ff5722) 0%, var(--hp-orange-deep, #e64a19) 100%);
  box-shadow: 0 6px 22px rgba(255, 87, 34, 0.35);
  transition:
    transform 0.2s ease,
    box-shadow 0.2s ease;
}

.btn-submit:hover {
  transform: translateY(-1px);
  box-shadow: 0 8px 28px rgba(255, 87, 34, 0.42);
}

.switch-form {
  text-align: center;
  color: var(--hp-muted, rgba(255, 255, 255, 0.7));
  margin-top: 1.25rem;
  font-size: 0.92rem;
}

.switch-form a {
  color: var(--hp-gold, #f5cc4c);
  text-decoration: none;
  font-weight: 700;
}

.switch-form a:hover {
  text-decoration: underline;
}

.error-message {
  color: #fca5a5;
  text-align: center;
  margin-top: 1rem;
  font-weight: 600;
  font-size: 0.92rem;
}

.success-message {
  color: #86efac;
  text-align: center;
  margin-top: 1rem;
  font-weight: 600;
  font-size: 0.92rem;
}
</style>