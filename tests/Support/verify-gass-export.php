<?php

// Read-only check of the local database. Creates a workbook and private test
// payload; run the companion Node script to compare the UI's own JS formulas.
require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$year = (int) ($argv[1] ?? now()->year);
$request = Illuminate\Http\Request::create('/', 'GET', ['year' => $year]);
$page = app(App\Http\Controllers\GassController::class)->index($request);
$html = view('admin.gass.partials.gass_physical_table_rows', $page->getData())->render();
$sources = [];
foreach (['physical_target' => 'physical_targets', 'physical_accomplishment' => 'physical_accomplishments', 'financial_target' => 'financial_target', 'financial_accomplishment' => 'financial_accomplishment'] as $kind => $table) {
    foreach (Illuminate\Support\Facades\DB::table($table)->where('sector', 'gass')->where('year', $year)->get() as $record) {
        $sources[$kind][$record->row_id][$record->indicator_id][$record->office_id] = (array) $record;
    }
}
$path = storage_path("app/GASS-{$year}-UI-verified.xlsx");
$rows = app(App\Support\GassUiReport::class)->rows($request, $year);
(new App\Support\SimpleXlsxWriter)->writePerformanceReport($path, 'GASS', 'GENERAL ADMINISTRATION AND SUPPORT SERVICES (GASS)', $year, $rows, now());
$sheet = iterator_to_array((new App\Support\SimpleXlsxReader)->rows($path, 'GASS', false));
file_put_contents(storage_path('app/gass-export-verification.json'), json_encode(['html' => $html, 'sources' => $sources, 'sheet' => $sheet, 'month' => now()->month]));
echo json_encode(['workbook' => $path, 'export_rows' => count($rows)], JSON_PRETTY_PRINT).PHP_EOL;
