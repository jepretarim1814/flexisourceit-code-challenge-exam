# Code Challenge

### Table of contents
- [Requirements](#requirements)
- [Installation](#installation)
- [Import](#import)
- [Unit Test](#unit-test)

### Requirements
- PHP >= 7.4 | 8.0

### Installation
This project uses the latest or 8.x of [Lumen](https://lumen.laravel.com/docs/8.x).

1. Run the composer installation:
    ```sh 
    composer install
   ```

2. Create a copy `.env.example` and make your changes.

3. Provision the database:
    ```sh
    php artisan doctrine:schema:create
   ```

4. Serve the application by using [Laravel Homestead](http://laravel.com/docs/homestead), 
[Laravel Valet](http://laravel.com/docs/valet) or the built-in PHP development server:
    ```sh
    php -S localhost:8000 -t public
   ```

### Import
You can easily import customers based on your default driver or `env('CUSTOMER_IMPORTER_DRIVER')` and run the command
below:

```sh
php artisan customer:import --count=[How many users to import, default: 100]
```

### Unit Test
Simple, run:

```sh 
vendor/bin/phpunit
```
* * *
###### Create and developed by [Jepre Tarim](https://github.com/jepretarim1814)
