<?php

namespace App\Models;

use App\Models\Concerns\UsesConsolidatedPhysicalTable;

abstract class LegacyPhysicalAccomplishment extends PhysicalAccomplishment
{
    use UsesConsolidatedPhysicalTable;

    public function getTable(): string
    {
        return 'physical_accomplishments';
    }
}
