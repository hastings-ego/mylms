# Fun Maths Mastery – Digital Resource System

A complete PHP/MySQL application for selling digital math products (PDFs, links) to students. Includes online store, student dashboard, and admin panel.

The app also runs cleanly on SQLite for local development. If `app/maths_mastery.db` is missing, the database layer will create it, build the schema, and seed demo accounts/products automatically.

## Features
- Student authentication (register/login) with role-based access (student/admin)
- Admin dashboard: manage products (PDF/link), view orders, manage users
- Online store with product catalog, cart, and simulated checkout
- Digital product delivery: download PDFs or access external links after purchase
- Student dashboard: view purchased resources, update profile, see recommended products
- "All My Courses" – list all purchased products
- "Referencing & Sheets" – view reference materials (category 'Reference')
- "1-on-1 Math Tutors" – static tutor listing (extendable)
- Account settings form

## Technology Stack
- PHP 8.x (with sessions, PDO, file uploads)
- MySQL database
- Tailwind CSS (frontend styling, retained from original design)
- No external payment gateway – checkout simulates successful order

## Setup Instructions

### 1. Requirements
- PHP 7.4+ (with PDO MySQL, fileinfo, GD extensions)
- MySQL 5.7+
- Web server (Apache/Nginx) with mod_rewrite

### 2. Installation
1. Clone the repository into your web root (e.g., `htdocs/maths-mastery`)
2. Create MySQL database and import `database.sql` (see schema below)
3. Configure database connection in `config/db.php` (update `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`)
4. Ensure `uploads/` directory exists and is writable (for product PDFs)
5. Set document root to the project folder.

### 3. Default Admin Account
- **Email:** `admin@example.com`
- **Password:** `admin123`

### 4. Demo Student Account
- **Email:** `student@example.com`
- **Password:** `student123`

### 5. Testing the Application
- Visit `http://localhost/maths-mastery`
- Use the demo student account to test the student portal, or use admin credentials to access admin panel (`/admin`)
- Admin can add products (choose PDF file or external link)
- Students can browse store, add to cart, and checkout (simulated)
- After checkout, purchased products appear in "All My Courses" and Dashboard.

## Database Schema
Execute the SQL script `database.sql` to create tables:
- `users` (id, name, email, password, role)
- `products` (id, title, description, price, category, file_type, file_path, is_active)
- `orders` (id, user_id, order_date, total, status)
- `order_items` (id, order_id, product_id, price, created_at)
- `user_product_access` (id, user_id, product_id, purchase_date)

## Folder Structure

project/
├── assets/ (logo images)
├── uploads/products/ (uploaded PDF files)
├── admin/
│ ├── dashboard.php
│ ├── products.php
│ ├── orders.php
│ └── users.php
├── config/
│ ├── db.php
│ └── functions.php
├── includes/
│ ├── header.php
│ ├── sidebar.php
│ └── footer.php
├── index.php
├── login.php
├── register.php
├── logout.php
├── dashboard.php
├── my-courses.php
├── library.php
├── tutoring.php
├── settings.php
├── store.php
├── cart.php
├── checkout.php
├── product.php
├── download.php
└── database.sql



## Important Notes
- Checkout does **not** process real payments – it marks orders as `completed` and grants access.
- PDF files are served via `download.php` after verifying purchase.
- Only active products (`is_active = 1`) appear in the store.
- Students can access any purchased product multiple times.
- Admin panel only accessible to users with `role = 'admin'`.

## Extending the System
- Add real payment gateway (PayPal, Stripe)
- Implement email notifications after purchase
- Add file versioning or access expiry
- Build tutor booking system (currently static)
