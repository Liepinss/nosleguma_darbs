<template>
  <div id="app">
    <header id="home">
      <div class="container nav">
        <div class="brand">JOSIP FAMILY CARS</div>
        <button 
          class="hamburger" 
          @click="toggleMenu"
          :class="{ active: menuOpen }"
          aria-label="Toggle menu"
        >
          <span></span>
          <span></span>
          <span></span>
        </button>
        <nav :class="{ open: menuOpen }">
          <ul class="menu">
            <li><a href="#home" @click="closeMenu">Sākums</a></li>
            <li><a href="#services" @click="closeMenu">Pakalpojumi</a></li>
            <li><a href="#about" @click="closeMenu">Par mums</a></li>
            <li><a href="#contact" @click="closeMenu">Kontakti</a></li>
          </ul>
        </nav>
      </div>
    </header>

    <HomeView />

    <footer>
      <div class="container footer-wrap">
        <div>© {{ currentYear }} JOSIP FAMILY CARS. Visas tiesības aizsargātas.</div>
      </div>
    </footer>
  </div>
</template>

<script>
import HomeView from './views/HomeView.vue'

export default {
  name: 'App',
  components: {
    HomeView
  },
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
  --black: #000000;
  --yellow: #FFD700;
  --dark-gray: #1a1a1a;
  --light-gray: #2a2a2a;
  --white: #ffffff;
}

body {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background-color: var(--black);
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
}

header {
  background-color: var(--dark-gray);
  padding: 1rem 0;
  position: sticky;
  top: 0;
  z-index: 1000;
  border-bottom: 3px solid var(--yellow);
}

.nav {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.brand {
  font-size: 1.5rem;
  font-weight: bold;
  color: var(--yellow);
  letter-spacing: 2px;
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
  background-color: var(--yellow);
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
}

.menu a {
  color: var(--white);
  text-decoration: none;
  font-weight: 500;
  transition: color 0.3s;
  padding: 0.5rem 1rem;
  border-radius: 20px;
}

.menu a:hover {
  color: var(--yellow);
  background-color: rgba(255, 215, 0, 0.1);
}

footer {
  background-color: var(--dark-gray);
  padding: 2rem 0;
  margin-top: auto;
  border-top: 3px solid var(--yellow);
  text-align: center;
}

.footer-wrap {
  color: var(--white);
}

@media (max-width: 768px) {
  .hamburger {
    display: flex;
  }

  nav {
    position: fixed;
    top: 0;
    right: -100%;
    height: 100vh;
    width: 70%;
    max-width: 300px;
    background-color: var(--dark-gray);
    transition: right 0.3s ease;
    padding-top: 80px;
    border-left: 3px solid var(--yellow);
  }

  nav.open {
    right: 0;
  }

  .menu {
    flex-direction: column;
    gap: 0;
    padding: 2rem;
  }

  .menu li {
    margin: 0.5rem 0;
  }

  .menu a {
    display: block;
    padding: 1rem;
  }
}
</style>