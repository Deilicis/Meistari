# Meistari

<!-- BILDE: Projekta logo vai galvenā bannera bilde -->

Tiessaistes platforma, kas savieno Latvijas pakalpojumu sniedzejus (meistarus) ar klientiem (mekletajiem). Platforma nodrosina ertu vidi, kur meistari var publicet savus pakalpojumus un veidot profesionalu profilu, bet klienti var meklet specialistus, iesniegt darba pieprasijumus un novertēt sadarbibas kvalitati.

---

## Par projektu

Meistari ir tīmekļa platforma, kas veidota ka starpnieks starp pakalpojumu sniedzeju un klientu. Latvijas tirgū trūkst universāla risinājuma, kas aptvertu dažādu nozaru speciālistus vienuviet.

Šī platforma ļauj jebkuras jomas profesionālim - IT, dizains, tulkošana, reklāma, būvniecība un citi - atrast klientus un demonstrēt savu darbu, savukārt klientiem - ātri atrast uzticamu meistaru un vienoties par sadarbību.

### Galvenās iespējas

- Lietotāju reģistrācija ar lomu sistēmu (meistars, meklētājs, administrators, moderators)
- Personalizēti profili ar portfeli, pieredzi un kontaktinformāciju
- Pakalpojumu publicēšana un pieprasījumu izveide
- Meklēšana un filtrēšana pēc kategorijām, atrašanās vietas, cenas un vērtējuma
- Pieteikšanās uz klientu pieprasījumiem
- Atsauksmju un vērtējumu sistēma
- Iebūvēta tērzēšanas funkcija
- Administratīvais panelis satura un lietotāju pārvaldībai

---

## Izmantotās tehnoloģijas

### Klienta puse (Frontend)

| Tehnoloģija | Pielietojums |
|---|---|
| **Vue.js 3** | Lietotāja saskarnes izstrāde ar komponentu arhitektūru |
| **TypeScript** | Statiskā tipēšana, koda kvalitātes uzlabošana |
| **Inertia.js** | Servera un klienta puses sasaiste bez atsevišķa REST API |
| **Tailwind CSS** | Adaptīvs un responsīvs dizains ar utilītu klasēm |
| **Vite** | Frontend build rīks un izstrādes serveris |

### Servera puse (Backend)

| Tehnoloģija | Pielietojums |
|---|---|
| **Laravel 12** | PHP ietvars ar MVC arhitektūru |
| **Inertia.js (Laravel adapter)** | Datu pārsūtīšana starp serveri un klientu |
| **Laravel Sail** | Docker izstrādes vide |

### Datubāze un rīki

| Tehnoloģija | Pielietojums |
|---|---|
| **MySQL 8.0** | Relāciju datubāze |
| **Git** | Versiju kontrole |

---

## Projekta struktūra

```
meistari/
├── app/
│   ├── Enums/              # PHP enum klases (lomas, statusi, tipi)
│   ├── Http/
│   │   ├── Controllers/    # Kontrolieri, sadalīti pa jomām
│   │   ├── Middleware/     # CheckRole un citi middleware
│   │   └── Requests/       # FormRequest validācijas klases
│   └── Models/             # Eloquent modeļi
├── database/
│   ├── factories/          # Model factories testēšanai un seedošanai
│   ├── migrations/         # Datubāzes migrācijas
│   └── seeders/            # Datu iesēšana
├── resources/
│   ├── js/
│   │   ├── Components/     # Vue komponentes
│   │   ├── Layouts/        # Izkārtojumu komponentes
│   │   └── Pages/          # Inertia lapu komponentes
│   └── css/                # Tailwind stili
├── routes/
│   └── web.php             # Visi maršruti
└── docker-compose.yml      # Sail konfigurācija
```

---

## Priekšnosacījumi

Pirms projekta palaišanas pārliecinies, ka tavā datorā ir uzstādīts:

- **Docker Desktop** — [https://www.docker.com/products/docker-desktop](https://www.docker.com/products/docker-desktop)
- **Git** — [https://git-scm.com](https://git-scm.com)

Sail izmanto Docker, tāpēc nav nepieciešams lokāli instalēt PHP, Composer, Node.js vai MySQL — viss darbojas konteineros.

---

## Instalācija un palaišana

### 1. Klonē repozitoriju

```bash
git clone https://github.com/TAVS_LIETOTAJVARDS/meistari.git
cd meistari
```

### 2. Nokopē vides konfigurācijas failu

```bash
cp .env.example .env
```

### 3. Instalē Composer atkarības caur Docker

Ja tev vēl nav Sail instalēts, izmanto šo komandu, lai palaistu Composer caur pagaidu Docker konteineru:

```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php84-composer:latest \
    composer install --ignore-platform-reqs
```

### 4. Palaid Sail konteinerus

```bash
./vendor/bin/sail up -d
```

Pirmajā reizē Docker lejupielādēs nepieciešamos attēlus — tas var aizņemt dažas minūtes.

### 5. Ģenerē aplikācijas atslēgu

```bash
./vendor/bin/sail artisan key:generate
```

### 6. Izpildi datubāzes migrācijas un iesēj datus

```bash
./vendor/bin/sail artisan migrate --seed
```

### 7. Instalē NPM atkarības un palaid frontend izstrādes serveri

```bash
./vendor/bin/sail npm install
./vendor/bin/sail npm run dev
```

### 8. Atver pārlūkprogrammā

Platforma ir pieejama adresē: [http://localhost](http://localhost)

---

## Noklusējuma lietotāji

Pēc `migrate --seed` izpildes ir pieejami šādi testa konti:

| Loma | E-pasts | Parole |
|---|---|---|
| Administrators | `admin@meistari.lv` | `password` |
| Moderators | `moderator@meistari.lv` | `password` |
| Meistars | `meistars@meistari.lv` | `password` |
| Meklētājs | `mekletajs@meistari.lv` | `password` |

---

## Licence

Šis projekts ir izstrādāts kā kvalifikācijas eksāmena praktiskā daļa un nav paredzēts komerciālai izmantošanai.
