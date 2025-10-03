# FleetFox Task Manager

A simple task manager application built with Laravel and Vue as part of the FleetFox test assignment.
NB: This app is not meant to be used in production.

## Technology Stack

### Backend
- **Laravel 12** - PHP framework
- **PHP 8.4** - Programming language
- **SQLite** - Database
- **Inertia.js** - Server-side rendering adapter
- **Laravel Fortify** - Authentication scaffolding
- **Pest** - Testing framework

### Frontend
- **Vue 3** - JavaScript framework
- **TypeScript** - Type safety
- **Vite** - Build tool and dev server
- **Tailwind CSS 4** - Utility-first CSS framework
- **Reka UI** - Component library
- **Lucide Icons** - Icon system

## Features

- Task management with basic CRUD operations
- Task categories with basic CRUD operations
- Task filtering by search, category, and status
- Due date tracking with visual indicators
- Mark tasks as done/undone
- User authentication with 2FA support
- Responsive design with dark mode support

## Getting Started

Use Docker to run the application:
- `docker-compose up -d`
- `docker-compose exec app composer install`
- `docker-compose exec app php artisan migrate`
- `docker-compose exec app php artisan db:seed --class=UserSeeder`
- `docker-compose exec app php artisan key:generate`
- Edit `.env` accordingly

Application will be available at http://localhost:8000

### Useful Docker commands
- Stop containers: `docker-compose down`
- View logs: `docker-compose logs -f`
- Run Laravel Tinker: `docker-compose exec app php artisan tinker`
- Rebuild containers: `docker-compose up -d --build`

## Testing

```bash
# Run all tests
docker-compose exec app composer test

# Or use Pest directly
docker-compose exec app ./vendor/bin/pest

# Run specific test file
docker-compose exec app ./vendor/bin/pest tests/Feature/TaskControllerTest.php
```

## Potential Improvements
- Improve design responsiveness in search forms
- Add debouncing to search input on task/category index pages
- There is a small chance category slug may exceed 255 characters, which will cause an error when creating a new task. Handle this gracefully
- Implement toast notifications for success/error messages instead of session flashes
- Implement task sorting options (by due date, priority, created date)
- Add email notifications for upcoming due dates
- Add data export functionality (CSV, PDF)
- Implement soft deletes for tasks and categories with restore functionality