# 🌟 Dreamania IBO Pro - Management Panel & API Server

A modern, high-performance IPTV Web Management Panel and API Server designed for **Dreamania IBO Pro** (compatible with SanSat Ultra V10 and IBO Player Pro apps).

Built with clean PHP 8.2+ and SQLite3 (WAL mode) for seamless, zero-configuration deployment on **Coolify**, Docker, or any standard web server.

---

## ✨ Features

- **⚡ Add IBO Users (Xtream Codes)**:
  - Add users linked to pre-configured DNS servers.
  - Automatic MAC address formatting and normalization.
  - Generates standard Xtream M3U streams with credentials.
- **📄 Add M3U Users**:
  - Add direct M3U playlist URLs for any MAC address.
  - Custom display naming.
- **📋 Playlists & Devices Management**:
  - View all registered devices and playlists.
  - Display MAC addresses and 6-digit Device Keys.
  - Delete or inspect playlists.
- **🌐 DNS Servers Manager**:
  - Add, update, and remove Xtream Codes DNS portals.
- **⚙️ Panel & Application Settings**:
  - Customizable admin password (default: `200600`).
  - In-app notification title & message (displayed directly on TV screens).
  - Default account expiry and trial days.
  - APK update download link configuration.
- **🔒 Reverse Engineered API Compatibility**:
  - Fully compatible with `SanSatUltraV10.apk` / `com.shadeed.SanSatUp`.
  - Implements the exact obfuscation/deobfuscation algorithm (`CryptoHelper`) matching Dalvik class `t2.g`.
  - Supports `/authenticate`, `/playlists`, `/playlists/delete`, `/parent-control/update`, and `player_api.php`.

---

## 🚀 Deployment on Coolify

### Option A: Using Dockerfile (Recommended)
1. In your **Coolify** dashboard, click **+ New Resource** -> **Public/Private Repository**.
2. Set the repository URL:
   ```
   https://github.com/DreamaniaCode/DreamaniaIBO.git
   ```
3. Set the build pack to **Dockerfile**.
4. Set the internal port to **80**.
5. Add a persistent volume for database storage:
   - Mount path: `/var/www/html/data`
6. Click **Deploy**!

### Option B: Using Docker Compose
Coolify automatically detects `docker-compose.yml` if you choose Docker Compose deployment.

---

## 💻 Local Development

Run with PHP's built-in server:
```bash
php -S 0.0.0.0:80 router.php
```
Open [http://localhost/](http://localhost/) in your browser.

- **Default Access Password**: `200600`

---

## 📁 Project Structure

```
├── .htaccess            # Apache URL rewrite rules for clean API paths
├── .gitignore           # Git ignore rules
├── Dockerfile           # Optimized PHP 8.2 Apache production image
├── docker-compose.yml   # Docker Compose definition with data volume
├── api.php              # API router (/authenticate, /delete, /parent-control)
├── crypto.php           # Payload obfuscator & deobfuscator matching app
├── db.php               # SQLite database layer with auto-migration
├── index.php            # Modern dark-themed administration panel
├── qr.php               # QR code generator endpoint
├── router.php           # Built-in PHP CLI development server router
└── data/                # Database directory (database.sqlite)
```

---

## 🔐 Default Credentials
- **Admin Password**: `200600` (can be changed in the Settings tab).

