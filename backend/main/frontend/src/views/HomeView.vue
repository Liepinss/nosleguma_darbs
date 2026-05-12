<template>
  <div id="start" class="home home-premium">

    <section class="hp-hero" aria-labelledby="hero-heading">
      <div class="hp-hero__mesh" aria-hidden="true" />
      <div class="container hp-hero__inner">
        <div class="hp-hero__copy">
          <p class="hp-hero__eyebrow">{{ t('hero.eyebrow') }}</p>
          <h1 id="hero-heading" class="hp-hero__title">
            <span class="hp-hero__title-line">{{ t('hero.title1') }}</span>
            <span class="hp-hero__title-script">{{ t('hero.title2') }}</span>
          </h1>
          <p class="hp-hero__lead">
            {{ t('hero.lead') }}
          </p>
          <div class="hp-hero__actions">
            <button type="button" class="hp-btn hp-btn--primary hp-hero__btn" @click.prevent="scrollToSection('animals')">
              {{ t('hero.ctaPick') }}
            </button>
            <button type="button" class="hp-btn hp-btn--ghost hp-hero__btn hp-hero__btn--ghost" @click.prevent="scrollToSection('how')">
              {{ t('hero.ctaHow') }}
            </button>
          </div>
          <div v-if="stats" class="hp-statbar hp-statbar--hero" role="list">
            <div class="hp-statbar__cell" role="listitem">
              <span class="hp-statbar__num">{{ stats.animals_available }}</span>
              <span class="hp-statbar__lbl">{{ t('stats.waiting') }}</span>
            </div>
            <div class="hp-statbar__cell" role="listitem">
              <span class="hp-statbar__num">{{ stats.animals_total }}</span>
              <span class="hp-statbar__lbl">{{ t('stats.inCare') }}</span>
            </div>
            <div class="hp-statbar__cell" role="listitem">
              <span class="hp-statbar__num">{{ stats.adoption_applications_total }}</span>
              <span class="hp-statbar__lbl">{{ t('stats.apps') }}</span>
            </div>
            <div class="hp-statbar__cell" role="listitem">
              <span class="hp-statbar__num">{{ stats.registered_users_total }}</span>
              <span class="hp-statbar__lbl">{{ t('stats.members') }}</span>
            </div>
          </div>
          <div v-else-if="statsLoading" class="hp-statbar hp-statbar--hero hp-statbar--skel" aria-hidden="true">
            <div v-for="n in 4" :key="n" class="hp-statbar__skel" />
          </div>
        </div>
        <div class="hp-hero__media">
          <figure class="hp-hero__visual">
            <img
              src="https://images.unsplash.com/photo-1548199973-03cce0bbc87b?auto=format&fit=crop&w=1100&q=85"
              :alt="t('hero.imgAlt')"
              width="1100"
              height="733"
              loading="eager"
              decoding="async"
            />
            <figcaption class="hp-hero__caption">
              <strong>{{ t('hero.captionStrong') }}</strong>
              <span>{{ t('hero.captionSub') }}</span>
            </figcaption>
          </figure>
        </div>
      </div>
    </section>

    <section class="hp-trust" :aria-label="t('trust.aria')">
      <div class="container hp-trust__grid">
        <article class="hp-trust__card">
          <div class="hp-trust__icon" aria-hidden="true">
            <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M24 4L8 12v12c0 11 7 20 16 22 9-2 16-11 16-22V12L24 4z" stroke="currentColor" stroke-width="2.2" stroke-linejoin="round" />
              <path d="M18 24l4 4 8-10" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </div>
          <h3 class="hp-trust__title">{{ t('trust.safety.title') }}</h3>
          <p class="hp-trust__text">{{ t('trust.safety.text') }}</p>
        </article>
        <article class="hp-trust__card">
          <div class="hp-trust__icon" aria-hidden="true">
            <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M8 22c0-8 6-14 16-14s16 6 16 14c0 10-10 18-16 22-6-4-16-12-16-22z" stroke="currentColor" stroke-width="2.2" stroke-linejoin="round" />
              <circle cx="24" cy="22" r="5" stroke="currentColor" stroke-width="2.2" />
            </svg>
          </div>
          <h3 class="hp-trust__title">{{ t('trust.match.title') }}</h3>
          <p class="hp-trust__text">{{ t('trust.match.text') }}</p>
        </article>
        <article class="hp-trust__card">
          <div class="hp-trust__icon" aria-hidden="true">
            <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M6 20h10l4-12 6 28 4-16h12" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </div>
          <h3 class="hp-trust__title">{{ t('trust.support.title') }}</h3>
          <p class="hp-trust__text">{{ t('trust.support.text') }}</p>
        </article>
      </div>
    </section>

    <section id="how" class="hp-steps">
      <div class="container">
        <header class="hp-sechead">
          <p class="hp-sechead__eyebrow">{{ t('steps.eyebrow') }}</p>
          <h2 class="hp-sechead__title">{{ t('steps.title') }}</h2>
        </header>
        <ol class="hp-steps__list">
          <li class="hp-steps__item">
            <span class="hp-steps__bubble">01</span>
            <h3 class="hp-steps__name">{{ t('steps.n1') }}</h3>
            <p class="hp-steps__desc">{{ t('steps.d1') }}</p>
          </li>
          <li class="hp-steps__item">
            <span class="hp-steps__bubble">02</span>
            <h3 class="hp-steps__name">{{ t('steps.n2') }}</h3>
            <p class="hp-steps__desc">{{ t('steps.d2') }}</p>
          </li>
          <li class="hp-steps__item">
            <span class="hp-steps__bubble">03</span>
            <h3 class="hp-steps__name">{{ t('steps.n3') }}</h3>
            <p class="hp-steps__desc">{{ t('steps.d3') }}</p>
          </li>
          <li class="hp-steps__item">
            <span class="hp-steps__bubble">04</span>
            <h3 class="hp-steps__name">{{ t('steps.n4') }}</h3>
            <p class="hp-steps__desc">{{ t('steps.d4') }}</p>
          </li>
        </ol>
      </div>
    </section>

    <section v-if="spotlightAnimals.length" class="hp-spotlight">
      <div class="container">
        <header class="hp-sechead hp-sechead--row">
          <div>
            <p class="hp-sechead__eyebrow">{{ t('spotlight.eyebrow') }}</p>
            <h2 class="hp-sechead__title">{{ t('spotlight.title') }}</h2>
          </div>
          <button type="button" class="hp-btn hp-btn--ghost hp-btn--sm" @click.prevent="scrollToSection('animals')">
            {{ t('spotlight.all') }}
          </button>
        </header>
        <div class="hp-spotlight-scroll">
          <div class="hp-spotlight__row">
            <button
              v-for="animal in spotlightAnimals"
              :key="animal.id"
              type="button"
              class="hp-spot"
              @click="openModal(animal)"
            >
              <span class="hp-spot__img">
                <img :src="animal.image" :alt="animal.name" loading="lazy" decoding="async" referrerpolicy="no-referrer" />
              </span>
              <span class="hp-spot__meta">
                <span class="hp-spot__species">{{ speciesLabel(animal.species) }}</span>
                <span class="hp-spot__name">{{ animal.name }}</span>
              </span>
            </button>
          </div>
        </div>
      </div>
    </section>

    <section class="hp-catalog" id="animals">
      <div class="container">
        <header class="hp-sechead">
          <p class="hp-sechead__eyebrow">{{ t('catalog.eyebrow') }}</p>
          <h2 class="hp-sechead__title">{{ t('catalog.title') }}</h2>
          <p class="hp-sechead__sub">
            {{ t('catalog.sub') }}
          </p>
        </header>

        <div class="hp-search">
          <div class="hp-search__field">
            <svg class="hp-search__glass" width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <circle cx="10" cy="10" r="6.5" stroke="currentColor" stroke-width="2" />
              <path d="M15 15l6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
            </svg>
            <input
              v-model="searchQuery"
              type="search"
              class="hp-search__input"
              :placeholder="t('search.placeholder')"
              autocomplete="off"
            />
          </div>
          <div
            ref="speciesFilterRoot"
            class="hp-search__select hp-species-dd"
            :class="{ 'hp-species-dd--open': speciesDropdownOpen }"
          >
            <span id="species-filter-label" class="visually-hidden">{{ t('species.filterAria') }}</span>
            <button
              id="species-filter-trigger"
              type="button"
              class="hp-species-dd__trigger"
              :aria-expanded="speciesDropdownOpen"
              aria-haspopup="listbox"
              aria-controls="species-filter-listbox"
              @click="toggleSpeciesDropdown"
              @keydown.escape.prevent="closeSpeciesDropdown"
            >
              <span class="hp-species-dd__trigger-inner">
                <span class="hp-species-dd__icon" aria-hidden="true">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                    <path
                      d="M12 5c1.2-2.2 4-2.2 5.2 0 1.1 2 0 4.5-1.8 5.8-.9.4-1.8.6-2.7.6h-.8c-.9 0-1.8-.2-2.7-.6-1.8-.8-2.9-3.3-1.8-5.8z"
                      stroke="currentColor"
                      stroke-width="1.5"
                      stroke-linejoin="round"
                    />
                    <path
                      d="M8.5 14.5c-1.8 1-3 2.8-3.2 5H4v3h16v-3h-1.3c-.2-2.2-1.4-4-3.2-5"
                      stroke="currentColor"
                      stroke-width="1.5"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                    />
                  </svg>
                </span>
                <span class="hp-species-dd__label">
                  <span class="hp-species-dd__label-k">{{ t('species.kind') }}</span>
                  <span class="hp-species-dd__label-v">{{ speciesFilterLabel }}</span>
                </span>
              </span>
              <span class="hp-species-dd__chev" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                  <path
                    d="M6 9l6 6 6-6"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                </svg>
              </span>
            </button>
            <Transition name="hp-species-dd">
              <ul
                v-show="speciesDropdownOpen"
                id="species-filter-listbox"
                class="hp-species-dd__menu"
                role="listbox"
                aria-labelledby="species-filter-label"
              >
                <li role="none">
                  <button
                    type="button"
                    class="hp-species-dd__opt"
                    :class="{ 'hp-species-dd__opt--active': !selectedSpecies }"
                    role="option"
                    :aria-selected="!selectedSpecies"
                    @click="selectSpecies('')"
                  >
                    <span class="hp-species-dd__opt-main">
                      <span class="hp-species-dd__opt-title">{{ t('species.all') }}</span>
                      <span class="hp-species-dd__pill">{{ animals.length }}</span>
                    </span>
                    <span v-if="!selectedSpecies" class="hp-species-dd__check" aria-hidden="true">✓</span>
                  </button>
                </li>
                <li v-for="sp in sortedSpeciesOptions" :key="sp" role="none">
                  <button
                    type="button"
                    class="hp-species-dd__opt"
                    :class="{ 'hp-species-dd__opt--active': selectedSpecies === sp }"
                    role="option"
                    :aria-selected="selectedSpecies === sp"
                    @click="selectSpecies(sp)"
                  >
                    <span class="hp-species-dd__opt-main">
                      <span class="hp-species-dd__opt-title">{{ speciesLabel(sp) }}</span>
                      <span class="hp-species-dd__pill">{{ countBySpecies(sp) }}</span>
                    </span>
                    <span v-if="selectedSpecies === sp" class="hp-species-dd__check" aria-hidden="true">✓</span>
                  </button>
                </li>
              </ul>
            </Transition>
          </div>
        </div>

        <div class="hp-grid-scroll">
          <div class="hp-grid">
            <article
              v-for="animal in filteredAnimals"
              :key="animal.id"
              class="hp-card"
            >
              <div class="hp-card__media">
                <img
                  :src="animal.image"
                  :alt="animal.name"
                  loading="lazy"
                  decoding="async"
                  referrerpolicy="no-referrer"
                />
                <div v-if="isTaken(animal.id)" class="hp-card__ribbon">{{ t('card.ribbon') }}</div>
              </div>
              <div class="hp-card__body">
                <p class="hp-card__species">{{ speciesLabel(animal.species) }}</p>
                <h3 class="hp-card__name">{{ animal.name }}</h3>
                <p class="hp-card__desc">{{ animal.description }}</p>
                <p class="hp-card__meta"><span>{{ t('card.gender') }}</span> {{ animal.gender || '—' }}</p>
                <button
                  type="button"
                  class="hp-btn hp-btn--card"
                  :disabled="isTaken(animal.id)"
                  @click="openModal(animal)"
                >
                  {{ isTaken(animal.id) ? t('card.taken') : t('card.open') }}
                </button>
              </div>
            </article>
          </div>
        </div>
      </div>
    </section>



    <section class="hp-about" id="about">
      <div class="container hp-about__layout">
        <div class="hp-about__visual">
          <div class="hp-about__frame">
            <img
              src="https://images.unsplash.com/photo-1601758228041-f3b2795255f1?auto=format&fit=crop&w=900&q=85"
              :alt="t('about.imgAlt')"
              loading="lazy"
              width="900"
              height="1125"
            />
          </div>
        </div>
        <div class="hp-about__content">
          <header class="hp-sechead hp-sechead--left">
            <p class="hp-sechead__eyebrow">{{ t('about.eyebrow') }}</p>
            <h2 class="hp-sechead__title">{{ t('about.title') }}</h2>
          </header>
          <p class="hp-about__lead">
            <strong>{{ t('about.leadStrong') }}</strong> {{ t('about.lead') }}
          </p>
          <ul class="hp-about__ticks">
            <li>{{ t('about.tick1') }}</li>
            <li>{{ t('about.tick2') }}</li>
            <li>{{ t('about.tick3') }}</li>
            <li>{{ t('about.tick4') }}</li>
          </ul>
        </div>
      </div>
    </section>

    <section class="hp-contact" id="contact">
      <div class="container hp-contact__grid">
        <div class="hp-contact__aside">
          <header class="hp-sechead hp-sechead--left">
            <p class="hp-sechead__eyebrow">{{ t('contact.eyebrow') }}</p>
            <h2 class="hp-sechead__title">{{ t('contact.title') }}</h2>
            <p class="hp-sechead__sub">
              <template v-if="userLoggedIn">
                {{ t('contact.subIn') }}
              </template>
              <template v-else>
                {{ t('contact.subOut') }}
              </template>
            </p>
          </header>
          <div class="hp-contact__tiles">
            <div class="hp-contact__tile">
              <h3>{{ t('contact.addrTitle') }}</h3>
              <p>Torņukalns, Mārupes iela 19<br>Rīga, LV-1002</p>
            </div>
            <div class="hp-contact__tile">
              <h3>{{ t('contact.phoneTitle') }}</h3>
              <p><a href="tel:+37129316942">+371 29 316 942</a></p>
            </div>
            <div class="hp-contact__tile">
              <h3>{{ t('contact.emailTitle') }}</h3>
              <p><a href="mailto:kontakti@dzivniekucentrs.lv">Laimigaskepas@gmail.com</a></p>
            </div>
            <div class="hp-contact__tile">
              <h3>{{ t('contact.hoursTitle') }}</h3>
              <p>{{ t('contact.hours') }}</p>
            </div>
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
            <img
              :src="selectedAnimal.image"
              :alt="selectedAnimal.name"
              decoding="async"
              referrerpolicy="no-referrer"
            />
          </div>

          <div class="modal-content">
            <h3>{{ selectedAnimal.name }}</h3>
            <p>{{ selectedAnimal.description }}</p>
            <p><strong>{{ t('modal.gender') }}</strong> {{ selectedAnimal.gender }}</p>
            <template v-if="userLoggedIn">
              <button type="button" class="btn-adopt" @click="openAdoptionModal">
                {{ t('modal.adopt') }}
              </button>
            </template>
            <template v-else>
              <p class="adopt-login-hint">
                {{ t('modal.adoptHint') }}
              </p>
              <router-link class="btn-adopt btn-adopt-link" to="/login">{{ t('modal.login') }}</router-link>
              <router-link class="btn-adopt-secondary" to="/signup">{{ t('modal.signup') }}</router-link>
            </template>
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

          <div class="modal-content adoption-modal-body">
            <h3>{{ adoptModalTitle }}</h3>

            <form class="adoption-form" @submit.prevent="submitAdoption">
              <div class="form-group">
                <label for="adopt-name">{{ t('adopt.name') }}</label>
                <input
                  id="adopt-name"
                  v-model="adoptionForm.name"
                  type="text"
                  class="adoption-field"
                  autocomplete="name"
                  required
                />
              </div>

              <div class="form-group">
                <label for="adopt-email">{{ t('adopt.email') }}</label>
                <input
                  id="adopt-email"
                  v-model.trim="adoptionForm.email"
                  type="email"
                  class="adoption-field"
                  inputmode="email"
                  autocomplete="email"
                  :placeholder="t('adopt.emailPh')"
                  required
                />
              </div>

              <div class="form-group">
                <label for="adopt-phone">{{ t('adopt.phone') }}</label>
                <div class="phone-field">
                  <span class="phone-prefix" aria-hidden="true">+371</span>
                  <input
                    id="adopt-phone"
                    v-model="adoptionPhoneDigits"
                    type="text"
                    class="adoption-field phone-digits"
                    inputmode="numeric"
                    maxlength="8"
                    placeholder="12345678"
                    pattern="[0-9]{8}"
                    required
                    @input="onAdoptionPhoneInput"
                  />
                </div>
                <p class="field-hint">{{ t('adopt.phoneHint') }}</p>
              </div>

              <div class="form-group">
                <label for="adopt-address">{{ t('adopt.address') }}</label>
                <input
                  id="adopt-address"
                  v-model="adoptionForm.address"
                  type="text"
                  class="adoption-field"
                  :placeholder="t('adopt.addressPh')"
                  autocomplete="street-address"
                  required
                />
              </div>

              <div class="form-group">
                <label for="adopt-exp">{{ t('adopt.exp') }}</label>
                <textarea
                  id="adopt-exp"
                  v-model="adoptionForm.experience"
                  class="adoption-field"
                  :placeholder="t('adopt.expPh')"
                  rows="4"
                  required
                ></textarea>
              </div>

              <button type="submit" class="btn-send">{{ t('adopt.submit') }}</button>
            </form>

            <p v-if="adoptionMessage" :class="adoptionMessageType">{{ adoptionMessage }}</p>
          </div>

        </div>

      </div>

    </transition>

  </div>
</template>

<script>
import { firstValidationMessage } from '@/api/http'
import {
  fetchAnimalsCatalog,
  fetchHomeStats,
  submitApplication,
} from '@/api/restApi'
import { translate } from '@/i18n/siteMessages'
import { useLocaleStore } from '@/stores/locale'
import { isUserLoggedIn } from '@/utils/authStorage'
import { mapState } from 'pinia'

export default {

  data() {
    return {

      modalOpen: false,
      adoptionModalOpen: false,
      searchQuery: '',
      selectedSpecies: '',
      speciesDropdownOpen: false,
      takenAnimalIds: [],

      selectedAnimal: {},

      animals: [],
      adoptionForm: {
        name: '',
        email: '',
        phone: '',
        address: '',
        experience: ''
      },
      /** Tikai 8 cipari; pilns numurs = +371 + digits */
      adoptionPhoneDigits: '',

      adoptionMessage: '',
      adoptionMessageType: '',

      userLoggedIn: false,

      stats: null,
      statsLoading: true,
    }
  },

  mounted() {
    void this.loadCatalog()
    void this.loadStats()
    this.refreshUserLoggedIn()
    window.addEventListener('animalsUpdated', this.loadCatalog)
    window.addEventListener('authUpdated', this.refreshUserLoggedIn)
    window.addEventListener('storage', this.onAuthStorage)
    document.addEventListener('click', this.onSpeciesFilterDocClick, true)
  },
  beforeUnmount() {
    window.removeEventListener('animalsUpdated', this.loadCatalog)
    window.removeEventListener('authUpdated', this.refreshUserLoggedIn)
    window.removeEventListener('storage', this.onAuthStorage)
    document.removeEventListener('click', this.onSpeciesFilterDocClick, true)
  },

  computed: {
    ...mapState(useLocaleStore, ['lang']),
    t() {
      return (key) => translate(this.lang, key)
    },
    adoptModalTitle() {
      const name = this.selectedAnimal?.name || ''
      return this.t('adopt.title').replace('{name}', name)
    },
    speciesOptions() {
      const species = this.animals.map((animal) => animal.species || 'Cits')
      return [...new Set(species)]
    },
    sortedSpeciesOptions() {
      const loc = this.lang === 'en' ? 'en' : 'lv'
      return [...this.speciesOptions].sort((a, b) => a.localeCompare(b, loc))
    },
    speciesFilterLabel() {
      if (!this.selectedSpecies) return this.t('species.all')
      return this.speciesLabel(this.selectedSpecies)
    },
    filteredAnimals() {
      let list = this.animals
      if (this.selectedSpecies) {
        list = list.filter((animal) => (animal.species || 'Cits') === this.selectedSpecies)
      }
      if (!this.searchQuery) return list

      const query = this.searchQuery.toLowerCase()
      return list.filter(
        (animal) =>
          animal.name.toLowerCase().includes(query) ||
          (animal.gender || '').toLowerCase().includes(query) ||
          (animal.description || '').toLowerCase().includes(query),
      )
    },
    spotlightAnimals() {
      return this.animals.filter((a) => !this.isTaken(a.id)).slice(0, 3)
    },
  },

  watch: {
    $route() {
      this.refreshUserLoggedIn()
    },
    modalOpen(open) {
      if (open) this.speciesDropdownOpen = false
    },
  },

  methods: {
    toggleSpeciesDropdown() {
      this.speciesDropdownOpen = !this.speciesDropdownOpen
    },
    closeSpeciesDropdown() {
      this.speciesDropdownOpen = false
    },
    selectSpecies(value) {
      this.selectedSpecies = value
      this.speciesDropdownOpen = false
    },
    countBySpecies(sp) {
      return this.animals.filter((a) => (a.species || 'Cits') === sp).length
    },
    speciesLabel(sp) {
      return sp === 'Cits' ? this.t('species.other') : sp
    },
    onSpeciesFilterDocClick(event) {
      const root = this.$refs.speciesFilterRoot
      if (!root || !this.speciesDropdownOpen) return
      if (!root.contains(event.target)) this.speciesDropdownOpen = false
    },

    scrollToSection(id) {
      document.getElementById(id)?.scrollIntoView({ behavior: 'smooth', block: 'start' })
    },

    async loadStats() {
      this.statsLoading = true
      try {
        this.stats = await fetchHomeStats()
      } catch {
        this.stats = null
      } finally {
        this.statsLoading = false
      }
    },

    refreshUserLoggedIn() {
      this.userLoggedIn = isUserLoggedIn()
    },

    onAuthStorage(event) {
      if (event.key === 'spa_auth_token' || event.key === 'spa_auth_user') {
        this.refreshUserLoggedIn()
      }
    },

    openModal(animal) {
      this.refreshUserLoggedIn()
      this.selectedAnimal = animal
      this.modalOpen = true
      this.adoptionModalOpen = false
    },

    closeModal() {
      this.modalOpen = false
    },

    openAdoptionModal() {
      this.refreshUserLoggedIn()
      if (!this.userLoggedIn) {
        this.$router.push('/login')
        return
      }
      this.adoptionMessage = ''
      this.adoptionMessageType = ''
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
      this.adoptionPhoneDigits = ''
      this.adoptionMessage = ''
      this.adoptionMessageType = ''
    },

    onAdoptionPhoneInput() {
      this.adoptionPhoneDigits = this.adoptionPhoneDigits.replace(/\D/g, '').slice(0, 8)
    },

    isValidAdoptionEmail(email) {
      return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(email || '').trim())
    },

    async submitAdoption() {
      this.refreshUserLoggedIn()
      if (!this.userLoggedIn) {
        this.adoptionMessage = translate(this.lang, 'adopt.error.login')
        this.adoptionMessageType = 'error'
        return
      }

      const email = this.adoptionForm.email.trim()
      if (!this.isValidAdoptionEmail(email)) {
        this.adoptionMessage = translate(this.lang, 'adopt.error.email')
        this.adoptionMessageType = 'error'
        return
      }

      const digits = this.adoptionPhoneDigits.replace(/\D/g, '')
      if (digits.length !== 8) {
        this.adoptionMessage = translate(this.lang, 'adopt.error.phone')
        this.adoptionMessageType = 'error'
        return
      }

      const phone = `+371${digits}`
      this.adoptionForm.email = email
      this.adoptionForm.phone = phone

      try {
        await submitApplication({
          animal_id: this.selectedAnimal.id,
          applicant_name: this.adoptionForm.name.trim(),
          applicant_email: email,
          phone,
          address: this.adoptionForm.address || null,
          experience: this.adoptionForm.experience || null,
        })
        await this.loadCatalog()
        this.adoptionMessage = translate(this.lang, 'adopt.ok')
        this.adoptionMessageType = 'success'
      } catch (e) {
        this.adoptionMessage = firstValidationMessage(e)
        this.adoptionMessageType = 'error'
        return
      }

      this.adoptionForm = {
        name: '',
        email: '',
        phone: '',
        address: '',
        experience: '',
      }
      this.adoptionPhoneDigits = ''

      setTimeout(() => {
        this.closeAdoptionModal()
        this.closeModal()
      }, 3000)
    },

    async loadCatalog() {
      try {
        const { animals, takenAnimalIds } = await fetchAnimalsCatalog()
        this.animals = animals.map((animal) => ({
          ...animal,
          species: animal.species || 'Cits',
        }))
        this.takenAnimalIds = takenAnimalIds
      } catch {
        this.animals = []
        this.takenAnimalIds = []
      }
    },

    isTaken(animalId) {
      return this.takenAnimalIds.includes(animalId)
    },

  }

}
</script>

<style scoped src="./home-premium.css"></style>
