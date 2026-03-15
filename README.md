# Online Birth Certificate System (OBCS)

Online Birth Certificate System is a PHP and MySQL web application for managing birth certificate applications. It includes a public landing page, a user portal for application submission and tracking, and an admin portal for verification and reporting.

## Features

- Public home page with quick links to user and admin portals
- User registration and login
- Birth certificate application form submission
- User dashboard for managing submitted forms and downloading certificates
- Admin dashboard with application statistics
- Admin workflows for:
  - New applications
  - Verified applications
  - Rejected applications
  - All applications
  - Registered users
  - Between-dates reporting
  - Certificate download
- Server-side database access using PDO
- Password hashing with `password_hash` / `password_verify`
- Backward compatibility for legacy MD5 password hashes (auto-upgrade after successful login)

## Tech Stack

- Backend: PHP
- Database: MySQL / MariaDB
- Frontend: HTML, CSS, JavaScript, Bootstrap, jQuery
- Local environment: XAMPP (Apache + MySQL)

## Project Structure

- `index.php`: Public landing page
- `obcsdb.sql`: Database schema and seed data
- `admin/`: Admin portal
- `user/`: User portal
- `css/`, `js/`, `img/`, `fonts/`: Shared public assets

## Prerequisites

- XAMPP (or equivalent Apache + MySQL + PHP environment)
- PHP 7.2+ recommended
- MySQL/MariaDB running locally

## Local Setup (XAMPP)

1. Place the project in your web root:

   - `c:\xampp\htdocs\online-birth-certification-system`

2. Start Apache and MySQL from the XAMPP Control Panel.

3. Create and import the database:

   - Open phpMyAdmin: `http://localhost/phpmyadmin`
   - Create database: `obcsdb`
   - Import file: `obcsdb.sql`

4. Verify database connection settings in:

   - `admin/includes/dbconnection.php`
   - `user/includes/dbconnection.php`

   Default values in this project:

   - `DB_HOST=localhost`
   - `DB_USER=root`
   - `DB_PASS=` (empty)
   - `DB_NAME=obcsdb`

5. Open the application:

   - `http://localhost/online-birth-certification-system/`

## Default Access

- Admin login page: `http://localhost/online-birth-certification-system/admin/login.php`
- User login page: `http://localhost/online-birth-certification-system/user/login.php`
- User registration page: `http://localhost/online-birth-certification-system/user/register.php`

Seed admin username in SQL dump:

- Username: `admin`

### Important Password Note

Current authentication verifies `password_hash` values and legacy MD5 hashes. If your imported admin password was stored as plain text, login will fail.

After importing `obcsdb.sql`, run this SQL once in phpMyAdmin to ensure the seed admin password works:

```sql
UPDATE tbladmin
SET Password = MD5('admin123')
WHERE UserName = 'admin';
```

Then login with:

- Username: `admin`
- Password: `admin123`

On successful login, the system can automatically upgrade the stored password to a modern hash.

## Common Workflows

### User

1. Register account
2. Login
3. Fill birth certificate form
4. Track application status
5. Download certificate when verified

### Admin

1. Login
2. Review incoming applications
3. Verify or reject with remarks
4. View reports and download certificates

## Security Notes

- Uses prepared statements (PDO) for SQL queries
- Uses `htmlspecialchars` helper (`obcs_escape`) for output escaping
- Uses session checks before accessing protected pages
- Passwords should never be stored in plain text

## Google OAuth Setup

The user login page supports "Sign in with Google". Follow these steps to enable it:

### 1. Create Google OAuth credentials

1. Go to the [Google Cloud Console](https://console.cloud.google.com/).
1. Create a project or select an existing one.
1. Navigate to **APIs & Services > Library** and enable the **Google People API**.
1. Navigate to **APIs & Services > Credentials**.
1. Click **Create Credentials > OAuth client ID**.
1. Application type: **Web application**.
1. Under **Authorised redirect URIs**, add:
   ```
   http://localhost/online-birth-certification-system/user/google-callback.php
   ```
1. Copy the **Client ID** and **Client Secret**.

### 2. Configure the application

Edit `user/google-config.php` and set your credentials:

```php
define('GOOGLE_CLIENT_ID',     'YOUR_GOOGLE_CLIENT_ID');
define('GOOGLE_CLIENT_SECRET', 'YOUR_GOOGLE_CLIENT_SECRET');
define('GOOGLE_REDIRECT_URI',  'http://localhost/online-birth-certification-system/user/google-callback.php');
```

### 3. Add the new database columns

Run this SQL once in phpMyAdmin (skip if you do a fresh import of `obcsdb.sql`):

```sql
ALTER TABLE `tbluser`
  ADD COLUMN IF NOT EXISTS `Email`    varchar(200) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `GoogleID` varchar(255) DEFAULT NULL;

ALTER TABLE `tbluser`
  ADD UNIQUE KEY IF NOT EXISTS `uq_tbluser_google_id` (`GoogleID`);
```

### How it works

| Scenario | Behaviour |
|---|---|
| User has signed in with Google before | Logged in using stored `GoogleID` |
| Existing account with matching email | `GoogleID` is linked to that account and user is logged in |
| No matching account | A new account is created using the Google profile |

Google-only users have no usable password stored; they must always authenticate via Google.

## Facebook OAuth Setup

The user login page also supports "Continue with Facebook". Follow these steps to enable it:

### 1. Create a Facebook app

1. Go to the [Meta for Developers](https://developers.facebook.com/) portal.
1. Create an app or select an existing one.
1. Add the **Facebook Login** product to the app.
1. In **Facebook Login > Settings**, add this valid OAuth redirect URI:
   ```
   http://localhost/online-birth-certification-system/user/facebook-callback.php
   ```
1. In **App Settings > Basic**, copy the **App ID** and **App Secret**.

### 2. Configure the application

Edit `user/google-config.php` and set the Facebook credentials:

```php
define('FACEBOOK_APP_ID',      'YOUR_FACEBOOK_APP_ID');
define('FACEBOOK_APP_SECRET',  'YOUR_FACEBOOK_APP_SECRET');
define('FACEBOOK_REDIRECT_URI', rtrim(OBCS_APP_URL, '/') . '/user/facebook-callback.php');
```

The same config file also defines:

```php
define('OBCS_APP_URL', 'http://localhost/online-birth-certification-system');
```

If you open the project from a different host or path, update `OBCS_APP_URL` so the generated redirect URI matches exactly.

### 3. Add the required database column

Run this SQL once in phpMyAdmin (skip if you do a fresh import of `obcsdb.sql`):

```sql
ALTER TABLE `tbluser`
  ADD COLUMN IF NOT EXISTS `FacebookID` varchar(255) DEFAULT NULL;

ALTER TABLE `tbluser`
  ADD UNIQUE KEY IF NOT EXISTS `uq_tbluser_facebook_id` (`FacebookID`);
```

### How it works

| Scenario | Behaviour |
|---|---|
| User has signed in with Facebook before | Logged in using stored `FacebookID` |
| Existing account with matching email | `FacebookID` is linked to that account and user is logged in |
| No matching account | A new account is created using the Facebook profile |

Facebook-only users have no usable password stored; they must always authenticate via Facebook.

## Troubleshooting

- Database connection error:
  - Ensure MySQL is running and credentials in both `dbconnection.php` files are correct.
- Login not working after fresh import:
  - Apply the SQL update in the password note above.
- Blank or broken pages:
  - Ensure Apache/PHP is running and file paths are under `htdocs`.

## License

No license file is currently included in this repository. Add a `LICENSE` file if you plan to distribute or reuse the project publicly.
