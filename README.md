# Translation Management Service

API-driven service for storing and serving translations across locales with tags, JWT auth, and high performance.

## Features
- JWT-secured CRUD for translations (keys, locales, value, context, tags)
- Search by tags, key, content (FULLTEXT where supported)
- JSON export endpoint for front-ends (always updated, no-store, ETag)
- Batch generator for 100k+ records and a seeder for quick data
- PSR-12, SOLID services
- OpenAPI spec at `public/openapi.yaml`
- Docker setup with MySQL
- Basic performance tests for export (<500ms target)

## Quickstart
```bash
# Local (requires PHP 8.3+ and MySQL)
cp .env.example .env
# Update DB_ credentials if needed
composer install
php artisan key:generate
php artisan migrate

# Optional sample data
php artisan db:seed --class=LargeDatasetSeeder
# Or: generate large dataset
php artisan app:generate-large-translations --count=100000 --batch=5000

# Serve
php artisan serve
```

### Docker
```bash
docker compose up --build -d
# App on http://localhost:8000
```

## Auth
- Register: POST `/api/auth/register` → `{ token }`
- Login: POST `/api/auth/login` → `{ token }`
- Use header: `Authorization: Bearer <token>`

## Endpoints
- GET `/api/translations` (filters: `locale,key,content,tags`)
- POST `/api/translations` (key, locale, value, context?, tags[]?)
- GET `/api/translations/{id}`
- PUT/PATCH `/api/translations/{id}`
- DELETE `/api/translations/{id}`
- GET `/api/translations/export?locale=en&tags=web,desktop` → `{ key: value }`

Export sets `Cache-Control: no-store` and `ETag` for CDN revalidation while ensuring freshness.

## Testing
```bash
php artisan test
```

## Design Notes
- Indexed schema: unique `(key, locale)`, fulltext on `value`
- Service layer encapsulates business logic and tag syncing
- Export chunks results to keep memory stable and fast

## Security
- JWT (api guard)
- Validation on all inputs

## OpenAPI
See `public/openapi.yaml` or load it in Swagger UI.

## Performance
- Target <200ms for standard endpoints, <500ms for export
- Batch DB writes in generator, chunked export reads
