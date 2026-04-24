# Laimīgās Ķepas — dzīvnieku adopcijas SPA

Viena lapa (**Vue 3**), kas runā ar **Laravel 12** API. Lietotāji pārlūko dzīvniekus, iesniedz adopcijas pieteikumus, saņem paziņojumus un var rakstīt **support čatā**. Administratoriem ir atsevišķs panelis ar ziņojumiem, pieteikumiem, dzīvniekiem, lietotājiem un čatu.

---

## Projekta struktūra

| Mape | Apraksts |
|------|----------|
| `main/backend/` | Laravel API, SQLite (noklusējums), Sanctum žetoni |
| `main/frontend/` | Vue 3 + Vite, maršruti, mājas lapa „premium” dizainā |

---

## Izstrādes rīki un tehnoloģijas

| Rīks / tehnoloģija | Versija / piezīme |
|--------------------|-------------------|
| **PHP** | ≥ 8.2 |
| **Composer** | Laravel 12 atkarībām |
| **Laravel** | API, Sanctum, Eloquent, migrācijas |
| **SQLite** | Noklusējuma DB izstrādei |
| **Node.js** | ^20.19 vai ≥ 22.12 |
| **npm** | Frontenda pakotnes |
| **Vue** | 3 (Composition Options API / SFC) |
| **Vite** | 7, izstrādes serveris un build |
| **Vue Router, Pinia** | Maršruti un valoda |
| **Vuetify 3** | Papildu UI komponentes |
| **ESLint, Prettier** | Koda stils |
| **PHPUnit** | Backend testi |
| **vite-plugin-pwa** | PWA (`manifest` + service worker pēc `npm run build`) |

---

## PWA (progresīvā tīmekļa lietotne)

Pēc `npm run build` mapē `main/frontend/dist/` tiek ģenerēti `manifest.webmanifest`, `sw.js` un `registerSW.js`. Vietni jāapkalpo caur **HTTPS** (vai `localhost`), lai pārlūks piedāvātu instalāciju. Ikona un `theme-color`: `public/logo3.png` un tumšais dizaina fons.

---

## Automātiskā testēšana (5 testa gadījumi)

Backend: `main/backend/tests/Feature/ApiNoslegumaPrasibasTest.php` — pieci API testi (statistika, reģistrācijas validācija, ielogošanās ar žetonu, dzīvnieku saraksts, admin PATCH dzīvniekam).

```powershell
cd main\backend
php artisan test --filter=ApiNoslegumaPrasibasTest
```

---

## Drošība (OWASP) un pieejamība (WCAG)

- **API:** validācija, Sanctum žetoni, admin `middleware`, ierobežota biežuma mēģinājumi uz `/api/register`, `/api/login`, `/api/admin/login`.
- **HTTP galvenes:** `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`, `Permissions-Policy` (skat. `SecurityHeadersMiddleware`).
- **Paroles:** Laravel `hashed` casts, nav glabāšanas atklātā tekstā.
- **WCAG (daļēji):** galvenes `aria-*`, saistītie `label`/`for` admin dzīvnieku formā, galvenais saturs `#main-content`.

Pilnai WCAG atbilstībai vēl jāveic manuāla pārbaude (kontrasts, visas formas, tastatūras secība).

---

## Ko tev vajag uz datora

- **PHP** ≥ 8.2, **Composer**
- **Node.js** ^20.19 vai ≥ 22.12, **npm**
- (Ieteicams) **Git**

---

## Ātrā palaišana izstrādei

Darbi jādara **divās konsolēs**: vispirms API, tad frontends.

### 1) Backend

```powershell
cd main\backend
copy .env.example .env
composer install
php artisan key:generate
```

**SQLite datubāze** (noklusējums `.env`):

```powershell
New-Item -ItemType File -Path database\database.sqlite -Force
php artisan migrate
php artisan db:seed
```

Pēc tam palaid serveri:

```powershell
php artisan serve
```

API būs pieejams, piemēram, `http://127.0.0.1:8000`. Maršruti ar prefiksu **`/api`** (piem. `GET /api/animals`).

### 2) Frontend

```powershell
cd main\frontend
npm install
npm run dev
```

Vite parasti dod `http://localhost:5173`. Pieprasījumi uz **`/api`** tiek **proxy** uz `http://127.0.0.1:8000` (skat. `vite.config.js`).

> Ja frontends tiek servēts citā veidā bez proxy, jānodrošina, ka pārlūks var sasniegt API (CORS / kopējs hosts).

---

## Būvēšana production

```powershell
cd main\frontend
npm run build
```

Izvade: `main/frontend/dist/`. To var servēt caur Laravel (`public` mapē) vai jebkuru statisko hostu — tad API adrese jāsaskaņo ar faktisko backendu.

---

## Svarīgākās `.env` vērtības (`main/backend`)

| Mainīgais | Nozīme |
|-----------|--------|
| `APP_URL` | Laravel publiskā adrese (svarīgi saitēm un sesijām) |
| `DB_CONNECTION=sqlite` | Noklusējums |
| `DB_DATABASE` | Ceļš uz `.sqlite` failu, ja neatbilst noklusējumam |
| `ADMIN_PANEL_PASSWORD` | Parole **admin paneļa** pieteikšanās ekrānam (`/admin`) |

Pilns piemērs: `.env.example`.

---

## Kā ielogoties

### Parastais lietotājs

Pēc `db:seed` var izmantot, piemēram:

- `demo@laimigas.lv` / `password`
- `test@example.com` / `password`

(Seederī `DatabaseSeeder` — paroles ir tās pašas.)

### Admin panelis (`/admin`)

1. Atver frontendā **`/admin`**.
2. Backend izveido / izmanto tehnisku lietotāju `panel-admin@laimigas-kepas.local` ar **Sanctum** žetonu un `is_admin`.

**Īpašnieka** konts komandā (seed): `TeamUserSeeder` — piem. `rudolfs.liepins@mapon.com` ar `is_owner` (aizsardzība pret bloķēšanu / admin noņemšanu).

---

## Galvenās lietotāju iespējas

- Mājas lapa: katalogs, meklēšana, **sugu izvēlne** (custom dropdown), adopcijas pieteikums (pēc pierakstīšanās).
- **Mans konts**: adopcijas pieteikumi un statuss.
- **Support čats**: peldoša poga (ne `/admin`), saruna ar adminiem.
- **Admin**: kontaktu ziņojumi, support čats, pieteikumi, dzīvnieku pievienošana/**rediģēšana** (PATCH API) un dzēšana, lietotāju pārvaldība.

---

## Noderīgas komandas

| Komanda | Kur | Mērķis |
|---------|-----|--------|
| `php artisan migrate` | `main/backend` | Jaunas migrācijas |
| `php artisan db:seed` | `main/backend` | Aizpildīt dzīvniekus un lietotājus |
| `php artisan migrate:fresh --seed` | `main/backend` | **Dzēš** DB un liek no jauna (uzmanīgi!) |
| `npm run lint` | `main/frontend` | ESLint |
| `npm run build` | `main/frontend` | Production build |
| `php artisan test --filter=ApiNoslegumaPrasibasTest` | `main/backend` | Pieci API feature testi |

---

## Problēmu meklēšana

- **401 / CORS**: pārliecinies, ka API darbojas un frontends izmanto proxy (`npm run dev`) vai pareizu bāzes URL.
- **SQLite „database not found”**: izveido `main/backend/database/database.sqlite` un palaid `migrate`.
- **Tukšs dzīvnieku saraksts**: palaid `php artisan db:seed` (vai pārbaudi, vai `AnimalSeeder` ir izpildījies).

---

## Licence

Skat. atsevišķus `LICENSE` failus, ja tādi ir pievienoti; šis repo satur gan Laravel, gan Vue atkarības ar to pašu licencēm.
