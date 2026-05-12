<template>
  <div class="about-page">
    <div class="about-page__inner">
      <p class="about-page__eyebrow">Par projektu</p>
      <h1 class="about-page__title">Adopcijas centrs</h1>
      <p class="about-page__text">
        Šī mājas lapa ir par adopcijas centru, kur var atrast draugus un iesniegt adopcijas pieteikumus.
        Pilnu informāciju un dzīvnieku katalogu skatiet sākumlapā.
      </p>
      <router-link to="/" class="about-page__cta">Uz sākumu</router-link>
    </div>
  </div>
</template>

<style scoped>
.about-page {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: clamp(2rem, 5vw, 4rem) 20px;
  background:
    radial-gradient(ellipse 70% 50% at 50% 0%, rgba(120, 45, 30, 0.35), transparent 55%),
    linear-gradient(168deg, #0c0d12 0%, #0a0a0c 100%);
}

.about-page__inner {
  max-width: 36rem;
  text-align: center;
}

.about-page__eyebrow {
  font-size: 0.68rem;
  font-weight: 700;
  letter-spacing: 0.2em;
  text-transform: uppercase;
  color: var(--hp-gold, #f5cc4c);
  margin-bottom: 0.75rem;
}

.about-page__title {
  font-family: 'Playfair Display', Georgia, serif;
  font-size: clamp(1.75rem, 4vw, 2.25rem);
  font-weight: 600;
  color: var(--hp-text, #f4f4f5);
  margin-bottom: 1rem;
}

.about-page__text {
  color: var(--hp-muted, rgba(255, 255, 255, 0.7));
  line-height: 1.65;
  font-size: 1.05rem;
  margin-bottom: 1.75rem;
}

.about-page__cta {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.65rem 1.5rem;
  border-radius: 999px;
  font-weight: 700;
  font-size: 0.92rem;
  text-decoration: none;
  color: #fff;
  background: linear-gradient(180deg, var(--hp-orange, #ff5722) 0%, var(--hp-orange-deep, #e64a19) 100%);
  box-shadow: 0 6px 22px rgba(255, 87, 34, 0.35);
  transition:
    transform 0.2s ease,
    box-shadow 0.2s ease;
}

.about-page__cta:hover {
  transform: translateY(-1px);
  box-shadow: 0 8px 28px rgba(255, 87, 34, 0.42);
}
</style>
