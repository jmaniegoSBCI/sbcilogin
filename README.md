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

## Deploy to Vercel

This project uses the maintained [`vercel-community/php`](https://github.com/vercel-community/php) runtime. The configuration pins `vercel-php@0.9.0`, which provides PHP 8.5; the project requires PHP `^8.2`, and Laravel 12 and the MongoDB Laravel integration support that PHP version. The runtime includes the MongoDB PHP extension. Vercel is explicitly set to the **Other** framework preset, skips the Vite build (this app serves its CSS directly from `public/css`), and uses `public` rather than the Vite default `dist` output directory.

1. Push this project to GitHub and import its repository in Vercel. Keep the project root set to this directory (the one containing `composer.json` and `vercel.json`). The checked-in Vercel config sets the framework preset and output directory; if you previously changed these in the dashboard, redeploy so the configuration is applied.
2. In the Vercel project's **Settings → Environment Variables**, configure:
   - `APP_KEY`: the production key generated with `php artisan key:generate --show`. Do not reuse a development key or commit it.
   - `APP_URL`: the production Vercel URL, including `https://` (for example, `https://your-project.vercel.app`).
   - `DB_CONNECTION`: `mongodb`.
   - `MONGODB_DATABASE`: `sbcilogin`.
   - `MONGODB_URI`: the MongoDB connection URI for the production database.
   - `MONGODB_AUTH_SOURCE`: usually `admin` for Atlas.
   - `ADMIN_EMAIL` and `ADMIN_PASSWORD`: set these if you will seed or update an administrator.
3. In MongoDB Atlas, allow network access from Vercel (or use an appropriate Atlas network access configuration for your deployment), and ensure the database user's credentials in `MONGODB_URI` are valid. Keep all credentials in Vercel's environment settings, not in Git.
4. Before sending traffic to the deployment, run `php artisan migrate --force` and `php artisan db:seed --force` against the production MongoDB database from a trusted machine with the production variables configured. Verify the database connection and administrator login.
5. Deploy from Vercel. The PHP entry point forwards application requests to Laravel and serves safe static assets from `public`; serverless-safe session, cache, compiled-view, and log settings are included in `vercel.json`.

Vercel's filesystem is ephemeral, so this deployment uses secure, encrypted cookie sessions and in-memory cache rather than relying on local session/cache files. No Docker installation or container configuration is needed; Vercel runs the PHP function using the configured runtime. The application database must be a persistent MongoDB service; do not use SQLite as the production database. The first Vercel deployment cannot complete sign-in or migration until the MongoDB URI, credentials, and network access are correct.

## Portal behavior

- Student registration accepts an email and password; profile details are required immediately after the first successful login.
- Student and administrator logins and logouts are retained in `activity_logs`, including email/name snapshots so historical activity remains readable if a student account is later archived.
- The admin dashboard counts active students seen in the last 15 minutes. The activity chart covers the current day and previous six days.
- Archiving disables student login without deleting the account or its history. Administrators can restore archived student accounts.
- Admin accounts use `/admin/login`; student accounts cannot access admin routes.

## Database portability

The application models select the SQL or MongoDB Eloquent base model from `DB_CONNECTION`. MongoDB stores app entities as documents in `users`, `activity_logs`, `active_sessions`, and `records`; migrations create their collections and indexes. Changing `DB_CONNECTION` alone does not copy existing records, so use the copy command above before switching the application over.
