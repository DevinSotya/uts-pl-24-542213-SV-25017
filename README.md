# uts-pl-24-542213-SV-25017

UTS KEPL: aplikasi **Books Tracker** (pencatat bacaan) dengan backend Laravel (REST API + token Sanctum), frontend Vue.js, Docker Compose, dan CI/CD GitHub Actions ke GitHub Container Registry (GHCR).

Repository: https://github.com/DevinSotya/uts-pl-24-542213-SV-25017

## Arsitektur

```
Browser --> Nginx (frontend, :8080) --/api/*--> FrankenPHP + Laravel (backend, :8000) --> MySQL 8.4 (db)
```

| Service | Teknologi | Port host | Keterangan |
|---|---|---|---|
| `frontend` | Vue 3 + Vue Router + Axios, disajikan Nginx 1.28 | 8080 | Permintaan `/api/*` diteruskan Nginx ke `backend:8000` lewat nama service |
| `backend` | Laravel 12 + Sanctum, dilayani FrankenPHP 1.13.3 (PHP 8.2) | 8000 | Migrasi dan seeder dijalankan otomatis saat container start |
| `db` | MySQL 8.4 | tidak dipublish | Data di named volume `mysql_data` |

## Struktur folder

```
backend/    Laravel (API, tes, Dockerfile, Caddyfile, koleksi Postman di backend/postman/)
frontend/   Vue (halaman login dan CRUD buku, Dockerfile multi-stage, nginx.conf)
docker-compose.yml
.env.example
.github/workflows/ci.yml
```

## Menjalankan dengan Docker Compose

Prasyarat: Docker Desktop (Docker Compose v2).

1. Salin contoh env, lalu isi nilainya (jangan di-commit):

   ```
   cp .env.example .env
   ```

   PowerShell: `Copy-Item .env.example .env`

2. Isi `.env`:

   | Variabel | Isi |
   |---|---|
   | `APP_KEY` | `base64:` + 32 byte acak yang di-encode base64 (cara membuatnya ada di bawah tabel) |
   | `DB_PASSWORD`, `DB_ROOT_PASSWORD` | Password acak (huruf dan angka saja, tanpa tanda `$`) |
   | `SEED_USER_EMAIL`, `SEED_USER_PASSWORD` | Akun login aplikasi; dibuat otomatis oleh seeder |

   Membuat `APP_KEY` tanpa PHP lokal (tambahkan awalan `base64:` pada hasilnya):

   ```
   docker run --rm alpine sh -c "head -c 32 /dev/urandom | base64"
   ```

   Jika PHP tersedia, cukup `php artisan key:generate --show` di folder `backend`.

3. Jalankan seluruh sistem dengan satu perintah:

   ```
   docker compose up -d --build
   ```

4. Buka aplikasi di http://localhost:8080 dan login dengan `SEED_USER_EMAIL` dan `SEED_USER_PASSWORD`. API tersedia di http://localhost:8000/api.

5. Berhenti:

   ```
   docker compose down
   ```

   Data tetap tersimpan di volume `mysql_data`. Perintah `docker compose down -v` **menghapus volume beserta datanya**.

## Endpoint API

Semua permintaan dan balasan berformat JSON. Semua endpoint selain login membutuhkan header `Authorization: Bearer <token>`.

| Method | Endpoint | Keterangan | Status |
|---|---|---|---|
| POST | `/api/login` | Login, mengembalikan token | 200, 401 (kredensial salah), 422 (input tidak valid) |
| POST | `/api/logout` | Mencabut token yang dipakai | 200 |
| GET | `/api/user` | Data user yang sedang login | 200 |
| GET | `/api/books` | Daftar buku | 200 |
| POST | `/api/books` | Tambah buku | 201, 422 |
| GET | `/api/books/{id}` | Detail buku | 200, 404 |
| PUT/PATCH | `/api/books/{id}` | Ubah buku | 200, 422 |
| DELETE | `/api/books/{id}` | Hapus buku | 200 |

Tanpa token, semua endpoint selain login mengembalikan 401 (JSON). Koleksi Postman: `backend/postman/UTS-KEPL-API.postman_collection.json`.

## Tes

```
cd backend
composer install
php artisan test
```

## CI/CD (GitHub Actions)

Workflow `.github/workflows/ci.yml`:

| Job | Berjalan di | Isi |
|---|---|---|
| `test` | Semua push dan pull request | PHP 8.2, service MySQL 8.4, `php artisan test` |
| `build` | Semua push dan pull request | Node 22, `npm ci`, `npm run build` |
| `deploy` | **Hanya push ke `main`** | Build image backend dan frontend, push ke GHCR dengan tag = nomor commit |

Image di GHCR (tag berupa hash commit penuh, tanpa `latest`):

- `ghcr.io/devinsotya/uts-pl-24-542213-sv-25017-backend`
- `ghcr.io/devinsotya/uts-pl-24-542213-sv-25017-frontend`

Nilai rahasia (`APP_KEY`, `DB_PASSWORD`) disimpan di GitHub Secrets, bukan di repository.

## Alur Git

Branch `main` dan `dev` dilindungi: perubahan hanya lewat Pull Request dan job `test` serta `build` harus lulus. Pekerjaan dilakukan di branch `feature/*`, digabung ke `dev`, lalu `dev` ke `main`.