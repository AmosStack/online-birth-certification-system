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

## Troubleshooting

- Database connection error:
  - Ensure MySQL is running and credentials in both `dbconnection.php` files are correct.
- Login not working after fresh import:
  - Apply the SQL update in the password note above.
- Blank or broken pages:
  - Ensure Apache/PHP is running and file paths are under `htdocs`.

## License

No license file is currently included in this repository. Add a `LICENSE` file if you plan to distribute or reuse the project publicly.