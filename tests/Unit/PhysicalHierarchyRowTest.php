<?php

namespace Tests\Unit;

use App\Support\PhysicalHierarchyRow;
use PHPUnit\Framework\TestCase;

class PhysicalHierarchyRowTest extends TestCase
{
    public function test_child_row_owns_its_displayed_hierarchy(): void
    {
        $row = $this->row([
            'row_id' => 2071,
            'sub_activity_row_id' => 2064,
            'sub_sub_activity_row_id' => 2071,
            'activities' => 'A. Design and implementation of training programs',
            'subactivities' => '3. New Employees Orientation',
        ]);

        $this->assertSame(2071, PhysicalHierarchyRow::displayedRowId($row));
        $this->assertTrue(PhysicalHierarchyRow::ownsDisplayedHierarchy($row));
    }

    public function test_parent_indicator_join_copy_does_not_own_child_hierarchy(): void
    {
        $row = $this->row([
            'row_id' => 2065,
            'sub_activity_row_id' => 2065,
            'sub_sub_activity_row_id' => 2071,
            'activities' => 'A. Design and implementation of training programs',
            'subactivities' => '3. New Employees Orientation',
        ]);

        $this->assertSame(2071, PhysicalHierarchyRow::displayedRowId($row));
        $this->assertFalse(PhysicalHierarchyRow::ownsDisplayedHierarchy($row));
    }

    public function test_each_indicator_row_owns_the_promoted_parent_hierarchy(): void
    {
        $firstIndicator = $this->row([
            'row_id' => 2064,
            'sub_activity_row_id' => 2064,
            'activities' => 'A. Design and implementation of training programs',
        ]);
        $secondIndicator = $this->row([
            'row_id' => 2065,
            'sub_activity_row_id' => 2065,
            'activities' => 'A. Design and implementation of training programs',
        ]);

        $this->assertTrue(PhysicalHierarchyRow::ownsDisplayedHierarchy($firstIndicator));
        $this->assertTrue(PhysicalHierarchyRow::ownsDisplayedHierarchy($secondIndicator));
    }

    public function test_deepest_visible_level_determines_the_owner(): void
    {
        $row = $this->row([
            'row_id' => 3100,
            'sub_activity_row_id' => 3000,
            'sub_sub_activity_row_id' => 3050,
            'sub_sub_sub_activity_row_id' => 3100,
            'activities' => 'A. Parent',
            'subactivities' => '1. Child',
            'subsubactivities' => 'a. Leaf',
        ]);

        $this->assertSame(3100, PhysicalHierarchyRow::displayedRowId($row));
        $this->assertTrue(PhysicalHierarchyRow::ownsDisplayedHierarchy($row));
    }

    private function row(array $overrides): object
    {
        return (object) array_merge([
            'id' => 2000,
            'row_id' => 2000,
            'program_row_id' => 2000,
            'project_row_id' => 0,
            'main_activity_row_id' => 0,
            'sub_activity_row_id' => 0,
            'sub_sub_activity_row_id' => 0,
            'sub_sub_sub_activity_row_id' => 0,
            'level_7_row_id' => 0,
            'level_8_row_id' => 0,
            'level_9_row_id' => 0,
            'title' => 'Physical Performance',
            'program' => null,
            'project' => null,
            'activities' => null,
            'subactivities' => null,
            'subsubactivities' => null,
            'level_6' => null,
            'level_7' => null,
            'level_8' => null,
        ], $overrides);
    }
}
