# SBCI Student Portal

A Laravel 12 student account and administration portal. Students register with an email and password, then complete their name, program, and year level the first time they sign in. Administrators have a separate login page and can review student activity, account totals, archived accounts, program/year counts, and a seven-day login/logout chart.

## Requirements

- PHP 8.2 or later with PDO SQLite enabled
- The MongoDB PHP extension (`ext-mongodb`) enabled for the PHP executable used by XAMPP/Composer
- Composer
- MongoDB Atlas or MongoDB Server

## MongoDB Atlas setup (XAMPP)

1. In Atlas, create a database user and allow the current development machine's IP address in Network Access. URL-encode special characters in the database password when placing it in the URI.
2. Download the MongoDB PHP extension from [the official Windows PECL builds](https://windows.php.net/downloads/pecl/releases/mongodb/) matching the XAMPP PHP build (PHP 8.2, thread safety, x64 architecture, and VS16 compiler). Place `php_mongodb.dll` in `C:\xampp\php\ext`, enable `extension=mongodb` in `C:\xampp\php\php.ini`, restart Apache and any running Artisan server, then confirm `php -m` lists `mongodb`.
3. From this directory, install dependencies and create the environment file:

   ```sh
   composer install
   copy .env.example .env
   php artisan key:generate
   ```

4. Set `DB_CONNECTION=mongodb`, `MONGODB_DATABASE=sbcilogin`, and `MONGODB_URI` (or Laravel's `DB_URI`) to the Atlas connection string in `.env`. Replace any `<db_password>` placeholder locally with the database user's actual password. Set `MONGODB_AUTH_SOURCE=admin` for the usual Atlas database-user authentication database. Keep the URI private; do not commit or share it. Set `SQLITE_SOURCE_DATABASE` to the absolute path of the current SQLite file if it is not at `database/database.sqlite`.
5. Set `ADMIN_EMAIL` and `ADMIN_PASSWORD` if you need the seeder to create or update an administrator.
6. Create the MongoDB collections/indexes and administrator account:

   ```sh
   php artisan migrate
   php artisan db:seed
   ```

7. Check the connection, preview the source data counts, then copy existing data:

   ```sh
   php artisan db:copy-sqlite-to-mongodb --dry-run
   php artisan db:copy-sqlite-to-mongodb
   ```

   The copy command upserts by document ID, so it can be safely re-run. It copies users, activity logs, active-session summaries, and records; timestamp fields are converted to BSON dates. It does not delete the SQLite source or remove any MongoDB data.

8. Serve the app:

   ```sh
   php artisan serve
   ```

Visit `http://127.0.0.1:8000`. The administrator sign-in link is in the footer. In XAMPP's Apache setup, point the site's document root at this project's `public` directory.

The `DatabaseSeeder` creates an administrator only when both `ADMIN_EMAIL` and `ADMIN_PASSWORD` are set. It does not create a public/default admin credential. Keep the SQLite database until you have verified the migrated account counts and can log in against MongoDB.

## Portal behavior

- Student registration accepts an email and password; profile details are required immediately after the first successful login.
- Student and administrator logins and logouts are retained in `activity_logs`, including email/name snapshots so historical activity remains readable if a student account is later archived.
- The admin dashboard counts active students seen in the last 15 minutes. The activity chart covers the current day and previous six days.
- Archiving disables student login without deleting the account or its history. Administrators can restore archived student accounts.
- Admin accounts use `/admin/login`; student accounts cannot access admin routes.

## Database portability

The application models select the SQL or MongoDB Eloquent base model from `DB_CONNECTION`. MongoDB stores app entities as documents in `users`, `activity_logs`, `active_sessions`, and `records`; migrations create their collections and indexes. Changing `DB_CONNECTION` alone does not copy existing records, so use the copy command above before switching the application over.
