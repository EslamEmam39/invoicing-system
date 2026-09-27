# Invoicing System Frontend

Vue 3 frontend for the invoicing system.

Built with Vue 3, TypeScript and Vite.

## Requirements

* Node.js
* npm

## Setup

Install dependencies:

```bash
npm install
```

Create `.env` and set the API URL:

```env
VITE_API_URL=http://localhost:8000/api
```

Start the development server:

```bash
npm run dev
```

The frontend will run on:

`http://localhost:5173`

## Build

To create a production build:

```bash
npm run build
```

## Project Structure

```text
src/
├── components/
│   ├── customers/
│   ├── invoices/
│   ├── products/
│   ├── layout/
│   └── ui/
├── composables/
├── router/
├── services/
├── types/
├── utils/
├── views/
├── App.vue
└── main.ts
```

### Main folders

* `components` - Reusable Vue components.
* `views` - Application pages.
* `composables` - Shared application logic.
* `services` - API requests.
* `types` - TypeScript types.
* `router` - Application routes.
* `utils` - Small helper functions.

## Main Pages

* Login
* Dashboard
* Products
* Customers
* Invoices
* Invoice Details
* Sales Returns

The frontend uses the Laravel API from the backend project.
