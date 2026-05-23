# EduPortal: Learning Management System

A minimalist, high-performance education platform built with Vanilla PHP, HTML5, Vanilla JS, and Tailwind CSS. 

## 🏗 System Architecture
This project intentionally avoids heavy JavaScript frameworks in favor of a fast, server-rendered vanilla PHP approach. 

* **Frontend:** HTML5 + Vanilla JavaScript for interactivity.
* **Styling:** Tailwind CSS (utility-first) for rapid, consistent UI development.
* **Backend:** Vanilla PHP 8.0+.
* **Database:** (To be configured - standard PDO MySQL/SQLite recommended).

## 🎨 Design System: "High-Contrast Minimal"
The UI is designed to get out of the student's way and focus entirely on the learning content.
* **Theme:** Pure white (`#ffffff`) and off-white (`#f8fafc`) backgrounds.
* **Typography:** `Inter` for all UI elements. High contrast text (`text-slate-900`).
* **Accents:** Stark black buttons (`bg-black text-white`) and subtle slate borders (`border-slate-200`) for structural separation. No heavy drop-shadows.

## 📂 Directory Structure (Proposed)
```text
/edu-portal
├── /assets
│   ├── /css        # Custom overrides (if any)
│   ├── /js         # Vanilla JS scripts
│   └── /images     # System assets
├── /includes       # Reusable PHP components (header.php, footer.php)
├── /portal         # Secure student dashboard and course views
├── index.php       # Landing page
└── README.md       # Project documentation