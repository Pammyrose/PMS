<?php

namespace App\Models\Concerns;

use App\Models\LegacyPhysicalBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;

trait UsesConsolidatedPhysicalTable
{
    protected static function bootUsesConsolidatedPhysicalTable(): void
    {
        static::addGlobalScope('physical_sector', function (Builder $query) {
            $query->where($query->getModel()->qualifyColumn('sector'), static::physicalSector());
        });

        static::creating(function ($model) {
            $model->sector = static::physicalSector();
        });
    }

    public function newEloquentBuilder($query): LegacyPhysicalBuilder
    {
        /** @var QueryBuilder $query */
        return new LegacyPhysicalBuilder($query);
    }

    public function getYearsAttribute(): int
    {
        return (int) ($this->attributes['year'] ?? 0);
    }

    public function setYearsAttribute(mixed $value): void
    {
        $this->attributes['year'] = $value;
    }

    public function getOfficeIdsAttribute(): ?int
    {
        $value = $this->attributes['office_id'] ?? null;

        return $value === null ? null : (int) $value;
    }

    public function setOfficeIdsAttribute(mixed $value): void
    {
        $this->attributes['office_id'] = filled($value) ? (int) $value : null;
    }

    public function getValuesAttribute(): string
    {
        return json_encode([
            'user_id' => $this->attributes['user_id'] ?? null,
            'program_id' => $this->attributes['program_id'] ?? null,
            'row_id' => $this->attributes['row_id'] ?? null,
            'indicator_id' => $this->attributes['indicator_id'] ?? null,
            'car_totals' => $this->decodeArray($this->attributes['car_totals'] ?? null),
            'group_totals' => $this->decodeArray($this->attributes['group_totals'] ?? null),
            'imported_from' => $this->attributes['imported_from'] ?? null,
        ]);
    }

    public function setValuesAttribute(mixed $value): void
    {
        $meta = $this->decodeArray($value);

        foreach (['user_id', 'program_id', 'row_id', 'indicator_id', 'imported_from'] as $key) {
            if (array_key_exists($key, $meta)) {
                $this->attributes[$key] = $meta[$key];
            }
        }

        foreach (['car_totals', 'group_totals'] as $key) {
            if (array_key_exists($key, $meta)) {
                $this->attributes[$key] = json_encode(is_array($meta[$key]) ? $meta[$key] : []);
            }
        }
    }

    protected static function physicalSector(): string
    {
        $className = class_basename(static::class);

        return strtolower((string) strtok($className, '_'));
    }

    private function decodeArray(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (! is_string($value) || trim($value) === '') {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
    }
}
