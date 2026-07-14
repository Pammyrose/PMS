<?php

declare(strict_types=1);

/**
 * Keep every physical-performance sector aligned with the GASS reference
 * controller, views, and styles.
 *
 * Usage:
 *   php tools/replicate-gass-physical.php
 *   php tools/replicate-gass-physical.php --check
 */

$root = dirname(__DIR__);
$checkOnly = in_array('--check', $argv, true);

$roles = ['admin', 'regional', 'users'];
$sectors = [
    'sto' => 'STO',
    'enf' => 'ENF',
    'pa' => 'PA',
    'engp' => 'ENGP',
    'lands' => 'LANDS',
    'soilcon' => 'SOILCON',
    'nra' => 'NRA',
    'paria' => 'PARIA',
    'cobb' => 'COBB',
    'continuing' => 'CONTINUING',
];
$sectorClasses = [
    'sto' => 'Sto',
    'enf' => 'Enf',
    'pa' => 'Pa',
    'engp' => 'Engp',
    'lands' => 'Lands',
    'soilcon' => 'Soilcon',
    'nra' => 'Nra',
    'paria' => 'Paria',
    'cobb' => 'Cobb',
    'continuing' => 'Continuing',
];
$importSectors = ['sto'];
$partialSuffixes = [
    'physical_header.blade.php',
    'physical_main_scripts.blade.php',
    'physical_main_scripts2.blade.php',
    'physical_modals.blade.php',
    'physical_modal_scripts.blade.php',
    'physical_table.blade.php',
    'physical_table_rows.blade.php',
    'physical_tabs.blade.php',
    'physical_toolbar.blade.php',
];

$changed = [];

$read = static function (string $path): string {
    $content = file_get_contents($path);

    if ($content === false) {
        throw new RuntimeException("Unable to read {$path}");
    }

    return str_replace("\r\n", "\n", $content);
};

$withoutExcelImport = static function (string $content, string $suffix): string {
    if ($suffix === 'physical_toolbar.blade.php') {
        return (string) preg_replace(
            "/^[ \t]*@include\('(?:admin|regional|users)\.gass\.partials\.gass_physical_excel_upload'\)[ \t]*\n/m",
            '',
            $content
        );
    }

    if ($suffix === 'physical_main_scripts.blade.php') {
        $start = strpos($content, "            const excelUploadForm = document.getElementById('gassExcelUploadForm');");
        $periods = $start === false ? false : strpos($content, '        const PERIODS = [', $start);

        if ($start === false || $periods === false) {
            throw new RuntimeException('Unable to locate the GASS Excel script block.');
        }

        return substr($content, 0, $start) . "        });\n" . substr($content, $periods);
    }

    if ($suffix === 'physical_modals.blade.php') {
        $start = strpos($content, '    <div class="modal fade" id="gassExcelPreviewModal"');
        $deleteModal = $start === false
            ? false
            : strpos($content, '    <div class="modal fade" id="deleteProgramConfirmModal"', $start);

        if ($start === false || $deleteModal === false) {
            throw new RuntimeException('Unable to locate the GASS Excel preview modal.');
        }

        return substr($content, 0, $start) . substr($content, $deleteModal);
    }

    return $content;
};

$adapt = static function (string $content, string $sector, string $label): string {
    // GASS is a safe source token: unlike short target codes such as PA or STO,
    // it cannot alter shared words like "partials" or "stored".
    return str_replace(
        ['GASS', 'Gass', 'gass'],
        [$label, $label, $sector],
        $content
    );
};

$adaptController = static function (
    string $content,
    string $sector,
    string $label,
    string $className
): string {
    // The PAP hierarchy records for every sector intentionally use the shared
    // legacy Gass_Physical model/table. All other GASS identifiers are scoped.
    $content = str_replace(
        ['Gass_Physical', "'gass_physical'"],
        ['__SHARED_PHYSICAL_MODEL__', "'__shared_physical_table__'"],
        $content
    );

    $content = str_replace(
        ['GASS', 'Gass', 'gass'],
        [$label, $className, $sector],
        $content
    );

    return str_replace(
        ['__SHARED_PHYSICAL_MODEL__', "'__shared_physical_table__'"],
        ['Gass_Physical', "'gass_physical'"],
        $content
    );
};

$writeOrCheck = static function (string $path, string $expected) use ($checkOnly, &$changed, $read): void {
    $actual = is_file($path) ? $read($path) : null;

    if ($actual === $expected) {
        return;
    }

    $changed[] = str_replace('\\', '/', $path);

    if ($checkOnly) {
        return;
    }

    if (file_put_contents($path, $expected) === false) {
        throw new RuntimeException("Unable to write {$path}");
    }
};

foreach ($roles as $role) {
    $gassDirectory = "{$root}/resources/views/{$role}/gass";

    foreach ($sectors as $sector => $label) {
        $targetDirectory = "{$root}/resources/views/{$role}/{$sector}";
        $main = $adapt($read("{$gassDirectory}/gass_physical.blade.php"), $sector, $label);
        $writeOrCheck("{$targetDirectory}/{$sector}_physical.blade.php", $main);

        foreach ($partialSuffixes as $suffix) {
            $content = $read("{$gassDirectory}/partials/gass_{$suffix}");

            if (!in_array($sector, $importSectors, true)) {
                $content = $withoutExcelImport($content, $suffix);
            }

            $content = $adapt($content, $sector, $label);
            $writeOrCheck("{$targetDirectory}/partials/{$sector}_{$suffix}", $content);
        }

        if (in_array($sector, $importSectors, true)) {
            $excelUpload = $adapt(
                $read("{$gassDirectory}/partials/gass_physical_excel_upload.blade.php"),
                $sector,
                $label
            );
            $writeOrCheck(
                "{$targetDirectory}/partials/{$sector}_physical_excel_upload.blade.php",
                $excelUpload
            );
        }
    }
}

$gassCss = $read("{$root}/public/css/admin/gass/gass_physical.css");
foreach ($sectors as $sector => $label) {
    $writeOrCheck(
        "{$root}/public/css/admin/{$sector}/{$sector}_physical.css",
        $adapt($gassCss, $sector, $label)
    );
}

$gassController = $read("{$root}/app/Http/Controllers/GassController.php");
foreach ($sectors as $sector => $label) {
    $className = $sectorClasses[$sector];
    $writeOrCheck(
        "{$root}/app/Http/Controllers/{$className}Controller.php",
        $adaptController($gassController, $sector, $label, $className)
    );
}

if ($changed === []) {
    fwrite(STDOUT, "All sector physical files match GASS.\n");
    exit(0);
}

if ($checkOnly) {
    fwrite(STDERR, "Sector physical files differ from GASS:\n - " . implode("\n - ", $changed) . "\n");
    exit(1);
}

fwrite(STDOUT, 'Updated ' . count($changed) . " sector files from GASS.\n");
