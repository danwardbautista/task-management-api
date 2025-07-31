# Laravel Version 10

Task Management API

## Live API
https://taskmanagement.danwardbautista.com/

## API Documentation
https://documenter.getpostman.com/view/16609457/2sB3B8tZej

## Prerequisite
1. Composer
2. MySQL XAMPP / PostgreSQL
3. PHP 8.1

## Running Development
### Package installation
```shell
composer install
```
### Local Setup
1. Run MySQL with XAMPP/ PostgreSQL with pgAdmin 4.
2. Create a database called `task_management_db`.
3. Duplicate `env.example` and change the name to `.env`.
4. Configure `.env` database section to your local MySQL/PostgreSQL configuration.

### Local
Migrate the database
```shell
php artisan migrate
```
Serve in port 8000
```shell
php artisan serve
```

## Job
To manually run the job of checking soft deleted files that will be deleted 
```shell
php artisan tinker
```
```shell
App\Jobs\CleanupTrashedTasks::dispatch();
```
