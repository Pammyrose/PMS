<?php

namespace App\Models;

use App\Models\Concerns\UsesConsolidatedPhysicalTable;

abstract class LegacyPhysicalTarget extends PhysicalTarget
{
    use UsesConsolidatedPhysicalTable;

    public function getTable(): string
    {
        return 'physical_targets';
    }
}
