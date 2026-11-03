# 🌟 Dreamania Player - Management Panel & API Server

A modern, high-performance IPTV Web Management Panel and API Server designed for **Dreamania Player** (powered by DreamaniaCode).

Built with clean PHP 8.2+, supporting both **PostgreSQL** (via `DATABASE_URL`) and **SQLite3** (WAL mode) for seamless zero-configuration production deployment with Docker or standard web servers.

---

## ✨ Features

- **⚡ Xtream Codes API & Direct M3U Support**:
  - Direct integration with Xtream Codes `player_api.php` protocol for instant category and stream loading.
  - Full support for standard M3U / M3U8 playlists with custom naming.
  - Automatic MAC address formatting and normalization.
- **💎 Device & Subscription Management**:
  - One-click **7-Day Trial** activation.
  - One-click **1-Year Subscription** activation.
  - One-click **Lifetime Subscription** activation.
  - Instant Block / Unblock access control.
- **👥 Reseller Management Portal**:
  - Dedicated reseller roles with secure credentials.
  - Credit tracking and automated deduction per subscription activation.
  - Resellers only see and manage their assigned customer devices.
- **📊 Active Lines & Stream Tracking**:
  - Real-time logging of active streams, client IP addresses, user agents, and playback timestamps.
- **🌐 DNS Portals Manager**:
  - Add, update, and manage multiple Xtream Codes DNS server endpoints.
- **⚙️ Panel & Application Settings**:
  - Customizable admin master password.
  - In-app notification title & message (displayed directly on TV screens).
  - Configurable trial days and default subscription duration.
  - Direct APK download endpoint at `https://dmibo.myskillscloud.com/download`.
- **🔒 API Protocol & App Security**:
  - Secure reverse-compatible payload obfuscation (`CryptoHelper`).
  - Supports `/authenticate`, `/playlists`, `/delete`, `/parent-control/update`, and `player_api.php`.

---

## 🚀 Production Deployment (Docker)

### Using Dockerfile
1. Deploy from repository:
   - Build pack: **Dockerfile**
   - Port: **80**
2. Environment Variables:
   - Set `DATABASE_URL` to your PostgreSQL connection string:
     ```
     DATABASE_URL=postgres://user:password@host:5432/dbname?sslmode=require
     ```
   *(If `DATABASE_URL` is omitted, the application automatically uses high-performance SQLite3 in `/var/www/html/data`)*.
3. Persistent Storage (optional for SQLite & uploaded APK storage):
   - Mount path: `/var/www/html/data`

### Using Docker Compose
Run with `docker compose up -d`:
```bash
docker compose up -d --build
```

---

## 💻 Local Development

Run with PHP's built-in server:
```bash
php -S 0.0.0.0:80 router.php
```
Open [http://localhost/](http://localhost/) in your browser.

- **Default Master Admin Password**: `200600`

---

## 📁 Project Structure

```
├── .htaccess            # Apache URL rewrite rules for clean API paths
├── .gitignore           # Git ignore rules
├── Dockerfile           # Production PHP 8.2 Apache image (PostgreSQL + SQLite)
├── docker-compose.yml   # Docker Compose definition with environment & volume
├── api.php              # API router (/authenticate, /delete, /parent-control, /download)
├── crypto.php           # High-speed payload obfuscator & deobfuscator
├── db.php               # Multi-engine database layer (PostgreSQL + SQLite) with auto-migration
├── index.php            # Modern dark-themed administration & reseller portal
├── qr.php               # Dynamic QR code generator
├── router.php           # Development server router
└── data/                # Data directory (database.sqlite, app.apk)
```

---

## 🔐 Credentials & Attribution
- **Default Master Password**: `200600` (can be updated in the Settings tab).
- **Official Download URL**: `https://dmibo.myskillscloud.com/download`
- **Powered by**: [DreamaniaCode](https://www.facebook.com/dreamaniacode)
