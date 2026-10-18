# Taylor's University Event Management & Ibu Niaga Platform

> **A Community Initiative by Taylor's University Impact Lab**  
> Empowering women micro-entrepreneurs through practical business education, event management, and community commerce.

[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777bb4?style=flat-square&logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-8.0%20%2F%20MariaDB-4479a1?style=flat-square&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Apache](https://img.shields.io/badge/Apache-2.4-d22128?style=flat-square&logo=apache&logoColor=white)](https://httpd.apache.org/)
[![License](https://img.shields.io/badge/License-Proprietary-red?style=flat-square)](#)

---

## 🌟 Overview

The **Taylor's University Event Management & Ibu Niaga Platform** is a web solution tailored for women micro-entrepreneurs and community partners across Subang Jaya and Selangor.

The platform bridges university culinary and business mentorship with grassroots entrepreneurship across four purpose-built workspaces:

1. **Public Discovery Portal:** Showcases mission, past impact projects, member directory, success stories, 4-stage skill pathways, community events, and the Open Bazaar.
2. **Member Workspace:** Guided multi-step program applications (with 5-stage status stepper), event RSVP & waitlist tracking, bazaar listing management, buyer inquiry messaging, and support ticketing.
3. **Moderator Workspace:** Application evaluation workflow (Approve, Reject, Request Documents), participant CRM with CSV export, bazaar listing moderation queue, event review moderation, and ticket support desk.
4. **Admin Workspace:** Executive dashboard with visual performance analytics, user and role management, workshop & waitlist administration, syllabus lesson builder, automated reminder dispatcher, multilingual translation dictionary editor, and system audit logs.

---

## 🎨 Design System & UI/UX

Engineered following the Taylor's University brand standards:

| Design Token | Value | Context |
| :--- | :--- | :--- |
| **Primary Red** | `#d71920` | Brand identity, primary CTAs, active highlights |
| **Ink** | `#231f20` | Primary headlines, bold typography, deep contrasts |
| **Cream** | `#fafafa` | Subtle surface backgrounds, priority action callouts |
| **Peach** | `#faeae6` | High-visibility banner callouts, warning indicators |
| **Sage** | `#f2f2f2` | Neutral secondary surfaces, card borders |
| **Font Family** | `Plus Jakarta Sans` | Modern, legible sans-serif for UI and editorial |

---

## 🚀 Key Features

### 1. Multi-Language Support (i18n)
- Seamless switcher dropdown for **English (`EN`)**, **Bahasa Melayu (`BM`)**, and **中文 (`ZH`)**.
- Stored per-user preference and persistent session cookie with database-backed dictionary fallbacks.

### 2. Five-Stage Application Pipeline
- Applications follow a transparent 5-stage progress stepper:
  $$\text{Draft} \longrightarrow \text{Submitted} \longrightarrow \text{In Review} \longrightarrow \text{Needs Documents} \longrightarrow \text{Approved} \ / \ \text{Rejected}$$
- Applicants can view feedback notes and upload missing identity or business verification documents.

### 3. Event Management & Automatic Waitlist
- One-click RSVPs for physical kitchen workshops and business masterclasses.
- Automatic waitlist promotion when registered attendees cancel.
- Post-event review submission with star ratings and moderation controls.

### 4. Open Bazaar & Buyer Inquiries
- Community marketplace connecting local buyers to Ibu Niaga businesses.
- Configurable approval policy (instant publish vs. moderator review).
- Direct buyer inquiry system with email alerts and response status tracking.

### 5. Enterprise Security & Auditability
- Role-Based Access Control (`admin`, `moderator`, `user`).
- CSRF token validation on all state-changing forms.
- Bcrypt password hashing (`PASSWORD_DEFAULT`).
- Strict file upload handling: randomized 32-hex file names, MIME type verification, and 5MB payload limit.
- Comprehensive system audit logs tracking entity changes, user IDs, actions, and IP addresses.

---

## 🛠️ Tech Stack & Requirements

- **Server:** Apache 2.4+ (XAMPP for Windows / Linux / macOS)
- **Runtime:** PHP 8.2+
- **Database:** MySQL 8.0+ or MariaDB 10.4+ with `utf8mb4_unicode_ci`
- **Frontend:** Semantic HTML5, Vanilla Modern CSS, Vanilla JavaScript (zero node/npm runtime dependencies)

---

## 📥 Installation & Setup

### 1. Clone the Repository
Clone into your web server's document root (e.g. `c:\xampp\htdocs\` or `/var/www/html/`):
```bash
cd c:\xampp\htdocs
git clone https://github.com/dicson0108/tu-ibu-niaga.git TUIbuNiaga
cd TUIbuNiaga
```

### 2. Configure Database & Environment
1. Ensure your Apache and MySQL servers are running in the XAMPP Control Panel.
2. The platform connects by default to `localhost` with user `root` and an empty password. (Edit [`includes/db.php`](includes/db.php) if your local MySQL credentials differ).
3. Initialize the database schema and seed demo data by visiting in your browser:
   ```
   http://localhost/TUIbuNiaga/setup_db.php
   ```
   *Alternatively, execute via terminal:*
   ```bash
   php setup_db.php
   ```

### 3. Access the Platform
Navigate to:
```
http://localhost/TUIbuNiaga/
```

---

## 🔑 Pre-Seeded Demo Accounts

| Role | Email | Password | Default Workspace |
| :--- | :--- | :--- | :--- |
| **System Admin** | `admin@taylors.edu.my` | `Admin@123` | `/admin/index.php` |
| **Community Moderator** | `moderator@taylors.edu.my` | `Mod@123` | `/moderator/index.php` |
| **Member Entrepreneur** | `aina@example.com` | `User@123` | `/member/index.php` |

---

## 📁 Project Directory Structure

```
TUIbuNiaga/
├── admin/                     # Admin workspace (analytics, users, events, modules, logs)
│   ├── index.php
│   ├── users.php
│   ├── events.php
│   ├── modules.php
│   ├── reminders.php
│   ├── translations.php
│   ├── settings.php
│   └── audit_logs.php
├── assets/                    # Static assets
│   ├── css/style.css          # Design token styling
│   ├── js/main.js             # Client interactions
│   └── images/                # Brand logos and graphics
├── includes/                  # Core modules
│   ├── config.php             # Session & base path config
│   ├── db.php                 # PDO database connector
│   ├── auth.php               # RBAC & authentication
│   ├── helpers.php            # Security & upload helpers
│   ├── i18n.php               # Multilingual translations
│   ├── mailer.php             # Email dispatcher & DB logger
│   ├── header.php             # Public header & lang dropdown
│   └── footer.php             # Refined footer & back-to-top
├── member/                    # Member entrepreneur workspace
│   ├── index.php              # Member dashboard
│   ├── applications.php       # 5-stage application tracker
│   ├── program_registration.php
│   ├── events.php             # Joined & waitlisted events
│   ├── bazaar.php             # Product listings manager
│   ├── enquiries.php          # Buyer order enquiries
│   └── tickets.php            # Support desk
├── moderator/                 # Moderator workspace
│   ├── index.php              # Moderator dashboard
│   ├── applications.php       # Review queue & decisions
│   ├── participants.php       # Member CRM & CSV export
│   ├── bazaar_moderation.php  # Listing approval queue
│   ├── feedback_moderation.php
│   └── tickets.php            # Ticket assignments
├── database.sql               # Full schema definition
├── setup_db.php               # Automated schema & seed script
└── README.md                  # Project documentation
```

---

## 📄 License & Attribution

Developed for **Taylor's University Impact Lab & Event Management**.  
© 2026 Taylor's University. All rights reserved.
