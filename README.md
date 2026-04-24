# Laimīgās Ķepas — dzīvnieku adopcijas SPA

## Kas ir šis projekts

**Laimīgās Ķepas** ir vienas lapas (**SPA**) lietotne dzīvnieku adopcijai: apmeklētāji skatās katalogu, lasa aprakstus, var sazināties ar centru un, būdami pierakstījušies, iesniegt adopcijas pieteikumus. Sistēmai ir **Laravel 12** REST API (JSON), **SQLite** datubāze izstrādei un **Vue 3 + Vite** frontends ar tumšu „premium” dizainu, LV/EN valodām un **PWA** atbalstu pēc `npm run build`.

**Galvenās iespējas:**

- Publisks dzīvnieku katalogs ar meklēšanu un sugu atlasi (dati no API / migrācijām un seederiem).
- Reģistrācija, ielogošanās (**Laravel Sanctum** žetoni), personīgais konts ar adopcijas pieteikumu statusiem.
- **Support čats** starp lietotāju un administratoriem (peldošais logu logā).
- **Kontaktu forma** un paziņojumi galvenē (apstiprinājumi, administrācijas ziņas utt.).
- **Admin panelis** (`/admin`): kontaktu ziņojumi, čatu pavedieni, adopcijas pieteikumu apstrāde, dzīvnieku CRUD, lietotāju saraksts (bloķēšana, **administratora lomas piešķiršana**), aktivitāšu žurnāls.

---

## Arhitektūra un mapes

| Mape | Apraksts |
|------|----------|
| `main/backend/` | Laravel API, SQLite (noklusējums), Sanctum, migrācijas, seederi |
| `main/frontend/` | Vue 3, Vite, Vue Router, Pinia, Vuetify 3; izstrādē **proxy** no `/api` uz `http://127.0.0.1:8000` |

Pārlūkā izstrādē jāatver **Vite** adrese (parasti `http://localhost:5173`), nevis tikai Laravel sakne uz `8000` — tā ir pilnā SPA.

---

## Lietotāju lomas

| Loma | Apraksts |
|------|----------|
| **Viesis** | Var skatīt katalogu, statistiku, kontaktformu; adopcijai un čatam vajadzīgs konts. |
| **Pierakstījies lietotājs** | Profils, pieteikumi, support čats, paziņojumi. Pēc reģistrācijas `is_admin` ir **false**. |
| **Administrators** (`is_admin`) | Var atvērt **`/admin`** un izsaukt visus **`/api/admin/*`** resursus (ar to pašu Sanctum žetonu, ko deva `/api/login`). |
| **Īpašnieks** (`is_owner`) | Īpašs konts: no admin API **nevar** mainīt `is_admin` / bloķēt (aizsardzība kodā). Pēc noklusējuma seederī tas ir komandas konts `rudolfs.liepins@mapon.com`. |

---

## Kā lietotājs iegūst administratora tiesības (svarīgi)

Šajā projektā **nav** atsevišķa „admin paroles” lauka pieteikšanās ekrānā, ar kuru ikviens varētu ielogoties kā admins.

1. **Jauns lietotājs** reģistrējas (`/api/register`) — vienmēr ar `is_admin = false`.
2. **Administrators ar piekļuvi admin panelim** (izstrādē pēc `db:seed` — praksē **īpašnieks** `rudolfs.liepins@mapon.com`) ieiet **`/admin`**, cilnē **Lietotāji** izvēlas lietotāju un nospiež **administratora lomas piešķiršanu** (poga, kas backendā izsauc `PATCH` ar `is_admin: true`).
3. Backend iestāda `is_admin` datubāzē un izveido **paziņojumu** (`ContactMessage` ar `source = admin_role_grant`), kas parādās lietotāja **paziņojumu** panelī. Ziņas tekstā backend iekļauj arī vērtību no **`ADMIN_PANEL_PASSWORD`** (`.env`) — tas galvenokārt ir **tehnisks / dokumentācijas fragments** (sasaistīts ar atsevišķo API `POST /api/admin/login`), nevis atsevišķs solis Vue saskarnē.
4. Lai **galvenē parādītos saite „Admin”** un lai **`/admin`** strādātu ar atjauninātiem datiem, lietotājam pēc lomas piešķiršanas ieteicams **iziet un atkārtoti ielogoties** (vai citādi atsvaidzināt sesiju pret `/api/user`), jo pārlūkā glabātais lietotāja objekts citādi var būt novecojis.

**Noraidīt admin lomu** var caur API (`POST /api/decline-admin-role` ar paziņojuma `id`); īpašnieka kontam šī plūsma ir ierobežota. Admini var arī atcelt „piedāvājumu” no admin paneļa, kamēr tas vēl ir saistīts ar `admin_role_grant`.

---

## Piekļuve `/admin` (Vue maršruts)

- Maršrutam **`/admin`** ir `meta.requiresAdmin`.
- Ja nav žetona → novirze uz **`/login`** ar `redirect`.
- Ja ir žetons, bet **`GET /api/user`** atgriež `is_admin: false` → novirze uz **`/account`** ar `noadmin=1` (ziņojums par trūkstošām tiesībām).

Tātad **pietiek ar parasto ielogošanos** ar e-pastu un paroli kontam, kuram serverī ir `is_admin: true`.

---

## Tehniskā piezīme: `POST /api/admin/login`

Backend piedāvā **`POST /api/admin/login`** ar JSON lauku `password`, kas jāatbilst **`ADMIN_PANEL_PASSWORD`**. Atbilde satur Sanctum žetonu lietotājam **`panel-admin@laimigas-kepas.local`**. Šo ceļu **pašreizējā Vue aplikācija neizmanto** ikdienas pieteikšanās plūsmai — tā ir rezerve / ārējiem rīkiem. SPA izmanto **`POST /api/login`** ar e-pastu un paroli.

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
| **Vue** | 3 (SFC) |
| **Vite** | 7 |
| **Vue Router, Pinia** | Maršruti, valoda |
| **Vuetify 3** | Papildu UI |
| **ESLint, Prettier** | Koda stils |
| **PHPUnit** | Backend testi |
| **vite-plugin-pwa** | PWA pēc `npm run build` |

---

## PWA (progresīvā tīmekļa lietotne)

Pēc `npm run build` mapē `main/frontend/dist/` tiek ģenerēti `manifest.webmanifest`, `sw.js` un `registerSW.js`. Vietni jāapkalpo caur **HTTPS** (vai `localhost`), lai pārlūks piedāvātu instalāciju. Ikona: `public/logo3.png`.

---

## Automātiskā testēšana (5 testa gadījumi)

Backend: `main/backend/tests/Feature/ApiNoslegumaPrasibasTest.php`.

```cmd
cd main\backend
php artisan test --filter=ApiNoslegumaPrasibasTest
```

---

## Drošība (OWASP) un pieejamība (WCAG)

- **API:** validācija, Sanctum, admin `middleware`, ierobežota biežuma mēģinājumi uz `/api/register`, `/api/login`, `/api/admin/login`.
- **HTTP galvenes:** `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`, `Permissions-Policy` (`SecurityHeadersMiddleware`).
- **Paroles:** Laravel `hashed` casts.
- **WCAG (daļēji):** `aria-*`, `label`/`for`, `#main-content`.

---

## Ko vajag uz datora

- **PHP** ≥ 8.2, **Composer**
- **Node.js** ^20.19 vai ≥ 22.12, **npm**
- (Ieteicams) **Git**
- **Windows:** šeit komandas **CMD**; SQLite izveidei — CMD vai PowerShell variants.

Pārbaudes: `php -v`, `composer -V`, `node -v`, `npm -v`.

---

## Kas **nav** Git repozitorijā

- **`main/backend/database/*.sqlite*`** — ignorēts `.gitignore` dēļ. Jaunā vidē izveido tukšu failu un palaid `migrate` + `db:seed`.
- **`.env`** — jāveido no `.env.example` lokāli; **necommitot** ar īstām parolēm.

---

## Ātrā palaišana izstrādei (CMD, Windows)

1. **Sakne** — mape ar apakšmapēm `main\backend` un `main\frontend` (pēc `git clone` vai ZIP struktūra var būt, piem., `...\nosleguma_darbs-main\main\`).
2. **Divi CMD logi**: vispirms `php artisan serve`, tad `npm run dev`; **abi** jātur darbībā.
3. Ej uz sakni, piem.: `cd /d C:\Ceļš\uz\projektu\main` (`/d` — arī diska maiņa).

### 1) Backend

```cmd
cd main\backend
copy .env.example .env
composer install
php artisan key:generate
```

**SQLite** (tukšs fails, tabulas, sākotnējie dati):

**CMD:**

```cmd
type nul > database\database.sqlite
php artisan migrate
php artisan db:seed
```

**PowerShell** (alternatīva tikai faila izveidei):

```powershell
New-Item -ItemType File -Path database\database.sqlite -Force
php artisan migrate
php artisan db:seed
```

```cmd
php artisan serve
```

API: `http://127.0.0.1:8000`, JSON zem **`/api`**.

### 2) Frontend

Otrs CMD logs, no projekta saknes:

```cmd
cd main\frontend
npm install
npm run dev
```

Atver **Vite** URL (bieži `http://localhost:5173`). Tas proxy uz API.

### Prezentācija / vājš internets

- `composer install` un `npm install` labāk ar internetu pirms demonstrācijas.
- Attēli seedā bieži no ārējiem URL — bez tīkla bildes var neielādēties.

---

## Būvēšana production

```cmd
cd main\frontend
npm run build
```

Izvade: `main/frontend/dist/`. API bāzes URL jāsaskaņo ar reālo hostu, ja nav Vite proxy.

---

## Svarīgākās `.env` vērtības (`main/backend`)

| Mainīgais | Nozīme |
|-----------|--------|
| `APP_URL` | Publiskā bāzes adrese |
| `DB_CONNECTION=sqlite` | Noklusējums |
| `DB_DATABASE` | Pilns ceļš uz `.sqlite`, ja atšķiras no noklusējuma |
| `ADMIN_PANEL_PASSWORD` | Parole **`POST /api/admin/login`**; nav Vue pieteikšanās lauks. Backend iekļauj to tekstā paziņojumam, kad kādam **piešķir** `is_admin`. Noklusējuma piemērs: `.env.example`. |

---

## Demo konti (pēc `db:seed`)

| Konts | Parole | Piezīme |
|-------|--------|---------|
| `demo@laimigas.lv` | `password` | Parasts lietotājs, **nav** admins. |
| `test@example.com` | `password` | Parasts lietotājs, **nav** admins. |
| `rudolfs.liepins@mapon.com` | `password` | **`is_admin` + `is_owner`** — var **`/admin`**, var piešķirt citiem admin lomu. |
| `eduards@laimigas.lv`, `kristers@laimigas.lv` | `password` | Komandas konti seederī **bez** admin tiesībām. |

Lai pārbaudītu **administratora piešķiršanas** plūsmu: ielogojies kā `rudolfs…`, piešķir admin lomu, piem., `demo@…`, tad ielogojies kā `demo…` (pēc iziešanas), vai atkārtoti ielogojies kā `demo…`, lai atjauninātu žetonu/lietotāja datus.

---

## Noderīgas komandas

| Komanda | Kur | Mērķis |
|---------|-----|--------|
| `php artisan migrate` | `main/backend` | Jaunas migrācijas |
| `php artisan db:seed` | `main/backend` | Dzīvnieki, lietotāji, komandas konti |
| `php artisan migrate:fresh --seed` | `main/backend` | **Dzēš** DB un liek no jauna |
| `npm run lint` | `main/frontend` | ESLint |
| `npm run build` | `main/frontend` | Production build |
| `php artisan test --filter=ApiNoslegumaPrasibasTest` | `main/backend` | Pieci feature testi |

---

## Problēmu meklēšana

- **`could not open input file: artisan`**: neesi `main\backend` (tur jābūt failam `artisan`).
- **401 / CORS**: API jādarbojas; izstrādē — `npm run dev` un proxy.
- **Tukša lapa / nav dzīvnieku**: abi serveri; pārlūkā **5173** (Vite), ne tikai `8000`.
- **Nav „Admin” saites** pēc tam, kad īpašnieks piešķīra lomu: **iziet un ielogoties** (vai pārlādēt un pārliecināties, ka `/api/user` atgriež `is_admin: true`).
- **„Nav administratora tiesību”** uz `/admin`: kontam nav `is_admin` — vajag piešķiršanu no admin paneļa vai izmantot `rudolfs…` pēc seed.
- **SQLite kļūda**: izveido `database\database.sqlite` un `migrate`.
- **Tukšs katalogs**: `php artisan db:seed`.

---

## Licence

Skat. atsevišķus `LICENSE` failus, ja tādi ir; atkarību licences — to upstream pakotņu dokumentācijā.
