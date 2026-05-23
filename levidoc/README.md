# Levidoc Agency – Complete E‑commerce Agency Management System

A full‑stack solution for a digital agency specializing in high‑performance e‑commerce. The system includes a marketing landing page, a client portal (SPA), an admin dashboard, and a PHP/SQLite backend API.

## 🚀 Features

### Public Landing Page
- Modern, responsive marketing website
- Services showcase, portfolio grid (syncs with backend projects)
- Client testimonials, statistics, and contact form
- Links to client portal and admin login

### Client Portal (SPA)
- User registration and login (session‑based)
- Submit project quotations (title, description, budget, tech stack)
- Dashboard with project status overview (pending / approved / construction)
- Detailed project list with admin notes
- Profile management

### Admin Panel (separate HTML file)
- Secure admin login (default: `admin@levidoc.com` / `admin123`)
- Overview metrics (total projects, pending, approved, construction)
- Manage all client quotations – change status, add internal notes
- View registered clients and their projects
- View contact messages from landing page
- Demo invoice & Yoco payment link management

### PHP/SQLite Backend API
- REST‑style endpoints for all frontend operations
- SQLite database (no external DB required)
- Session‑based authentication (admin / client roles)
- CORS support for local development
- Pre‑loaded demo data (users, projects, invoices)

## 📁 Project Structure

```
levidoc-agency/
├── index.html                # Landing page
├── client-portal.html        # Client SPA (quotes, dashboard, projects)
├── admin.html                # Admin dashboard
├── api.php                   # Single‑file PHP API + SQLite setup
├── levidoc.sqlite            # SQLite database (auto‑created on first request)
└── README.md                 # This file
```

## 🛠️ Installation & Setup

### Requirements
- PHP 7.4+ (with `pdo_sqlite` extension enabled)
- Web server (Apache, Nginx, or PHP built‑in server)
- Modern web browser

### Step 1: Clone or download the files
Place all files in your web server document root (e.g., `htdocs` for XAMPP, or any folder served by PHP).

### Step 2: Configure API endpoint URL
Open `client-portal.html`, `admin.html`, and `index.html`.  
Locate the `API_ENDPOINT` variable (near the top of the `<script>` block) and set it to the absolute URL of `api.php`.  
Example:
```javascript
const API_ENDPOINT = 'http://localhost/levidoc/api.php';
```

### Step 3: Start the PHP server (if using built‑in server)
```bash
php -S localhost:8000
```
Then open `http://localhost:8000/index.html`.

### Step 4: Database initialization
On the first request to `api.php`, the SQLite database (`levidoc.sqlite`) and all required tables will be created automatically.  
Demo data is inserted if no projects exist.

### Step 5: Test the system
- **Landing page**: `http://localhost:8000/index.html`
- **Client portal**: `http://localhost:8000/client-portal.html`
  - Register a new account or use demo client: `emma@example.com` / `pass123`
- **Admin panel**: `http://localhost:8000/admin.html`
  - Login: `admin@levidoc.com` / `admin123`

## 🔌 API Endpoints

All endpoints expect `application/json` and use session cookies for authentication.

| Action | Method | URL | Description | Auth |
|--------|--------|-----|-------------|------|
| `register` | POST | `api.php?action=register` | Create new client account | None |
| `login` | POST | `api.php?action=login` | Login, starts session | None |
| `logout` | GET | `api.php?action=logout` | Destroy session | Any |
| `update_profile` | POST | `api.php?action=update_profile` | Update name/email | Client |
| `client_data` | GET | `api.php?action=client_data` | Get client dashboard data | Client |
| `client_projects` | GET | `api.php?action=client_projects` | Get client’s projects | Client |
| `add_project` | POST | `api.php?action=add_project` | Submit new quotation | Client |
| `admin_dashboard` | GET | `api.php?action=admin_dashboard` | Admin metrics & data | Admin |
| `update_project_status` | POST | `api.php?action=update_project_status` | Change project status + notes | Admin |
| `get_messages` | GET | `api.php?action=get_messages` | Fetch contact messages | Admin |
| `contact_message` | POST | `api.php?action=contact_message` | Submit contact form | None |
| `toggle_payment_status` | POST | `api.php?action=toggle_payment_status` | Demo invoice toggle | Admin |
| `renew_yoco_link` | POST | `api.php?action=renew_yoco_link` | Generate new Yoco link | Admin |

### Example Request (Register)
```json
POST /api.php?action=register
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "secret123"
}
```

### Example Response
```json
{
    "success": true,
    "message": "Registration successful. Please login."
}
```

## 🧪 Demo Credentials

| Role   | Email                     | Password   |
|--------|---------------------------|------------|
| Admin  | admin@levidoc.com          | admin123   |
| Client | emma@example.com           | pass123    |
| Client | michael@brands.com         | pass123    |

## 📝 Notes

- The API uses PHP sessions. Ensure your server has session write permissions.
- For production, replace `password_hash` with a stronger algorithm and enable HTTPS.
- The portfolio on the landing page reads from approved/construction projects in the database.
- CORS header in `api.php` is set to `http://localhost:5500` – change it to match your frontend URL.

## 🧰 Technologies Used

- **Frontend**: HTML5, Tailwind CSS, JavaScript (SPA logic)
- **Backend**: PHP 7.4+, SQLite3
- **Icons**: Font Awesome 6
- **Authentication**: PHP sessions + password hashing

## 📄 License

MIT – free to use and modify for your own agency projects.

---

**Built by Levidoc Agency** – *Building high‑performance e‑commerce experiences for the modern world.*