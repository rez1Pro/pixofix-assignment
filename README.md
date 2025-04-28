# APP PREVIEW
## App Preview

![Dashboard](https://github.com/rez1Pro/pixofix-assignment/blob/main/public/img/image.png)

### Key Screens

| Screen | Description |
|--------|-------------|
| Authentication | User login and registration with Laravel Sanctum |
| Dashboard | Main application interface with analytics |
| User Management | Admin interface for managing users |
| Content Editor | Rich text editing with image upload capabilities |
| Settings | Application configuration options |

### Features Showcase

- **Responsive Design**: Fully responsive interface built with Tailwind CSS
- **Real-time Updates**: Using Laravel Echo and WebSockets
- **Dark/Light Mode**: Theme switching with persistent user preferences
- **Advanced Filtering**: Dynamic data filtering with Spatie Query Builder
- **API Documentation**: Auto-generated API documentation with Swagger

# Pixofix

A Laravel application built with Laravel 12, Inertia.js, Vue 3, and Tailwind CSS.

## Requirements

- PHP 8.4+
- Composer
- Node.js & npm/yarn
- MySQL 8.3+ or compatible database
- Redis (optional, for caching and queue)
- Memcached (optional)
- MinIO (optional, for S3-compatible object storage)

## Features

- Laravel 12 framework
- Inertia.js for seamless SPA-like experience
- Vue 3 frontend with TypeScript
- Tailwind CSS for styling
- Laravel Sanctum for API authentication
- Spatie Laravel Query Builder
- Spatie Laravel TypeScript Transformer

## Setup and Installation

### Using Laravel Sail (Docker)

Laravel Sail provides a simple Docker-based environment for Laravel development. 

1. Clone the repository:

```bash
git clone https://github.com/rez1Pro/pixofix-assignment.git
cd pixofix-assignment
```

2. Create environment file:

```bash
cp .env.example .env
```

3. Configure your .env file with your preferences.

4. Install PHP dependencies via Docker:

```bash
docker compose run buid

docker exec -i pixofix-assignment-laravel.test-1 /bin/bash composer install
```

5. Start the Sail environment:

```bash
./vendor/bin/sail up -d
```

6. Generate application key:

```bash
./vendor/bin/sail artisan key:generate
```

7. Run migrations:

```bash
./vendor/bin/sail artisan migrate -seed
```

8. Install frontend dependencies and compile assets:

```bash
./vendor/bin/sail npm install
./vendor/bin/sail npm run dev
```

9. Access your application at http://localhost

### Without Sail (Local Environment)

1. Clone the repository:

```bash
git clone https://github.com/rez1Pro/pixofix-assignment.git
cd pixofix-assignment
```

2. Create environment file:

```bash
cp .env.without_sail .env
```

3. Configure your .env file with your database credentials and other settings.

4. Install PHP dependencies:

```bash
composer install
```

5. Generate application key:

```bash
php artisan key:generate
```

6. Run migrations:

```bash
php artisan migrate
```

7. Install frontend dependencies:

```bash
npm install
# or using yarn
yarn install
```

8. Compile assets:

```bash
npm run dev
# or using yarn
yarn dev
```

9. Start the development server:

```bash
php artisan serve
```

10. Access your application at http://localhost:8000

## Development Environment

For local development, you can use the custom dev command which starts all necessary services:

```bash
composer dev
```

This command uses concurrently to run the following services:
- Laravel server (`php artisan serve`)
- Queue worker (`php artisan queue:listen --tries=1`)
- Laravel Pail for logging (`php artisan pail --timeout=0`)
- Vite server for frontend assets (`npm run dev`)

## Available Services

When using Sail, the following services are available:

- **MySQL**: Database server
- **Redis**: In-memory data structure store
- **Memcached**: Distributed memory caching system
- **MinIO**: S3-compatible object storage
- **phpMyAdmin**: Web interface for MySQL (accessible at http://localhost:8001)
- **MinIO Console**: Web interface for MinIO (accessible at http://localhost:8003)
