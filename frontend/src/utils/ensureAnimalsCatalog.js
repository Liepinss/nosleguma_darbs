import { DEFAULT_ANIMALS } from '@/data/defaultAnimals'

const SEED_FLAG = 'animalsCatalogSeededV1'
const IMAGE_FIX_FLAG = 'animalsImagesUnsplashV2'
const SHOKO_BELLA_FIX_FLAG = 'animalsShokoBellaImagesV3'
const SHOKO_UNSPLASH_FLAG = 'animalsShokoUnsplashV4'

function defaultById() {
  return new Map(DEFAULT_ANIMALS.map((a) => [a.id, a]))
}

/** Pirmās palaišanas reizē ieraksta 10 noklusējuma dzīvniekus, ja `animals` ir tukšs. */
export function ensureAnimalsCatalog() {
  const existing = JSON.parse(localStorage.getItem('animals') || '[]')

  if (localStorage.getItem(SEED_FLAG) !== '1') {
    if (!Array.isArray(existing) || existing.length === 0) {
      localStorage.setItem('animals', JSON.stringify(DEFAULT_ANIMALS))
    }
    localStorage.setItem(SEED_FLAG, '1')
  }

  if (localStorage.getItem(IMAGE_FIX_FLAG) !== '1') {
    const cur = JSON.parse(localStorage.getItem('animals') || '[]')
    const defs = defaultById()
    let changed = false
    const next = cur.map((a) => {
      const d = defs.get(a.id)
      if (!d) return a
      const img = String(a.image || '')
      if (img.includes('wikimedia.org')) {
        changed = true
        return { ...a, image: d.image }
      }
      return a
    })
    if (changed) {
      localStorage.setItem('animals', JSON.stringify(next))
    }
    localStorage.setItem(IMAGE_FIX_FLAG, '1')
  }

  if (localStorage.getItem(SHOKO_BELLA_FIX_FLAG) !== '1') {
    const cur = JSON.parse(localStorage.getItem('animals') || '[]')
    const defs = defaultById()
    let changed = false
    const next = cur.map((a) => {
      if (a.id !== 2 && a.id !== 6) return a
      const d = defs.get(a.id)
      if (!d) return a
      changed = true
      return { ...a, image: d.image }
    })
    if (changed) {
      localStorage.setItem('animals', JSON.stringify(next))
    }
    localStorage.setItem(SHOKO_BELLA_FIX_FLAG, '1')
  }

  if (localStorage.getItem(SHOKO_UNSPLASH_FLAG) !== '1') {
    const cur = JSON.parse(localStorage.getItem('animals') || '[]')
    const defs = defaultById()
    const shoko = defs.get(2)
    let changed = false
    const next = cur.map((a) => {
      if (a.id !== 2 || !shoko) return a
      const img = String(a.image || '')
      if (img.includes('pixabay.com') || img.includes('animal-1868916') || !img.trim()) {
        changed = true
        return { ...a, image: shoko.image }
      }
      return a
    })
    if (changed) {
      localStorage.setItem('animals', JSON.stringify(next))
    }
    localStorage.setItem(SHOKO_UNSPLASH_FLAG, '1')
  }
}
