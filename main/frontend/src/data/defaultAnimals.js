/** Sākotnējais dzīvnieku katalogs. Attēli: Unsplash (t.sk. Šoko — mierīgs suns). */
const u = (id) =>
  `https://images.unsplash.com/${id}?auto=format&fit=crop&w=640&q=82`

export const DEFAULT_ANIMALS = [
  {
    id: 1,
    name: 'Reksis',
    species: 'Suns',
    gender: 'Vīrietis',
    description: 'Draudzīgs un enerģisks suns, ideāls ģimenes draugs.',
    image: u('photo-1587300003388-59208cc962cb'),
  },
  {
    id: 2,
    name: 'Šoko',
    species: 'Suns',
    gender: 'Vīrietis',
    description: 'Mīļš un mierīgs suns, lieliski piemērots senioriem.',
    image: u('photo-1544568100-847a948585b9'),
  },
  {
    id: 3,
    name: 'Anna',
    species: 'Kaķis',
    gender: 'Sieviete',
    description: 'Jauns un rotaļīgs kaķēns, pilns ar enerģiju.',
    image: u('photo-1573865526739-10659fec78a5'),
  },
  {
    id: 4,
    name: 'Dingo',
    species: 'Suns',
    gender: 'Vīrietis',
    description: 'Aktīvs un uzticīgs suns, mīl garas pastaigas.',
    image: u('photo-1583511655857-d19b40a7a54e'),
  },
  {
    id: 5,
    name: 'Muris',
    species: 'Kaķis',
    gender: 'Vīrietis',
    description: 'Kluss un pieklājīgs kaķis, patīk tuvoties cilvēkiem.',
    image: u('photo-1514888286974-6c03e2ca1dba'),
  },
  {
    id: 6,
    name: 'Bella',
    species: 'Suns',
    gender: 'Sieviete',
    description: 'Maza auguma sunīte, draudzīga pret bērniem.',
    image:
      'https://cdn.pixabay.com/photo/2016/01/05/17/51/maltese-1123016_640.jpg',
  },
  {
    id: 7,
    name: 'Snēgs',
    species: 'Trusis',
    gender: 'Sieviete',
    description: 'Mīksts un mierīgs trusītis, piemērots dzīvoklim.',
    image: u('photo-1585110396000-c9ffd4e4b308'),
  },
  {
    id: 8,
    name: 'Rio',
    species: 'Putns',
    gender: 'Vīrietis',
    description: 'Krāsains papagailis, mīl uzmanību un sarunas.',
    image: u('photo-1552728089-57bdde30beb3'),
  },
  {
    id: 9,
    name: 'Maksis',
    species: 'Suns',
    gender: 'Vīrietis',
    description: 'Jautrs un mācāms suns, labi sadzīvo ar citiem dzīvniekiem.',
    image: u('photo-1543466835-00a7907e9de1'),
  },
  {
    id: 10,
    name: 'Zīģa',
    species: 'Kaķis',
    gender: 'Sieviete',
    description: 'Pieaugušs kaķis, mierīgs raksturs, meklē mājas mieram.',
    image: u('photo-1518791841217-8f162f1e1131'),
  },
]
