<template>
  <div class="home">

    <section class="hero">
      <div class="container">
        <h1 class="hero-title">Dzīvnieku adoptācijas centrs</h1>
        <p class="hero-subtitle">Dod mājas tiem, kam tās vajag</p>

        <div class="hero-img">
          <img
            src="https://images.unsplash.com/photo-1548199973-03cce0bbc87b"
            alt="animals"
          />
        </div>
      </div>
    </section>

    <!-- DZĪVNIEKU SEKCIJA -->
    <section class="animals-section" id="animals">
      <div class="container">
        <h2 class="section-title">Dzīvnieki adopcijai</h2>

        <!-- Search Bar -->
        <div class="search-container">
          <input 
            v-model="searchQuery" 
            type="text" 
            placeholder="Meklēt dzīvnieku pēc vārda, dzimuma vai apraksta..."
            class="search-bar"
          />
        </div>

        <div class="cards">
          <article
            v-for="animal in filteredAnimals"
            :key="animal.id"
            class="card"
          >

            <div class="card-img">
              <img :src="animal.image" />
            </div>

            <div class="card-body">
              <h3>{{ animal.name }}</h3>
              <p>{{ animal.description }}</p>
              <p><strong>Dzimums:</strong> {{ animal.gender }}</p>

              <button
                class="btn"
                @click="openModal(animal)"
              >
                Skatīt vairāk
              </button>

            </div>

          </article>

        </div>
      </div>
    </section>

    <!-- PAR SEKCIJU -->
    <section class="about-section" id="about">
      <div class="container">
        <h2 class="section-title">Par mums</h2>

        <div class="about-content">
          <div class="about-text">
            <p>
              Mēs esam <strong>Dzīvnieku Adoptācijas Centrs</strong>, kas veltīts pamesto un vardarbīgi apkalpoto dzīvnieku glābšanai un aprūpei.
            </p>
            <p>
              Mūsu misija ir atrast mīļas un draudzīgas mājas katram dzīvniekam, kas nonāk mūsu centrā. Mēs ticam, ka katrs dzīvnieks ir vērts mīlestības un aprūpes.
            </p>
            <h3>Mūsu pakalpojumi:</h3>
            <ul class="services-list">
              <li>✓ Draudzīga un droša vide dzīvniekiem</li>
              <li>✓ Veterinārā aprūpe un vakcinācija</li>
              <li>✓ Personīga mācīšana un socializācija</li>
              <li>✓ Pēcadopcijas atbalsts un konsultācijas</li>
            </ul>
            <p>
              Ja jūs meklējat jaunu draudzīgu biedru savai ģimenei, mūs lūdzam apmeklēt mūsu adoptācijas centru un iepazīties ar mūsu brīnumainajiem dzīvniekiem!
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- KONATKTU SEKCIJA -->
    <section class="contact-section" id="contact">
      <div class="container">
        <h2 class="section-title">Kontakti</h2>

        <div class="contact-content">
          <div class="contact-info">
            <div class="contact-item">
              <h3>📍 Adrese</h3>
              <p>Dzīvnieku Adoptācijas Centrs<br>Torņukalns, Mārupes iela 19, Rīga, LV-1002<br>Latvija</p>
            </div>
            <div class="contact-item">
              <h3>📞 Telefons</h3>
              <p>+371 29 123 456</p>
            </div>
            <div class="contact-item">
              <h3>📧 E-pasts</h3>
              <p><a href="mailto:kontakti@dzivniekucentrs.lv">Dīvniekucentrs@gamil.com</a></p>
            </div>
            <div class="contact-item">
              <h3>🕐 Darba laiks</h3>
              <p>Pirmdiena - Piektdiena: 10:00 - 18:00<br>
                Sestdiena: 10:00 - 16:00<br>
                Svētdiena: Slēgts</p>
            </div>
          </div>

          <div class="contact-form">
            <h3>Sūtiet mums jautājumus</h3>
            <form @submit.prevent="sendMessage">
              <div class="form-group">
                <input 
                  v-model="contactForm.name" 
                  type="text" 
                  placeholder="Jūsu vārds"
                  required
                />
              </div>
              <div class="form-group">
                <input 
                  v-model="contactForm.email" 
                  type="email" 
                  placeholder="Jūsu e-pasts"
                  required
                />
              </div>
              <div class="form-group">
                <textarea 
                  v-model="contactForm.message" 
                  placeholder="Jūsu ziņojums"
                  rows="5"
                  required
                ></textarea>
              </div>
              <button type="submit" class="btn-send">Sūtīt</button>
            </form>
            <p v-if="contactMessage" :class="contactMessageType">{{ contactMessage }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- MODELIS PRIEKS DZĪVNIEKU DETALĀM -->
    <transition name="modal">

      <div
        v-if="modalOpen && !adoptionModalOpen"
        class="modal"
        @click.self="closeModal"
      >

        <div class="modal-dialog">

          <button
            class="modal-close"
            @click="closeModal"
          >
            ×
          </button>

          <div class="modal-media">
            <img :src="selectedAnimal.image" />
          </div>

          <div class="modal-content">
            <h3>{{ selectedAnimal.name }}</h3>
            <p>{{ selectedAnimal.description }}</p>
            <p><strong>Dzimums:</strong> {{ selectedAnimal.gender }}</p>
            <button class="btn-adopt" @click="openAdoptionModal">
              Pieteikties adopcijai
            </button>
          </div>

        </div>

      </div>

    </transition>

    <!-- ADOPTION FORM MODAL -->
    <transition name="modal">

      <div
        v-if="adoptionModalOpen"
        class="modal"
        @click.self="closeAdoptionModal"
      >

        <div class="modal-dialog">

          <button
            class="modal-close"
            @click="closeAdoptionModal"
          >
            ×
          </button>

          <div class="modal-content">
            <h3>Pieteikšanās {{ selectedAnimal.name }} adopcijai</h3>
            
            <form @submit.prevent="submitAdoption">
              <div class="form-group">
                <label>Jūsu vārds</label>
                <input 
                  v-model="adoptionForm.name" 
                  type="text"
                  required
                />
              </div>

              <div class="form-group">
                <label>E-pasts</label>
                <input 
                  v-model="adoptionForm.email" 
                  type="email"
                  required
                />
              </div>

              <div class="form-group">
                <label>Telefons</label>
                <input 
                  v-model="adoptionForm.phone" 
                  type="tel"
                  required
                />
              </div>

              <div class="form-group">
                <label>dzīvojamā vieta</label>
                <input 
                  v-model="adoptionForm.address" 
                  type="text"
                  placeholder="Pilsēta, iela"
                  required
                />
              </div>

              <div class="form-group">
                <label>Vai ir pieredze ar dzīvniekiem? Ja jā, padalies ar mums.</label>
                <textarea 
                  v-model="adoptionForm.experience" 
                  placeholder="Pastāstiet par savu pieredzi"
                  rows="4"
                  required
                ></textarea>
              </div>

              <button type="submit" class="btn-send">Pieteikties</button>
            </form>

            <p v-if="adoptionMessage" :class="adoptionMessageType">{{ adoptionMessage }}</p>
          </div>

        </div>

      </div>

    </transition>

  </div>
</template>

<script>
export default {

  data() {
    return {

      modalOpen: false,
      adoptionModalOpen: false,
      searchQuery: '',

      selectedAnimal: {},

      animals: [
        {
          id: 1,
          name: "Reksis",
          gender: "Vīrietis",
          description: "Draudzīgs un enerģisks suns, ideāls ģimenes draugs",
          image: "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTglMbr5eE2rhr-f1qPDLurT4p5eT_f5OvlQQ&s"
        },
        {
          id: 2,
          name: "Šoko",
          gender: "Vīrietis",
          description: "Mīļš un mierīgs suns, ideāls senioru kompanija",
          image: "https://upload.wikimedia.org/wikipedia/commons/6/6e/Golde33443.jpg"
        },
        {
          id: 3,
          name: "Anna",
          gender: "Sieviete",
          description: "Jauns un rotaļīgs kaķēns, pilns ar enerģiju",
          image: "https://upload.wikimedia.org/wikipedia/commons/7/74/A-Cat.jpg"
        },
        {
          id: 4,
          name: "Dingo",
          gender: "vīritis",
          description: "Draudzīgs un enerģisks suns, ideāls ģimenes draugs",
          image: "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTglMbr5eE2rhr-f1qPDLurT4p5eT_f5OvlQQ&s"
        },
      ],
      adoptionForm: {
        name: '',
        email: '',
        phone: '',
        address: '',
        experience: ''
      },

      adoptionMessage: '',
      adoptionMessageType: '',

      contactForm: {
        name: '',
        email: '',
        message: ''
      },

      contactMessage: '',
      contactMessageType: ''

    }
  },

  computed: {
    filteredAnimals() {
      if (!this.searchQuery) return this.animals
      
      const query = this.searchQuery.toLowerCase()
      return this.animals.filter(animal => 
        animal.name.toLowerCase().includes(query) || 
        animal.gender.toLowerCase().includes(query) ||
        animal.description.toLowerCase().includes(query)
      )
    }
  },

  methods: {

    openModal(animal) {
      this.selectedAnimal = animal
      this.modalOpen = true
      this.adoptionModalOpen = false
    },

    closeModal() {
      this.modalOpen = false
    },

    openAdoptionModal() {
      this.adoptionModalOpen = true
    },

    closeAdoptionModal() {
      this.adoptionModalOpen = false
      this.adoptionForm = {
        name: '',
        email: '',
        phone: '',
        address: '',
        experience: ''
      }
      this.adoptionMessage = ''
    },

    submitAdoption() {
      // Save adoption application to localStorage
      const adoptions = JSON.parse(localStorage.getItem('adoptions')) || []
      adoptions.push({
        id: Date.now(),
        animalName: this.selectedAnimal.name,
        animalId: this.selectedAnimal.id,
        ...this.adoptionForm,
        submittedAt: new Date().toISOString()
      })
      localStorage.setItem('adoptions', JSON.stringify(adoptions))

      this.adoptionMessage = 'Paldies! Jūsu pieteikums tika saņemts. Mēs ar jums sazināsimies drīzumā!'
      this.adoptionMessageType = 'success'

      // Reset form
      this.adoptionForm = {
        name: '',
        email: '',
        phone: '',
        address: '',
        experience: ''
      }

      // AIZVĒRT ADOPCIJAS MODALI PĒC 3 SEKUNDĒM
      setTimeout(() => {
        this.closeAdoptionModal()
        this.closeModal()
      }, 3000)
    },

    sendMessage() {
      // SAGLABA ZIŅOJUMU LOCALSTORAGE
      const messages = JSON.parse(localStorage.getItem('contactMessages')) || []
      messages.push({
        id: Date.now(),
        ...this.contactForm,
        sentAt: new Date().toISOString()
      })
      localStorage.setItem('contactMessages', JSON.stringify(messages))

      this.contactMessage = 'Paldies! Jūsu ziņojums tika sūtīts veiksmīgi!'
      this.contactMessageType = 'success'

      // Reset form
      this.contactForm = {
        name: '',
        email: '',
        message: ''
      }

      // IZDZĒSTI ZIŅOJUMA PĒC 3 SEKUNDĒM
      setTimeout(() => {
        this.contactMessage = ''
      }, 3000)
    }

  }

}
</script>

<style scoped>

.hero {
  padding: 40px;
  text-align: center;
}

.hero-title {
  font-size: 40px;
  color:#FF6B35;
}

.hero-subtitle {
  color: #df6335db;
}

.hero-img img {
  width: 100%;
  max-width: 600px;
  border-radius: 20px;
}

.section-title {
  text-align: center;
  color: #FF6B35;
  margin: 30px;
  font-size: 2rem;
}

.animals-section {
  padding: 50px 20px;
  background-color: var(--dark-bg);
}

/* SEARCH BAR */
.search-container {
  display: flex;
  justify-content: center;
  margin-bottom: 30px;
}

.search-bar {
  width: 100%;
  max-width: 500px;
  padding: 12px 20px;
  border: 2px solid #FF6B35;
  border-radius: 25px;
  font-size: 1rem;
  background: rgba(255, 107, 53, 0.1);
  color: white;
}

.search-bar::placeholder {
  color: rgba(255, 255, 255, 0.6);
}

.search-bar:focus {
  outline: none;
  background: rgba(255, 107, 53, 0.2);
  box-shadow: 0 0 10px rgba(255, 107, 53, 0.5);
}

.cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, 250px);
  gap: 20px;
  justify-content: center;
}

.card {
  background: linear-gradient(135deg, #FF6B35, #FFD23F);
  border-radius: 15px;
  padding: 10px;
  box-shadow: 0 0 10px #7596ea;
}

.card img {
  width: 100%;
  height: 200px;
  object-fit: cover;
  border-radius: 10px;
}

.card-body {
  padding: 10px;
  color: white;
}

.card-body h3 {
  margin: 10px 0 5px 0;
}

.btn {
  background: #2928273c;
  color: white;
  border: none;
  width: 70px; 
  height: 50px;
  border-radius: 40px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  padding: 0;
  margin-top: 10px;
}

/* ABOUT SECTION */
.about-section {
  padding: 50px 20px;
  background: linear-gradient(135deg, #1A1A2E, #16213E);
}

.about-content {
  max-width: 800px;
  margin: 0 auto;
}

.about-text {
  color: white;
  font-size: 1.1rem;
  line-height: 1.8;
}

.about-text p {
  margin-bottom: 20px;
  text-align: center;
}

.about-text h3 {
  color: #FF6B35;
  margin: 30px 0 15px 0;
  text-align: center;
}

.services-list {
  list-style: none;
  padding: 0;
  margin-bottom: 20px;
}

.services-list li {
  padding: 10px;
  margin: 5px 0;
  background: rgba(255, 107, 53, 0.1);
  border-left: 4px solid #FF6B35;
  border-radius: 5px;
  color: white;
}

/* CONTACT SECTION */
.contact-section {
  padding: 50px 20px;
  background-color: var(--dark-bg);
}

.contact-content {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 40px;
  max-width: 1000px;
  margin: 0 auto;
}

.contact-info {
  color: white;
}

.contact-item {
  margin-bottom: 30px;
  padding: 20px;
  background: rgba(255, 107, 53, 0.1);
  border-left: 4px solid #FF6B35;
  border-radius: 8px;
}

.contact-item h3 {
  color: #FF6B35;
  margin-bottom: 10px;
}

.contact-item p {
  margin: 5px 0;
}

.contact-item a {
  color: #FFD23F;
  text-decoration: none;
}

.contact-item a:hover {
  text-decoration: underline;
}

.contact-form {
  background: linear-gradient(135deg, #FF6B35, #FFD23F);
  padding: 30px;
  border-radius: 15px;
  box-shadow: 0 10px 30px rgba(255, 107, 53, 0.5);
}

.contact-form h3 {
  color: white;
  margin-bottom: 20px;
  text-align: center;
}

.form-group {
  margin-bottom: 15px;
}

.form-group label {
  display: block;
  color: white;
  font-weight: bold;
  margin-bottom: 5px;
}

.contact-form input,
.contact-form textarea,
.modal-dialog input,
.modal-dialog textarea {
  width: 100%;
  padding: 12px;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-family: inherit;
  box-sizing: border-box;
}

.contact-form input:focus,
.contact-form textarea:focus,
.modal-dialog input:focus,
.modal-dialog textarea:focus {
  outline: none;
  box-shadow: 0 0 10px rgba(255, 255, 255, 0.3);
}

.btn-send {
  width: 100%;
  padding: 12px;
  background: rgba(0, 0, 0, 0.3);
  color: white;
  font-weight: bold;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: background 0.3s ease;
}

.btn-send:hover {
  background: rgba(0, 0, 0, 0.5);
}

.success {
  margin-top: 15px;
  padding: 10px;
  background-color: rgba(76, 175, 80, 0.3);
  color: white;
  border-radius: 8px;
  text-align: center;
}

/* MODAL */
.modal {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.7);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 100;
}

.modal-dialog {
  background: linear-gradient(135deg, #FF6B35, #FFD23F);
  padding: 30px;
  border-radius: 30px;
  max-width: 500px;
  width: 90%;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 10px 30px rgba(255, 107, 53, 0.5);
  animation: bounceIn 0.5s ease-out;
  position: relative;
}

@keyframes bounceIn {
  0% {
    transform: scale(0.3);
    opacity: 0;
  }
  50% {
    transform: scale(1.05);
  }
  100% {
    transform: scale(1);
    opacity: 1;
  }
}

.modal-close {
  position: absolute;
  top: 15px;
  right: 15px;
  background: rgba(255, 255, 255, 0.8);
  border: none;
  border-radius: 50%;
  width: 30px;
  height: 30px;
  cursor: pointer;
  font-size: 18px;
  color: #FF6B35;
  display: flex;
  align-items: center;
  justify-content: center;
}

.modal-media img {
  max-width: 100%;
  max-height: 300px;
  width: auto;
  height: auto;
  border-radius: 15px;
  display: block;
  margin: 0 auto;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

.modal-content {
  color: white;
}

.modal-content h3 {
  text-align: center;
  margin: 20px 0 15px 0;
  color: white;
}

.modal-content p {
  text-align: center;
  margin: 10px 0;
}

.btn-adopt {
  width: 100%;
  padding: 12px;
  background: rgba(0, 0, 0, 0.3);
  color: white;
  font-weight: bold;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: background 0.3s ease;
  margin-top: 15px;
}

.btn-adopt:hover {
  background: rgba(0, 0, 0, 0.5);
}

@media (max-width: 768px) {
  .contact-content {
    grid-template-columns: 1fr;
    gap: 20px;
  }

  .hero-title {
    font-size: 2rem;
  }

  .section-title {
    font-size: 1.5rem;
  }

  .search-bar {
    max-width: 100%;
  }
}

</style>