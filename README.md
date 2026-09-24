<p align="center">
  <img src="public/denr_logo.png" alt="DENR logo" width="110">
</p>

<h1 align="center">DENR-CAR Performance Management System</h1>

<p align="center">
  A role-based web application for recording, monitoring, reviewing, and reporting the physical and financial performance of DENR-CAR programs, activities, and projects.
</p>

<p align="center">
  <img alt="PHP 8.2+" src="https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php&logoColor=white">
  <img alt="Laravel 12" src="https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white">
  <img alt="Vite 7" src="https://img.shields.io/badge/Vite-7-646CFF?logo=vite&logoColor=white">
  <img alt="Tests" src="https://img.shields.io/badge/tests-122%20passing-brightgreen">
</p>

## About the project

The DENR-CAR Performance Management System (PMS) centralizes Work and Financial Plan data across sectors, reporting years, and offices. It gives administrators, Regional Office personnel, PENROs, and CENRO/users interfaces suited to their responsibilities while preserving office-level access controls and an audit trail of changes.

The application is a server-rendered Laravel modular monolith. Blade provides the user interface, Laravel controllers and services coordinate the workflows, and Eloquent persists performance data to a relational database.

## Features

- Role-based authentication and office-scoped access
- Dashboard summaries, performance rankings, and delay trends
- Monthly, quarterly, and annual physical target/accomplishment monitoring
- Financial target and accomplishment entry
- Work and Financial Plan (WFP) Excel import previews and transactional imports
- Official WFP Excel exports
- Approval workflow for corrections to locked reporting periods
- User notifications for approval decisions
- User management and a predefined Regional Office/PENRO/CENRO hierarchy
- Filterable edit-history and audit records
- Eleven program areas: GASS, STO, ENF, PA, ENGP, LANDS, SOILCON, NRA, PARIA, COBB, and Continuing Activities

## Technology stack

| Layer | Technologies |
| --- | --- |
| Backend | PHP 8.2+, Laravel 12, Eloquent ORM |
| Frontend | Blade, Bootstrap 5, Tailwind CSS 4, JavaScript |
| Build tooling | Vite 7, Laravel Vite Plugin, npm |
| Database | SQLite for local setup; MySQL-compatible databases are also supported |
| Spreadsheet handling | Application-level XLSX reader/writer using PHP ZIP and XML extensions |
| Testing | PHPUnit 11, Laravel test utilities |

## Requirements

Install the following before setting up the project:

- PHP 8.2 or newer
- Composer 2
- Node.js 20.19+ and npm
- SQLite, or a MySQL-compatible database
- PHP extensions required by Laravel, plus `pdo_sqlite` or `pdo_mysql`, `zip`, `xml`, and `xmlreader`

## Local installation

1. Clone the repository and enter the project directory.

   ```bash
   git clone https://github.com/Pammyrose/PMS.git
   cd PMS
   ```

2. Install the PHP dependencies and create the environment file.

   ```bash
   composer install
   cp .env.example .env
   php artisan key:generate
   ```

   On Windows PowerShell, replace the `cp` command with:

   ```powershell
   Copy-Item .env.example .env
   ```

3. Create the local SQLite database.

   macOS/Linux:

   ```bash
   touch database/database.sqlite
   ```

   Windows PowerShell:

   ```powershell
   New-Item database/database.sqlite -ItemType File -Force
   ```

   The default `.env.example` is already configured to use SQLite. To use MySQL instead, update the database section in `.env`:

   ```dotenv
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=pms
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. Create the database tables and seed the reference data.

   ```bash
   php artisan migrate --seed
   ```

5. Install and build the frontend assets.

   ```bash
   npm install
   npm run build
   ```

6. Start the application.

   ```bash
   php artisan serve
   ```

   Open [http://127.0.0.1:8000](http://127.0.0.1:8000) in your browser.

### Development mode

To run the Laravel server, queue listener, application log viewer, and Vite development server together:

```bash
composer run dev
```

## Development login

Running the seeders creates an administrator account for local development:

```text
Email:    admin@denr.gov.ph
Password: password
```

> **Important:** These credentials are public development defaults. Change or remove every seeded password before deploying the application or using real data.

Additional sample roles are defined in `database/seeders/UsersSeeder.php`. PENRO and CENRO accounts should be assigned to the appropriate office before they are used for office-scoped workflows.

## Testing

Run the complete automated test suite with:

```bash
composer test
```

Run Laravel Pint to check or fix PHP formatting:

```bash
./vendor/bin/pint
```

On Windows PowerShell, use `vendor/bin/pint` if the shell does not recognize the Unix-style path.

## Project structure

```text
app/                 Controllers, middleware, models, services, and XLSX utilities
bootstrap/           Laravel application bootstrapping
config/              Application and service configuration
database/            Migrations, factories, and seeders
public/              Web entry point and public assets
resources/           Blade views, source CSS/JS, and the official WFP template
routes/               Web and console routes
specs/                Architecture, frontend, backend, and Excel specifications
tests/                PHPUnit unit and feature tests
tools/                Project maintenance scripts
```

More detailed technical documentation is available in [`specs/global.md`](specs/global.md), with dedicated architecture, backend, frontend, and Excel-import specifications in the same directory.

## Production notes

Before deployment:

- Set `APP_ENV=production`, `APP_DEBUG=false`, and the correct `APP_URL`.
- Generate a unique application key and use strong database credentials.
- Replace all seeded credentials and keep `.env` out of version control.
- Configure the web server document root to `public/`.
- Build optimized assets with `npm run build`.
- Run `php artisan migrate --force` during a controlled deployment.
- Configure a persistent queue worker if queued jobs are introduced or enabled.
- Use HTTPS, regular backups, and access controls appropriate for operational government data.

## Contributing

1. Create a feature branch from the current main branch.
2. Keep changes consistent across every affected sector and role-specific view.
3. Add or update automated tests for behavioral changes.
4. Run `composer test` and Laravel Pint before opening a pull request.
5. Update the files in `specs/` when behavior, data structures, roles, or deployment requirements change.

## License

This repository does not currently include a software license. Add an appropriate license before allowing third parties to copy, modify, or redistribute the project.
