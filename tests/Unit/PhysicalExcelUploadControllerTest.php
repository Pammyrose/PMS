<?php

namespace Tests\Unit;

use App\Http\Controllers\EnfController;
use App\Http\Controllers\EngpController;
use App\Http\Controllers\GassController;
use App\Http\Controllers\PaController;
use App\Http\Controllers\PhysicalExcelUploadController;
use App\Support\SimpleXlsxReader;
use App\Support\SimpleXlsxWriter;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use ZipArchive;

class PhysicalExcelUploadControllerTest extends TestCase
{
    public function test_enf_importer_uses_enf_configuration(): void
    {
        $config = PhysicalExcelUploadController::sectorConfiguration('enf');

        $this->assertSame('ENF', $config['sheet_name']);
        $this->assertSame(['NRE&RP'], $config['sheet_aliases']);
        $this->assertSame(['A.03.g.1 Natural Resources'], $config['start_markers']);
        $this->assertSame(['TENURES:'], $config['unlocated_section_markers']);
        $this->assertTrue($config['unlocated_section_children_are_rows']);
        $this->assertSame('enf', $config['sector']);
        $this->assertSame('ENF', $config['type_code']);
    }

    public function test_enf_header_detection_uses_the_enf_title(): void
    {
        $method = new ReflectionMethod(PhysicalExcelUploadController::class, 'isStoSheetHeaderText');
        $controller = $this->controller('enf');

        $this->assertTrue($method->invoke($controller, 'ENFORCEMENT'));
        $this->assertTrue($method->invoke($controller, 'ENFORCEMENT ENF'));
        $this->assertTrue($method->invoke($controller, 'ENFORCEMENT NRE&RP'));
        $this->assertFalse($method->invoke($controller, 'SUPPORT TO OPERATIONS'));
    }

    public function test_enf_importer_accepts_enf_and_nre_rp_worksheet_names(): void
    {
        $method = new ReflectionMethod(PhysicalExcelUploadController::class, 'resolveExcelSheetName');
        $controller = $this->controller('enf');

        foreach (['ENF', 'NRE&RP'] as $sheetName) {
            $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.'enf-sheet-alias-'.bin2hex(random_bytes(6)).'.xlsx';

            try {
                (new SimpleXlsxWriter)->writeWfp($path, $sheetName, 'ENFORCEMENT', 2026, []);
                $this->assertSame($sheetName, $method->invoke($controller, $path));
            } finally {
                if (is_file($path)) {
                    unlink($path);
                }
            }
        }
    }

    public function test_enf_importer_uses_neutral_data_rules_for_a_renamed_gass_worksheet(): void
    {
        $source = dirname(__DIR__, 2).'/resources/templates/DENR-CAR-2026-WFP-GAA-MIP.xlsx';
        $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.'renamed-gass-as-enf-'.bin2hex(random_bytes(6)).'.xlsx';
        copy($source, $path);

        try {
            $zip = new ZipArchive;
            $this->assertTrue($zip->open($path) === true);
            $workbook = (string) $zip->getFromName('xl/workbook.xml');
            $workbook = preg_replace('/(<sheet\b[^>]*\bname=")GASS("[^>]*>)/u', '$1ENF$2', $workbook, 1);
            $this->assertIsString($workbook);
            $zip->addFromString('xl/workbook.xml', $workbook);
            $zip->close();

            $controller = $this->controller('enf');
            $reader = new SimpleXlsxReader;
            $configure = new ReflectionMethod(PhysicalExcelUploadController::class, 'configureExcelParsingRules');
            $configure->invoke($controller, $reader, $path, 'ENF');

            $startMarkers = new ReflectionMethod(PhysicalExcelUploadController::class, 'excelStartMarkers');
            $programPrefixes = new ReflectionMethod(PhysicalExcelUploadController::class, 'excelProgramHeaderPrefixes');
            $programHeader = new ReflectionMethod(PhysicalExcelUploadController::class, 'isStoProgramHeader');

            $this->assertSame([], $startMarkers->invoke($controller));
            $this->assertSame([], $programPrefixes->invoke($controller));
            $this->assertTrue($programHeader->invoke(
                $controller,
                'GENERAL ADMINISTRATION AND SUPPORT',
                ['C' => 'CAR']
            ));
        } finally {
            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    public function test_enf_importer_keeps_enf_rules_when_the_rows_identify_the_enf_layout(): void
    {
        $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.'enf-data-layout-'.bin2hex(random_bytes(6)).'.xlsx';

        try {
            (new SimpleXlsxWriter)->writeWfp($path, 'ENF', 'ENFORCEMENT', 2026, [[
                'pap' => 'A.03.g.1 Natural Resources',
                'office' => 'CAR',
            ]]);

            $controller = $this->controller('enf');
            $configure = new ReflectionMethod(PhysicalExcelUploadController::class, 'configureExcelParsingRules');
            $configure->invoke($controller, new SimpleXlsxReader, $path, 'ENF');

            $startMarkers = new ReflectionMethod(PhysicalExcelUploadController::class, 'excelStartMarkers');
            $programPrefixes = new ReflectionMethod(PhysicalExcelUploadController::class, 'excelProgramHeaderPrefixes');

            $this->assertSame(['A.03.g.1 Natural Resources'], $startMarkers->invoke($controller));
            $this->assertSame(['A.03.g.'], $programPrefixes->invoke($controller));
        } finally {
            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    public function test_enf_template_does_not_promote_tenure_labels_to_program_titles(): void
    {
        $programHeader = new ReflectionMethod(PhysicalExcelUploadController::class, 'isStoProgramHeader');
        $styledHeader = new ReflectionMethod(PhysicalExcelUploadController::class, 'isStyledStoPapHeaderRow');
        $controller = $this->controller('enf');

        $this->assertTrue($programHeader->invoke($controller, 'A.03.g.1 Natural Resources', ['C' => 'CAR']));
        $this->assertFalse($programHeader->invoke($controller, 'NATURAL RESOURCES ENFORCEMENT', ['C' => 'CAR']));
        $this->assertFalse($programHeader->invoke($controller, 'CBFMA', ['C' => 'CAR']));
        $this->assertFalse($styledHeader->invoke(
            $controller,
            'Tenures:',
            [],
            ['A' => ['bold' => true, 'fill' => 'FFFFFFFF', 'fill_type' => 'solid']],
            'target'
        ));
    }

    public function test_enf_template_recognizes_compact_alpha_number_hierarchy_codes(): void
    {
        $method = new ReflectionMethod(PhysicalExcelUploadController::class, 'excelHeadingInfo');
        $controller = $this->controller('enf');

        $this->assertSame('alpha_number', $method->invoke($controller, 'A1.1. Processing')['type']);
        $this->assertSame('alpha_number', $method->invoke($controller, 'a.1. Processing')['type']);
        $this->assertSame(2, $method->invoke($controller, 'A.3. a. Compliance Monitoring')['depth']);
        $this->assertSame('number_letter', $method->invoke($controller, '1a. Wildlife permits')['type']);
        $this->assertSame('roman', $method->invoke($controller, 'I. Roman section')['type']);
        $this->assertSame('upper_letter', $method->invoke($controller, 'A. Letter section')['type']);
        $this->assertSame('lower_letter', $method->invoke($controller, 'a. Lowercase section')['type']);
        $this->assertSame('number', $method->invoke($controller, '1. Numbered section')['type']);
        $this->assertSame(2, $method->invoke($controller, '1.1 Numbered subsection')['depth']);
    }

    public function test_enf_hierarchy_keeps_menu_children_and_lowercase_letters_under_their_real_parents(): void
    {
        $pushHeading = new ReflectionMethod(PhysicalExcelUploadController::class, 'pushExcelHierarchyHeader');
        $pushOfficeLabel = new ReflectionMethod(PhysicalExcelUploadController::class, 'pushExcelOfficeBackedHierarchyLabel');
        $controller = $this->controller('enf');
        $headers = [];

        foreach ([
            'Forest Protection Program',
            'Menu 10. Sustainable implementation',
            '10.1 Support to Full Operationalization',
            '1d. Conduct of Annual Strategic Planning',
            '10.2 Support to PAMANA Program',
            '1. Hiring of Forest Guards',
        ] as $heading) {
            $arguments = [&$headers, $heading];
            $pushHeading->invokeArgs($controller, $arguments);
        }

        $this->assertSame([
            'Forest Protection Program',
            'Menu 10. Sustainable implementation',
            '10.2 Support to PAMANA Program',
            '1. Hiring of Forest Guards',
        ], $headers);

        $officeArguments = [&$headers, 'PMS and other Mandatories'];
        $pushOfficeLabel->invokeArgs($controller, $officeArguments);
        $headingArguments = [&$headers, 'a. Fixed expenditures'];
        $pushHeading->invokeArgs($controller, $headingArguments);

        $this->assertSame([
            'Forest Protection Program',
            'Menu 10. Sustainable implementation',
            '10.2 Support to PAMANA Program',
            'PMS and other Mandatories',
            'a. Fixed expenditures',
        ], $headers);
    }

    public function test_enf_alpha_number_siblings_do_not_remain_nested_under_a_lowercase_child(): void
    {
        $method = new ReflectionMethod(PhysicalExcelUploadController::class, 'pushExcelHierarchyHeader');
        $controller = $this->controller('enf');
        $headers = [];

        foreach (['A. Issuance', 'A1.1. Processing', 'c. Renewal', 'A.1.2. Expansion'] as $heading) {
            $arguments = [&$headers, $heading];
            $method->invokeArgs($controller, $arguments);
        }

        $this->assertSame(['A. Issuance', 'A.1.2. Expansion'], $headers);
    }

    public function test_enf_display_sorting_uses_numeric_segments_for_template_labels(): void
    {
        $method = new ReflectionMethod(EnfController::class, 'hierarchySortValue');
        $controller = new EnfController;

        $this->assertLessThan(
            $method->invoke($controller, 'Menu 10. Sustainable implementation'),
            $method->invoke($controller, 'Menu 2. Law enforcement')
        );
        $this->assertLessThan(
            $method->invoke($controller, 'A1.10. Tenth item'),
            $method->invoke($controller, 'A1.2. Second item')
        );
        $this->assertLessThan(
            $method->invoke($controller, '1b. Second letter'),
            $method->invoke($controller, '1a. First letter')
        );
        $this->assertLessThan(
            $method->invoke($controller, 'V. Fifth Roman item'),
            $method->invoke($controller, 'I. First Roman item')
        );
    }

    public function test_enf_display_prefers_excel_source_order_over_label_sorting(): void
    {
        $method = new ReflectionMethod(EnfController::class, 'sourceOrderedHierarchySortValue');
        $controller = new EnfController;

        $earlierExcelRow = $method->invoke($controller, 100, 'Z. Earlier in Excel');
        $laterExcelRow = $method->invoke($controller, 200, 'A. Later in Excel');

        $this->assertLessThan($laterExcelRow, $earlierExcelRow);
        $this->assertStringStartsWith('1|', $method->invoke($controller, null, 'A. Manual record'));
    }

    public function test_enf_a5_subgroups_and_c2_letter_children_keep_their_contextual_parents(): void
    {
        $method = new ReflectionMethod(PhysicalExcelUploadController::class, 'pushExcelHierarchyHeader');
        $controller = $this->controller('enf');
        $headers = [];

        foreach (['A. Issuance', 'A5. Project Management', 'A. Issuance under A5'] as $heading) {
            $arguments = [&$headers, $heading];
            $method->invokeArgs($controller, $arguments);
        }

        $this->assertSame([
            'A. Issuance',
            'A5. Project Management',
            'A. Issuance under A5',
        ], $headers);

        foreach (['B. Patrimonial Properties', 'C. Wildlife Trade', 'C1. Wildlife Permits', '1a. First permit'] as $heading) {
            $arguments = [&$headers, $heading];
            $method->invokeArgs($controller, $arguments);
        }

        $this->assertSame([
            'A. Issuance',
            'A5. Project Management',
            'C. Wildlife Trade',
            'C1. Wildlife Permits',
            '1a. First permit',
        ], $headers);

        foreach (['C2. Compliance monitoring', 'C2a. Wildlife Farm Permit'] as $heading) {
            $arguments = [&$headers, $heading];
            $method->invokeArgs($controller, $arguments);
        }

        $this->assertSame([
            'A. Issuance',
            'A5. Project Management',
            'C. Wildlife Trade',
            'C2. Compliance monitoring',
            'C2a. Wildlife Farm Permit',
        ], $headers);

        $arguments = [&$headers, 'C2b. Certificate of Wildlife Registration'];
        $method->invokeArgs($controller, $arguments);
        $this->assertSame('C2b. Certificate of Wildlife Registration', $headers[4]);

        $arguments = [&$headers, 'D. Inventory of Structures'];
        $method->invokeArgs($controller, $arguments);
        $this->assertSame(['D. Inventory of Structures'], $headers);
    }

    public function test_pa_importer_uses_pa_configuration(): void
    {
        $config = PhysicalExcelUploadController::sectorConfiguration('pa');

        $this->assertSame('PA', $config['sheet_name']);
        $this->assertSame(['Biodiv'], $config['sheet_aliases']);
        $this->assertSame(['P/A/P'], $config['pap_header_aliases']);
        $this->assertSame(['OFFICE'], $config['location_header_aliases']);
        $gassConfig = PhysicalExcelUploadController::sectorConfiguration('gass');
        $identityKeys = array_flip([
            'sheet_name',
            'sheet_aliases',
            'pap_header_aliases',
            'location_header_aliases',
            'default_title',
            'sector',
            'type_code',
            'label',
            'indicator_model',
        ]);
        $this->assertSame(
            array_diff_key($gassConfig, $identityKeys),
            array_diff_key($config, $identityKeys)
        );
        $this->assertSame('pa', $config['sector']);
        $this->assertSame('Biodiv', $config['type_code']);
        $this->assertSame('PA', $config['label']);
    }

    public function test_pa_importer_recognizes_the_biodiv_core_headers(): void
    {
        $controller = $this->controller('pa');
        $configure = new ReflectionMethod(PhysicalExcelUploadController::class, 'configureExcelColumnLayoutFromRows');
        $configure->invoke($controller, [
            7 => [
                'A' => 'P/A/P',
                'B' => 'PERFORMANCE INDICATORS',
                'C' => 'OFFICE',
                'I' => 'FY2026 PHYSICAL TARGET',
                'Y' => 'Grand Total',
                'Z' => 'EXPENSE CLASS',
                'AA' => "FY2026 FINANCIAL TARGET ('000)",
                'AQ' => 'Grand Total',
            ],
            9 => [
                'I' => 'Jan', 'J' => 'Feb', 'K' => 'Mar', 'L' => 'Total',
                'M' => 'Apr', 'N' => 'May', 'O' => 'Jun', 'P' => 'Total',
                'Q' => 'Jul', 'R' => 'Aug', 'S' => 'Sep', 'T' => 'Total',
                'U' => 'Oct', 'V' => 'Nov', 'W' => 'Dec', 'X' => 'Total',
            ],
        ], 'Biodiv');

        $normalize = new ReflectionMethod(PhysicalExcelUploadController::class, 'normalizeExcelCoreColumns');
        $row = $normalize->invoke($controller, [
            'A' => 'Protected Areas PAP',
            'B' => 'Protected Areas indicator',
            'C' => 'CAR',
        ]);

        $this->assertSame('Protected Areas PAP', $row['A']);
        $this->assertSame('Protected Areas indicator', $row['B']);
        $this->assertSame('CAR', $row['C']);
    }

    public function test_pa_importer_accepts_pa_and_biodiv_worksheet_names(): void
    {
        $method = new ReflectionMethod(PhysicalExcelUploadController::class, 'resolveExcelSheetName');
        $controller = $this->controller('pa');

        foreach (['PA', 'Biodiv'] as $sheetName) {
            $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.'pa-sheet-alias-'.bin2hex(random_bytes(6)).'.xlsx';

            try {
                (new SimpleXlsxWriter)->writeWfp($path, $sheetName, 'PROTECTED AREAS', 2026, []);
                $this->assertSame($sheetName, $method->invoke($controller, $path));
            } finally {
                if (is_file($path)) {
                    unlink($path);
                }
            }
        }
    }

    public function test_pa_display_sorting_matches_gass_sorting(): void
    {
        $paMethod = new ReflectionMethod(PaController::class, 'hierarchySortValue');
        $gassMethod = new ReflectionMethod(GassController::class, 'hierarchySortValue');
        $paController = new PaController;
        $gassController = new GassController;

        foreach ([
            '1. First item',
            '2. Second item',
            '10. Tenth item',
            '1.a Nested letter item',
            'I. Roman item',
            'V. Fifth Roman item',
            'A. Letter item',
            'Plain section title',
        ] as $label) {
            $this->assertSame(
                $gassMethod->invoke($gassController, $label),
                $paMethod->invoke($paController, $label)
            );
        }

        $this->assertLessThan(
            $paMethod->invoke($paController, '10. Tenth item'),
            $paMethod->invoke($paController, '2. Second item')
        );
    }

    public function test_pa_header_detection_uses_the_pa_title(): void
    {
        $method = new ReflectionMethod(PhysicalExcelUploadController::class, 'isStoSheetHeaderText');
        $controller = $this->controller('pa');

        $this->assertTrue($method->invoke($controller, 'PROTECTED AREAS'));
        $this->assertTrue($method->invoke($controller, 'PROTECTED AREAS PA'));
        $this->assertFalse($method->invoke($controller, 'ENFORCEMENT'));
    }

    public function test_pa_styled_program_header_behavior_matches_gass(): void
    {
        $styledHeader = new ReflectionMethod(PhysicalExcelUploadController::class, 'isStyledStoPapHeaderRow');
        $paController = $this->controller('pa');
        $gassController = $this->controller('gass');
        $boldStyle = ['A' => ['bold' => true, 'fill' => 'FFECECEC', 'fill_type' => 'solid']];

        foreach ([
            'Program heading',
            'Nested activity heading',
            'Plain workbook label',
        ] as $label) {
            $this->assertSame(
                $styledHeader->invoke($gassController, $label, [], $boldStyle, 'target'),
                $styledHeader->invoke($paController, $label, [], $boldStyle, 'target')
            );
        }
    }

    public function test_pa_styled_section_header_behavior_matches_gass(): void
    {
        $sectionHeader = new ReflectionMethod(PhysicalExcelUploadController::class, 'isStyledStoSectionHeaderRow');
        $paController = $this->controller('pa');
        $gassController = $this->controller('gass');
        $sectionStyle = ['A' => ['bold' => true, 'fill' => 'FFECECEC', 'fill_type' => 'solid']];

        $this->assertSame(
            $sectionHeader->invoke($gassController, 'Workbook section', ['C' => 'CAR'], $sectionStyle, 'target'),
            $sectionHeader->invoke($paController, 'Workbook section', ['C' => 'CAR'], $sectionStyle, 'target')
        );
    }

    public function test_engp_importer_uses_the_combined_sheet_configuration(): void
    {
        $config = PhysicalExcelUploadController::sectorConfiguration('engp');

        $this->assertSame('ENGP', $config['sheet_name']);
        $this->assertSame(
            ['E-NGP +Soilcon -rev', 'E-NGP +Soilcon'],
            $config['sheet_aliases']
        );
        $this->assertSame('engp', $config['sector']);
        $this->assertSame('ENGP', $config['type_code']);
        $this->assertTrue($config['persists_source_order']);
        $this->assertSame(
            ['Soil Conservation and Watershed Management'],
            $config['stop_markers']
        );
    }

    public function test_engp_importer_accepts_engp_and_combined_worksheet_names(): void
    {
        $method = new ReflectionMethod(PhysicalExcelUploadController::class, 'resolveExcelSheetName');
        $controller = $this->controller('engp');

        foreach (['ENGP', 'E-NGP +Soilcon -rev', 'E-NGP +Soilcon'] as $sheetName) {
            $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.'engp-sheet-alias-'.bin2hex(random_bytes(6)).'.xlsx';

            try {
                (new SimpleXlsxWriter)->writeWfp($path, $sheetName, 'FOREST AND WATERSHED MANAGEMENT', 2026, []);
                $this->assertSame($sheetName, $method->invoke($controller, $path));
            } finally {
                if (is_file($path)) {
                    unlink($path);
                }
            }
        }
    }

    public function test_engp_header_detection_uses_the_engp_title(): void
    {
        $method = new ReflectionMethod(PhysicalExcelUploadController::class, 'isStoSheetHeaderText');
        $controller = $this->controller('engp');

        $this->assertTrue($method->invoke($controller, 'FOREST AND WATERSHED MANAGEMENT'));
        $this->assertTrue($method->invoke($controller, 'FOREST AND WATERSHED MANAGEMENT E-NGP +Soilcon'));
        $this->assertFalse($method->invoke($controller, 'SOIL CONSERVATION AND WATERSHED MANAGEMENT'));
    }

    public function test_engp_display_sorting_matches_gass_sorting(): void
    {
        $engpMethod = new ReflectionMethod(EngpController::class, 'hierarchySortValue');
        $gassMethod = new ReflectionMethod(GassController::class, 'hierarchySortValue');
        $engpController = new EngpController;
        $gassController = new GassController;

        foreach ([
            '1. First item',
            '2. Second item',
            '10. Tenth item',
            '1.a Nested letter item',
            '2.1.b Deeper letter item',
            'I. Letter item',
            'V. Fifth Roman item',
            'A. Alpha item',
            'Plain section title',
        ] as $label) {
            $this->assertSame(
                $gassMethod->invoke($gassController, $label),
                $engpMethod->invoke($engpController, $label),
                $label
            );
        }

        $this->assertLessThan(
            $engpMethod->invoke($engpController, '10. Tenth item'),
            $engpMethod->invoke($engpController, '2. Second item')
        );
    }

    public function test_engp_display_prefers_excel_source_order(): void
    {
        $method = new ReflectionMethod(EngpController::class, 'sourceOrderedHierarchySortValue');
        $controller = new EngpController;

        $numberedParent = $method->invoke(
            $controller,
            92,
            '1. Plantation Maintenance and Protection'
        );
        $letterChild = $method->invoke(
            $controller,
            115,
            'a. Plantation Maintenance and Protection'
        );

        $this->assertLessThan($letterChild, $numberedParent);
        $this->assertStringStartsWith('1|', $method->invoke($controller, null, 'Manual ENGP PAP'));
    }

    public function test_lands_importer_uses_lands_configuration(): void
    {
        $config = PhysicalExcelUploadController::sectorConfiguration('lands');

        $this->assertSame('LANDS', $config['sheet_name']);
        $this->assertSame('lands', $config['sector']);
        $this->assertSame('Lands', $config['type_code']);
        $this->assertSame('LANDS', $config['label']);
    }

    public function test_lands_header_detection_uses_the_lands_title(): void
    {
        $method = new ReflectionMethod(PhysicalExcelUploadController::class, 'isStoSheetHeaderText');
        $controller = $this->controller('lands');

        $this->assertTrue($method->invoke($controller, 'LAND MANAGEMENT'));
        $this->assertTrue($method->invoke($controller, 'LAND MANAGEMENT LANDS'));
        $this->assertFalse($method->invoke($controller, 'FOREST AND WATERSHED MANAGEMENT'));
    }

    public function test_soilcon_importer_uses_the_combined_sheet_configuration(): void
    {
        $config = PhysicalExcelUploadController::sectorConfiguration('soilcon');

        $this->assertSame('E-NGP +Soilcon', $config['sheet_name']);
        $this->assertSame('soilcon', $config['sector']);
        $this->assertSame('Soilcon', $config['type_code']);
        $this->assertSame(
            ['Soil Conservation and Watershed Management'],
            $config['start_markers']
        );
    }

    public function test_soilcon_header_detection_uses_the_soilcon_title(): void
    {
        $method = new ReflectionMethod(PhysicalExcelUploadController::class, 'isStoSheetHeaderText');
        $controller = $this->controller('soilcon');

        $this->assertTrue($method->invoke($controller, 'SOIL CONSERVATION AND WATERSHED MANAGEMENT'));
        $this->assertTrue($method->invoke($controller, 'SOIL CONSERVATION AND WATERSHED MANAGEMENT E-NGP +Soilcon'));
        $this->assertFalse($method->invoke($controller, 'FOREST AND WATERSHED MANAGEMENT'));
    }

    public function test_nra_importer_uses_nra_configuration(): void
    {
        $config = PhysicalExcelUploadController::sectorConfiguration('nra');

        $this->assertSame('NRA', $config['sheet_name']);
        $this->assertSame('nra', $config['sector']);
        $this->assertSame('NRA', $config['type_code']);
        $this->assertSame('NRA', $config['label']);
    }

    public function test_nra_header_detection_uses_the_nra_title(): void
    {
        $method = new ReflectionMethod(PhysicalExcelUploadController::class, 'isStoSheetHeaderText');
        $controller = $this->controller('nra');

        $this->assertTrue($method->invoke($controller, 'NATURAL RESOURCES ASSESSMENT'));
        $this->assertTrue($method->invoke($controller, 'NATURAL RESOURCES ASSESSMENT NRA'));
        $this->assertFalse($method->invoke($controller, 'LAND MANAGEMENT'));
    }

    public function test_paria_importer_uses_paria_configuration(): void
    {
        $config = PhysicalExcelUploadController::sectorConfiguration('paria');

        $this->assertSame('PARIA', $config['sheet_name']);
        $this->assertSame('paria', $config['sector']);
        $this->assertSame('PARIA', $config['type_code']);
        $this->assertSame('PARIA', $config['label']);
    }

    public function test_paria_header_detection_uses_the_paria_title(): void
    {
        $method = new ReflectionMethod(PhysicalExcelUploadController::class, 'isStoSheetHeaderText');
        $controller = $this->controller('paria');

        $this->assertTrue($method->invoke($controller, 'PARIA'));
        $this->assertFalse($method->invoke($controller, 'NATURAL RESOURCES ASSESSMENT'));
    }

    public function test_cobb_importer_uses_cobb_configuration(): void
    {
        $config = PhysicalExcelUploadController::sectorConfiguration('cobb');

        $this->assertSame('COBB', $config['sheet_name']);
        $this->assertSame('cobb', $config['sector']);
        $this->assertSame('COBB', $config['type_code']);
        $this->assertSame('COBB', $config['label']);
    }

    public function test_cobb_header_detection_uses_the_cobb_title(): void
    {
        $method = new ReflectionMethod(PhysicalExcelUploadController::class, 'isStoSheetHeaderText');
        $controller = $this->controller('cobb');

        $this->assertTrue($method->invoke($controller, 'COBB'));
        $this->assertFalse($method->invoke($controller, 'PARIA'));
    }

    public function test_continuing_importer_uses_continuing_configuration(): void
    {
        $config = PhysicalExcelUploadController::sectorConfiguration('continuing');

        $this->assertSame('CONTINUING', $config['sheet_name']);
        $this->assertSame('continuing', $config['sector']);
        $this->assertSame('CONTINUING', $config['type_code']);
        $this->assertSame('CONTINUING', $config['label']);
    }

    public function test_continuing_header_detection_uses_the_continuing_title(): void
    {
        $method = new ReflectionMethod(PhysicalExcelUploadController::class, 'isStoSheetHeaderText');
        $controller = $this->controller('continuing');

        $this->assertTrue($method->invoke($controller, 'CONTINUING'));
        $this->assertFalse($method->invoke($controller, 'COBB'));
    }

    private function controller(string $sector): PhysicalExcelUploadController
    {
        return (new PhysicalExcelUploadController)->forSector($sector);
    }
}
