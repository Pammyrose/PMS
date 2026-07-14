# DENR-CAR PMS Excel Upload Specification

## Purpose

This document defines the user experience, workbook format, parsing rules, validation, persistence, security, and extension requirements for importing physical targets from Microsoft Excel into the DENR-CAR Performance Management System (PMS).

The import feature converts a sector workbook into the same PAP hierarchy, indicator assignments, office rows, and consolidated physical-target records used by manual entry.

## Current Scope

Excel import is currently implemented for:

- **GASS** through the `GASS` worksheet and `GassExcelUploadController`
- **STO** through the `STO` worksheet and `StoExcelUploadController`

The current import writes **physical targets only**. It does not import physical accomplishments, financial inputs, remarks, users, or audit history from the workbook.

Import and preview routes are currently inside administrator-protected sector route groups. Upload controls must be rendered only for roles that can successfully call those routes.

## Import Architecture

```text
Upload button and hidden file input
              |
              v
Asynchronous preview request
              |
              v
Sector Excel upload controller
              |
              v
SimpleXlsxReader
|-- opens XLSX ZIP package
|-- resolves sector worksheet
|-- reads shared/inline strings
|-- reads cell styles where required
`-- yields visible rows incrementally
              |
              v
Sector parser
|-- detects PAP hierarchy
|-- joins continuation text
|-- resolves indicators and offices
|-- reads physical target periods
`-- builds preview rows and warnings
              |
              v
Preview modal
              |
              | user confirms
              v
Transactional import request
|-- create/reuse PAP hierarchy
|-- create/reuse indicator
|-- synchronize indicator offices
`-- upsert physical targets
              |
              v
Redirect with success/error feedback
```

## Frontend Workflow

### 1. Select a Workbook

The sector toolbar provides an **Upload Excel** button. It opens a hidden file input configured with `accept=".xlsx"`. The form includes:

- A CSRF token
- The selected reporting year
- The workbook file
- The sector preview URL in a `data-preview-url` attribute

### 2. Preview Before Import

Selecting a file must not immediately persist data. JavaScript submits the same multipart form to the preview endpoint with:

- `X-CSRF-TOKEN`
- `X-Requested-With: XMLHttpRequest`
- `Accept: application/json`

While parsing, the modal shows a loading indicator and disables confirmation.

### 3. Review the Preview

The preview modal displays:

- Parsed item count
- Importable office-row count
- Skipped row/item count
- Warning count
- Excel source row
- Parsed PAP hierarchy
- Performance indicator
- Matched offices
- Presence of a CAR total

The preview displays at most 120 parsed items and at most 80 warning details, while the count fields report the complete parser totals.

### 4. Confirm or Cancel

- **Cancel** closes the modal, clears the selected file, and performs no write.
- **Import Excel** becomes enabled only after a successful preview.
- Confirmation disables the button, shows an importing state, and submits the original form to the import endpoint.
- The same workbook is parsed again on confirmation; preview data is never trusted as the persistence payload.

### 5. Show the Result

The import endpoint redirects back to the sector page with a success or error message. The page must make the result visible through an accessible alert. Where practical, success feedback should include imported, skipped, and placeholder counts.

## File Validation

Both preview and confirmed import must apply the same server-side rules.

| Input | Rule |
| --- | --- |
| `excel_file` | Required uploaded file |
| Extension/type | `.xlsx` only |
| Maximum size | 51,200 KB (50 MB) |
| `year` | Optional integer |
| Year range | 2000–2099 |

Browser `accept` filtering is only a convenience. The server-side file validation is authoritative.

## Workbook Contract

### Worksheet

The workbook must contain a worksheet whose name matches the importing sector:

| Sector | Required worksheet |
| --- | --- |
| GASS | `GASS` |
| STO | `STO` |

Worksheet matching is case-insensitive. A missing or unreadable worksheet causes preview/import failure.

### Starting Row

Rows before Excel row 10 are treated as document headers and ignored. Hidden worksheet rows are skipped.

### Core Columns

| Excel column | Meaning | Import behavior |
| --- | --- | --- |
| A | PAP hierarchy or heading text | Builds Title/Program/Project/Activity hierarchy and continuation text |
| B | Performance indicator | Starts or continues an indicator block |
| C | Location or office | Resolves CAR, PENRO/province, CENRO, or another known office |
| D–H | Non-physical metadata/template columns | Not persisted by the physical-target importer |
| I–Y | Physical target periods | Parsed into monthly, quarterly, and annual values |
| AA and later financial-only rows | Financial template content | Ignored by the physical-target importer |

### Physical Period Mapping

| Column | Period | Column | Period |
| --- | --- | --- | --- |
| I | January | Q | July |
| J | February | R | August |
| K | March | S | September |
| L | Q1 | T | Q3 |
| M | April | U | October |
| N | May | V | November |
| O | June | W | December |
| P | Q2 | X | Q4 |
| Y | Annual total |  |  |

Empty, non-numeric, error-like, or comment-prefixed numeric cells are treated as zero. When the annual value in column Y is zero or absent, the importer calculates it as `Q1 + Q2 + Q3 + Q4`.

## XLSX Reader Behavior

`SimpleXlsxReader` reads the workbook directly as an Open XML ZIP package. It does not use Excel automation and does not evaluate workbook formulas or macros.

The reader:

- Confirms that the temporary uploaded file exists
- Opens the `.xlsx` ZIP archive
- Resolves the requested worksheet through workbook relationships
- Reads shared strings and inline strings
- Normalizes repeated whitespace in text
- Preserves source row numbers
- Can expose bold and fill-style metadata
- Uses a generator so rows can be processed incrementally
- Skips rows marked hidden when requested

STO parsing uses style-aware rows because bold/fill information can help distinguish hierarchy headers. GASS currently uses normalized cell values without requiring style metadata.

## Parser Model

The parser is stateful because the spreadsheet represents hierarchy by position and formatting instead of repeating full parent information on every row.

### Parser State

Each sector parser maintains concepts equivalent to:

- Current top-level program/title
- Program and project metadata
- Current hierarchy headers
- Standalone heading context
- Current indicator block
- Current parent office
- Duplicate-summary suppression
- Imported, skipped, parsed, placeholder, and warning counts

### Import Block

One logical indicator block contains:

```text
Source Excel row
Title / Program / Project
Hierarchy header fragments
Activity continuation fragments
Indicator text fragments
CAR totals
PENRO/group totals
Office target rows
Placeholder indicator flag
```

A block is flushed when a new program, hierarchy/indicator block, footer, or end of worksheet is encountered.

The block is skipped when it has no matched office rows, has no usable indicator, represents an all-`N/A` placeholder that should not be stored, or contains no importable physical record.

## PAP Hierarchy Mapping

The Excel hierarchy must map to the canonical PMS PAP fields in this order:

```text
title
`-- program
    `-- project
        `-- activities
            `-- subactivities
                `-- subsubactivities
                    `-- level_6
                        `-- level_7
                            `-- level_8
```

Rules:

- Program/title headers reset the active hierarchy context.
- Numeric, Roman-numeral, and alphabetic headings establish hierarchy depth.
- Wrapped or split text rows are joined to the appropriate hierarchy or indicator fragment.
- Standalone title sections can become their own top-level PAP block.
- Missing Program or Project values use `N/A` where the existing PAP storage contract requires a value.
- PAP text is trimmed and limited to the storage length; oversized labels are truncated safely.
- The deepest populated hierarchy field becomes the PAP leaf/`row_id` used by the target record.
- Re-importing the same normalized hierarchy should reuse the existing PAP records rather than create duplicates.

The import hierarchy must render according to the canonical PAP layout in `frontend.md` after the page reloads.

## Sorting Validation

Preview performs a separate hierarchy-order check and reports warnings tied to Excel row numbers.

The checker detects:

- Missing values in Roman-numeral sequences
- Missing values in numeric root sequences
- Missing values in alphabetic sequences
- A child numeric heading appearing before its parent root
- Jumps within nested numeric sequences
- Headings that may have been attached under the wrong parent section

Warnings do not automatically modify workbook order. A user must review them before confirming import. Danger-level warnings should be visually stronger than ordinary warnings.

## Performance Indicator Resolution

When flushing an import block, the importer:

1. Joins indicator continuation fragments.
2. Normalizes the final indicator name.
3. Attempts to infer the indicator type from the workbook hierarchy and office pattern.
4. Finds an existing sector indicator using a case-insensitive trimmed-name comparison.
5. Creates the indicator if none exists.
6. Updates the type assignment when a reliable inferred type differs from missing/outdated metadata.
7. Links the indicator to the exact PAP leaf and the resolved office IDs.

The supported indicator types remain Cumulative, Non-cumulative, and Semi-cumulative. The import must not create duplicate indicators that differ only in case or surrounding whitespace.

## Office Resolution

Office matching normalizes both database names and workbook labels by converting them to uppercase and removing punctuation and whitespace.

### Special Rows

- `CAR` represents the regional aggregate and does not become a normal editable office row.
- A province/PENRO header sets the current parent office context.
- A plain `PENRO` row resolves to the current parent province office.
- Province and child-office aliases may resolve known variations such as Mountain Province abbreviations.
- Recognized RO rows may contribute to CAR totals.

### Office Grouping

- Office rows remain associated with their parent PENRO/province group.
- Parent-office target rows can supply group totals.
- Duplicate rows for the same office are normalized; a row with actual targets takes precedence over an empty duplicate.
- If no explicit CAR values are supplied, CAR totals are calculated from RO or group totals, falling back to available totals as defined by the parser.
- Unmatched office labels are excluded from target persistence and surfaced through preview warnings when they leave an item without importable office rows.

New aliases must be added deliberately and tested against the office table. A broad fuzzy match that could assign values to the wrong office is not acceptable.

## Preview Response Contract

A successful preview returns HTTP `200` with this shape:

```json
{
  "success": true,
  "preview": {
    "year": 2026,
    "imported": 0,
    "skipped": 0,
    "parsed_rows": 0,
    "shown_rows": 0,
    "placeholders": 0,
    "rows": [],
    "warnings": [],
    "warning_count": 0
  }
}
```

`imported` in preview represents the number of office target rows that would be imported; preview itself performs no database write.

Each item in `rows` may contain:

- `row`: source Excel row number
- `title`, `program`, and `project`
- `hierarchy`: displayable Activity-through-level-8 path
- `indicator`: normalized performance indicator
- `offices`: matched office labels
- `office_count`: number of importable office rows
- `has_car_total`: whether the block supplied a CAR total

A preview parsing failure returns HTTP `422` with `success: false` and a readable message.

## Confirmed Import and Persistence

The confirmed import wraps the complete workbook operation in one database transaction.

For each valid block, it:

1. Creates or reuses the PAP hierarchy.
2. Creates or reuses the indicator.
3. Synchronizes the indicator/PAP/office assignment.
4. Upserts one `PhysicalTarget` per resolved office.
5. Stores CAR and group totals.
6. Marks the row with `imported_from = "excel"`.

The consolidated target identity is:

```text
sector + year + office_id + row_id + indicator_id
```

Re-importing the same identity updates the existing physical target. It must not create a duplicate. The importing user becomes the record's current `user_id`.

An exception anywhere in the confirmed import rolls back PAP, indicator, assignment, and target changes made by that request. Physical accomplishments and financial inputs are not changed.

## Audit Behavior

- Confirmed imports run through `field.history` and can create an audit entry after a successful response.
- Preview routes are explicitly excluded because preview does not mutate business data.
- The audit snapshot must not copy workbook binary content.
- Import audit data should identify sector, year, route, actor, and high-level result without storing excessive row payloads.

## Error Handling

### Preview Errors

- Keep the preview modal open.
- Replace the loading state with a readable failure message.
- Clear stale statistics and preview rows.
- Disable Import Excel.
- Allow the user to cancel and select another workbook.

### Import Errors

- Roll back the database transaction.
- Redirect to the originating sector page.
- Display an error alert.
- Log detailed server context for administrators while keeping production user messages free of stack traces, SQL, filesystem paths, and secrets.

## Security Requirements

- Require authenticated administrator authorization for current import routes.
- Require a valid CSRF token for preview and import.
- Validate file type and size on both endpoints.
- Never trust the filename, worksheet text, preview JSON, or client-reported MIME type by itself.
- Read only the expected sector worksheet.
- Do not execute formulas, VBA, macros, external links, or embedded objects.
- Escape all preview text before inserting it into the DOM.
- Use database transactions and parameterized persistence operations.
- Do not expose temporary upload paths or raw exception details in production.
- Enforce reasonable processing limits to protect memory, CPU, and audit storage.
- Retain the original workbook only if an approved records-management policy explicitly requires it.

## Adding Excel Import to Another Sector

New sector support must follow the same preview-first contract.

### Required Backend Work

1. Add preview and import named routes inside the authorized sector group.
2. Create a sector upload controller or extract the common importer into reusable services.
3. Define the required worksheet name and default sector title.
4. Reuse `SimpleXlsxReader`.
5. Map workbook hierarchy to the canonical PAP fields.
6. Resolve offices through the shared office normalization/alias strategy.
7. Resolve indicators without case/whitespace duplicates.
8. Upsert unified `PhysicalTarget` records with the correct sector key.
9. Wrap confirmed import in a transaction.
10. Add feature and parser tests.

### Required Frontend Work

1. Add the sector upload form partial.
2. Add a preview modal with loading, statistics, warnings, and row table.
3. Add preview JavaScript using the sector's named preview route.
4. Disable confirmation until preview succeeds.
5. Escape every workbook-derived string.
6. Show result alerts after redirect.
7. Render the control only for roles authorized by the import route.

### Shared-versus-Sector Rules

These behaviors must remain shared:

- File/year validation
- Preview response shape
- Preview modal structure
- Period column mapping
- PAP field order
- Office normalization principles
- Transaction and upsert identity
- Audit and security requirements

Only these should normally vary:

- Sector key and label
- Worksheet name
- Default title
- Workbook-specific heading/style detection
- Approved office aliases
- Sector-specific exceptions for known official templates

Prefer extracting shared parser, preview, and persistence services over copying the GASS/STO controllers. Sector-specific parsing rules should be isolated behind a small strategy interface so fixes do not drift across importers.

## Verification Checklist

### File and Worksheet

- A valid `.xlsx` under 50 MB is accepted.
- Other file types and oversized files are rejected.
- A missing sector worksheet fails cleanly.
- Hidden rows and pre-row-10 headers are skipped.

### Preview

- Preview performs no database write.
- Counts match the parsed workbook.
- Source row, hierarchy, indicator, and offices display correctly.
- Warning counts include warnings beyond the displayed 80-item limit.
- Import remains disabled after preview failure.
- Closing the modal clears the file selection.

### Hierarchy and Sorting

- Title through `level_8` map correctly.
- Wrapped hierarchy and indicator text are joined correctly.
- Numeric, Roman, and alphabetic order warnings identify the right Excel row.
- `N/A` placeholders do not create unintended PAP/indicator duplicates.

### Offices and Values

- CAR, PENRO/province, CENRO, and aliases resolve correctly.
- Unmatched offices are warned and not silently assigned.
- Columns I–Y map to the correct periods.
- Annual fallback equals Q1 + Q2 + Q3 + Q4.
- CAR and PENRO/group totals match their source office rows.

### Persistence

- Confirmed import creates the expected PAPs, indicators, assignments, and targets.
- A repeated import updates matching identities without duplicates.
- All imported target rows contain `imported_from = "excel"`.
- One failing block rolls back the entire confirmed import.
- Accomplishments and financial inputs remain unchanged.
- The sector page renders imported records using the canonical PAP layout.

---

[Back to Project Documentation](global.md) | [Frontend Specification](frontend.md) | [Backend Specification](backend.md)
