# Secure File Storage System

![Secure File Storage](public/cryptologo.png)

A secure, user-friendly file storage system built with **Laravel 12** and **MongoDB**. Store, manage, and share your files safely with end-to-end encryption support.

---

## 🚀 Features

- **User Authentication** - Secure login & registration with SHA-512 password hashing
- **File Management** - Upload, download, delete, and organize files
- **File Encryption** - Encrypted file storage for maximum security
- **User Dashboard** - Intuitive interface to manage your files
- **Database** - MongoDB for flexible and scalable data storage
- **Modern UI** - Built with Tailwind CSS for responsive design
- **Database Migrations** - Easy setup with Laravel migrations
- **API Ready** - RESTful API for programmatic access (future)

---

## 📋 Requirements

Before you begin, ensure you have the following installed:

- **PHP** 8.2 or higher
- **Composer** (PHP dependency manager)
- **Node.js** 16+ & **npm** (for frontend assets)
- **MongoDB** 5.0+ (or MongoDB Atlas cloud)
- **Git** (optional, for version control)

---

## ⚙️ Installation & Setup

### Quick Start (One Command)

```bash
composer setup
```

This will automatically:
- Install PHP dependencies
- Create `.env` configuration file
- Generate application key
- Run database migrations
- Install npm packages
- Build frontend assets

### Manual Setup (If needed)

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd "Secure File Storage System"
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Copy environment file**
   ```bash
   cp .env.example .env
   ```

4. **Generate application key**
   ```bash
   php artisan key:generate
   ```

5. **Configure Database**
   - Open `.env` and update MongoDB connection settings:
   ```env
   DB_CONNECTION=mongodb
   DB_HOST=localhost
   DB_PORT=27017
   DB_DATABASE=secure_file_storage
   ```

6. **Run database migrations**
   ```bash
   php artisan migrate
   ```

7. **Install & build frontend assets**
   ```bash
   npm install
   npm run build
   ```

---

## 🏃 Running the Application

### Development Mode (Recommended)

```bash
composer run dev
```

This starts all services simultaneously:
- **Laravel Server** - Backend API (http://localhost:8000)
- **Queue Worker** - Background job processing
- **Vite Dev Server** - Hot-reload for frontend assets
- **Logs** - Real-time application logs

### Production Mode

```bash
php artisan serve
npm run build
```

Then open your browser and go to: **http://localhost:8000**

---

## 📖 Usage Guide

### 1. Register a New Account

1. Navigate to the **Register** page
2. Enter your email and create a secure password
3. Click **Register** to create your account
4. Password will be hashed with SHA-512 for security

### 2. Login

1. Go to the **Login** page
2. Enter your credentials
3. You'll be redirected to your **Dashboard**

### 3. Upload Files

1. In the **Dashboard**, click **Upload File**
2. Select file(s) from your computer
3. Files are automatically encrypted and stored securely
4. View uploaded files in your file list

### 4. Manage Files

- **Download** - Click the download icon to get your file
- **View Details** - See file size, upload date, and more
- **Delete** - Remove files you no longer need
- **Organize** - Create folders to organize your files

### 5. Logout

Click the **Logout** button in the navigation bar to end your session.

---

## 🧪 Running Tests

```bash
composer run test
```

This runs all unit and feature tests defined in the `tests/` directory.

---

## 📁 Project Structure

```
Secure File Storage System/
├── app/                    # Application logic (Controllers, Models, etc.)
├── bootstrap/              # Framework bootstrapping files
├── config/                 # Configuration files
├── database/               # Migrations, seeders, factories
├── public/                 # Publicly accessible files (images, CSS, JS)
│   └── images/            # Application images & logos
├── resources/              # Views, CSS, JavaScript
│   ├── views/             # Blade template files
│   ├── css/               # Stylesheets
│   └── js/                # JavaScript files
├── routes/                 # Application route definitions
├── storage/                # User uploads, logs, cache
├── tests/                  # Unit & Feature tests
├── vendor/                 # Composer dependencies
├── .env                    # Environment configuration (create from .env.example)
├── .gitignore              # Git ignore rules
├── composer.json           # PHP dependencies
├── package.json            # Node.js dependencies
├── vite.config.js          # Frontend build configuration
└── phpunit.xml             # Test configuration
```

---

## 🔐 Security Features

- ✅ **Password Hashing** - SHA-512 with bcrypt (12 rounds)
- ✅ **Session Management** - Secure database-driven sessions
- ✅ **CSRF Protection** - Built-in Laravel CSRF tokens
- ✅ **File Encryption** - Encrypted storage for sensitive files
- ✅ **Input Validation** - All user inputs are validated
- ✅ **SQL Injection Prevention** - Eloquent ORM protection

---

## 🔧 Environment Variables

Key environment variables in `.env`:

```env
APP_NAME=Secure File Storage System
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mongodb
DB_HOST=localhost
DB_DATABASE=secure_file_storage

FILESYSTEM_DISK=local
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

MAIL_MAILER=log
```

---

## 📚 Technologies Used

- **Laravel 12** - PHP Web Framework
- **MongoDB** - NoSQL Database (via laravel-mongodb)
- **Tailwind CSS** - Utility-first CSS framework
- **Vite** - Next-generation frontend build tool
- **Blade** - Laravel templating engine
- **Composer** - PHP Package Manager
- **npm** - Node.js Package Manager

---

## 🐛 Troubleshooting

### Issue: "Key already exists" during setup
- Delete `.env` and run `php artisan key:generate` again

### Issue: MongoDB connection fails
- Ensure MongoDB is running: `mongod`
- Check connection settings in `.env`

### Issue: Assets not loading (CSS/JS)
- Run: `npm run build`
- For development with hot reload: `npm run dev`

### Issue: "No such table" errors
- Run migrations: `php artisan migrate`
- Force fresh migration: `php artisan migrate:fresh`

---

## 📞 Support & Contribution

- Found a bug? Report it in the Issues section
- Have suggestions? Create a Pull Request
- Questions? Check the documentation or contact the team

---

## 📄 License

This project is licensed under the **MIT License** - see the LICENSE file for details.

---

**Happy and Secure File Sharing!** 🎉

Last Updated: 2026-06-03
