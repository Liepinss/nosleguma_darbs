<template>
  <div class="home">
    <section class="hero">
      <div class="container">
        <h1 class="hero-title">Ņem un brauc kopā ar JOSIP GROUP</h1>
        <p class="hero-subtitle">Premium automašīnas jūsu komfortam</p>
        <div class="hero-img">
          <img 
            src="https://wallpapers.com/images/hd/yellow-porsche718-cayman-g-t4-side-view-vu9bdfhtyo0mmjus.jpg" 
            alt="Porsche Cayman GT4" 
          />
        </div>
      </div>
    </section>

    <section class="cars-section">
      <div class="container">
        <h2 class="section-title">Mašīnas mūsu uzņēmumā</h2>
        
        <div class="cards">
          <article 
            v-for="car in cars" 
            :key="car.id" 
            class="card"
          >
            <div class="card-img">
              <img :src="car.image" :alt="car.title" />
            </div>
            <div class="card-body">
              <h3>{{ car.title }}</h3>
              <p>{{ car.description }}</p>
              <button 
                class="btn" 
                @click="openModal(car)"
              >Uzzināt vairāk</button>
            </div>
          </article>
        </div>
      </div>
    </section>

    <section id="about" class="about-section">
      <div class="container">
        <h2 class="section-title">Par mums</h2>
        <p>JOSIP FAMILY CARS piedāvā premium klases automašīnas nomai un VIP transfēriem. Mēs garantējam kvalitāti, komfortu un individuālu pieeju katram klientam.</p>
      </div>
    </section>

    <transition name="modal">
      <div 
        v-if="modalOpen" 
        class="modal" 
        @click.self="closeModal"
      >
        <div class="modal-dialog">
          <button class="modal-close" @click="closeModal"><span>x</span></button>
          <div class="modal-media">
            <img :src="selectedCar.image" :alt="selectedCar.title" />
          </div>
          <div class="modal-content">
            <h3>{{ selectedCar.title }}</h3>
            <p>{{ selectedCar.fullDescription }}</p>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script>
export default {
  name: 'HomeView',
  data() {
    return {
      modalOpen: false,
      selectedCar: {},
      cars: [
        {
          id: 1,
          title: 'AUDI RS7',
          description: 'Ekonomisks un ērts pilsētai. Zems degvielas patēriņš.',
          fullDescription: 'Labs pretendents Citroen Ami. Jauda 40Kw, ka arī svarums 5000kg.',
          image: 'https://vehicle-images.dealerinspire.com/stock-images/chrome/077b8289802463398e6de305fe3388be.png'
        },
        {
          id: 2,
          title: 'MERCEDES G63',
          description: 'Augsts klīrenss un daudz vietas. Ideāls ģimenei vai ceļojumiem.',
          fullDescription: 'Stabils bezceļu apstākļos un ērts garos braucienos. Daudz vietas bagāžai. Leģendārs apvidus automobilis ar greznu salonu.',
          image: 'https://static.tcimg.net/vehicles/primary/900faa1b01db704a/2025-Mercedes-Benz-G-Class-black-full_color-driver_side_front_quarter.png?auto=format&fill=solid&fit=fill&h=202&pad=20&w=360'
        },
        {
          id: 3,
          title: 'BMW M5 G90',
          description: 'Stils un hasls. Brīvdienām un iespaidiem.',
          fullDescription: 'AMG, RS killer. Ar tādu brauc tikai true racers un programmeistari',
          image: 'https://www.bmw.is/content/dam/bmw/common/all-models/m-series/m5-sedan/2024/bmw-m-series-m5-sedan.png'
        },
        {
          id: 4,
          title: 'BMW X7',
          description: 'Super Idol car for real gangsteriem.',
          fullDescription: 'Mašīna kur var vest cilvēkus un kartupeļus',
          image: 'https://platform.cstatic-images.com/in/v2/stock_photos/0d852f0d-b173-412b-ac98-d4550bcb1a96/bfa04392-82ba-48ec-9a66-be64541db163.png'
        }
      ]
    }
  },
  methods: {
    openModal(car) {
      this.selectedCar = car
      this.modalOpen = true
      document.body.style.overflow = 'hidden'
    },
    closeModal() {
      this.modalOpen = false
      document.body.style.overflow = 'auto'
    }
  },
  beforeUnmount() {
    document.body.style.overflow = 'auto'
  }
}
</script>

<style scoped>
.hero {
  padding: 4rem 0 2rem;
  text-align: center;
}

.hero-title {
  font-size: 2.5rem;
  color: var(--yellow);
  margin-bottom: 1rem;
  font-weight: bold;
}

.hero-subtitle {
  font-size: 1.2rem;
  color: var(--white);
  margin-bottom: 2rem;
  opacity: 0.9;
}

.hero-img {
  max-width: 900px;
  margin: 0 auto;
  border-radius: 30px;
  overflow: hidden;
  box-shadow: 0 10px 40px rgba(255, 215, 0, 0.2);
}

.hero-img img {
  width: 100%;
  height: auto;
  display: block;
}

.cars-section {
  padding: 4rem 0;
}

.section-title {
  font-size: 2rem;
  color: var(--yellow);
  text-align: center;
  margin-bottom: 3rem;
  font-weight: bold;
}

.cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 9rem;
  margin-top: 2rem;
}

.card {
  background-color: var(--light-gray);
  border-radius: 25px;
  overflow: hidden;
  transition: transform 0.3s, box-shadow 0.3s;
  border: 2px solid transparent;
}

.card:hover {
  transform: translateY(-10px);
  box-shadow: 0 15px 40px rgba(255, 215, 0, 0.3);
  border-color: var(--yellow);
}

.card-img {
  background-color: var(--dark-gray);
  padding: 2rem;
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 200px;
}

.card-img img {
  width: 100%;
  height: auto;
  max-height: 180px;
  object-fit: contain;
}

.card-body {
  padding: 1.5rem;
}

.card-body h3 {
  color: var(--yellow);
  font-size: 1.5rem;
  margin-bottom: 1rem;
}

.card-body p {
  color: var(--white);
  margin-bottom: 1.5rem;
  line-height: 1.6;
}

.btn {
  background-color: var(--yellow);
  color: var(--black);
  border: none;
  padding: 0.8rem 2rem;
  border-radius: 25px;
  font-size: 1rem;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.3s;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.btn:hover {
  background-color: var(--white);
  transform: scale(1.05);
  box-shadow: 0 5px 15px rgba(255, 215, 0, 0.4);
}

.about-section {
  background-color: var(--dark-gray);
  padding: 4rem 0;
  border-radius: 30px;
  margin: 2rem auto;
  max-width: 1200px;
}

.about-section p {
  text-align: center;
  font-size: 1.1rem;
  line-height: 1.8;
  max-width: 800px;
  margin: 0 auto;
}

.modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.9);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000;
  padding: 20px;
}

.modal-dialog {
  background-color: var(--light-gray);
  border-radius: 30px;
  max-width: 700px;
  width: 100%;
  position: relative;
  overflow: hidden;
  border: 3px solid var(--yellow);
  animation: modalSlide 0.3s ease;
}

@keyframes modalSlide {
  from {
    transform: translateY(-50px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

.modal-close {
  position: absolute;
  top: 15px;
  right: 15px;
  background-color: var(--yellow);
  color: var(--black);
  border: none;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  font-size: 1.5rem;
  cursor: pointer;
  z-index: 1;
  transition: all 0.3s;
  font-weight: bold;
}
.modal-close span {
  top: -3px;
  position: relative;
}

.modal-close:hover {
  background-color: var(--white);
}

.modal-media {
  background-color: var(--dark-gray);
  padding: 2rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

.modal-media img {
  width: 100%;
  max-height: 300px;
  object-fit: contain;
}

.modal-content {
  padding: 2rem;
}

.modal-content h3 {
  color: var(--yellow);
  font-size: 2rem;
  margin-bottom: 1rem;
}

.modal-content p {
  color: var(--white);
  font-size: 1.1rem;
  line-height: 1.8;
}

.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

@media (max-width: 768px) {
  .hero-title {
    font-size: 1.8rem;
  }

  .hero-subtitle {
    font-size: 1rem;
  }

  .section-title {
    font-size: 1.5rem;
  }

  .cards {
    grid-template-columns: 1fr;
  }

  .modal-dialog {
    margin: 20px;
  }

  .modal-content h3 {
    font-size: 1.5rem;
  }
}
</style>