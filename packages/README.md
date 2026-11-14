# Laravel with Redis

A Laravel package providing a Product API backed by Redis cache-aside: pagination over large datasets, Cache Stampede protection, and automatic cache invalidation through an Observer.

## Requirements

- PHP ^8.3 (with the `phpredis` extension), Composer
- Laravel ^13
- MySQL 8 and Redis
- Docker (recommended; `docker-compose.yml` is provided in the project root)

## Setup

### 1. Start the containers

```bash
docker compose up -d --build
docker exec al2023 composer install
docker exec al2023 composer dump-autoload
```

### 2. Configure `.env`

```env
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=app_db
DB_USERNAME=app_user
DB_PASSWORD=secret

CACHE_STORE=redis
REDIS_CLIENT=phpredis
REDIS_HOST=redis
REDIS_PORT=6379
REDIS_PERSISTENT=true
```

Inside the container use `DB_HOST=db` and `REDIS_HOST=redis`. When running on the host machine, use `127.0.0.1` instead.

```bash
docker exec al2023 php artisan config:clear
```

### 3. Migrate and seed

```bash
docker exec al2023 php artisan migrate
docker exec al2023 php artisan db:seed --class="Laravel\Seeders\ProductSeeder"
```

The seeder inserts 1,000,000 rows in batches of 5,000 and takes a few minutes. Clear the cache afterwards:

```bash
docker exec al2023 php artisan cache:clear
```

Seeding twice results in 2 million rows. To start over, run `php artisan migrate:fresh`.

## API

Base URL: `http://localhost:8089/api/v1`

### `GET /version`

```json
{ "version": "1.0.0" }
```

### `GET /products`

Lists products ordered by `id` descending.

| Parameter | Default | Limits | Description |
|---|---|---|---|
| `page` | 1 | minimum 1 | Current page |
| `per_page` | 20 | 10 to 100 | Rows per page |

```bash
curl "http://localhost:8089/api/v1/products?page=1&per_page=20"
```

Response `200`:

```json
{
  "source": "mysql",
  "version": 1,
  "data": {
    "current_page": 1,
    "data": [{ "id": 1000000, "name": "Product #1000000", "price": 137952 }],
    "per_page": 20,
    "total": 1000000
  }
}
```

- `source`: `mysql` when read from the database, `redis` when served from cache.
- `version`: cache version of the list; it increases when product data changes.

### `GET /products/{id}`

Returns a single product. `id` must be numeric.

```bash
curl "http://localhost:8089/api/v1/products/10"
```

| Status | Meaning |
|---|---|
| 200 | `{ "source": "...", "data": { "id": 10, "name": "...", "price": ..., "created_at": "...", "updated_at": "..." } }` |
| 404 | `{ "message": "Product not found" }` |


## Directory structure

```
packages/
├── composer.json                  # autoload Laravel\ => src/, provider discovery
├── database/migrations/           # products table
└── src/
    ├── LaravelServicesProvider.php
    ├── Contracts/RepositoryInterface.php   # shared CRUD contract
    ├── Controllers/               # ProductController (thin: request in, JSON out)
    ├── Models/Product.php         # registers ProductObserver
    ├── Observers/ProductObserver.php
    ├── Repositories/
    │   ├── BaseRepository.php     # find, create, update, delete
    │   ├── ProductRepository.php  # MySQL queries
    │   └── ProductCacheRepository.php  # Redis read layer, key and invalidation owner
    ├── Services/
    │   ├── CacheAside.php         # lock, double-check, negative caching
    │   └── CacheResult.php        # result carrying source and version
    ├── Providers/RouteServiceProvider.php
    ├── Routers/api.php
    └── Seeders/ProductSeeder.php
```
