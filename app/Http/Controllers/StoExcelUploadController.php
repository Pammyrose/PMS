<?php
namespace App\Http\Controllers;
use App\Models\Sto_Indicator;
use App\Models\Sto_Target;
use App\Models\Office;
use App\Support\SimpleXlsxReader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
class StoExcelUploadController extends StoController
{
    private const EXCEL_SHEET_NAME = 'STO';
    private const DEFAULT_STO_TITLE = 'SUPPORT TO OPERATIONS';
    public function importExcel(Request $request)
    {
        $validated = $request->validate([
            'excel_file' => 'required|file|mimes:xlsx|max:51200',
            'year' => 'nullable|integer|min:2000|max:2099',
        ]);

        $year = (int) ($validated['year'] ?? $request->input('year', 2026));
        $filePath = $request->file('excel_file')->getRealPath();

        DB::beginTransaction();

        try {
            $result = $this->importStoPhysicalRowsFromExcel($filePath, $year);
            DB::commit();

            return redirect()
                ->back()
                ->with('success', 'STO Excel import complete.');
        } catch (\Throwable $exception) {
            DB::rollBack();

            return redirect()
                ->back()
                ->with('error', 'STO Excel import failed: ' . $exception->getMessage());
        }
    }

    public function previewExcelImport(Request $request)
    {
        $validated = $request->validate([
            'excel_file' => 'required|file|mimes:xlsx|max:51200',
            'year' => 'nullable|integer|min:2000|max:2099',
        ]);

        $year = (int) ($validated['year'] ?? $request->input('year', 2026));
        $filePath = $request->file('excel_file')->getRealPath();

        try {
            return response()->json([
                'success' => true,
                'preview' => $this->previewStoPhysicalRowsFromExcel($filePath, $year),
            ]);
        } catch (\Throwable $exception) {
            return response()->json([
                'success' => false,
                'message' => 'STO Excel preview failed: ' . $exception->getMessage(),
            ], 422);
        }
    }

    private function importStoPhysicalRowsFromExcel(string $filePath, int $year): array
    {
        $reader = new SimpleXlsxReader();
        $officeMap = $this->getOfficeImportMap();
        $currentProgram = null;
        $currentProgramMeta = [];
        $currentHeaders = [];
        $currentBlock = null;
        $standaloneHeaderContext = null;
        $currentParentOfficeId = null;
        $imported = 0;
        $skipped = 0;
        $placeholders = 0;
        $skipDuplicateSummaryProgram = false;

        $flushBlock = function () use (&$currentBlock, &$imported, &$skipped, &$placeholders, $year) {
            if ($currentBlock === null) {
                return;
            }

            if (empty($currentBlock['office_rows'])) {
                $skipped++;
                $currentBlock = null;
                return;
            }

            $indicatorName = $this->joinExcelFragments($currentBlock['indicator_parts']);
            if ($indicatorName === '') {
                $skipped++;
                $currentBlock = null;
                return;
            }

            if ($this->shouldSkipAllNaExcelPlaceholderBlock($currentBlock, $indicatorName)) {
                $skipped++;
                $currentBlock = null;
                return;
            }

            $pap = $this->storePapHierarchyInPpa($this->papDataFromExcelBlock($currentBlock, $year));
            $indicatorTypeId = $this->inferIndicatorTypeIdFromExcelBlock($currentBlock);
            $indicator = $this->firstOrCreateImportedIndicator($indicatorName, $indicatorTypeId);
            $officeIds = collect($currentBlock['office_rows'])
                ->pluck('office_id')
                ->filter(fn ($officeId) => $officeId !== null)
                ->unique()
                ->values()
                ->all();

            $this->syncProgramIndicatorInPpa((int) $pap->row_id, (int) $indicator->id, $officeIds);

            if (!empty($currentBlock['placeholder_indicator'])) {
                $placeholders++;
            }

            if (!$this->hasAnyTargetValue($currentBlock['car_totals'] ?? [])) {
                $currentBlock['car_totals'] = $this->sumImportedCarTotalsForBlock($currentBlock);
            }

            foreach ($currentBlock['office_rows'] as $officeRow) {
                $this->upsertImportedStoTarget(
                    (int) $pap->id,
                    (int) $pap->row_id,
                    (int) $indicator->id,
                    $officeRow['office_id'],
                    $year,
                    $officeRow['targets'],
                    $currentBlock['car_totals'] ?? [],
                    $currentBlock['group_totals'] ?? []
                );
                $imported++;
            }

            $currentBlock = null;
        };

        $startPlaceholderBlock = function (int $sourceRow, array $targetValues = []) use (&$currentBlock, &$currentHeaders, &$currentProgram, &$currentProgramMeta) {
            if (empty($currentHeaders)) {
                return;
            }

            $currentBlock = [
                'source_row' => $sourceRow,
                'title' => $currentProgram ?: self::DEFAULT_STO_TITLE,
                'program' => $currentProgramMeta[0] ?? 'N/A',
                'project' => $currentProgramMeta[1] ?? 'N/A',
                'headers' => $currentHeaders,
                'activity_parts' => [],
                'indicator_parts' => ['N/A'],
                'car_totals' => $targetValues,
                'group_totals' => [],
                'office_rows' => [],
                'placeholder_indicator' => true,
                'standalone_title' => $this->isStandaloneExcelTitleHeader((string) ($currentHeaders[0] ?? '')),
            ];
        };

        foreach ($reader->rowsWithStyles($filePath, self::EXCEL_SHEET_NAME, true) as $rowNumber => $styledRow) {
            $row = $styledRow['values'] ?? [];
            $rowStyles = $styledRow['styles'] ?? [];
            $papText = $this->cleanExcelText($row['A'] ?? '');

            if ($rowNumber < 10) {
                continue;
            }

            $indicatorText = $this->cleanExcelText($row['B'] ?? '');
            $locationName = $this->cleanExcelText($row['C'] ?? '');
            $targetValues = $this->physicalTargetValuesFromExcelRow($row);
            $hasTargets = $this->hasAnyTargetValue($targetValues);

            if ($papText === '' && $indicatorText === '' && $locationName === '') {
                if ($currentBlock !== null && empty($currentBlock['placeholder_indicator'])) {
                    $flushBlock();
                }
                $skipped++;
                continue;
            }

            $isStyledPapHeader = $this->isStyledStoPapHeaderRow($papText, $row, $rowStyles);
            if ($this->isStoProgramHeader($papText, $row) || $isStyledPapHeader) {
                if ($isStyledPapHeader && $currentProgram !== null && empty($currentHeaders) && $currentBlock === null) {
                    $currentProgram = $this->isStoSheetHeaderText($currentProgram)
                        ? $papText
                        : $this->joinExcelFragments([$currentProgram, $papText]);
                    continue;
                }

                $flushBlock();
                if ($this->isDuplicateStoSummaryProgramHeader($papText)) {
                    $skipDuplicateSummaryProgram = true;
                    $currentProgram = null;
                    $currentProgramMeta = [];
                    $currentHeaders = [];
                    $standaloneHeaderContext = null;
                    $skipped++;
                    continue;
                }

                $skipDuplicateSummaryProgram = false;
                $currentProgram = $papText;
                $currentProgramMeta = [];
                $currentHeaders = [];
                $standaloneHeaderContext = null;
                continue;
            }

            if ($skipDuplicateSummaryProgram) {
                $skipped++;
                continue;
            }

            if ($indicatorText !== '' && strcasecmp($indicatorText, 'PERFORMANCE INDICATOR') === 0) {
                $skipped++;
                continue;
            }

            if ($locationName === '' && !$hasTargets && $this->isExcelSignatureFooterRow($papText, $indicatorText, $locationName)) {
                $flushBlock();
                $skipped++;
                continue;
            }

            $officeId = $this->resolveImportedOfficeId($locationName, $officeMap, $currentParentOfficeId);
            $isCarRow = strcasecmp($locationName, 'CAR') === 0;
            $isRoRow = $this->isRoOfficeRow($locationName);
            $headingDepth = $this->excelHeadingDepth($papText);

            if (
                $papText !== ''
                && $indicatorText === ''
                && !$hasTargets
                && $this->isStyledStoSectionHeaderRow($papText, $row, $rowStyles)
            ) {
                $flushBlock();
                $this->pushExcelHierarchyHeader($currentHeaders, $papText);
                continue;
            }

            if ($isCarRow || $isRoRow) {
                $currentParentOfficeId = null;
            } elseif ($this->isParentOfficeRow($locationName) && $officeId !== null) {
                $currentParentOfficeId = $officeId;
            }

            if (!$hasTargets && !$this->isOfficeBackedExcelLabel($locationName, $officeId) && $this->isExcelSignatureFooterRow($papText, $indicatorText, $locationName)) {
                $flushBlock();
                $skipped++;
                continue;
            }

            if ($papText !== '' && $indicatorText === '' && !$hasTargets && $isCarRow && $this->isStandaloneExcelTitleHeader($papText)) {
                $flushBlock();
                $currentHeaders = [$papText];
                $standaloneHeaderContext = $papText;
                continue;
            }

            if (
                $papText !== ''
                && $indicatorText === ''
                && !$hasTargets
                && $isCarRow
                && $standaloneHeaderContext !== null
                && ($this->isPsRequirementsChild($papText) || $this->isStandaloneExcelSectionHeader($papText))
            ) {
                $flushBlock();
                $currentHeaders = [$standaloneHeaderContext, $papText];
                $startPlaceholderBlock($rowNumber, $targetValues);
                continue;
            }

            if (
                $currentBlock !== null
                && !$isCarRow
                && $headingDepth === null
                && ($this->isExcelContinuationText($papText) || $this->isExcelContinuationText($indicatorText))
            ) {
                if ($this->isExcelContinuationText($papText)) {
                    if (!empty($currentBlock['merge_text_only_rows'])) {
                        $currentBlock['activity_parts'][] = $papText;
                    } else {
                        $this->appendExcelHierarchyLeafFragment($currentBlock['headers'], $papText);
                        $this->appendExcelHierarchyLeafFragment($currentHeaders, $papText);
                    }
                }

                if ($this->isExcelContinuationText($indicatorText)) {
                    $currentBlock['indicator_parts'][] = $indicatorText;
                }

                if ($officeId !== null && ($hasTargets || $this->isParentOfficeRow($locationName) || $isRoRow || !empty($currentBlock['placeholder_indicator']))) {
                    $this->applyRoAsCarTotal($currentBlock, $locationName, $targetValues);

                    $currentBlock['office_rows'][] = [
                        'office_id' => $officeId,
                        'targets' => $targetValues,
                    ];

                    if ($this->isParentOfficeRow($locationName)) {
                        $currentBlock['group_totals']['group-' . (count($currentBlock['group_totals']) + 1)] = $targetValues;
                    }
                }

                continue;
            }

            if ($papText !== '' && $indicatorText !== '' && $headingDepth !== null && !$isCarRow) {
                $flushBlock();
                $this->pushExcelHierarchyHeader($currentHeaders, $papText);
                $currentBlock = [
                    'title' => $currentProgram ?: self::DEFAULT_STO_TITLE,
                    'program' => $currentProgramMeta[0] ?? 'N/A',
                    'project' => $currentProgramMeta[1] ?? 'N/A',
                    'headers' => $currentHeaders,
                    'activity_parts' => [],
                    'duplicate_leaf' => true,
                    'indicator_parts' => [$indicatorText],
                    'car_totals' => [],
                    'group_totals' => [],
                    'office_rows' => [],
                ];

                $this->applyRoAsCarTotal($currentBlock, $locationName, $targetValues);

                if ($officeId !== null && ($hasTargets || $this->isParentOfficeRow($locationName) || $isRoRow)) {
                    $currentBlock['office_rows'][] = [
                        'office_id' => $officeId,
                        'targets' => $targetValues,
                    ];

                    if ($this->isParentOfficeRow($locationName)) {
                        $currentBlock['group_totals']['group-' . (count($currentBlock['group_totals']) + 1)] = $targetValues;
                    }
                }

                continue;
            }

            if ($papText !== '' && $indicatorText !== '' && $isCarRow && $headingDepth !== null) {
                $flushBlock();
                if ($headingDepth === 1 && $this->shouldMergeConstructionLeafFragment($currentProgram, $papText)) {
                    $currentBlock = [
                        'title' => $currentProgram ?: self::DEFAULT_STO_TITLE,
                        'program' => $currentProgramMeta[0] ?? 'N/A',
                        'project' => $currentProgramMeta[1] ?? 'N/A',
                        'headers' => $currentHeaders,
                        'activity_parts' => [$papText],
                        'merge_text_only_rows' => true,
                        'indicator_parts' => [$indicatorText],
                        'car_totals' => $targetValues,
                        'group_totals' => [],
                        'office_rows' => [],
                    ];
                    continue;
                }

                $this->pushExcelHierarchyHeader($currentHeaders, $papText);
                $currentBlock = [
                    'title' => $currentProgram ?: self::DEFAULT_STO_TITLE,
                    'program' => $currentProgramMeta[0] ?? 'N/A',
                    'project' => $currentProgramMeta[1] ?? 'N/A',
                    'headers' => $currentHeaders,
                    'activity_parts' => [],
                    'duplicate_leaf' => true,
                    'indicator_parts' => [$indicatorText],
                    'car_totals' => $targetValues,
                    'group_totals' => [],
                    'office_rows' => [],
                ];
                continue;
            }

            if (
                $currentBlock !== null
                && $headingDepth === null
                && ($this->isExcelContinuationText($papText) || $this->isExcelContinuationText($indicatorText))
            ) {
                if ($this->isExcelContinuationText($indicatorText)) {
                    $currentBlock['indicator_parts'][] = $indicatorText;
                }

                if ($this->isExcelContinuationText($papText)) {
                    if (!empty($currentBlock['merge_text_only_rows'])) {
                        $currentBlock['activity_parts'][] = $papText;
                    } else {
                        $this->appendExcelHierarchyLeafFragment($currentBlock['headers'], $papText);
                        $this->appendExcelHierarchyLeafFragment($currentHeaders, $papText);
                    }
                }

                if ($officeId !== null && ($hasTargets || $this->isParentOfficeRow($locationName) || $isRoRow || !empty($currentBlock['placeholder_indicator']))) {
                    $this->applyRoAsCarTotal($currentBlock, $locationName, $targetValues);

                    $currentBlock['office_rows'][] = [
                        'office_id' => $officeId,
                        'targets' => $targetValues,
                    ];

                    if ($this->isParentOfficeRow($locationName)) {
                        $currentBlock['group_totals']['group-' . (count($currentBlock['group_totals']) + 1)] = $targetValues;
                    }
                }

                continue;
            }

            if ($currentBlock === null && $indicatorText !== '' && $officeId !== null && $papText === '') {
                $currentBlock = [
                    'title' => $currentProgram ?: self::DEFAULT_STO_TITLE,
                    'program' => $currentProgramMeta[0] ?? 'N/A',
                    'project' => $currentProgramMeta[1] ?? 'N/A',
                    'headers' => $currentHeaders,
                    'activity_parts' => [],
                    'indicator_parts' => [$indicatorText],
                    'car_totals' => [],
                    'group_totals' => [],
                    'office_rows' => [],
                ];

                $this->applyRoAsCarTotal($currentBlock, $locationName, $targetValues);

                if ($hasTargets || $this->isParentOfficeRow($locationName) || $isRoRow) {
                    $currentBlock['office_rows'][] = [
                        'office_id' => $officeId,
                        'targets' => $targetValues,
                    ];

                    if ($this->isParentOfficeRow($locationName)) {
                        $currentBlock['group_totals']['group-' . (count($currentBlock['group_totals']) + 1)] = $targetValues;
                    }
                }

                continue;
            }

            if ($currentBlock === null && $indicatorText !== '' && $officeId !== null && $papText !== '' && !empty($currentHeaders) && $headingDepth === null) {
                $currentBlock = [
                    'title' => $currentProgram ?: self::DEFAULT_STO_TITLE,
                    'program' => $currentProgramMeta[0] ?? 'N/A',
                    'project' => $currentProgramMeta[1] ?? 'N/A',
                    'headers' => $currentHeaders,
                    'activity_parts' => [$papText],
                    'merge_text_only_rows' => true,
                    'indicator_parts' => [$indicatorText],
                    'car_totals' => [],
                    'group_totals' => [],
                    'office_rows' => [],
                ];

                $this->applyRoAsCarTotal($currentBlock, $locationName, $targetValues);

                if ($hasTargets || $this->isParentOfficeRow($locationName) || $isRoRow) {
                    $currentBlock['office_rows'][] = [
                        'office_id' => $officeId,
                        'targets' => $targetValues,
                    ];

                    if ($this->isParentOfficeRow($locationName)) {
                        $currentBlock['group_totals']['group-' . (count($currentBlock['group_totals']) + 1)] = $targetValues;
                    }
                }

                continue;
            }

            if ($currentBlock !== null && $isCarRow && empty($currentBlock['car_totals']) && $hasTargets) {
                if ($indicatorText !== '') {
                    $currentBlock['indicator_parts'][] = $indicatorText;
                }
                $currentBlock['car_totals'] = $targetValues;
                continue;
            }

            if ($indicatorText !== '' && $isCarRow) {
                $flushBlock();
                $currentBlock = [
                    'title' => $currentProgram ?: self::DEFAULT_STO_TITLE,
                    'program' => $currentProgramMeta[0] ?? 'N/A',
                    'project' => $currentProgramMeta[1] ?? 'N/A',
                    'headers' => $currentHeaders,
                    'activity_parts' => $papText !== '' ? [$papText] : [],
                    'duplicate_leaf' => $papText === '' && !empty($currentHeaders),
                    'indicator_parts' => [$indicatorText],
                    'car_totals' => $targetValues,
                    'group_totals' => [],
                    'office_rows' => [],
                ];
                continue;
            }

            if (
                $currentBlock !== null
                && !empty($currentBlock['merge_text_only_rows'])
                && $papText !== ''
                && $indicatorText === ''
                && !$hasTargets
                && $this->isExcelContinuationText($papText)
            ) {
                $currentBlock['activity_parts'][] = $papText;
                continue;
            }

            if (
                $currentBlock !== null
                && $papText !== ''
                && $indicatorText === ''
                && !$hasTargets
                && $this->isExcelContinuationText($papText)
                && !$this->isOfficeBackedExcelLabel($locationName, $officeId)
                && preg_match('/^(?!\d+\.\s|[A-Z]\.\s).+/u', $papText) === 1
            ) {
                $this->appendExcelHierarchyLeafFragment($currentBlock['headers'], $papText);
                $this->appendExcelHierarchyLeafFragment($currentHeaders, $papText);
                continue;
            }

            if ($papText !== '' && $indicatorText === '' && !$hasTargets) {
                if (
                    empty($currentHeaders)
                    && $headingDepth === null
                    && !$this->isOfficeBackedExcelLabel($locationName, $officeId)
                    && !$this->isStandaloneExcelTitleHeader($papText)
                ) {
                    $flushBlock();
                    $currentProgram = $currentProgram === null
                        ? $papText
                        : $this->joinExcelFragments([$currentProgram, $papText]);
                    $currentProgramMeta = [];
                    $standaloneHeaderContext = null;
                    continue;
                }

                if (
                    $this->isStandaloneExcelTitleHeader($papText)
                    && $this->isOfficeBackedExcelLabel($locationName, $officeId)
                ) {
                    $flushBlock();
                    $currentHeaders = [$papText];
                    $standaloneHeaderContext = $papText;
                    continue;
                }

                if (
                    $currentBlock !== null
                    && !empty($currentBlock['placeholder_indicator'])
                    && $this->isOfficeBackedExcelLabel($locationName, $officeId)
                    && $headingDepth === null
                    && !$this->isStandaloneExcelTitleHeader($papText)
                ) {
                    $this->appendExcelHierarchyLeafFragment($currentBlock['headers'], $papText);
                    $this->appendExcelHierarchyLeafFragment($currentHeaders, $papText);

                    if ($officeId !== null) {
                        $this->applyRoAsCarTotal($currentBlock, $locationName, $targetValues);

                        $currentBlock['office_rows'][] = [
                            'office_id' => $officeId,
                            'targets' => $targetValues,
                        ];

                        if ($this->isParentOfficeRow($locationName)) {
                            $currentBlock['group_totals']['group-' . (count($currentBlock['group_totals']) + 1)] = $targetValues;
                        }
                    }

                    continue;
                }

                $flushBlock();
                if ($currentProgram !== null && $this->shouldAppendToProgramTitle($currentProgram, $papText)) {
                    $currentProgram = $this->joinExcelFragments([$currentProgram, $papText]);
                    continue;
                }
                if (
                    $currentProgram !== null
                    && empty($currentHeaders)
                    && $headingDepth === null
                    && !$this->isStandaloneExcelTitleHeader($papText)
                    && $this->shouldStoreProgramSupplement($papText, true)
                ) {
                    if (count($currentProgramMeta) < 2) {
                        $currentProgramMeta[] = $papText;
                    } else {
                        $lastIndex = count($currentProgramMeta) - 1;
                        $currentProgramMeta[$lastIndex] = $this->joinExcelFragments([$currentProgramMeta[$lastIndex], $papText]);
                    }
                    continue;
                }

                if ($this->isOfficeBackedExcelLabel($locationName, $officeId) && ($headingDepth !== null || strcasecmp($locationName, 'CAR') === 0)) {
                    if ($headingDepth !== null) {
                        if ($standaloneHeaderContext !== null && !$this->isStandaloneExcelTitleHeader((string) ($currentHeaders[0] ?? ''))) {
                            $currentHeaders = [$standaloneHeaderContext];
                        }
                        $this->pushExcelHierarchyHeader($currentHeaders, $papText);
                    } elseif ($this->isStandaloneExcelTitleHeader($papText)) {
                        $currentHeaders = [$papText];
                        $standaloneHeaderContext = $papText;
                        continue;
                    } else {
                        $currentHeaders = !empty($currentHeaders)
                            ? array_merge(array_slice($currentHeaders, 0, 1), [$papText])
                            : [$papText];
                    }
                    $startPlaceholderBlock($rowNumber, $targetValues);

                    if ($currentBlock !== null && $officeId !== null) {
                        $this->applyRoAsCarTotal($currentBlock, $locationName, $targetValues);

                        $currentBlock['office_rows'][] = [
                            'office_id' => $officeId,
                            'targets' => $targetValues,
                        ];

                        if ($this->isParentOfficeRow($locationName)) {
                            $currentBlock['group_totals']['group-' . (count($currentBlock['group_totals']) + 1)] = $targetValues;
                        }
                    }

                    continue;
                }

                if ($this->isOfficeBackedExcelLabel($locationName, $officeId) && !empty($currentHeaders)) {
                    $this->appendExcelHierarchyLeafFragment($currentHeaders, $papText);
                    continue;
                }

                $this->pushExcelHierarchyHeader($currentHeaders, $papText);
                continue;
            }

            if ($currentBlock !== null && $this->shouldAppendPerformanceIndicatorFragment($currentBlock, $indicatorText, $officeId)) {
                $currentBlock['indicator_parts'][] = $indicatorText;
            }

            if ($currentBlock !== null && $papText !== '') {
                if (!empty($currentBlock['merge_text_only_rows']) && $this->isExcelContinuationText($papText)) {
                    $currentBlock['activity_parts'][] = $papText;
                } elseif ($this->isExcelContinuationText($papText)) {
                    $this->appendExcelHierarchyLeafFragment($currentBlock['headers'], $papText);
                    $this->appendExcelHierarchyLeafFragment($currentHeaders, $papText);
                } else {
                    $currentBlock['activity_parts'][] = $papText;
                }
            }

            if ($currentBlock !== null && $officeId !== null && ($hasTargets || $this->isParentOfficeRow($locationName) || $isRoRow || !empty($currentBlock['placeholder_indicator']))) {
                $this->applyRoAsCarTotal($currentBlock, $locationName, $targetValues);

                $currentBlock['office_rows'][] = [
                    'office_id' => $officeId,
                    'targets' => $targetValues,
                ];

                if ($this->isParentOfficeRow($locationName)) {
                    $currentBlock['group_totals']['group-' . (count($currentBlock['group_totals']) + 1)] = $targetValues;
                }

                continue;
            }

            $skipped++;
        }

        $flushBlock();

        return [
            'imported' => $imported,
            'skipped' => $skipped,
            'placeholders' => $placeholders,
        ];
    }

    private function previewStoPhysicalRowsFromExcel(string $filePath, int $year): array
    {
        $reader = new SimpleXlsxReader();
        $officeMap = $this->getOfficeImportMap();
        $currentProgram = null;
        $currentProgramMeta = [];
        $currentHeaders = [];
        $currentBlock = null;
        $standaloneHeaderContext = null;
        $currentParentOfficeId = null;
        $previewRows = [];
        $warnings = $this->previewExcelSortingWarnings($filePath);
        $imported = 0;
        $skipped = 0;
        $totalParsedRows = 0;
        $placeholders = 0;
        $skipDuplicateSummaryProgram = false;

        $flushBlock = function () use (&$currentBlock, &$previewRows, &$warnings, &$imported, &$skipped, &$totalParsedRows, &$placeholders, $year) {
            if ($currentBlock === null) {
                return;
            }

            $sourceRow = (int) ($currentBlock['source_row'] ?? 0);

            if (empty($currentBlock['office_rows'])) {
                $skipped++;
                if ($sourceRow > 0) {
                    $warnings[] = [
                        'row' => $sourceRow,
                        'level' => 'warning',
                        'message' => 'This parsed item has no matched office rows. Please check the office/location column.',
                    ];
                }
                $currentBlock = null;
                return;
            }

            $indicatorName = $this->joinExcelFragments($currentBlock['indicator_parts']);
            if ($indicatorName === '') {
                $skipped++;
                if ($sourceRow > 0) {
                    $warnings[] = [
                        'row' => $sourceRow,
                        'level' => 'warning',
                        'message' => 'This parsed item has no performance indicator.',
                    ];
                }
                $currentBlock = null;
                return;
            }

            if ($this->shouldSkipAllNaExcelPlaceholderBlock($currentBlock, $indicatorName)) {
                $skipped++;
                $currentBlock = null;
                return;
            }

            $papData = $this->papDataFromExcelBlock($currentBlock, $year);
            $hierarchy = collect([
                $papData['activities'] ?? null,
                $papData['subactivities'] ?? null,
                $papData['subsubactivities'] ?? null,
                $papData['level_6'] ?? null,
                $papData['level_7'] ?? null,
                $papData['level_8'] ?? null,
            ])
                ->filter(fn ($value) => $value !== null && trim((string) $value) !== '' && strtoupper(trim((string) $value)) !== 'N/A')
                ->values()
                ->all();

            $officeNames = collect($currentBlock['office_rows'])
                ->pluck('office_name')
                ->filter()
                ->unique()
                ->values()
                ->all();

            $totalParsedRows++;
            if (count($previewRows) < 120) {
                $previewRows[] = [
                    'row' => $sourceRow,
                    'title' => $papData['title'] ?? '',
                    'program' => $papData['program'] ?? '',
                    'project' => $papData['project'] ?? '',
                    'hierarchy' => $hierarchy,
                    'indicator' => $indicatorName,
                    'offices' => $officeNames,
                    'office_count' => count($currentBlock['office_rows']),
                    'has_car_total' => !empty($currentBlock['car_totals']),
                ];
            }

            $imported += count($currentBlock['office_rows']);
            if (!empty($currentBlock['placeholder_indicator'])) {
                $placeholders++;
            }
            $currentBlock = null;
        };

        $startPlaceholderPreviewBlock = function (int $sourceRow, array $targetValues = []) use (&$currentBlock, &$currentHeaders, &$currentProgram, &$currentProgramMeta) {
            if (empty($currentHeaders)) {
                return;
            }

            $currentBlock = [
                'source_row' => $sourceRow,
                'title' => $currentProgram ?: self::DEFAULT_STO_TITLE,
                'program' => $currentProgramMeta[0] ?? 'N/A',
                'project' => $currentProgramMeta[1] ?? 'N/A',
                'headers' => $currentHeaders,
                'activity_parts' => [],
                'indicator_parts' => ['N/A'],
                'car_totals' => $targetValues,
                'group_totals' => [],
                'office_rows' => [],
                'placeholder_indicator' => true,
                'standalone_title' => $this->isStandaloneExcelTitleHeader((string) ($currentHeaders[0] ?? '')),
            ];
        };

        foreach ($reader->rowsWithStyles($filePath, self::EXCEL_SHEET_NAME, true) as $rowNumber => $styledRow) {
            $row = $styledRow['values'] ?? [];
            $rowStyles = $styledRow['styles'] ?? [];
            $papText = $this->cleanExcelText($row['A'] ?? '');

            if ($rowNumber < 10) {
                continue;
            }

            $indicatorText = $this->cleanExcelText($row['B'] ?? '');
            $locationName = $this->cleanExcelText($row['C'] ?? '');
            $targetValues = $this->physicalTargetValuesFromExcelRow($row);
            $hasTargets = $this->hasAnyTargetValue($targetValues);

            if ($papText === '' && $indicatorText === '' && $locationName === '') {
                if ($currentBlock !== null && empty($currentBlock['placeholder_indicator'])) {
                    $flushBlock();
                }
                $skipped++;
                continue;
            }

            $isStyledPapHeader = $this->isStyledStoPapHeaderRow($papText, $row, $rowStyles);
            if ($this->isStoProgramHeader($papText, $row) || $isStyledPapHeader) {
                if ($isStyledPapHeader && $currentProgram !== null && empty($currentHeaders) && $currentBlock === null) {
                    $currentProgram = $this->isStoSheetHeaderText($currentProgram)
                        ? $papText
                        : $this->joinExcelFragments([$currentProgram, $papText]);
                    continue;
                }

                $flushBlock();
                if ($this->isDuplicateStoSummaryProgramHeader($papText)) {
                    $skipDuplicateSummaryProgram = true;
                    $currentProgram = null;
                    $currentProgramMeta = [];
                    $currentHeaders = [];
                    $standaloneHeaderContext = null;
                    $skipped++;
                    continue;
                }

                $skipDuplicateSummaryProgram = false;
                $currentProgram = $papText;
                $currentProgramMeta = [];
                $currentHeaders = [];
                $standaloneHeaderContext = null;
                continue;
            }

            if ($skipDuplicateSummaryProgram) {
                $skipped++;
                continue;
            }

            if ($indicatorText !== '' && strcasecmp($indicatorText, 'PERFORMANCE INDICATOR') === 0) {
                $skipped++;
                continue;
            }

            if ($locationName === '' && !$hasTargets && $this->isExcelSignatureFooterRow($papText, $indicatorText, $locationName)) {
                $flushBlock();
                $skipped++;
                continue;
            }

            $officeId = $this->resolveImportedOfficeId($locationName, $officeMap, $currentParentOfficeId);
            $isCarRow = strcasecmp($locationName, 'CAR') === 0;
            $isRoRow = $this->isRoOfficeRow($locationName);
            $headingDepth = $this->excelHeadingDepth($papText);

            if (
                $papText !== ''
                && $indicatorText === ''
                && !$hasTargets
                && $this->isStyledStoSectionHeaderRow($papText, $row, $rowStyles)
            ) {
                $flushBlock();
                $this->pushExcelHierarchyHeader($currentHeaders, $papText);
                continue;
            }

            if ($isCarRow || $isRoRow) {
                $currentParentOfficeId = null;
            } elseif ($this->isParentOfficeRow($locationName) && $officeId !== null) {
                $currentParentOfficeId = $officeId;
            }

            if (!$hasTargets && !$this->isOfficeBackedExcelLabel($locationName, $officeId) && $this->isExcelSignatureFooterRow($papText, $indicatorText, $locationName)) {
                $flushBlock();
                $skipped++;
                continue;
            }

            if ($papText !== '' && $indicatorText === '' && !$hasTargets && $isCarRow && $this->isStandaloneExcelTitleHeader($papText)) {
                $flushBlock();
                $currentHeaders = [$papText];
                $standaloneHeaderContext = $papText;
                continue;
            }

            if (
                $papText !== ''
                && $indicatorText === ''
                && !$hasTargets
                && $isCarRow
                && $standaloneHeaderContext !== null
                && ($this->isPsRequirementsChild($papText) || $this->isStandaloneExcelSectionHeader($papText))
            ) {
                $flushBlock();
                $currentHeaders = [$standaloneHeaderContext, $papText];
                $startPlaceholderPreviewBlock($rowNumber, $targetValues);
                continue;
            }

            if (
                $currentBlock !== null
                && !$isCarRow
                && $headingDepth === null
                && ($this->isExcelContinuationText($papText) || $this->isExcelContinuationText($indicatorText))
            ) {
                if ($this->isExcelContinuationText($papText)) {
                    if (!empty($currentBlock['merge_text_only_rows'])) {
                        $currentBlock['activity_parts'][] = $papText;
                    } else {
                        $this->appendExcelHierarchyLeafFragment($currentBlock['headers'], $papText);
                        $this->appendExcelHierarchyLeafFragment($currentHeaders, $papText);
                    }
                }

                if ($this->isExcelContinuationText($indicatorText)) {
                    $currentBlock['indicator_parts'][] = $indicatorText;
                }

                if ($officeId !== null && ($hasTargets || $this->isParentOfficeRow($locationName) || $isRoRow || !empty($currentBlock['placeholder_indicator']))) {
                    $this->applyRoAsCarTotal($currentBlock, $locationName, $targetValues);

                    $currentBlock['office_rows'][] = [
                        'office_id' => $officeId,
                        'office_name' => $locationName,
                        'targets' => $targetValues,
                    ];

                    if ($this->isParentOfficeRow($locationName)) {
                        $currentBlock['group_totals']['group-' . (count($currentBlock['group_totals']) + 1)] = $targetValues;
                    }
                }

                continue;
            }

            if ($papText !== '' && $indicatorText !== '' && $headingDepth !== null && !$isCarRow) {
                $flushBlock();
                $this->pushExcelHierarchyHeader($currentHeaders, $papText);
                $currentBlock = [
                    'source_row' => $rowNumber,
                    'title' => $currentProgram ?: self::DEFAULT_STO_TITLE,
                    'program' => $currentProgramMeta[0] ?? 'N/A',
                    'project' => $currentProgramMeta[1] ?? 'N/A',
                    'headers' => $currentHeaders,
                    'activity_parts' => [],
                    'duplicate_leaf' => true,
                    'indicator_parts' => [$indicatorText],
                    'car_totals' => [],
                    'group_totals' => [],
                    'office_rows' => [],
                ];

                $this->applyRoAsCarTotal($currentBlock, $locationName, $targetValues);

                if ($officeId !== null && ($hasTargets || $this->isParentOfficeRow($locationName) || $isRoRow)) {
                    $currentBlock['office_rows'][] = [
                        'office_id' => $officeId,
                        'office_name' => $locationName,
                        'targets' => $targetValues,
                    ];

                    if ($this->isParentOfficeRow($locationName)) {
                        $currentBlock['group_totals']['group-' . (count($currentBlock['group_totals']) + 1)] = $targetValues;
                    }
                }

                continue;
            }

            if ($papText !== '' && $indicatorText !== '' && $isCarRow && $headingDepth !== null) {
                $flushBlock();
                if ($headingDepth === 1 && $this->shouldMergeConstructionLeafFragment($currentProgram, $papText)) {
                    $currentBlock = [
                        'source_row' => $rowNumber,
                        'title' => $currentProgram ?: self::DEFAULT_STO_TITLE,
                        'program' => $currentProgramMeta[0] ?? 'N/A',
                        'project' => $currentProgramMeta[1] ?? 'N/A',
                        'headers' => $currentHeaders,
                        'activity_parts' => [$papText],
                        'merge_text_only_rows' => true,
                        'indicator_parts' => [$indicatorText],
                        'car_totals' => $targetValues,
                        'group_totals' => [],
                        'office_rows' => [],
                    ];
                    continue;
                }

                $this->pushExcelHierarchyHeader($currentHeaders, $papText);
                $currentBlock = [
                    'source_row' => $rowNumber,
                    'title' => $currentProgram ?: self::DEFAULT_STO_TITLE,
                    'program' => $currentProgramMeta[0] ?? 'N/A',
                    'project' => $currentProgramMeta[1] ?? 'N/A',
                    'headers' => $currentHeaders,
                    'activity_parts' => [],
                    'duplicate_leaf' => true,
                    'indicator_parts' => [$indicatorText],
                    'car_totals' => $targetValues,
                    'group_totals' => [],
                    'office_rows' => [],
                ];
                continue;
            }

            if (
                $currentBlock !== null
                && $headingDepth === null
                && ($this->isExcelContinuationText($papText) || $this->isExcelContinuationText($indicatorText))
            ) {
                if ($this->isExcelContinuationText($indicatorText)) {
                    $currentBlock['indicator_parts'][] = $indicatorText;
                }

                if ($this->isExcelContinuationText($papText)) {
                    if (!empty($currentBlock['merge_text_only_rows'])) {
                        $currentBlock['activity_parts'][] = $papText;
                    } else {
                        $this->appendExcelHierarchyLeafFragment($currentBlock['headers'], $papText);
                        $this->appendExcelHierarchyLeafFragment($currentHeaders, $papText);
                    }
                }

                if ($officeId !== null && ($hasTargets || $this->isParentOfficeRow($locationName) || $isRoRow || !empty($currentBlock['placeholder_indicator']))) {
                    $this->applyRoAsCarTotal($currentBlock, $locationName, $targetValues);

                    $currentBlock['office_rows'][] = [
                        'office_id' => $officeId,
                        'office_name' => $locationName,
                        'targets' => $targetValues,
                    ];

                    if ($this->isParentOfficeRow($locationName)) {
                        $currentBlock['group_totals']['group-' . (count($currentBlock['group_totals']) + 1)] = $targetValues;
                    }
                }

                continue;
            }

            if ($currentBlock === null && $indicatorText !== '' && $officeId !== null && $papText === '') {
                $currentBlock = [
                    'source_row' => $rowNumber,
                    'title' => $currentProgram ?: self::DEFAULT_STO_TITLE,
                    'program' => $currentProgramMeta[0] ?? 'N/A',
                    'project' => $currentProgramMeta[1] ?? 'N/A',
                    'headers' => $currentHeaders,
                    'activity_parts' => [],
                    'indicator_parts' => [$indicatorText],
                    'car_totals' => [],
                    'group_totals' => [],
                    'office_rows' => [],
                ];

                $this->applyRoAsCarTotal($currentBlock, $locationName, $targetValues);

                if ($hasTargets || $this->isParentOfficeRow($locationName) || $isRoRow) {
                    $currentBlock['office_rows'][] = [
                        'office_id' => $officeId,
                        'office_name' => $locationName,
                        'targets' => $targetValues,
                    ];

                    if ($this->isParentOfficeRow($locationName)) {
                        $currentBlock['group_totals']['group-' . (count($currentBlock['group_totals']) + 1)] = $targetValues;
                    }
                }

                continue;
            }

            if ($currentBlock === null && $indicatorText !== '' && $officeId !== null && $papText !== '' && !empty($currentHeaders) && $headingDepth === null) {
                $currentBlock = [
                    'source_row' => $rowNumber,
                    'title' => $currentProgram ?: self::DEFAULT_STO_TITLE,
                    'program' => $currentProgramMeta[0] ?? 'N/A',
                    'project' => $currentProgramMeta[1] ?? 'N/A',
                    'headers' => $currentHeaders,
                    'activity_parts' => [$papText],
                    'merge_text_only_rows' => true,
                    'indicator_parts' => [$indicatorText],
                    'car_totals' => [],
                    'group_totals' => [],
                    'office_rows' => [],
                ];

                $this->applyRoAsCarTotal($currentBlock, $locationName, $targetValues);

                if ($hasTargets || $this->isParentOfficeRow($locationName) || $isRoRow) {
                    $currentBlock['office_rows'][] = [
                        'office_id' => $officeId,
                        'office_name' => $locationName,
                        'targets' => $targetValues,
                    ];

                    if ($this->isParentOfficeRow($locationName)) {
                        $currentBlock['group_totals']['group-' . (count($currentBlock['group_totals']) + 1)] = $targetValues;
                    }
                }

                continue;
            }

            if ($currentBlock !== null && $isCarRow && empty($currentBlock['car_totals']) && $hasTargets) {
                if ($indicatorText !== '') {
                    $currentBlock['indicator_parts'][] = $indicatorText;
                }
                $currentBlock['car_totals'] = $targetValues;
                continue;
            }

            if ($indicatorText !== '' && $isCarRow) {
                $flushBlock();
                $currentBlock = [
                    'source_row' => $rowNumber,
                    'title' => $currentProgram ?: self::DEFAULT_STO_TITLE,
                    'program' => $currentProgramMeta[0] ?? 'N/A',
                    'project' => $currentProgramMeta[1] ?? 'N/A',
                    'headers' => $currentHeaders,
                    'activity_parts' => $papText !== '' ? [$papText] : [],
                    'duplicate_leaf' => $papText === '' && !empty($currentHeaders),
                    'indicator_parts' => [$indicatorText],
                    'car_totals' => $targetValues,
                    'group_totals' => [],
                    'office_rows' => [],
                ];
                continue;
            }

            if (
                $currentBlock !== null
                && !empty($currentBlock['merge_text_only_rows'])
                && $papText !== ''
                && $indicatorText === ''
                && !$hasTargets
                && $this->isExcelContinuationText($papText)
            ) {
                $currentBlock['activity_parts'][] = $papText;
                continue;
            }

            if (
                $currentBlock !== null
                && $papText !== ''
                && $indicatorText === ''
                && !$hasTargets
                && $this->isExcelContinuationText($papText)
                && !$this->isOfficeBackedExcelLabel($locationName, $officeId)
                && preg_match('/^(?!\d+\.\s|[A-Z]\.\s).+/u', $papText) === 1
            ) {
                $this->appendExcelHierarchyLeafFragment($currentBlock['headers'], $papText);
                $this->appendExcelHierarchyLeafFragment($currentHeaders, $papText);
                continue;
            }

            if ($papText !== '' && $indicatorText === '' && !$hasTargets) {
                if (
                    empty($currentHeaders)
                    && $headingDepth === null
                    && !$this->isOfficeBackedExcelLabel($locationName, $officeId)
                    && !$this->isStandaloneExcelTitleHeader($papText)
                ) {
                    $flushBlock();
                    $currentProgram = $currentProgram === null
                        ? $papText
                        : $this->joinExcelFragments([$currentProgram, $papText]);
                    $currentProgramMeta = [];
                    $standaloneHeaderContext = null;
                    continue;
                }

                if (
                    $this->isStandaloneExcelTitleHeader($papText)
                    && $this->isOfficeBackedExcelLabel($locationName, $officeId)
                ) {
                    $flushBlock();
                    $currentHeaders = [$papText];
                    $standaloneHeaderContext = $papText;
                    continue;
                }

                if (
                    $currentBlock !== null
                    && !empty($currentBlock['placeholder_indicator'])
                    && $this->isOfficeBackedExcelLabel($locationName, $officeId)
                    && $headingDepth === null
                    && !$this->isStandaloneExcelTitleHeader($papText)
                ) {
                    $this->appendExcelHierarchyLeafFragment($currentBlock['headers'], $papText);
                    $this->appendExcelHierarchyLeafFragment($currentHeaders, $papText);

                    if ($officeId !== null) {
                        $this->applyRoAsCarTotal($currentBlock, $locationName, $targetValues);

                        $currentBlock['office_rows'][] = [
                            'office_id' => $officeId,
                            'office_name' => $locationName,
                            'targets' => $targetValues,
                        ];

                        if ($this->isParentOfficeRow($locationName)) {
                            $currentBlock['group_totals']['group-' . (count($currentBlock['group_totals']) + 1)] = $targetValues;
                        }
                    }

                    continue;
                }

                $flushBlock();
                if ($currentProgram !== null && $this->shouldAppendToProgramTitle($currentProgram, $papText)) {
                    $currentProgram = $this->joinExcelFragments([$currentProgram, $papText]);
                    continue;
                }
                if (
                    $currentProgram !== null
                    && empty($currentHeaders)
                    && $headingDepth === null
                    && !$this->isStandaloneExcelTitleHeader($papText)
                    && $this->shouldStoreProgramSupplement($papText, true)
                ) {
                    if (count($currentProgramMeta) < 2) {
                        $currentProgramMeta[] = $papText;
                    } else {
                        $lastIndex = count($currentProgramMeta) - 1;
                        $currentProgramMeta[$lastIndex] = $this->joinExcelFragments([$currentProgramMeta[$lastIndex], $papText]);
                    }
                    continue;
                }

                if ($this->isOfficeBackedExcelLabel($locationName, $officeId) && ($headingDepth !== null || strcasecmp($locationName, 'CAR') === 0)) {
                    if ($headingDepth !== null) {
                        if ($standaloneHeaderContext !== null && !$this->isStandaloneExcelTitleHeader((string) ($currentHeaders[0] ?? ''))) {
                            $currentHeaders = [$standaloneHeaderContext];
                        }
                        $this->pushExcelHierarchyHeader($currentHeaders, $papText);
                    } elseif ($this->isStandaloneExcelTitleHeader($papText)) {
                        $currentHeaders = [$papText];
                        $standaloneHeaderContext = $papText;
                        continue;
                    } else {
                        $currentHeaders = !empty($currentHeaders)
                            ? array_merge(array_slice($currentHeaders, 0, 1), [$papText])
                            : [$papText];
                    }
                    $startPlaceholderPreviewBlock($rowNumber, $targetValues);

                    if ($currentBlock !== null && $officeId !== null) {
                        $this->applyRoAsCarTotal($currentBlock, $locationName, $targetValues);

                        $currentBlock['office_rows'][] = [
                            'office_id' => $officeId,
                            'office_name' => $locationName,
                            'targets' => $targetValues,
                        ];

                        if ($this->isParentOfficeRow($locationName)) {
                            $currentBlock['group_totals']['group-' . (count($currentBlock['group_totals']) + 1)] = $targetValues;
                        }
                    }

                    continue;
                }

                if ($this->isOfficeBackedExcelLabel($locationName, $officeId) && !empty($currentHeaders)) {
                    $this->appendExcelHierarchyLeafFragment($currentHeaders, $papText);
                    continue;
                }

                $this->pushExcelHierarchyHeader($currentHeaders, $papText);
                continue;
            }

            if ($currentBlock !== null && $this->shouldAppendPerformanceIndicatorFragment($currentBlock, $indicatorText, $officeId)) {
                $currentBlock['indicator_parts'][] = $indicatorText;
            }

            if ($currentBlock !== null && $papText !== '') {
                if (!empty($currentBlock['merge_text_only_rows']) && $this->isExcelContinuationText($papText)) {
                    $currentBlock['activity_parts'][] = $papText;
                } elseif ($this->isExcelContinuationText($papText)) {
                    $this->appendExcelHierarchyLeafFragment($currentBlock['headers'], $papText);
                    $this->appendExcelHierarchyLeafFragment($currentHeaders, $papText);
                } else {
                    $currentBlock['activity_parts'][] = $papText;
                }
            }

            if ($currentBlock !== null && $officeId !== null && ($hasTargets || $this->isParentOfficeRow($locationName) || $isRoRow || !empty($currentBlock['placeholder_indicator']))) {
                $this->applyRoAsCarTotal($currentBlock, $locationName, $targetValues);

                $currentBlock['office_rows'][] = [
                    'office_id' => $officeId,
                    'office_name' => $locationName,
                    'targets' => $targetValues,
                ];

                if ($this->isParentOfficeRow($locationName)) {
                    $currentBlock['group_totals']['group-' . (count($currentBlock['group_totals']) + 1)] = $targetValues;
                }

                continue;
            }

            $skipped++;
        }

        $flushBlock();

        return [
            'year' => $year,
            'imported' => $imported,
            'skipped' => $skipped,
            'parsed_rows' => $totalParsedRows,
            'shown_rows' => count($previewRows),
            'placeholders' => $placeholders,
            'rows' => $previewRows,
            'warnings' => array_slice($warnings, 0, 80),
            'warning_count' => count($warnings),
        ];
    }

    private function previewExcelSortingWarnings(string $filePath): array
    {
        $reader = new SimpleXlsxReader();
        $warnings = [];
        $currentProgram = 'STO';
        $currentRoman = '';
        $seenNumericRoots = [];
        $lastSequences = [];

        foreach ($reader->rowsWithStyles($filePath, self::EXCEL_SHEET_NAME, true) as $rowNumber => $styledRow) {
            $row = $styledRow['values'] ?? [];
            $rowStyles = $styledRow['styles'] ?? [];
            $papText = $this->cleanExcelText($row['A'] ?? '');

            if ($rowNumber < 10) {
                continue;
            }

            if ($papText === '') {
                continue;
            }

            $isStyledPapHeader = $this->isStyledStoPapHeaderRow($papText, $row, $rowStyles);
            if ($this->isStoProgramHeader($papText, $row) || $isStyledPapHeader) {
                $currentProgram = $isStyledPapHeader && $currentProgram !== 'STO' && !$this->isStoSheetHeaderText($currentProgram)
                    ? $this->joinExcelFragments([$currentProgram, $papText])
                    : $papText;
                $currentRoman = '';
                $seenNumericRoots = [];
                $lastSequences = [];
                continue;
            }

            $heading = $this->excelHeadingPreviewInfo($papText);
            if ($heading === null) {
                continue;
            }

            if ($heading['type'] === 'roman') {
                $key = $currentProgram . '|roman';
                $last = $lastSequences[$key] ?? null;
                if ($last !== null && $heading['sequence'] > $last + 1) {
                    $warnings[] = [
                        'row' => $rowNumber,
                        'level' => 'warning',
                        'message' => "Roman sorting jumps from {$this->integerToRoman($last)}. to {$heading['label']}. Check if a section is missing.",
                    ];
                }

                $lastSequences[$key] = max($last ?? 0, $heading['sequence']);
                $currentRoman = 'roman:' . $heading['sequence'];
                $seenNumericRoots = [];
                continue;
            }

            $context = $currentProgram . '|' . $currentRoman;

            if ($heading['type'] === 'number') {
                $segments = $heading['segments'];
                $depth = count($segments);

                if ($depth === 1) {
                    $key = $context . '|number-root';
                    $last = $lastSequences[$key] ?? null;
                    if ($last !== null && $heading['sequence'] > $last + 1) {
                        $warnings[] = [
                            'row' => $rowNumber,
                            'level' => 'warning',
                            'message' => "Number sorting jumps from {$last}. to {$heading['label']}. Check if a numbered row is missing or mistyped.",
                        ];
                    }

                    $lastSequences[$key] = max($last ?? 0, $heading['sequence']);
                    $seenNumericRoots[$context . '|number:' . $segments[0]] = true;
                    continue;
                }

                $rootKey = $context . '|number:' . $segments[0];
                if (empty($seenNumericRoots[$rootKey])) {
                    $warnings[] = [
                        'row' => $rowNumber,
                        'level' => 'danger',
                        'message' => "{$heading['label']} appears without {$segments[0]}. in this section. It may be attached under the wrong green header.",
                    ];
                }

                $parentSegments = array_slice($segments, 0, -1);
                $key = $context . '|number:' . implode('.', $parentSegments);
                $last = $lastSequences[$key] ?? null;
                if ($last !== null && $heading['sequence'] > $last + 1) {
                    $parentLabel = implode('.', $parentSegments);
                    $warnings[] = [
                        'row' => $rowNumber,
                        'level' => 'warning',
                        'message' => "Number sorting under {$parentLabel} jumps from {$last} to {$heading['sequence']}.",
                    ];
                }

                $lastSequences[$key] = max($last ?? 0, $heading['sequence']);
                continue;
            }

            if ($heading['type'] === 'letter') {
                $key = $context . '|letter-root';
                $last = $lastSequences[$key] ?? null;
                if ($last !== null && $heading['sequence'] > $last + 1) {
                    $warnings[] = [
                        'row' => $rowNumber,
                        'level' => 'warning',
                        'message' => "Letter sorting jumps from " . chr(64 + $last) . ". to {$heading['label']}. Check if a letter row is missing.",
                    ];
                }

                $lastSequences[$key] = max($last ?? 0, $heading['sequence']);
            }
        }

        return $warnings;
    }

    private function excelHeadingPreviewInfo(string $text): ?array
    {
        $heading = $this->excelHeadingInfo($text);

        if ($heading === null) {
            return null;
        }

        return [
            'type' => in_array($heading['type'], ['upper_letter', 'lower_letter'], true) ? 'letter' : $heading['type'],
            'label' => $heading['label'],
            'segments' => $heading['segments'],
            'sequence' => $heading['sequence'],
            'depth' => $heading['depth'],
        ];
    }

    private function integerToRoman(int $number): string
    {
        $map = [
            'M' => 1000,
            'CM' => 900,
            'D' => 500,
            'CD' => 400,
            'C' => 100,
            'XC' => 90,
            'L' => 50,
            'XL' => 40,
            'X' => 10,
            'IX' => 9,
            'V' => 5,
            'IV' => 4,
            'I' => 1,
        ];
        $result = '';

        foreach ($map as $roman => $value) {
            while ($number >= $value) {
                $result .= $roman;
                $number -= $value;
            }
        }

        return $result;
    }

    private function upsertImportedStoTarget(int $programId, int $rowId, int $indicatorId, ?int $officeId, int $year, array $values, array $carTotals = [], array $groupTotals = []): void
    {
        $existing = Sto_Target::query()
            ->where('years', $year)
            ->where('office_ids', $officeId)
            ->get()
            ->first(function ($target) use ($rowId, $indicatorId) {
                $meta = $this->parseSectionValues($target->values ?? null);

                return (int) ($meta['row_id'] ?? 0) === $rowId
                    && (int) ($meta['indicator_id'] ?? 0) === $indicatorId;
            });

        $target = $existing ?? new Sto_Target();
        $target->office_ids = $officeId;
        $target->years = $year;

        foreach ($values as $key => $value) {
            $target->{$key} = $value;
        }

        $target->values = json_encode([
            'user_id' => Auth::id(),
            'program_id' => $programId,
            'row_id' => $rowId,
            'indicator_id' => $indicatorId,
            'car_totals' => $carTotals,
            'group_totals' => $groupTotals,
            'imported_from' => 'excel',
        ]);
        $target->save();
    }

    private function sumImportedCarTotalsForBlock(array $block): array
    {
        $sumRows = [];

        if (!empty($block['ro_totals'])) {
            $sumRows[] = $block['ro_totals'];
        }

        foreach ($block['group_totals'] ?? [] as $groupTotals) {
            $sumRows[] = $groupTotals;
        }

        if (empty($sumRows) && !empty($block['car_totals'])) {
            $sumRows[] = $block['car_totals'];
        }

        return $this->sumTargetValueRows($sumRows);
    }

    private function sumTargetValueRows(array $rows): array
    {
        $keys = [
            'jan', 'feb', 'mar', 'q1',
            'apr', 'may', 'jun', 'q2',
            'jul', 'aug', 'sep', 'q3',
            'oct', 'nov', 'dec', 'q4',
            'annual_total',
        ];

        $totals = array_fill_keys($keys, 0.0);

        foreach ($rows as $row) {
            foreach ($keys as $key) {
                $totals[$key] += $this->excelNumber($row[$key] ?? 0);
            }
        }

        return $totals;
    }

    private function papDataFromExcelBlock(array $block, int $year): array
    {
        $headers = array_values(array_filter($block['headers'] ?? []));
        $activityName = $this->joinExcelFragments($block['activity_parts'] ?? []);

        if (!empty($block['standalone_title']) && !empty($headers)) {
            $standaloneTitle = array_shift($headers);

            return [
                'title' => $this->excelPpaName($standaloneTitle ?: ($block['title'] ?: self::DEFAULT_STO_TITLE)),
                'program' => $this->excelPpaName('N/A'),
                'project' => $this->excelPpaName('N/A'),
                'activities' => $this->excelPpaName($headers[0] ?? ($activityName ?: 'N/A')),
                'subactivities' => $this->excelPpaName($headers[1] ?? null),
                'subsubactivities' => $this->excelPpaName($headers[2] ?? null),
                'level_6' => $this->excelPpaName($headers[3] ?? null),
                'level_7' => $this->excelPpaName($headers[4] ?? null),
                'level_8' => $this->excelPpaName($headers[5] ?? null),
                'year' => $year,
                'duplicate_leaf' => false,
            ];
        }

        if ($this->isPsRequirementsChild((string) ($headers[0] ?? $activityName))) {
            return [
                'title' => $this->excelPpaName('PS Requirements'),
                'program' => $this->excelPpaName('N/A'),
                'project' => $this->excelPpaName('N/A'),
                'activities' => $this->excelPpaName($headers[0] ?? ($activityName ?: 'N/A')),
                'subactivities' => $this->excelPpaName($headers[1] ?? null),
                'subsubactivities' => $this->excelPpaName($headers[2] ?? null),
                'level_6' => $this->excelPpaName($headers[3] ?? null),
                'level_7' => $this->excelPpaName($headers[4] ?? null),
                'level_8' => $this->excelPpaName($headers[5] ?? null),
                'year' => $year,
                'duplicate_leaf' => false,
            ];
        }

        $activities = $headers[0] ?? ($activityName ?: 'N/A');
        $subactivities = isset($headers[0]) ? ($headers[1] ?? ($activityName ?: null)) : null;
        $subsubactivities = isset($headers[1]) ? ($headers[2] ?? ($activityName ?: null)) : null;
        $level6 = isset($headers[2]) ? ($headers[3] ?? ($activityName ?: null)) : null;
        $level7 = isset($headers[3]) ? ($headers[4] ?? ($activityName ?: null)) : null;
        $level8 = isset($headers[4]) ? ($headers[5] ?? ($activityName ?: null)) : null;

        return [
            'title' => $this->excelPpaName($block['title'] ?: self::DEFAULT_STO_TITLE),
            'program' => $this->excelPpaName($block['program'] ?? 'N/A'),
            'project' => $this->excelPpaName($block['project'] ?? 'N/A'),
            'activities' => $this->excelPpaName($activities),
            'subactivities' => $this->excelPpaName($subactivities),
            'subsubactivities' => $this->excelPpaName($subsubactivities),
            'level_6' => $this->excelPpaName($level6),
            'level_7' => $this->excelPpaName($level7),
            'level_8' => $this->excelPpaName($level8),
            'year' => $year,
            'duplicate_leaf' => false,
        ];
    }

    private function firstOrCreateImportedIndicator(string $indicatorName, ?int $indicatorTypeId = null): Sto_Indicator
    {
        $indicator = Sto_Indicator::query()
            ->whereRaw('LOWER(TRIM(name)) = ?', [strtolower($indicatorName)])
            ->first();

        if ($indicator) {
            if ($indicatorTypeId !== null && $this->hasIndicatorColumn('indicator_type_id') && empty($indicator->indicator_type_id)) {
                $indicator->indicator_type_id = $indicatorTypeId;
                $indicator->save();
            }

            return $indicator;
        }

        $indicator = new Sto_Indicator();
        $indicator->name = $indicatorName;
        if ($this->hasIndicatorColumn('user_id')) {
            $indicator->user_id = Auth::id();
        }
        if ($indicatorTypeId !== null && $this->hasIndicatorColumn('indicator_type_id')) {
            $indicator->indicator_type_id = $indicatorTypeId;
        }
        $indicator->save();

        return $indicator;
    }

    private function inferIndicatorTypeIdFromExcelBlock(array $block): ?int
    {
        $typeName = $this->inferIndicatorTypeNameFromOfficeHierarchy($block['office_rows'] ?? [])
            ?? $this->inferIndicatorTypeNameFromExcelRows(
            collect($block['office_rows'] ?? [])
                ->pluck('targets')
                ->filter(fn ($values) => is_array($values) && $this->hasAnyTargetValue($values))
                ->values()
                ->all()
        );

        return $typeName ? $this->indicatorTypeIdByName($typeName) : null;
    }

    private function inferIndicatorTypeNameFromOfficeHierarchy(array $officeRows): ?string
    {
        $rowsByOfficeId = collect($officeRows)
            ->filter(fn ($row) => isset($row['office_id'], $row['targets']) && is_array($row['targets']))
            ->groupBy(fn ($row) => (int) $row['office_id']);

        $parentChildOfficeIds = $this->provinceChildOfficeIdMap();
        if (empty($parentChildOfficeIds)) {
            return null;
        }

        $periodKeys = [
            'jan', 'feb', 'mar', 'q1',
            'apr', 'may', 'jun', 'q2',
            'jul', 'aug', 'sep', 'q3',
            'oct', 'nov', 'dec', 'q4',
            'annual_total',
        ];

        $sumMatches = 0;
        $maxMatches = 0;

        foreach ($parentChildOfficeIds as $parentOfficeId => $childOfficeIds) {
            $parentRow = $rowsByOfficeId->get((int) $parentOfficeId, collect())->first();
            if (!$parentRow) {
                continue;
            }

            $childRows = collect($childOfficeIds)
                ->flatMap(fn ($childOfficeId) => $rowsByOfficeId->get((int) $childOfficeId, collect()))
                ->values();

            if ($childRows->isEmpty()) {
                continue;
            }

            foreach ($periodKeys as $periodKey) {
                $parentValue = $this->excelNumber($parentRow['targets'][$periodKey] ?? 0);
                $childValues = $childRows
                    ->map(fn ($row) => $this->excelNumber($row['targets'][$periodKey] ?? 0))
                    ->values()
                    ->all();

                $sum = array_sum($childValues);
                $max = empty($childValues) ? 0.0 : max($childValues);

                if ($parentValue === 0.0 && $sum === 0.0) {
                    continue;
                }

                if (abs($sum - $max) < 0.00001) {
                    continue;
                }

                if (abs($parentValue - $sum) < 0.00001) {
                    $sumMatches++;
                }

                if (abs($parentValue - $max) < 0.00001) {
                    $maxMatches++;
                }
            }
        }

        if ($maxMatches > $sumMatches) {
            return 'non-cumulative';
        }

        if ($sumMatches > $maxMatches) {
            return 'cumulative';
        }

        return null;
    }

    private function provinceChildOfficeIdMap(): array
    {
        static $map = null;

        if ($map !== null) {
            return $map;
        }

        $map = [];
        foreach (Office::groupedForUi() as $parent) {
            $childIds = collect($parent->children ?? [])
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->values()
                ->all();

            if (!empty($childIds)) {
                $map[(int) $parent->id] = $childIds;
            }
        }

        return $map;
    }

    private function inferIndicatorTypeNameFromExcelRows(array $targetRows): ?string
    {
        $sumMatches = 0;
        $maxMatches = 0;
        $quarterGroups = [
            [['jan', 'feb', 'mar'], 'q1'],
            [['apr', 'may', 'jun'], 'q2'],
            [['jul', 'aug', 'sep'], 'q3'],
            [['oct', 'nov', 'dec'], 'q4'],
        ];

        foreach ($targetRows as $values) {
            foreach ($quarterGroups as [$monthKeys, $quarterKey]) {
                $months = array_map(fn ($key) => $this->excelNumber($values[$key] ?? 0), $monthKeys);
                $quarter = $this->excelNumber($values[$quarterKey] ?? 0);
                $sum = array_sum($months);
                $max = max($months);

                if ($quarter === 0.0 && $sum === 0.0) {
                    continue;
                }

                if (abs($sum - $max) < 0.00001) {
                    continue;
                }

                if (abs($quarter - $sum) < 0.00001) {
                    $sumMatches++;
                }

                if (abs($quarter - $max) < 0.00001) {
                    $maxMatches++;
                }
            }
        }

        if ($maxMatches > $sumMatches) {
            return 'non-cumulative';
        }

        if ($sumMatches > 0) {
            return 'cumulative';
        }

        return null;
    }

    private function indicatorTypeIdByName(string $name): ?int
    {
        $id = DB::table('indicator_types')
            ->whereRaw('LOWER(TRIM(name)) = ?', [strtolower($name)])
            ->value('id');

        return $id ? (int) $id : null;
    }

    private function joinExcelFragments(array $parts): string
    {
        $cleaned = collect($parts)
            ->map(fn ($part) => $this->cleanExcelText($part))
            ->filter()
            ->values()
            ->all();

        return trim(preg_replace('/\s+/', ' ', implode(' ', $cleaned)));
    }

    private function pushExcelHierarchyHeader(array &$headers, string $text): void
    {
        $heading = $this->excelHeadingInfo($text);
        $headingDepth = $heading['depth'] ?? null;
        $rootKind = $this->excelHeadingInfo((string) ($headers[0] ?? ''))['type'] ?? null;
        $parentKind = $this->excelHeadingInfo((string) ($headers[1] ?? ''))['type'] ?? null;

        if ($heading !== null) {
            if ($heading['type'] === 'number' && in_array($rootKind, ['upper_letter', 'roman'], true)) {
                $segmentDepth = count($heading['segments'] ?? []);
                $headingDepth = $segmentDepth > 1 ? $segmentDepth + 1 : max(2, $headingDepth);
            }

            if (
                $heading['type'] === 'number'
                && $this->isStandaloneExcelTitleHeader((string) ($headers[0] ?? ''))
                && isset($headers[1])
            ) {
                $headingDepth = count($heading['segments'] ?? []) + 2;
            }

            if (
                $heading['type'] === 'lower_letter'
                && in_array($rootKind, ['upper_letter', 'roman'], true)
                && $parentKind === 'number'
            ) {
                $headingDepth = 3;
            } elseif (
                in_array($heading['type'], ['upper_letter', 'lower_letter'], true)
                && in_array($rootKind, ['number', 'roman'], true)
            ) {
                $headingDepth = 2;
            }
        }

        if ($headingDepth !== null) {
            $headers = array_slice($headers, 0, max(0, $headingDepth - 1));
            $headers[$headingDepth - 1] = $text;
            $headers = array_values($headers);
            return;
        }

        if (empty($headers)) {
            $headers[] = $text;
            return;
        }

        $lastIndex = count($headers) - 1;
        $headers[$lastIndex] = trim($headers[$lastIndex] . ' ' . $text);
    }

    private function appendExcelHierarchyLeafFragment(array &$headers, string $text): void
    {
        $text = trim($text);

        if ($text === '') {
            return;
        }

        if (empty($headers)) {
            $headers[] = $text;
            return;
        }

        $lastIndex = count($headers) - 1;
        $headers[$lastIndex] = $this->joinExcelFragments([$headers[$lastIndex], $text]);
    }

    private function romanToInteger(string $roman): int
    {
        $map = ['i' => 1, 'v' => 5, 'x' => 10, 'l' => 50, 'c' => 100, 'd' => 500, 'm' => 1000];
        $roman = strtolower($roman);
        $value = 0;
        $length = strlen($roman);

        for ($index = 0; $index < $length; $index++) {
            $current = $map[$roman[$index]] ?? 0;
            $next = $index + 1 < $length ? ($map[$roman[$index + 1]] ?? 0) : 0;
            $value += $current < $next ? -$current : $current;
        }

        return $value;
    }

    private function applyRoAsCarTotal(array &$block, string $locationName, array $targetValues): void
    {
        if (!$this->isRoOfficeRow($locationName)) {
            return;
        }

        $block['ro_totals'] = $targetValues;

        if (empty($block['car_totals'])) {
            $block['car_totals'] = $targetValues;
        }
    }

    private function isRoOfficeRow(string $locationName): bool
    {
        return $this->normalizeImportedOfficeName($locationName) === 'RO';
    }

    private function isOfficeBackedExcelLabel(string $locationName, ?int $officeId): bool
    {
        if ($officeId !== null) {
            return true;
        }

        $normalized = $this->normalizeImportedOfficeName($locationName);

        return in_array($normalized, [
            'CAR',
            'PENRO',
            'RO',
            'ABRA',
            'APAYAO',
            'BENGUET',
            'IFUGAO',
            'KALINGA',
            'MOUNTAINPROVINCE',
            'MTPROVINCE',
        ], true);
    }

    private function isStandaloneExcelTitleHeader(string $text): bool
    {
        $normalized = preg_replace('/[^A-Z0-9]+/', '', strtoupper(trim($text)));

        return in_array($normalized, [
            'PSREQUIREMENTS',
            'PMS',
        ], true);
    }

    private function isStandaloneExcelSectionHeader(string $text): bool
    {
        $normalized = preg_replace('/[^A-Z0-9]+/', '', strtoupper(trim($text)));

        return in_array($normalized, [
            'MANDATORIESANDIMPOSITIONS',
        ], true);
    }

    private function isPsRequirementsChild(string $text): bool
    {
        $normalized = preg_replace('/[^A-Z0-9]+/', '', strtoupper(trim($text)));
        $normalized = preg_replace('/^\d+/', '', $normalized);

        return in_array($normalized, [
            'PSREGULAR',
            'RLIP',
        ], true);
    }

    private function shouldSkipAllNaExcelPlaceholderBlock(array $block, string $indicatorName): bool
    {
        if (empty($block['placeholder_indicator']) || !$this->isExcelNaValue($indicatorName)) {
            return false;
        }

        $headers = collect($block['headers'] ?? [])
            ->map(fn ($header) => $this->cleanExcelText($header))
            ->filter()
            ->values()
            ->all();

        if (!empty($block['standalone_title']) && !empty($headers)) {
            array_shift($headers);
        }

        $activityName = $this->joinExcelFragments($block['activity_parts'] ?? []);
        $hierarchyParts = array_merge($headers, [$activityName]);

        return collect($hierarchyParts)
            ->filter(fn ($value) => !$this->isExcelNaValue($value))
            ->isEmpty();
    }

    private function isExcelNaValue($value): bool
    {
        $normalized = strtoupper(trim((string) $value));

        return $normalized === '' || $normalized === 'N/A' || $normalized === 'NA';
    }

    private function isExcelSignatureFooterRow(string ...$cells): bool
    {
        $text = strtoupper($this->joinExcelFragments($cells));

        if ($text === '') {
            return false;
        }

        return preg_match('/\b(RECOMMENDING APPROVAL|APPROVED|PREPARED BY|SUBMITTED BY|CERTIFIED CORRECT|ENGR\.?|ATTY\.?|CESO|REGIONAL EXECUTIVE DIRECTOR)\b/u', $text) === 1;
    }

    private function shouldAppendToProgramTitle(string $currentProgram, string $text): bool
    {
        $currentProgram = trim($currentProgram);
        $text = trim($text);

        if ($currentProgram === '' || $text === '') {
            return false;
        }

        if (preg_match('/^\d+\.\s+/u', $text) === 1 || preg_match('/^(?![IVXLCDM]+\.\s)[A-Z]\.\s+/i', $text) === 1) {
            return false;
        }

        if (strtoupper($text) !== $text) {
            return false;
        }

        return preg_match('/\b(AND|OF|FOR|TO|IN|ON|WITH|INCLUDING|THROUGH|UNDER|FROM|THE|ITS|PROPERTY|MOTOR|VEHICLES|MAINTENANCE|INSURANCE)\s*$/i', $currentProgram) === 1;
    }

    private function shouldStoreProgramSupplement(string $text, bool $allowLeadingLetterHeading = false): bool
    {
        $text = trim($text);

        if ($text === '') {
            return false;
        }

        if ($allowLeadingLetterHeading && preg_match('/^(?![IVXLCDM]+\.\s)[A-Z]\.\s+/i', $text) === 1) {
            return true;
        }

        if ($this->excelHeadingDepth($text) !== null) {
            return false;
        }

        return true;
    }

    private function shouldAppendPerformanceIndicatorFragment(array $currentBlock, string $text, ?int $officeId): bool
    {
        return $this->isExcelContinuationText($text);
    }

    private function isExcelContinuationText(string $text): bool
    {
        $text = trim($text);

        return $text !== '' && $this->excelHeadingDepth($text) === null;
    }

    private function shouldMergeConstructionLeafFragment(?string $currentProgram, string $text): bool
    {
        $currentProgram = trim((string) $currentProgram);
        $text = trim($text);

        if ($currentProgram === '' || $text === '') {
            return false;
        }

        return preg_match('/CONSTRUCTION,\s*REPAIR AND MAINTENANCE AND INSURANCE OF PROPERTY INCLUDING MOTOR VEHICLES/i', $currentProgram) === 1
            && preg_match('/^1\.\s+Repair and Maintenance of Buildings and$/i', $text) === 1;
    }

    private function excelHeadingDepth(string $text): ?int
    {
        return $this->excelHeadingInfo($text)['depth'] ?? null;
    }

    private function excelHeadingInfo(string $text): ?array
    {
        $text = trim($text);

        if ($text === '') {
            return null;
        }

        if (
            preg_match('/^(\d+(?:\.\d+)*)([.)-]+)(?=\s|[A-Za-z(]|$)/u', $text, $matches)
            || preg_match('/^(\d+(?:\.\d+)+)(?=\s|[A-Za-z(]|$)/u', $text, $matches)
        ) {
            $segments = array_map('intval', explode('.', rtrim($matches[1], '.')));

            return [
                'type' => 'number',
                'label' => $matches[1],
                'segments' => $segments,
                'sequence' => end($segments),
                'depth' => substr_count($matches[1], '.') + 1,
            ];
        }

        if (preg_match('/^([VX]|[IVXLCDM]{2,})[.)-]\s*/u', $text, $matches)) {
            return [
                'type' => 'roman',
                'label' => strtoupper($matches[1]),
                'segments' => [],
                'sequence' => $this->romanToInteger($matches[1]),
                'depth' => 1,
            ];
        }

        if (preg_match('/^([A-Z])[.)-]\s*/u', $text, $matches)) {
            return [
                'type' => 'upper_letter',
                'label' => strtoupper($matches[1]),
                'segments' => [],
                'sequence' => ord($matches[1]) - 64,
                'depth' => 1,
            ];
        }

        if (preg_match('/^([a-z])[.)-]\s*/u', $text, $matches)) {
            return [
                'type' => 'lower_letter',
                'label' => strtoupper($matches[1]),
                'segments' => [],
                'sequence' => ord(strtoupper($matches[1])) - 64,
                'depth' => 1,
            ];
        }

        return null;
    }

    private function isStoProgramHeader(string $text, array $row): bool
    {
        if ($text === '' || isset($row['B']) || !isset($row['C']) || strcasecmp((string) $row['C'], 'CAR') !== 0) {
            return false;
        }

        if ($this->excelHeadingDepth($text) !== null) {
            return false;
        }

        if ($this->isStandaloneExcelTitleHeader($text) || $this->isStandaloneExcelSectionHeader($text)) {
            return false;
        }

        return strtoupper($text) === $text;
    }

    private function isStyledStoPapHeaderRow(string $text, array $row, array $rowStyles): bool
    {
        if ($text === '' || empty($rowStyles['A']['bold'])) {
            return false;
        }

        if (isset($row['B']) || $this->hasAnyTargetValue($this->physicalTargetValuesFromExcelRow($row)) || $this->excelHeadingDepth($text) !== null) {
            return false;
        }

        if (isset($row['C']) && strcasecmp((string) $row['C'], 'CAR') !== 0) {
            return false;
        }

        if ($this->isStandaloneExcelTitleHeader($text) || $this->isStandaloneExcelSectionHeader($text)) {
            return false;
        }

        return !$this->isExcelSignatureFooterRow($text);
    }

    private function isStoSheetHeaderText(?string $text): bool
    {
        $normalized = preg_replace('/[^A-Z0-9]+/', '', strtoupper(trim((string) $text)));

        return in_array($normalized, [
            'SUPPORTTOOPERATIONS',
            'SUPPORTTOOPERATIONSSTO',
        ], true);
    }

    private function isStyledStoSectionHeaderRow(string $text, array $row, array $rowStyles): bool
    {
        $heading = $this->excelHeadingInfo($text);
        if (($heading['type'] ?? null) !== 'upper_letter') {
            return false;
        }

        if (isset($row['B']) || $this->hasAnyTargetValue($this->physicalTargetValuesFromExcelRow($row))) {
            return false;
        }

        return $this->isGreenExcelFill($rowStyles['A']['fill'] ?? null)
            || $this->isGreenExcelFill($rowStyles['A']['fill_type'] ?? null)
            || !empty($rowStyles['A']['bold']);
    }

    private function isGreenExcelFill($fill): bool
    {
        $fill = strtoupper(trim((string) $fill));
        if ($fill === '') {
            return false;
        }

        return str_contains($fill, '008000')
            || str_contains($fill, '00B050')
            || str_contains($fill, '70AD47')
            || str_contains($fill, '92D050')
            || str_contains($fill, '548235')
            || str_contains($fill, 'SOLID');
    }

    private function isDuplicateStoSummaryProgramHeader(string $text): bool
    {
        $normalized = preg_replace('/[^A-Z0-9]+/', '', strtoupper(trim($text)));

        return in_array($normalized, [
            'MANDATORIESANDIMPOSITIONS',
        ], true);
    }

    private function isParentOfficeRow(string $locationName): bool
    {
        return in_array($this->normalizeImportedOfficeName($locationName), [
            'ABRA',
            'APAYAO',
            'BENGUET',
            'IFUGAO',
            'KALINGA',
            'MOUNTAINPROVINCE',
            'MTPROVINCE',
        ], true);
    }

    private function physicalTargetValuesFromExcelRow(array $row): array
    {
        $values = [
            'jan' => $this->excelNumber($row['I'] ?? 0),
            'feb' => $this->excelNumber($row['J'] ?? 0),
            'mar' => $this->excelNumber($row['K'] ?? 0),
            'q1' => $this->excelNumber($row['L'] ?? 0),
            'apr' => $this->excelNumber($row['M'] ?? 0),
            'may' => $this->excelNumber($row['N'] ?? 0),
            'jun' => $this->excelNumber($row['O'] ?? 0),
            'q2' => $this->excelNumber($row['P'] ?? 0),
            'jul' => $this->excelNumber($row['Q'] ?? 0),
            'aug' => $this->excelNumber($row['R'] ?? 0),
            'sep' => $this->excelNumber($row['S'] ?? 0),
            'q3' => $this->excelNumber($row['T'] ?? 0),
            'oct' => $this->excelNumber($row['U'] ?? 0),
            'nov' => $this->excelNumber($row['V'] ?? 0),
            'dec' => $this->excelNumber($row['W'] ?? 0),
            'q4' => $this->excelNumber($row['X'] ?? 0),
        ];

        $values['annual_total'] = $this->excelNumber($row['Y'] ?? 0);
        if ($values['annual_total'] <= 0) {
            $values['annual_total'] = $values['q1'] + $values['q2'] + $values['q3'] + $values['q4'];
        }

        return $values;
    }

    private function hasAnyTargetValue(array $values): bool
    {
        foreach ($values as $value) {
            if ((float) $value !== 0.0) {
                return true;
            }
        }

        return false;
    }

    private function getOfficeImportMap(): array
    {
        return Office::query()
            ->get(['id', 'name'])
            ->mapWithKeys(fn ($office) => [$this->normalizeImportedOfficeName($office->name) => (int) $office->id])
            ->all();
    }

    private function resolveImportedOfficeId(string $name, array $officeMap, ?int $currentParentOfficeId = null): ?int
    {
        $normalized = $this->normalizeImportedOfficeName($name);
        $aliases = [
            'CAR' => null,
            'PENRO' => $currentParentOfficeId,
            'MOUNTAINPROVINCE' => $officeMap['MTPROVINCE'] ?? null,
            'MT.PROVINCE' => $officeMap['MTPROVINCE'] ?? null,
            'MTPROVINCE' => $officeMap['MTPROVINCE'] ?? null,
            'MPROVINCE' => $officeMap['MTPROVINCE'] ?? null,
            'MTNPROVINCE' => $officeMap['MTPROVINCE'] ?? null,
            'MT' => $officeMap['MTPROVINCE'] ?? null,
            'ALFONSOLISTA' => $officeMap['ALFONSOLISTA'] ?? null,
            'ALISTA' => $officeMap['ALFONSOLISTA'] ?? null,
            'C PARACELIS' => $officeMap['PARACELIS'] ?? null,
            'CPARACELIS' => $officeMap['PARACELIS'] ?? null,
            'C SABANGAN' => $officeMap['SABANGAN'] ?? null,
            'CSABANGAN' => $officeMap['SABANGAN'] ?? null,
        ];

        if (array_key_exists($normalized, $aliases)) {
            return $aliases[$normalized];
        }

        return $officeMap[$normalized] ?? null;
    }

    private function normalizeImportedOfficeName(string $name): string
    {
        return preg_replace('/[^A-Z0-9]+/', '', strtoupper($name));
    }

    private function cleanExcelText($value): string
    {
        $value = trim((string) $value);
        if ($value === '' || str_starts_with($value, '#')) {
            return '';
        }

        return $value;
    }

    private function excelPpaName($value): ?string
    {
        $value = $this->cleanExcelText($value);
        if ($value === '') {
            return null;
        }

        return mb_strlen($value) > 255 ? mb_substr($value, 0, 252) . '...' : $value;
    }

    private function excelNumber($value): float
    {
        $value = trim((string) $value);
        if ($value === '' || str_starts_with($value, '#') || !is_numeric($value)) {
            return 0.0;
        }

        return (float) $value;
    }

    private function isFinancialOnlyExcelRow(array $row): bool
    {
        return isset($row['AA']) && !isset($row['B']) && !isset($row['I']);
    }

}
