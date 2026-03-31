<template>
  <div id="app">
    <header id="home">
      <div class="container nav">
        <div class="brand">ADOPTĀCIJAS CENTRS</div>

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
            <li><router-link to="/" :to="`/#start`" @click="closeMenu">Sākums</router-link></li>
            <li><a href="#animals" @click="closeMenu">Dzīvnieki</a></li>
            <li><a href="#about" @click="closeMenu">Par mums</a></li>
            <li><a href="#contact" @click="closeMenu">Kontakti</a></li>
          
          </ul>
        </nav>

        <div class="auth-buttons">
          <router-link to="/login" class="btn-login">Pierakstīties</router-link>
          <router-link to="/signup" class="btn-signup">Reģistrējieties</router-link>
          <router-link to="/admin-login" class="btn-admin">Admin</router-link>
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
      currentYear: new Date().getFullYear()
    }
  },
  methods: {
    toggleMenu() {
      this.menuOpen = !this.menuOpen
    },
    closeMenu() {
      this.menuOpen = false
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

.brand {
  font-size: 1.5rem;
  font-weight: bold;
  color: var(--primary);
  letter-spacing: 2px;
  white-space: nowrap;
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