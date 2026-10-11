# GrandStay Hotel Management Website

This project is a hotel management website built as a college project using a PHP + MySQL backend.

## Tech stack

- Frontend: HTML, CSS, JavaScript
- Backend: PHP
- Database: MySQL
- Admin panel: PHP sessions

## Features

- Hotel landing page and room showcase
- Room booking form
- Booking storage in MySQL
- Admin login page
- Admin dashboard for recent reservations
- Responsive website design

## Project structure

- `index.html` – frontend hotel page
- `style.css` – website styling
- `script.js` – booking form logic and API calls
- `api/bookings.php` – booking API for create/list operations
- `config/db.php` – MySQL connection
- `admin/login.php` – admin login page
- `admin/dashboard.php` – admin dashboard
- `admin/logout.php` – logout handler
- `db/schema.sql` – MySQL database schema

## Setup instructions

1. Start MySQL and create the database schema.
2. Import the file `db/schema.sql` into your MySQL server.
3. Update the database credentials in `config/db.php` if needed.
4. Run the project using PHP:

```bash
php -S localhost:8000
```

5. Open the project in your browser:

```text
http://localhost:8000
```

6. For admin access, open:

```text
http://localhost:8000/admin/login.php
```

Default admin credentials:
- Username: `admin`
- Password: `admin123`

## Notes

This is a demo project intended for learning and academic use. The backend is intentionally simple and can be extended with more rooms, customer details, payment integration, and staff management.
