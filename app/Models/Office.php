<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;


class Office extends Model
{
    private const CENRO_PARENT_NAMES = [
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

    public static function groupedForUi(): Collection
    {
        $offices = static::query()
            ->orderBy('office_types_id')
            ->orderBy('name')
            ->get();

        $cenrosByParent = $offices
            ->filter(fn (Office $office) => (int) ($office->office_types_id ?? 0) === 3)
            ->groupBy(function (Office $office) {
                return self::CENRO_PARENT_NAMES[strtoupper(trim((string) $office->name))] ?? null;
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

    public static function penroOfficeIdFor(int $officeId): ?int
    {
        $office = static::query()->find($officeId);

        if (! $office) {
            return null;
        }

        if ((int) $office->office_types_id === 2) {
            return (int) $office->id;
        }

        if ((int) $office->office_types_id !== 3) {
            return null;
        }

        $parentName = self::CENRO_PARENT_NAMES[strtoupper(trim((string) $office->name))] ?? null;

        if ($parentName === null) {
            return null;
        }

        $penroId = static::query()
            ->where('office_types_id', 2)
            ->whereRaw('UPPER(TRIM(name)) = ?', [$parentName])
            ->value('id');

        return $penroId === null ? null : (int) $penroId;
    }

    public static function serviceAreaOfficeIdsForPenro(int $penroOfficeId): array
    {
        $penro = static::query()->find($penroOfficeId);

        if (! $penro || (int) $penro->office_types_id !== 2) {
            return [];
        }

        $penroName = strtoupper(trim((string) $penro->name));
        $cenroNames = collect(self::CENRO_PARENT_NAMES)
            ->filter(fn (string $parentName) => $parentName === $penroName)
            ->keys()
            ->all();

        $cenroIds = static::query()
            ->where('office_types_id', 3)
            ->get(['id', 'name'])
            ->filter(fn (Office $office) => in_array(
                strtoupper(trim((string) $office->name)),
                $cenroNames,
                true
            ))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        return array_values(array_unique([$penroOfficeId, ...$cenroIds]));
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
