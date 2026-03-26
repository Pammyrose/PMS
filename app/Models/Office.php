<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;


class Office extends Model
{
    public static function groupedForUi(): Collection
    {
        $cenroParentMap = [
            'BANGUED' => 'ABRA',
            'LAGANGILANG' => 'ABRA',
            'CALANASAN' => 'APAYAO',
            'CONNER' => 'APAYAO',
            'BUGUIAS' => 'BENGUET',
            'BAGUIO' => 'BENGUET',
            'LAMUT' => 'IFUGAO',
            'ALFONSO LISTA' => 'IFUGAO',
            'PINUKPUK' => 'KALINGA',
            'TABUK' => 'KALINGA',
            'PARACELIS' => 'MT.PROVINCE',
            'SABANGAN' => 'MT.PROVINCE',
        ];

        $offices = static::query()
            ->orderBy('office_types_id')
            ->orderBy('name')
            ->get();

        $cenrosByParent = $offices
            ->filter(fn (Office $office) => (int) ($office->office_types_id ?? 0) === 3)
            ->groupBy(function (Office $office) use ($cenroParentMap) {
                return $cenroParentMap[strtoupper(trim((string) $office->name))] ?? null;
            });

        return $offices
            ->filter(fn (Office $office) => in_array((int) ($office->office_types_id ?? 0), [1, 2], true))
            ->values()
            ->map(function (Office $office) use ($cenrosByParent) {
                $office->setRelation(
                    'children',
                    collect($cenrosByParent->get(strtoupper(trim((string) $office->name)), collect()))
                        ->sortBy('name')
                        ->values()
                );

                return $office;
            });
    }
 

    public function children()
    {
        return $this->hasMany(Office::class, 'id');
    }

    public function parent()
    {
        return $this->belongsTo(Office::class, 'id');
    }
}