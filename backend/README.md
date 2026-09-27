# Simple Invoicing System API

Laravel API for managing products, customers, invoices and sales returns.

## Requirements

* PHP 8.3+
* Composer
* MySQL
* BCMath

## Setup

Run the following commands from the backend directory:

```bash
composer install
```

Copy `.env.example` to `.env` and configure the database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

Then run:

```bash
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

The API will be available at:

`http://localhost:8000/api`

## Demo Accounts

Both accounts use the password:

`password`

| Role     | Email                                                     |
| -------- | --------------------------------------------------------- |
| Admin    | [admin@invoicing.test](mailto:admin@invoicing.test)       |
| Employee | [employee@invoicing.test](mailto:employee@invoicing.test) |

These accounts are for local development only.

## API

The API includes:

* Authentication
* Products
* Customers
* Invoices
* Sales Returns
* Admin operations

Authentication uses Bearer tokens.

## Postman

Import:

`postman/Invoicing-System-API.postman_collection.json`

Set the required variables and login to get the Bearer token.

The collection contains requests for authentication, products, customers, invoices, sales returns and admin operations.

## Pagination

List endpoints support pagination.

Default:

```text
15 items
```

Maximum:

```text
100 items
```

Example:

```text
?page=1&per_page=25
```

## API Response

Successful response:

```json
{
    "success": true,
    "message": "Success",
    "data": {}
}
```

Error response:

```json
{
    "success": false,
    "message": "Something went wrong.",
    "data": null,
    "errors": {}
}
```

## Project Structure

```text
app/
├── Enums/
├── Exceptions/
├── Http/
│   ├── Controllers/
│   ├── Middleware/
│   ├── Requests/
│   └── Resources/
├── Models/
├── Policies/
├── Repositories/
├── Services/
├── Support/
└── Traits/

database/
├── migrations/
└── seeders/

routes/
├── api.php
├── admin.php
└── web.php

tests/
├── Feature/
└── Unit/

postman/
└── Invoicing-System-API.postman_collection.json
```

## Tests

Run:

```bash
php vendor/phpunit/phpunit/phpunit --do-not-cache-result
```

Run Laravel Pint:

```bash
vendor/bin/pint --test
```

Tests use SQLite in memory and cover authentication, CRUD operations, permissions, invoices, returns, stock and calculations.

## CORS

The default frontend origins are:

* `http://localhost:5173`
* `http://127.0.0.1:5173`

To change them, set:

```env
CORS_ALLOWED_ORIGINS=http://localhost:5173,http://127.0.0.1:5173
```

Then run:

```bash
php artisan config:clear
```
