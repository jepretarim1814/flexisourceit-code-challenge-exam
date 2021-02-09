# Code Challenge

### Table of contents
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Import](#import)
- [Unit Test](#unit-test)

### Requirements
- PHP >= 7.3 | 8.0

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

### Configuration
You can update the configuration on `config/customer.php` to add and edit driver for import on `importer_drivers` 
attribute.

Currently, we are only used [randomuser.me API](https://randomuser.me/documentation) as `default` driver. The config
file look like this:

```php
...
[
    'driver' => 'default', // The name of the driver
    'url' => 'https://randomuser.me/api/', // The url to request
    'version' => '1.3', // The default version
    'nationalities' => [ // An array values of nationalities
        'au'
    ],
    'fields' => [ // An array of included fields
        'name', // Where first and last name
        'email',
        'login', // Where username
        'gender',
        'location', // Where country and city
        'phone',
    ],
    'count' => 100, // How many results to import
],
...
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
