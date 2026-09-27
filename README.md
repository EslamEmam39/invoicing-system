# Invoicing System

A simple invoicing system with a Laravel backend and Vue frontend.

## Project Structure

```text
invoicing-system/
├── backend/     # Laravel API
├── frontend/    # Vue frontend
└── README.md
```

## Backend

Laravel API for:

* Authentication
* Products
* Customers
* Invoices
* Sales Returns

To run the backend:

```bash
cd backend
composer install
php artisan migrate --seed
php artisan serve
```

API:

```text
http://localhost:8000/api
```

See `backend/README.md` for more details.

## Frontend

Vue 3 frontend for managing the invoicing system.

To run the frontend:

```bash
cd frontend
npm install
npm run dev
```

Frontend:

```text
http://localhost:5173
```

See `frontend/README.md` for more details.
