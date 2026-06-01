> Status: Unfinished

# Prerequisites

* Linux environment (Ubuntu WSL was used during development)

# Installation

## 1. Install Frontend Dependencies

Navigate to the `backend` directory:

```bash
cd backend
npm install
npm run dev
```

## 2. Start Docker Containers

From the project root directory:

```bash
docker compose up --build
```

## 3. Access the Application Container

```bash
docker compose exec app sh
```

## 4. Install PHP Dependencies

```bash
composer install
```

## 5. Fix File Permissions

```bash
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

chmod -R 775 /var/www/storage /var/www/bootstrap/cache
```

## 6. Run Database Migrations

```bash
php artisan migrate
```
