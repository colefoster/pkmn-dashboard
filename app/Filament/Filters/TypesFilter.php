<?php

namespace App\Filament\Filters;

use App\Models\Type;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\ToggleButtons;
use Filament\Tables\Filters\Filter;

class TypesFilter
{
    /**
     * Create a reusable Types filter with configurable column layout
     *
     * @param int|null $columns Number of columns (2, 3, or 4). Null for single column, 0 for grouped.
     * @return Filter
     */
    public static function make(?int $columns = null): Filter
    {
        return Filter::make('types')
            ->schema([
                Select::make('query_mode')
                    ->label('Filter Mode')
                    ->options([
                        'any' => 'Any of these types',
                        'all' => 'All of these types',
                        'none' => 'None of these types',
                        'only' => 'Only these types ',
                    ])
                    ->default('any')
                    ->native(false),
                self::getToggleButtons($columns)
            ])
            ->query(function ($query, array $data) {
                if (!filled($data['type_ids'])) {
                    return;
                }

                $mode = $data['query_mode'] ?? 'any';
                $typeIds = $data['type_ids'];

                switch ($mode) {
                    case 'any':
                        // Pokemon that have ANY of the selected types
                        $query->whereHas('types', function ($q) use ($typeIds) {
                            $q->whereIn('types.id', $typeIds);
                        });
                        break;

                    case 'all':
                        // Pokemon that have ALL of the selected types (add a whereHas for each type)
                        foreach ($typeIds as $typeId) {
                            $query->whereHas('types', function ($q) use ($typeId) {
                                $q->where('types.id', $typeId);
                            });
                        }
                        break;

                    case 'none':
                        // Pokemon that have NONE of the selected types
                        $query->whereDoesntHave('types', function ($q) use ($typeIds) {
                            $q->whereIn('types.id', $typeIds);
                        });
                        break;

                    case 'only':
                        // Pokemon that have ONLY these types (exact match - all selected types, no extras)
                        // First, ensure they have all the selected types
                        foreach ($typeIds as $typeId) {
                            $query->whereHas('types', function ($q) use ($typeId) {
                                $q->where('types.id', $typeId);
                            });
                        }
                        // Second, ensure they have exactly this many types (no extras)
                        $query->has('types', '=', count($typeIds));
                        break;
                }
            })
            ->indicateUsing(function (array $data): array {
                if (!filled($data['type_ids'])) {
                    return [];
                }

                $types = Type::whereIn('id', $data['type_ids'])->get();
                $mode = $data['query_mode'] ?? 'any';

                $modeLabels = [
                    'any' => 'Any of',
                    'all' => 'All of',
                    'none' => 'None of',
                    'only' => 'Only',
                ];

                $indicators = [
                    \Filament\Tables\Filters\Indicator::make($modeLabels[$mode] . ': ' . $types->pluck('name')->map(fn($n) => ucfirst($n))->join(', '))
                        ->removeField('type_ids', 'query_mode')
                ];

                return $indicators;
            });
    }

    /**
     * Get the ToggleButtons component with configured columns
     *
     * @param int|null $columns
     * @return ToggleButtons
     */
    protected static function getToggleButtons(?int $columns): ToggleButtons
    {
        $toggleButtons = ToggleButtons::make('type_ids')
            ->label('Type')
            ->options(function () {
                return Type::orderBy('name')->pluck('name', 'id')
                    ->mapWithKeys(fn($name, $id) => [$id => ucfirst($name)]);
            })
            ->colors(function () {
                return Type::orderBy('name')->pluck('name', 'id');
            })
            ->multiple();

        switch ($columns) {
            case 0:
                $toggleButtons = $toggleButtons->grouped();
                break;
            case 4:
            case 3:
            case 2:
                $toggleButtons = $toggleButtons->columns($columns);
                break;
            default:
            case 1:
                break;
        }

        return $toggleButtons;
    }
}
