<?php

namespace App\Support;

final class PhysicalHierarchyRow
{
    /**
     * Return the PPA row that owns the deepest hierarchy label currently
     * displayed by a flattened physical-performance row.
     *
     * A hierarchy query can return multiple copies of the same path when a
     * parent detail has multiple indicators. Only the row that owns the
     * displayed leaf should render at that leaf; the other copies are SQL
     * join fan-out and are rendered separately when their parent is promoted.
     */
    public static function displayedRowId(object|array $row): int
    {
        $levels = [
            ['level_8', 'level_9_row_id'],
            ['level_7', 'level_8_row_id'],
            ['level_6', 'level_7_row_id'],
            ['subsubactivities', 'sub_sub_sub_activity_row_id'],
            ['subactivities', 'sub_sub_activity_row_id'],
            ['activities', 'sub_activity_row_id'],
            ['project', 'main_activity_row_id'],
            ['program', 'project_row_id'],
            ['title', 'program_row_id'],
        ];

        foreach ($levels as [$labelField, $rowIdField]) {
            $label = trim((string) self::value($row, $labelField));
            $rowId = (int) self::value($row, $rowIdField);

            if ($label !== '' && $rowId > 0) {
                return $rowId;
            }
        }

        return (int) (self::value($row, 'row_id') ?: self::value($row, 'id'));
    }

    public static function ownsDisplayedHierarchy(object|array $row): bool
    {
        $actualRowId = (int) (self::value($row, 'row_id') ?: self::value($row, 'id'));
        $displayedRowId = self::displayedRowId($row);

        return $actualRowId > 0 && $actualRowId === $displayedRowId;
    }

    private static function value(object|array $row, string $field): mixed
    {
        return is_array($row)
            ? ($row[$field] ?? null)
            : ($row->{$field} ?? null);
    }
}
