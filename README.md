# Nwafth

Laravel 13 application running on Docker (PHP 8.4-FPM + nginx + MySQL 8.4), with an
Inertia v3 + Vue 3 + Tailwind CSS v4 front-end.

| Surface | Base URI | Consumer | Auth |
| --- | --- | --- | --- |
| **Website** (Inertia) | `/` | visitors — bilingual (ar/en), light/dark | **none — fully public** |
| **Dashboard** (Inertia) | `/dashboard` | admins | session (`web` guard) bound to `App\Models\Admin` |
| **Website API** | `/api/website/v1` | external front-ends | **none — fully public** |
| **Admin API** | `/api/admin/v1` | external admin clients | Sanctum token bound to `App\Models\Admin` |

The Inertia website/dashboard and the JSON APIs share the same models and
`App\Services\*` classes, so business rules (and website cache invalidation) live
in one place.

## Website & dashboard

- **Routes:** `routes/web.php` (website, `website.*`) and `routes/dashboard.php`
  (`dashboard.*`, loaded from `bootstrap/app.php`).
- **Pages:** `resources/js/pages/website/*` and `resources/js/pages/dashboard/*`; the
  layout is chosen by page name in `resources/js/app.js`.
- **Languages:** `App\Enums\Locale` (ar/en). `App\Http\Middleware\SetLocale` resolves
  the locale from the session, then a cookie, then `Accept-Language`;
  `GET /locale/{ar|en}` switches it. Front-end strings use `$t('English text')`, with
  Arabic in `lang/ar.json` (shared to Vue by `HandleInertiaRequests`). Bilingual
  content columns (`title_ar` / `title_en`, ...) are read with
  `HasLocalizedAttributes::localized()`.
- **Dark/light mode:** toggled client-side (`useTheme`), persisted in `localStorage`,
  applied before first paint by an inline script in `resources/views/app.blade.php`.
- **Caching:** homepage sections and settings are cached as plain arrays by
  `App\Support\Cache\WebsiteContentCache`; every content service write flushes it.
- **Site settings** (phone, email, address, social links, company profile URL) are
  edited in the dashboard and stored in the `settings` table (`App\Enums\SettingKey`).

Front-end assets:

```bash
npm install
npm run dev     # during development (Vite dev server)
npm run build   # before deploying — the Docker image does not build assets
```

## Requirements

- Docker Desktop (or Docker Engine + Compose v2)
- Node.js 20+ (front-end assets)

PHP, Composer and MySQL all run inside containers.

## Quick start

```bash
./run.sh
```

This creates `.env` (if missing), builds the image, starts `app` / `nginx` / `mysql`,
installs Composer deps, generates `APP_KEY`, runs migrations, and seeds the default
accounts. When it finishes:

| What | Where |
| --- | --- |
| Website | http://localhost:8000 |
| Dashboard | http://localhost:8000/dashboard |
| Website API | http://localhost:8000/api/website/v1 |
| Admin API | http://localhost:8000/api/admin/v1 |
| MySQL | `127.0.0.1:3306` — db `nwafth`, user `nwafth`, pass `secret` |
| Default admin (super admin) | `admin@example.com` / `password` |

## Website API

Fully public — no authentication, no user accounts. Add more controllers under
`App\Http\Controllers\Api\Website\V1\` and register routes in
`routes/api/website/v1.php` as new public endpoints are needed.

The website has no login flow and no user accounts of its own. The only accounts
in this application are admin-panel accounts (`App\Models\Admin`).

### Hero Text

A `HeroText` (`id`, `text_ar`, `text_en`, timestamps) is the single, site-wide
headline shown in the homepage hero section — content the admin panel manages and the
website reads. Unlike every other resource below, it's a **singleton**: there's
exactly one row, managed via get/update rather than a list CRUD (no `index`, `store`
or `destroy` — no id in the URL at all).

| Method | Path | Auth | Purpose |
| --- | --- | --- | --- |
| GET | `/api/website/v1/hero-text` | – | The current hero text, or `data: null` if unset |
| GET | `/api/admin/v1/hero-text` | admin | Same, for the admin panel |
| PUT | `/api/admin/v1/hero-text` | admin | Create (first call) or replace (every call after) |

Open to any admin (`admin` or `super_admin`) — unlike `/admins`, it isn't
role-restricted.

### Products

A `Product` (`id`, `title_ar`, `title_en`, `description_ar`, `description_en`,
`cover_url`, `icon_url`, `links`, `order`, timestamps) plus multiple media files via
[Spatie Media Library](https://spatie.be/docs/laravel-medialibrary). `cover_url` /
`icon_url` are plain URL fields (e.g. hosted on a CDN) for a single main image and a
small icon; `links` is a JSON array of `{"url": "...", "type": "..."}` objects (e.g. an
external store link, a video). The media gallery is managed separately from the text
fields, as `multipart/form-data` uploads. Still a paginated catalog (unlike the
"small set" resources below) — just sorted by `order` instead of recency.

| Method | Path | Auth | Purpose |
| --- | --- | --- | --- |
| GET | `/api/website/v1/products` | – | Paginated list, ordered by `order` by default (`?search=`, `?per_page=`, `?sort=latest` for newest-first by `created_at`) |
| GET | `/api/website/v1/products/{product}` | – | Show one product, with its media |
| GET | `/api/admin/v1/products` | admin | Paginated list, ordered (`?search=`, `?per_page=`) |
| POST | `/api/admin/v1/products` | admin | Create (JSON body — text fields only) |
| GET | `/api/admin/v1/products/{product}` | admin | Show |
| PUT | `/api/admin/v1/products/{product}` | admin | Update text fields |
| DELETE | `/api/admin/v1/products/{product}` | admin | Delete (media is auto-removed with it) |
| POST | `/api/admin/v1/products/{product}/media` | admin | Upload one or more files (field `media[]`) |
| DELETE | `/api/admin/v1/products/{product}/media/{media}` | admin | Remove one media item |

```bash
# upload media
curl -X POST http://localhost:8000/api/admin/v1/products/1/media \
  -H "Authorization: Bearer $TOKEN" \
  -F "media[]=@photo1.jpg" -F "media[]=@photo2.jpg"
```

Uploaded files are stored on the `public` disk (`storage/app/public`, served via the
existing `public/storage` symlink) and returned as `{ id, url, name, mime_type, size }`
under each product's `media` array. Product management, like the rest of content
management, is open to any admin.

### Technologies

A `Technology` (`id`, `title_ar`, `title_en`, `icon_url`, `order`, timestamps) — a
small, complete set (e.g. a "tech stack" logo list), so the website endpoint returns
everything unpaginated and ordered, same as Departments and Expertise.

| Method | Path | Auth | Purpose |
| --- | --- | --- | --- |
| GET | `/api/website/v1/technologies` | – | List all technologies |
| GET | `/api/website/v1/technologies/{technology}` | – | Show one technology |
| GET | `/api/admin/v1/technologies` | admin | Paginated list (`?search=`, `?per_page=`) |
| POST | `/api/admin/v1/technologies` | admin | Create |
| GET | `/api/admin/v1/technologies/{technology}` | admin | Show |
| PUT | `/api/admin/v1/technologies/{technology}` | admin | Update |
| DELETE | `/api/admin/v1/technologies/{technology}` | admin | Delete |

Same as products: open to any admin, not role-restricted.

### Departments

A `Department` (`id`, `title_en`, `title_ar`, `sub_title_ar`, `sub_title_en`,
`cover_url`, `order`, timestamps) — another small, complete set, so the website
endpoint returns everything unpaginated (and manually ordered), same as Technologies
and Expertise. `cover_url` is required (it's the department's one visual); `sub_title_*`
are optional taglines.

| Method | Path | Auth | Purpose |
| --- | --- | --- | --- |
| GET | `/api/website/v1/departments` | – | List all departments |
| GET | `/api/website/v1/departments/{department}` | – | Show one department |
| GET | `/api/admin/v1/departments` | admin | Paginated list (`?search=`, `?per_page=`) |
| POST | `/api/admin/v1/departments` | admin | Create |
| GET | `/api/admin/v1/departments/{department}` | admin | Show |
| PUT | `/api/admin/v1/departments/{department}` | admin | Update |
| DELETE | `/api/admin/v1/departments/{department}` | admin | Delete |

Same as products/technologies: open to any admin, not role-restricted.

### Expertise

An `Expertise` (`id`, `title_ar`, `title_en`, `description_ar`, `description_en`,
`order`, timestamps) — areas of expertise shown on the site (e.g. "Web Development",
"Cybersecurity"). Same shape and rules as Departments: manually ordered (`order` ascending,
ties broken by `id`), small complete set so the website endpoint is unpaginated. Note
the URI is singular (`/expertise`, not `/expertises`) even though the underlying table
is `expertises` (Eloquent's default pluralization) — the route name doesn't have to
match the table name.

| Method | Path | Auth | Purpose |
| --- | --- | --- | --- |
| GET | `/api/website/v1/expertise` | – | List all, ordered for display |
| GET | `/api/website/v1/expertise/{expertise}` | – | Show one |
| GET | `/api/admin/v1/expertise` | admin | Paginated list (`?search=`, `?per_page=`) |
| POST | `/api/admin/v1/expertise` | admin | Create |
| GET | `/api/admin/v1/expertise/{expertise}` | admin | Show |
| PUT | `/api/admin/v1/expertise/{expertise}` | admin | Update |
| DELETE | `/api/admin/v1/expertise/{expertise}` | admin | Delete |

Same as products/technologies/departments: open to any admin, not
role-restricted.

### Differences

A `Difference` (`id`, `cover_url`, `title_ar`, `title_en`, `order`, timestamps) — "what
sets us apart" style points (e.g. "24/7 Support", "Certified Team"). Same shape and
rules as Departments: `cover_url` required (the one visual), manually ordered, small
complete set so the website endpoint is unpaginated.

| Method | Path | Auth | Purpose |
| --- | --- | --- | --- |
| GET | `/api/website/v1/differences` | – | List all, ordered for display |
| GET | `/api/website/v1/differences/{difference}` | – | Show one |
| GET | `/api/admin/v1/differences` | admin | Paginated list (`?search=`, `?per_page=`) |
| POST | `/api/admin/v1/differences` | admin | Create |
| GET | `/api/admin/v1/differences/{difference}` | admin | Show |
| PUT | `/api/admin/v1/differences/{difference}` | admin | Update |
| DELETE | `/api/admin/v1/differences/{difference}` | admin | Delete |

Same as the other content resources: open to any admin, not role-restricted.

## Admin API — authentication

Bearer tokens issued by [Laravel Sanctum](https://laravel.com/docs/sanctum), bound to
`App\Models\Admin`.

```bash
curl -X POST http://localhost:8000/api/admin/v1/auth/login \
  -H 'Content-Type: application/json' \
  -d '{"email":"admin@example.com","password":"password"}'
# -> { "data": { "admin": {...}, "token": "1|abc..." } }

curl http://localhost:8000/api/admin/v1/auth/me \
  -H 'Authorization: Bearer 1|abc...'
```

`App\Http\Middleware\EnsureIsAdmin` (alias `admin`) also rejects inactive accounts
(`is_active = false`).

## Admin API — authorization

Authentication only answers "is this an admin token at all?". What a given admin can
*do* is governed by role-based policies on top of that:

| Role (`App\Enums\AdminRole`) | Can |
| --- | --- |
| `admin` (default) | Manage content resources and their own profile |
| `super_admin` | Everything an `admin` can, **plus** manage admin accounts (`/admins`) |

Enforced by a policy (`Illuminate\Support\Facades\Gate::authorize(...)` in the
controllers — 403 on failure):

- **`App\Policies\AdminPolicy`** — `/admins` CRUD is super-admin only. A super admin
  can't delete their own account, and can't change their own `role` (both blocked so a
  super admin can never lock themselves out).

The default seeded admin is a `super_admin` so it can create the first regular admins.
`GET|PUT /admin/v1/profile` lets any admin manage their own name/email/password
without needing admin-management rights (role/active-status are not editable there).

## Endpoints

### Website — `/api/website/v1`

Public, unauthenticated. See [Hero Text](#hero-text), [Products](#products),
[Technologies](#technologies), [Departments](#departments), [Expertise](#expertise) and
[Differences](#differences) above; add more as the front-end needs them.

### Admin — `/api/admin/v1`

| Method | Path | Auth | Purpose |
| --- | --- | --- | --- |
| POST | `/auth/login` | – | Returns token |
| GET | `/auth/me` | admin | Current admin |
| POST | `/auth/logout` | admin | Revoke current token |
| GET / PUT | `/profile` | admin | View/update own name, email, password |
| GET | `/admins` | **super_admin** | List admins |
| POST | `/admins` | **super_admin** | Create an admin (`role`: `admin` \| `super_admin`) |
| GET | `/admins/{admin}` | **super_admin** | Show an admin |
| PUT | `/admins/{admin}` | **super_admin** | Update an admin (not their own role) |
| DELETE | `/admins/{admin}` | **super_admin** | Delete an admin (not themselves) |
| GET/PUT | `/hero-text` | admin | See [Hero Text](#hero-text) |
| GET/POST/PUT/DELETE | `/products`, `/products/{product}` | admin | See [Products](#products) |
| POST/DELETE | `/products/{product}/media[/{media}]` | admin | See [Products](#products) |
| GET/POST/PUT/DELETE | `/technologies`, `/technologies/{technology}` | admin | See [Technologies](#technologies) |
| GET/POST/PUT/DELETE | `/departments`, `/departments/{department}` | admin | See [Departments](#departments) |
| GET/POST/PUT/DELETE | `/expertise`, `/expertise/{expertise}` | admin | See [Expertise](#expertise) |
| GET/POST/PUT/DELETE | `/differences`, `/differences/{difference}` | admin | See [Differences](#differences) |

`php artisan route:list --path=api` shows the full list.

## Response envelope

```jsonc
// success
{ "message": "Login successful.", "data": { ... } }
// paginated (API Resource collections)
{ "data": [ ... ], "links": { ... }, "meta": { ... } }
// error
{ "message": "The email field is required.", "errors": { "email": ["..."] } }
```

## Project structure

```
app/
├── Http/
│   ├── Controllers/Api/
│   │   ├── ApiController.php            base controller (JSON response helpers)
│   │   ├── Website/V1/                  HeroTextController, ProductController, TechnologyController,
│   │   │                                DepartmentController, ExpertiseController, DifferenceController
│   │   │                                (public, read-only)
│   │   └── Admin/V1/                    AuthController, ProfileController, AdminController,
│   │                                    HeroTextController, ProductController, ProductMediaController,
│   │                                    TechnologyController, DepartmentController, ExpertiseController,
│   │                                    DifferenceController
│   ├── Middleware/
│   │   ├── ForceJsonResponse.php        every /api/* request is treated as JSON
│   │   └── EnsureIsAdmin.php            alias: admin (authentication: rejects non-Admin tokens)
│   ├── Requests/Api/Admin/V1/           form-request validation for the admin API
│   └── Resources/{Website,Admin}/V1/    API resources (output shaping) per surface
├── Enums/AdminRole.php                 admin | super_admin
├── Models/                             Admin (Sanctum via HasApiTokens), HeroText (singleton),
│                                        Product (HasMedia), Technology, Department, Expertise, Difference
├── Policies/                           AdminPolicy (authorization, role-based)
└── Support/Concerns/ApiResponse.php    respondOk / respondCreated / respondError ...

routes/
├── api.php                             mounts the two surfaces + JSON 404 fallback
└── api/
    ├── website/v1.php                  prefix /api/website/v1 · public
    └── admin/v1.php                    prefix /api/admin/v1   · names api.admin.v1.*

database/
├── migrations/                         users (+ is_active), admins (+ role), hero_texts, products, technologies,
│                                        departments, expertises, differences, media, personal_access_tokens
├── factories/                          AdminFactory (states: inactive, superAdmin), HeroTextFactory,
│                                        ProductFactory, TechnologyFactory, DepartmentFactory, ExpertiseFactory,
│                                        DifferenceFactory
└── seeders/                            AdminSeeder (default super admin), HeroTextSeeder, ProductSeeder,
                                         TechnologySeeder, DepartmentSeeder, ExpertiseSeeder, DifferenceSeeder
                                         (all local only) + DatabaseSeeder
```

### Adding `v2`

Add `routes/api/website/v2.php` (or `admin/v2.php`), a matching `V2` controller
namespace, then mount it in `routes/api.php` with prefix `website/v2` (or `admin/v2`).

## `run.sh` commands

| Command | Description |
| --- | --- |
| `./run.sh` | Build (if needed) and start the whole stack. |
| `./run.sh stop` / `down` | Stop / remove containers (database volume kept). |
| `./run.sh destroy` | Remove everything **including the database volume**. |
| `./run.sh fresh` | Wipe the volume, rebuild, re-migrate + re-seed. |
| `./run.sh seed` | Re-run seeders. |
| `./run.sh artisan <...>` | Run Artisan in the `app` container. |
| `./run.sh composer <...>` | Run Composer in the `app` container. |
| `./run.sh test` | Run the PHPUnit suite. |
| `./run.sh shell` | Bash shell in the `app` container. |
| `./run.sh logs` | Tail container logs. |

## Configuration (`.env`)

| Variable | Default | Purpose |
| --- | --- | --- |
| `APP_PORT` | `8000` | Host port for the API. |
| `DB_FORWARD_PORT` | `3306` | Host port forwarded to MySQL. |
| `DB_DATABASE` / `DB_USERNAME` / `DB_PASSWORD` | `nwafth` / `nwafth` / `secret` | App DB credentials. |
| `DB_ROOT_PASSWORD` | `root` | MySQL root password. |
| `ADMIN_NAME` / `ADMIN_EMAIL` / `ADMIN_PASSWORD` | `Super Admin` / `admin@example.com` / `password` | Seeded default admin. |

`DB_HOST` is `mysql` (the compose service name) — leave it as is.

## Docker layout

```
docker/
├── Dockerfile           PHP 8.4-FPM + pdo_mysql/gd/intl/zip/opcache + Composer
├── nginx/default.conf    nginx vhost -> php-fpm (app:9000)
└── php/php.ini           PHP overrides (memory, upload size, OPcache)
docker-compose.yml        app + nginx + mysql (healthcheck + named volume)
run.sh                    lifecycle helper
```

## Tests

```bash
./run.sh test
```

Feature tests live in `tests/Feature/Api/{Admin,Website}/`. They run against an
in-memory SQLite database (see `phpunit.xml`) and a faked `public` disk for media, so
they touch neither MySQL nor real files.

## Postman collection

`docs/postman/Nwafth-API.postman_collection.json` covers all 50 endpoints (both API
surfaces, every resource) — regenerate it with `python3 docs/postman/build_collection.py`
after adding or changing an endpoint. Import it into Postman and:

1. Run **Admin API → Auth → Login** — the token is captured automatically and reused by
   every other Admin API request.
2. Run anything else. Each `Create` request saves its new record's id (e.g.
   `product_id`) to a collection variable that the matching `Show`/`Update`/`Delete`/
   website `Show` requests reuse, so a whole folder can be run top-to-bottom. The Hero
   Text folder is the exception — it's a singleton (get/update only, no id).
3. `Admin API → Logout (run this last)` is placed at the very end on purpose — running
   it earlier would revoke the token the other folders still need.

Defaults to `http://localhost:8000` (the `base_url` collection variable) and the seeded
admin credentials. Verified end-to-end with `newman run` — 50/50 requests complete; a
handful of `Create` assertions can fail on a re-run against a non-fresh database
(unique-title validation colliding with data a previous run already created — re-seed
with `./run.sh fresh` for a clean pass), and the media-upload step needs a real file
attached, so it only fails under an unattended newman run without one and works
normally from the Postman GUI where you select a file.
