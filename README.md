# Secure File Storage System

<img src="public/cryptologo.png" alt="Secure File Storage" width="250" height="200" style="display: block; margin: 0 auto;">

A secure file storage system for uploading, managing, and sharing files safely.

---

## 🚀 Features

- Secure user authentication with SHA-512 password hashing
- Two-Factor Authentication (email OTP) during login
- Password strength checker with real-time feedback during registration
- End-to-end encrypted user chat (client-side encryption)
- File upload, download, and management
- File encryption and secure storage
- Share encrypted files with other registered users by email
- Revoke shared access from the dashboard
- User-friendly dashboard

---

## 📋 Requirements

- PHP 8.2+
- Composer
- Node.js 16+ & npm
- SQLite (included with PHP)

---

## ⚙️ Installation & Setup

### Quick Start
```bash
composer install
php artisan key:generate
php artisan migrate
```

### Manual Steps

1. **Install dependencies**
   ```bash
   composer install
   ```

2. **Copy environment file**
   ```bash
   cp .env.example .env
   ```

3. **Generate key**
   ```bash
   php artisan key:generate
   ```

4. **Run migrations**
   ```bash
   php artisan migrate
   ```


---

## 🏃 Running the Application

```bash
php artisan serve
```

Open your browser and go to: **http://localhost:8000**

---

## 📖 Usage Guide

1. **Register** - Create a new account with email and password
2. **Login** - Sign in with your credentials
3. **Upload** - Upload files to your secure storage
4. **Share** - Open the dashboard and share an encrypted file with another registered user by email
5. **Manage** - Download, view, delete, or revoke shared access from the dashboard
6. **Logout** - End your session

---

## 🔐 Security

- SHA-512 password hashing
- Email-based 2FA for login verification
- Password strength scoring and weak-password feedback
- Browser-side end-to-end encryption for chat messages
- CSRF protection
- Input validation
- Encrypted file storage
- Session management

---

## 📁 Project Structure

```
├── app/              # Controllers, Models, Business Logic
├── database/         # Migrations, Seeders
├── public/           # Assets, Images
├── resources/        # Views, CSS, JavaScript
├── routes/           # API & Web Routes
├── storage/          # User Uploads, Logs
└── tests/            # Unit & Feature Tests
```

---

## 🔧 Environment Variables

```env
APP_NAME=Secure File Storage System
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

SESSION_DRIVER=database
CACHE_STORE=database
```

---

