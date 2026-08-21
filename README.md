# Pati — recipe site

A cooking-recipe site. One admin manages recipes via Filament; everyone else
browses as a guest (no public accounts). Site copy is Romanian.

- **Public** (Blade): `/` recipe index + `/recipes/{slug}` detail.
- **Admin** (Filament): `/admin` — recipe CRUD with a rich-text method editor.

Stack: Laravel 13 · PHP 8.3 · PostgreSQL 16 · Filament 5 · mews/purifier ·
hand-written Docker Compose (no Sail).

## Local development

```bash
docker compose up -d --build
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan storage:link   # first run only
```

- Site: http://localhost:8080
- Admin: http://localhost:8080/admin
- Seeded admin login: `ADMIN_EMAIL` / `ADMIN_PASSWORD` from `.env` (defaults
  `taranmihai98@gmail.com` / `changeme` — change these).

The Docker image maps `www-data` to UID/GID 1000 so the bind-mounted
`storage/` is writable. If your host user isn't 1000, rebuild with:
`docker compose build --build-arg UID=$(id -u) --build-arg GID=$(id -g) app`.

### Common commands

```bash
docker compose exec app php artisan test        # run the test suite
docker compose exec app php artisan migrate:fresh --seed
docker compose exec app composer <...>
```

## Production

HTTPS only, via `docker-compose.prod.yml`:

```bash
cp .env.prod.example .env.prod          # then fill in real secrets
# set server_name in docker/nginx/app-prod.conf, drop TLS certs in docker/nginx/ssl/
docker compose --env-file .env.prod -f docker-compose.prod.yml up -d --build
docker compose --env-file .env.prod -f docker-compose.prod.yml exec app \
  composer install --no-dev --optimize-autoloader
docker compose --env-file .env.prod -f docker-compose.prod.yml exec app php artisan migrate --force
```

The app code is bind-mounted, so a plain `up -d` never rebuilds the image — pass
`--build` after any `Dockerfile` change (e.g. a new PHP extension), or the
container keeps running the old one.

Certs: `docker/nginx/ssl/fullchain.pem` + `privkey.pem` (git-ignored), or point
that volume at your Let's Encrypt live dir. HTTP is refused by design.

## Design source

`design/CookingSite.dc.html` (markup + Romanian copy + the 6 seed recipes) and
`design/styles.css` (the design system) are the source of truth for the two
public pages. `public/css/styles.css` is that stylesheet plus the `.recipe-method`
and photo rules the Blade views add.

## Data notes

- Single `recipes` table; `ingredients` is a JSON array of display strings.
- The method lives in `description` as a sanitized `<ol>` — no `steps` column.
  It is sanitized (mews/purifier) on save in `Recipe::saving()` and rendered
  with `{!! !!}` inside `.recipe-method`.
- `calories` shows on the index card meta line only, not the detail page.
- Photos upload to the `public` disk under `recipes/`, served via `storage:link`.
