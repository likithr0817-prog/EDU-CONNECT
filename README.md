#Edu-Connect - Learning Management System
College Project | PHP + MySQL + XAMPP

---

 📁 FOLDER STRUCTURE

```
edu-connect/
├── index.php                    ← Root entry point (auto-redirects)
│
├── presentation/                ← Layer 1: UI (HTML/CSS/JS)
│   ├── css/
│   │   └── style.css
│   ├── js/
│   │   └── main.js
│   ├── layout.php               ← Shared sidebar/header layout
│   ├── login.php
│   ├── register.php
│   ├── student/
│   │   ├── dashboard.php
│   │   ├── materials.php
│   │   ├── notifications.php
│   │   └── profile.php
│   ├── teacher/
│   │   ├── dashboard.php
│   │   ├── materials.php
│   │   ├── upload.php
│   │   ├── notifications.php
│   │   └── profile.php
│   └── admin/
│       ├── dashboard.php
│       ├── users.php
│       ├── approvals.php
│       ├── materials.php
│       └── notifications.php
│
├── application/                 ← Layer 2: Business Logic (PHP)
│   ├── config.php               ← Session, helpers, constants
│   ├── auth.php                 ← Login, register, logout
│   ├── materials.php            ← Upload, delete materials
│   ├── admin_actions.php        ← Approve, reject, notify
│   └── profile.php              ← Profile update, mark read
│
├── data/                        ← Layer 3: Data Layer
│   ├── db.php                   ← MySQL connection
│   └── educonnect.sql           ← Database schema + seed data
│
└── uploads/                     ← File storage (auto-created)
    ├── pdfs/
    ├── videos/
    ├── images/
    └── .htaccess                ← Prevents PHP execution in uploads
```

---

 ⚙️ STEP-BY-STEP SETUP ON XAMPP

 Step 1 — Install XAMPP
Download from https://www.apachefriends.org and install.

Step 2 — Copy Project
Copy the entire `edu-connect` folder into:
```
C:\xampp\htdocs\edu-connect\
```

Step 3 — Start XAMPP
Open XAMPP Control Panel and start:
- ✅ Apache
- ✅ MySQL

 Step 4 — Create Database
1. Open your browser and go to: http://localhost/phpmyadmin
2. Click **"New"** in the left sidebar
3. Create a database named **`educonnect`**
4. Click the **`educonnect`** database
5. Click **"Import"** tab
6. Click **"Choose File"** → select `data/educonnect.sql`
7. Click **"Go"** (import button)

Step 5 — Configure Database (if needed)
Open `data/db.php` and update credentials if your MySQL password is different:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');       // ← change if you have a MySQL password
define('DB_NAME', 'educonnect');
```

Step 6 — Set Upload Permissions
Ensure the `uploads/` folder is writable. On Windows with XAMPP this is usually automatic.

On Linux/Mac:
```bash
chmod -R 777 uploads/
```

 Step 7 — Open the App
Go to: **http://localhost/edu-connect/**

---
 🔐 DEFAULT LOGIN CREDENTIALS

| Role  | Email                     | Password   |
|-------|---------------------------|------------|
| Admin | admin@educonnect.com      | password   |

> Note: Students and Teachers must register and be approved by admin before they can login.

---

 👥 USER WORKFLOW

 For Students/Teachers:
1. Go to http://localhost/edu-connect/presentation/register.php
2. Fill in the registration form
3. Wait for admin to approve the account
4. Login at http://localhost/edu-connect/presentation/login.php

 For Admin:
1. Login with admin credentials above
2. Go to **Approvals** to approve/reject registrations
3. Go to **Notifications** to send announcements
4. Monitor all users and materials from dashboard

---

 📋 DATABASE TABLES

| Table               | Description                              |
|---------------------|------------------------------------------|
| users               | All users (base auth table)              |
| students            | Student-specific data (class)            |
| teachers            | Teacher-specific data (subject)          |
| admin               | Admin record                             |
| materials           | Uploaded files and links                 |
| notifications       | Admin announcements                      |
| notification_reads  | Tracks who read which notification       |
| approvals           | Approval/rejection log with timestamps   |

---

 🎨 FEATURES SUMMARY

 ✅ Authentication
- Role-based registration (Student/Teacher)
- Auto-generated unique IDs (STU-000001, TEA-000001)
- Password hashing with PHP password_hash()
- Session-based login with role-based redirect

✅ Admin Panel
- Approve/reject registrations
- View all users with search
- Monitor all uploaded materials
- Delete inappropriate content
- Send notifications to all/students/teachers
- Dashboard statistics

✅ Study Materials
- Upload PDFs, Videos, Images, or external links
- MIME type + file size validation (max 50MB)
- Files stored in /uploads/ subdirectory
- Delete own uploads (or admin deletes any)

 ✅ Notifications
- Admin sends to all, students only, or teachers only
- Unread count shown in sidebar and topbar
- Mark as read via AJAX

✅ Profiles
- View unique ID prominently displayed
- Edit name, age, college, contact, class/subject
- Approval status shown on profile

 ✅ Security
- Prepared statements (prevents SQL injection)
- Password hashing
- File type MIME validation
- PHP execution blocked in uploads/
- Role-based access control on every page
- Session authentication required

---

 🛠️ TROUBLESHOOTING

**"Database connection failed"**
→ Make sure MySQL is running in XAMPP
→ Check credentials in `data/db.php`

**"Could not save file"**
→ Make sure `uploads/` and subdirectories exist and are writable

**"404 Not Found"**
→ Make sure the folder is exactly named `edu-connect` in htdocs

**Admin password not working**
→ The SQL uses a specific hash. If it doesn't work, go to phpMyAdmin → educonnect → users table → edit the admin row → set password to a new `password_hash()` value.

Or run this in phpMyAdmin SQL tab:
```sql
UPDATE users SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' WHERE email = 'admin@educonnect.com';
```
This sets the password to `password`.
#
