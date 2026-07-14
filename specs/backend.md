# DENR-CAR PMS Backend Specification

## Purpose

This document defines the backend architecture, responsibilities, data flow, and implementation conventions of the DENR-CAR Performance Management System (PMS). The backend provides authentication, role and office authorization, PAP and indicator management, performance-data persistence, dashboard aggregation, Excel import, and audit history.

## Backend Overview

PMS is implemented as a Laravel modular monolith. Web pages and JSON-based interactions use the same Laravel application, route file, session, domain models, and relational database. There is no separate public REST API or independent backend service.

### Technology Stack

- **Language:** PHP 8.2+
- **Framework:** Laravel 12
- **Persistence:** Eloquent ORM and Laravel query builder
- **Database:** MySQL-compatible relational database
- **Authentication:** Laravel session authentication
- **Authorization:** Route middleware plus controller-level office scoping
- **Caching:** Laravel cache facade
- **Imports:** Custom XLSX reader and sector import controllers
- **Testing:** PHPUnit 11 through Laravel's test runner
- **Code formatting:** Laravel Pint

## Backend Structure

```text
app/
|-- Http/
|   |-- Controllers/
|   |   |-- Controller.php                  # Shared role and office helpers
|   |   |-- AuthController.php              # Login handling
|   |   |-- DashboardController.php         # Dashboard aggregation
|   |   |-- PhysicalInputController.php     # Shared target/accomplishment writes
|   |   |-- FinancialInputController.php    # Shared financial writes
|   |   |-- ProgramController.php           # Program administration
|   |   |-- UserController.php              # User administration
|   |   |-- HistoryController.php           # Audit-history queries
|   |   |-- {Sector}Controller.php          # Sector PAP and indicator workflows
|   |   `-- *ExcelUploadController.php      # GASS/STO import workflows
|   `-- Middleware/
|       |-- EnsureUserHasRole.php            # Role allow-list enforcement
|       `-- LogFieldEdits.php                # Mutation audit trail
|-- Models/
|   |-- User.php, Office.php, OfficeType.php
|   |-- Ppa.php, Indicator.php, RecordType.php
|   |-- PhysicalTarget.php
|   |-- PhysicalAccomplishment.php
|   |-- FinancialInput.php
|   |-- EditHistory.php
|   |-- LegacyPhysical*.php                 # Compatibility layer
|   `-- Concerns/
|       `-- UsesConsolidatedPhysicalTable.php
|-- Providers/
`-- Support/
    `-- SimpleXlsxReader.php

bootstrap/app.php                            # Middleware aliases and app setup
routes/web.php                               # Web and JSON endpoint definitions
database/migrations/                        # Versioned database schema
tests/Unit/                                  # Unit tests
tests/Feature/                               # HTTP and integration tests
```

## Request Lifecycle

```text
HTTP request
    |
    v
routes/web.php
    |
    v
Web middleware
|-- session and CSRF
|-- auth
|-- role
`-- field.history on audited mutations
    |
    v
Controller action
|-- validate request
|-- enforce sector and office scope
|-- execute domain query or transaction
`-- select role view or create JSON response
    |
    v
Eloquent / query builder
    |
    v
Relational database
    |
    v
Blade view, redirect, or JSON response
```

## Routing

All application routes are defined in `routes/web.php` and use Laravel's web middleware stack.

### Route Categories

| Category | Representative routes | Responsibility |
| --- | --- | --- |
| Authentication | `GET /`, `POST /login`, `POST /logout` | Session creation and termination |
| Dashboard | `GET /dashboard` | Role-aware performance overview |
| Sector pages | `GET /{sector}_physical/{program?}` | Physical and financial performance views |
| Shared physical input | `POST /{sector}_physical/targets/store` and `accomplishments/store` | Office-scoped grid persistence |
| Shared financial input | `POST /financial-inputs/{sector}/store` | Financial grid persistence |
| Administration | `/admin/{sector}_physical/...` | PAP, indicator, row, and import management |
| Users | `/user` and `/users/...` | Administrator user management |
| Audit history | `GET /history` | Administrator audit review |

Route names are part of the application contract. They are used by Blade templates, JavaScript endpoint configuration, redirects, and the audit middleware; renaming a route requires updating all consumers.

## Authentication and Authorization

### Authentication

- Login creates a Laravel-authenticated session.
- Protected routes require the `auth` middleware.
- Logout invalidates the session and regenerates the CSRF token.
- User passwords use Laravel's hashed cast and must never be logged or returned in responses.

### Role Middleware

`EnsureUserHasRole` compares the authenticated user's stored role with the allow-list declared by the route. A missing user or disallowed role receives HTTP `403`.

The current route groups distinguish:

- **Administrator mutations:** `admin`
- **General viewing:** `super-admin`, `admin`, `user`, `penro`, `cenro`, `ro-office`, and `ro office`
- **Office-user physical writes:** `user`, `penro`, and `cenro`

### Office Scoping

Role authorization is supplemented by record-level office scoping. The base controller identifies users who must be restricted to their assigned office and provides helpers to:

- Select the user's office for physical pages
- Filter indicators assigned to the office
- Remove inaccessible PAP rows
- Filter target and accomplishment sections
- Reject submitted physical or financial entries for another office

Route middleware protects the operation type; controller rules protect the data scope. Both checks are required.

## Controller Responsibilities

### Base Controller

The application base controller contains shared behavior for:

- Selecting `admin`, `regional`, or `users` Blade namespaces
- Resolving office context
- Filtering PAP, indicator, and section data
- Determining whether the current account is office-scoped

### Dashboard Controller

The dashboard controller:

- Validates sector and year filters
- Applies the authenticated user's office scope
- Aggregates monthly target and accomplishment values
- Calculates overall progress, sector status, trends, active sectors, and rankings
- Builds PAP and indicator lists
- Caches dashboard summaries and year options for short periods
- Returns the appropriate role-specific dashboard view

Controllers should pass display-ready data to Blade, but HTML rendering must remain in the view layer.

### Sector Controllers

Each sector controller coordinates its own PAP hierarchy and indicator screens for:

- GASS
- STO
- ENF
- PA
- ENGP
- LANDS
- SOILCON
- NRA
- PARIA
- COBB
- Continuing programs

Sector controllers load the relevant type, PAP hierarchy, assigned indicators, physical records, and office information. Administrator actions may create or delete PAPs, create or update indicators, and remove physical rows.

### Shared Physical Input Controller

`PhysicalInputController` is the write boundary for unified physical targets and accomplishments. It:

1. Resolves and validates the sector from the route default.
2. Finds the sector type record.
3. Confirms that PAP rows belong to that sector.
4. Validates indicator, office, year, period, aggregate, and remarks fields.
5. Enforces office scope for restricted users.
6. Upserts every submitted row inside a database transaction.
7. Returns created and updated counts as JSON.

Targets and accomplishments share the same write workflow. Accomplishments additionally accept remarks.

### Shared Financial Input Controller

`FinancialInputController` applies the same sector, PAP, indicator, year, numeric-period, and office rules to financial data. Rows are upserted transactionally and reported through a JSON response.

### User Controller

The user controller validates account details, role values, office requirements, and password handling. PENRO and CENRO accounts must be associated with an appropriate office as required by the current workflow.

### Excel Import Controllers

GASS and STO provide preview and import endpoints. Import processing must:

- Validate file type, file size, and reporting year
- Parse the workbook without executing embedded content
- Normalize headings, office labels, numbers, and hierarchy levels
- Preview detected data and warnings before permanent import
- Resolve or create valid PAP and indicator references
- Upsert targets using the same consolidated physical schema
- Return understandable errors for invalid or unsupported sheets

Import preview requests are intentionally excluded from edit-history logging because they do not persist business data.

## Data Model

### Core Relationships

```text
OfficeType
  `-- Office
      `-- User

Type / Sector
  `-- PAP hierarchy (ppa + ppa_details)
      `-- Indicator
          |-- PhysicalTarget
          |-- PhysicalAccomplishment
          `-- FinancialInput

User
  `-- EditHistory
```

### Unified Performance Identity

Physical and financial rows are uniquely identified by:

```text
sector + year + office_id + row_id + indicator_id
```

`program_id` preserves the owning program while `row_id` identifies the exact PAP hierarchy row represented by the entry.

### Performance Periods

The three consolidated performance tables store:

- Monthly values: `jan` through `dec`
- Quarterly values: `q1` through `q4`
- Annual value: `annual_total`
- Structured rollups: `car_totals` and `group_totals`
- Ownership/context: sector, user, office, program, row, indicator, and year

`physical_accomplishments` additionally stores `remarks`. Physical rows can store an `imported_from` marker.

### Referential Behavior

- Deleting a referenced PAP or indicator cascades to consolidated performance entries.
- Deleting a user or office sets the corresponding performance foreign key to `NULL`.
- Unique constraints prevent duplicate performance identities.
- Composite indexes support sector/year/office page queries.

## Legacy Compatibility

The database originally used separate target and accomplishment tables for every sector. The consolidated migration copies valid legacy rows into `physical_targets` and `physical_accomplishments`.

Legacy sector model class names remain available through:

- `UsesConsolidatedPhysicalTable`, which adds a sector scope and maps legacy attributes
- `LegacyPhysicalBuilder`, which maps legacy column names such as `years` and `office_ids`
- `LegacyPhysicalTarget` and `LegacyPhysicalAccomplishment`, which point old sector models at the consolidated tables

New backend code should prefer `PhysicalTarget`, `PhysicalAccomplishment`, and `FinancialInput`. The compatibility layer should be removed only after all sector controllers and reports no longer depend on legacy model contracts.

## Validation and Persistence

### Validation Rules

State-changing endpoints must validate input before any write. Performance input rules include:

- A non-empty `entries` array
- Valid sector-specific PAP identifiers
- Existing indicator and office identifiers
- Reporting year from 2000 through 2100
- Non-negative numeric monthly, quarterly, and annual values
- Array-shaped CAR and group totals
- Bounded import-source metadata
- Optional text remarks for accomplishments

### Transaction Rules

- Multi-row performance writes must use `DB::transaction`.
- A failure in any submitted row must roll back the complete request.
- Upserts must use the consolidated unique identity.
- Controllers must return counts or clear feedback indicating the result.
- Destructive actions must verify the referenced record and its allowed scope.

## Audit History

The `field.history` middleware records successful `POST`, `PUT`, `PATCH`, and `DELETE` requests after the controller response completes.

Each audit entry can include:

- User ID, name, and role
- Module and edited area
- Action and HTTP method
- Route name and record identifier
- Changed fields
- A normalized request snapshot
- Previous and changed value previews where available

Passwords, password confirmations, current passwords, CSRF tokens, and method fields are excluded. Snapshots are depth-, length-, and item-limited to prevent unbounded audit payloads. Failed responses and non-persistent import previews are not logged.

## Responses and Error Handling

### HTML Workflows

Page requests return Blade views. Successful traditional form actions generally redirect with session feedback; validation failures return Laravel's standard error bag and old input.

### JSON Workflows

Grid persistence, modal operations, import previews, and selected administrative actions return JSON. Successful responses should include a stable `success` flag, a human-readable message, and useful result data such as created/updated counts.

Use conventional HTTP status codes:

- `200` or redirect for success
- `403` for role or office denial
- `404` for an unknown sector or missing resource
- `422` for validation or unavailable configuration
- `500` only for unexpected server failures

Do not expose stack traces, SQL, secrets, or sensitive request data in production responses.

## Caching and Performance

- Dashboard summaries are cached by year, selected sector, and office scope.
- Year options are cached separately and for a slightly longer interval.
- Aggregate queries select only required columns and use composite indexes.
- Schema/column existence checks are cached within the dashboard controller instance.
- Large legacy migrations process rows in chunks.
- Large imports should avoid loading unnecessary workbook data into memory.

Any mutation that affects cached dashboard totals must account for cache freshness. The present short cache duration limits staleness; explicit invalidation may be introduced if immediate consistency becomes required.

## Security Requirements

- Require `auth` on every non-public application route.
- Declare a role allow-list for privileged routes.
- Apply office scoping to both reads and writes.
- Use CSRF protection for every session-authenticated mutation.
- Validate all IDs against the database and the selected sector.
- Use Eloquent or parameterized query-builder operations; never concatenate user input into SQL.
- Exclude credentials and secrets from logs and audit snapshots.
- Validate uploaded workbooks before parsing or persistence.
- Keep environment secrets outside source control.
- Avoid mass assignment unless the model explicitly defines safe fillable fields or the controller supplies a controlled payload.

## Backend Conventions

- Use named routes and group related endpoints by prefix and middleware.
- Keep controllers responsible for request orchestration, not HTML generation.
- Reuse the shared physical and financial controllers for common persistence behavior.
- Use Eloquent relationships for clear domain navigation and the query builder for deliberate aggregate queries.
- Put reusable non-HTTP parsing or mapping behavior in support classes or focused services.
- Keep migrations reversible and safe for existing data.
- Use explicit return types where practical.
- Return consistent response shapes for browser JavaScript.
- Add feature tests for authorization, validation, office scoping, and persistence regressions.

## Testing and Verification

The test environment uses an in-memory SQLite database, array-backed cache and sessions, synchronous queues, and a reduced bcrypt cost.

### Run All Backend Tests

```powershell
composer test
```

or:

```powershell
php artisan test
```

### Format Check

```powershell
vendor\bin\pint --test
```

Backend changes should verify:

- Guest requests are redirected or rejected as expected.
- Each role can access only its declared routes.
- Office-scoped users cannot read or write another office's data.
- Invalid sector, PAP, indicator, office, year, and numeric values are rejected.
- Multi-row writes are atomic and do not create duplicates.
- Target, accomplishment, financial, and remarks values reload correctly.
- Audit records are created only for successful mutations and omit sensitive fields.
- Dashboard aggregates respect year, sector, and office filters.
- Import preview does not persist data; confirmed import does.
- Migrations run successfully on a clean database and preserve supported legacy data.

---

[Back to Project Documentation](global.md) | [Architecture Specification](architecture.md) | [Frontend Specification](frontend.md)
