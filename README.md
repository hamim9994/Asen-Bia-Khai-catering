# Asen Bia Khai — Wedding Catering

A single-page website for a wedding catering service, with a small PHP + PostgreSQL
backend for capturing orders and customer reviews. Built by a team of three — see
[Team](#team).

The front end is hand-written HTML, CSS and vanilla JavaScript — there is no build
step, no bundler and no `package.json`. Open `index.html` in a browser and it works.
The backend is two PHP endpoints served by Apache, containerised for deployment.

## Contents

- [Features](#features)
- [Tech stack](#tech-stack)
- [Team](#team)
- [Project structure](#project-structure)
- [Running locally](#running-locally)
- [Database](#database)
- [API](#api)
- [Deployment](#deployment)
- [Known issues](#known-issues)
- [License](#license)

## Features

`index.html` is the whole site — six sections linked by the anchors in the header nav:

| Section | Anchor | What's there |
| --- | --- | --- |
| Hero | `#home` | Full-height banner, "Request a Quote" / "View Menus" buttons |
| Menus | `#menus` | Four catering packages as hover-dimmed cards, priced per person in BDT (৳110 – ৳500) |
| Gallery | `#gallery` | Four-column masonry grid of event photography |
| Testimonials | `#testimonials` | Auto-advancing slider (5s), prev/next arrows and clickable dots |
| About | `#about` | Four stat cards with Font Awesome icons |
| Contact | `#contact` | Inquiry form — name, email, event date, guest-count range, message |

`contact.html` is a standalone copy of the contact section, reachable only by typing
the URL — nothing on the site links to it.

Fonts come from Google Fonts (Cormorant Garamond, Poppins) and icons from a Font
Awesome 6.5.2 CDN, so the site needs a network connection to render as designed.

## Tech stack

- **Front end** — HTML5, CSS3 (flexbox, `:has()`, media queries at 900px and 770px), vanilla JS
- **Back end** — PHP 8.2, PDO
- **Database** — PostgreSQL
- **Container** — `php:8.2-apache` with `pdo_pgsql` and `mod_rewrite`

## Team

A three-person team project. Each section of `index.html` was built by one member, and
the code is annotated accordingly — the `<!-- eva -->`, `<!-- hamim -->` and
`<!-- limon -->` comments in `index.html` bracket each person's work.

| Member | GitHub | Sections |
| --- | --- | --- |
| Md Hikmotier Rahman Hamim | [@hamim9994](https://github.com/hamim9994) | Menus (`#menus`), Gallery (`#gallery`) |
| Mst. Israt Jahan Eva | [@ISRAT510](https://github.com/ISRAT510) | Hero (`#home`), Contact (`#contact`) |
| Limon | — | Testimonials (`#testimonials`), About (`#about`) |

Shared across the team: `style.css`, `script.js`, the PHP backend, and the Docker setup.

## Project structure

```
.
├── index.html              Main single-page site
├── contact.html            Standalone contact page
├── style.css               All styles (~750 lines, one file for both pages)
├── script.js               Testimonial slider only
├── index.php               Front controller — routes /, /backend/api/*, static files
├── Dockerfile              php:8.2-apache image
├── .htaccess               Empty; the Dockerfile writes the real one at build time
├── backend/
│   ├── config/
│   │   └── database.php    PDO connection from DB_* env vars
│   └── api/
│       ├── oders.php       Orders endpoint   (filename typo — see Known issues)
│       └── reviwe.php      Reviews endpoint  (filename typo — see Known issues)
└── src/Images/
    ├── hero/               Hero photography
    ├── gallary/            Gallery photography (13 files)
    └── *.png               Menu card artwork
```

Sections in `index.html` are bracketed by `<!-- name -->` comments marking which team
member wrote each one — see [Team](#team). They are ownership markers, not code.

## Running locally

### Front end only

No tooling required. Open `index.html` directly, or serve the folder:

```bash
python -m http.server 8000
# → http://localhost:8000
```

The slider, hover states and layout all work. The forms don't submit anywhere —
see [Known issues](#known-issues).

### Full stack with Docker

```bash
docker build -t asen-bia-khai .
docker run -p 8080:80 \
  -e DB_HOST=host.docker.internal \
  -e DB_PORT=5432 \
  -e DB_NAME=catering_db \
  -e DB_USER=postgres \
  -e DB_PASSWORD=your_password \
  asen-bia-khai
# → http://localhost:8080
```

### Full stack with PHP's built-in server

Requires PHP 8.2+ with `pdo_pgsql` enabled and a reachable PostgreSQL instance.

```bash
export DB_HOST=localhost DB_PORT=5432 DB_NAME=catering_db \
       DB_USER=postgres DB_PASSWORD=your_password
php -S localhost:8080
```

## Database

`backend/config/database.php` reads these environment variables, each with a
fallback for local development:

| Variable | Default |
| --- | --- |
| `DB_HOST` | `localhost` |
| `DB_PORT` | `5432` |
| `DB_NAME` | `catering_db` |
| `DB_USER` | `postgres` |
| `DB_PASSWORD` | hard-coded literal — **see [Known issues](#known-issues)** |

### Schema

The repository has no migration file. The two tables below are inferred from the
`INSERT`/`SELECT` statements in the API endpoints — create them before the API will
work:

```sql
CREATE TABLE orders (
    id           SERIAL PRIMARY KEY,
    customer_name TEXT        NOT NULL,
    email        TEXT         NOT NULL,
    phone        TEXT,
    event_date   DATE,
    menu_items   TEXT,
    message      TEXT,
    created_at   TIMESTAMPTZ  NOT NULL DEFAULT now()
);

CREATE TABLE reviews (
    id            SERIAL PRIMARY KEY,
    customer_name TEXT        NOT NULL,
    rating        INTEGER     NOT NULL,
    comment       TEXT        NOT NULL,
    is_approved   BOOLEAN     NOT NULL DEFAULT FALSE,
    created_at    TIMESTAMPTZ NOT NULL DEFAULT now()
);
```

Reviews default to unapproved; `GET /backend/api/reviwe.php` only returns rows where
`is_approved = TRUE`, so new submissions stay hidden until flipped manually.

## API

Both endpoints send `Content-Type: application/json` and `Access-Control-Allow-Origin: *`.
Paths are the literal filenames, typos included.

### `POST /backend/api/oders.php`

Records a catering order. Body is JSON; `name` and `email` are required, the rest optional.

```bash
curl -X POST http://localhost:8080/backend/api/oders.php \
  -H 'Content-Type: application/json' \
  -d '{
    "name": "Emma & Jack",
    "email": "emma@example.com",
    "phone": "+8801700000000",
    "event_date": "2026-12-01",
    "menu_items": "BURI BHOJ MOHA AYOJON",
    "message": "200 guests, outdoor venue"
  }'
```

```json
{ "success": true, "message": "Order placed successfully!" }
```

Any method other than `POST` returns `{"success": false, "message": "Method not allowed"}`.

### `POST /backend/api/reviwe.php`

Submits a review. `name`, `rating` and `comment` are all required.

```bash
curl -X POST http://localhost:8080/backend/api/reviwe.php \
  -H 'Content-Type: application/json' \
  -d '{"name": "Sophia", "rating": 5, "comment": "Everything was perfect."}'
```

```json
{ "success": true, "message": "Review submitted successfully!" }
```

### `GET /backend/api/reviwe.php`

Returns every approved review as a JSON array, newest first.

```bash
curl http://localhost:8080/backend/api/reviwe.php
```

## Deployment

The `Dockerfile` targets a container host — it was written for Render, judging by the
comment in `backend/config/database.php`. At build time it installs `pdo_pgsql`,
enables `mod_rewrite`, copies the project to `/var/www/html/`, sets
`DirectoryIndex index.php index.html`, overwrites `.htaccess` with a rewrite of `/`
to `index.php`, and exposes port 80.

Set the five `DB_*` variables in the host's environment. Because `index.php` is the
directory index, requests to `/` go through the front controller, which reads
`index.html` from disk and returns it.

## Known issues

Real gaps in the current code, roughly by severity.

**A live database password is committed to the repository.**
`backend/config/database.php:7` uses a real-looking PostgreSQL password as its
fallback default. It is in the git history, so rotating the credential is not enough
on its own — the value must be treated as compromised, rotated at the database, and
the fallback replaced with a failure (or an empty default) rather than a secret.

**Neither contact form submits anything.** The `<form>` elements in `index.html` and
`contact.html` have no `action`, no `method` and no JavaScript handler. Pressing
"SEND INQUIRY" reloads the page and discards the input. Nothing in the front end
calls either API endpoint — `script.js` contains only the slider. Wiring the form to
`POST /backend/api/oders.php` is the main piece of missing work.

**`click1()` is undefined.** `index.html:88` sets `onclick="click1()"` on the first
menu card, but no such function exists. Clicking it throws a `ReferenceError`.

**Two gallery images are missing.** `index.html:179-180` reference
`src/Images/gallary/download (1).jpg` and `download (2).jpg`, which are not in the
repository — the third gallery column renders as broken images. Meanwhile
`pexels-jonathanborba-12876498.jpg`, `pngtree-catering-wedding-decoration-…jpg` and
`street-food-still-life.jpg` are present but never referenced.

**`.quote` is overloaded.** It styles the hero's "Request a Quote" button
(`style.css:605`) *and* is applied to the `❝` glyph in each testimonial
(`index.html:216`), so the quote mark renders as a gold pill. The rule intended for
it, `.quote-icon` (`style.css:459`), is never used. Separately, `.quote-btn`
(`style.css:205,214`) is defined but matches no element.

**`contact.html` nav is inert.** Every link except Home and Contact points at `#`,
and the Home link targets `index.html` while the container serves `index.php` as the
directory index.

**The API's `require` breaks under the front controller.** `oders.php` and
`reviwe.php` both `require_once '../config/database.php'`, which PHP resolves against
the working directory. Apache serves those files directly today, so it works — but if
a request is ever routed through `index.php`'s `/backend/api/` branch, the path
resolves outside the web root and fails. Latent, not currently firing.

**`.gitignore` is the wrong template.** It is the standard Python `.gitignore`
(`__pycache__/`, `*.egg-info`, virtualenvs) and covers nothing in this project. Note
that its `lib/` entry would ignore a directory of that name if one were ever added.

**No tests, linting, or CI.**

## License

MIT — see [LICENSE](LICENSE). Copyright (c) 2026 H.R Hamim, on behalf of the
project [team](#team).
