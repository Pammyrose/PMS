<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class LegacyPhysicalBuilder extends Builder
{
    public function get($columns = ['*'])
    {
        return parent::get($this->mapSelectedColumns($columns));
    }

    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        return parent::where($this->mapColumn($column), $operator, $value, $boolean);
    }

    public function whereIn($column, $values, $boolean = 'and', $not = false)
    {
        $this->query->whereIn($this->mapColumn($column), $values, $boolean, $not);

        return $this;
    }

    public function whereNull($columns, $boolean = 'and', $not = false)
    {
        $this->query->whereNull($this->mapColumn($columns), $boolean, $not);

        return $this;
    }

    public function whereNotNull($columns, $boolean = 'and')
    {
        return $this->whereNull($columns, $boolean, true);
    }

    public function pluck($column, $key = null)
    {
        return parent::pluck(
            $this->mapColumn($column),
            $key === null ? null : $this->mapColumn($key)
        );
    }

    private function mapSelectedColumns(array|string $columns): array
    {
        $columns = is_array($columns) ? $columns : [$columns];
        $mapped = [];

        foreach ($columns as $column) {
            if ($column === 'values') {
                $mapped = array_merge($mapped, [
                    'user_id',
                    'program_id',
                    'row_id',
                    'indicator_id',
                    'car_totals',
                    'group_totals',
                    'imported_from',
                ]);
                continue;
            }

            $mapped[] = $this->mapColumn($column);
        }

        return array_values(array_unique($mapped));
    }

    private function mapColumn(mixed $column): mixed
    {
        if (is_array($column)) {
            $mapped = [];

            foreach ($column as $key => $value) {
                $mapped[is_string($key) ? $this->mapColumnName($key) : $key] = $value;
            }

            return $mapped;
        }

        return is_string($column) ? $this->mapColumnName($column) : $column;
    }

    private function mapColumnName(string $column): string
    {
        return match ($column) {
            'years' => 'year',
            'office_ids' => 'office_id',
            default => str_ends_with($column, '.years')
                ? substr($column, 0, -strlen('years')) . 'year'
                : (str_ends_with($column, '.office_ids')
                    ? substr($column, 0, -strlen('office_ids')) . 'office_id'
                    : $column),
        };
    }
}
