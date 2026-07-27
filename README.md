# SIINSOS

Aplikasi gabungan **landing** (mahasiswa/dosen) + **dashboard admin** (Tim MK Penciri) dalam satu proyek Laravel.

## Mulai cepat

Lihat panduan lengkap: **[PANDUAN_ADAPTASI_SIINSOS.md](./PANDUAN_ADAPTASI_SIINSOS.md)**

```bash
composer install
copy .env.example .env   # atau: cp .env.example .env
# Edit .env → DB MySQL + APP_URL (samakan APP_DASHBOARD_URL & APP_LANDING_URL)
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
php artisan serve
```

- Landing: `http://localhost:8000`
- Admin: `http://localhost:8000/admin`
- Login admin seed: `admin@insos.test` / `password123`

## Catatan

- Satu database MySQL saja.
- Tidak membutuhkan folder sibling `project-akhir` / `dashboard`.
- Detail database: `docs/DEPLOYMENT-DATABASE.md`
