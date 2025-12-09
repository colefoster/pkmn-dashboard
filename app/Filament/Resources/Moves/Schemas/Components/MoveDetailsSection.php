<?php

namespace App\Filament\Resources\Moves\Schemas\Components;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Fieldset;
use Filament\Support\Colors\Color;

class MoveDetailsSection
{
    public static function make(): FieldSet
    {
        return FieldSet::make('Move Details')
            ->columns([
                'sm' => 2,
                'md' => 3,
                'lg' => 2,
                'xl' => 4,
            ])
            ->schema([
                TextEntry::make('name')
                    ->label('Name')
                    ->formatStateUsing(fn ($state) => ucwords(str_replace('-', ' ', $state)))
                    ->placeholder('-'),
                TextEntry::make('power')
                    ->numeric()
                    ->badge()
                    ->color(fn ($state) => match(true) {
                        $state === null => Color::Gray,
                        $state >= 100 => Color::Red,
                        $state >= 80 => Color::Orange,
                        $state >= 60 => Color::Yellow,
                        default => Color::Green,
                    })
                    ->placeholder('-')
                    ->hidden(fn ($state) => $state === null),
                TextEntry::make('pp')
                    ->label('PP')
                    ->numeric()
                    ->placeholder('-')
                    ->hidden(fn ($state) => $state === null),
                TextEntry::make('accuracy')
                    ->numeric()
                    ->formatStateUsing(fn ($state) => $state !== null ? $state . '%' : '-')
                    ->placeholder('-')
                    ->hidden(fn ($state) => $state === null),
                TextEntry::make('priority')
                    ->numeric()
                    ->badge()
                    ->color(fn ($state) => match(true) {
                        $state === null => Color::Gray,
                        $state > 0 => Color::Green,
                        $state < 0 => Color::Red,
                        default => Color::Neutral,
                    })
                    ->placeholder('-')
                    ->hidden(fn ($state) => $state === null || $state === 0),
                TextEntry::make('type.name')
                    ->label('Type')
                    ->badge()
                    ->color(fn($state): string => $state ?? 'gray')
                    ->formatStateUsing(fn($state) => ucfirst($state ?? '-'))
                    ->placeholder('-')
                    ->hidden(fn ($state) => $state === null),
                TextEntry::make('damage_class')
                    ->label('Damage Class')
                    ->badge()
                    ->formatStateUsing(fn ($state) => ucfirst($state ?? '-'))
                    ->color(fn ($state) => match($state) {
                        'physical' => Color::Red,
                        'special' => Color::Blue,
                        'status' => Color::Gray,
                        default => Color::Neutral,
                    })
                    ->placeholder('-')
                    ->hidden(fn ($state) => $state === null),
                TextEntry::make('generation')
                    ->label('Generation')
                    ->badge()
                    ->formatStateUsing(function ($state) {
                        if (!$state) return '-';
                        $gens = [
                            "generation-i" => "Gen 1",
                            "generation-ii" => "Gen 2",
                            "generation-iii" => "Gen 3",
                            "generation-iv" => "Gen 4",
                            "generation-v" => "Gen 5",
                            "generation-vi" => "Gen 6",
                            "generation-vii" => "Gen 7",
                            "generation-viii" => "Gen 8",
                            "generation-ix" => "Gen 9",
                            "generation-x" => "Gen 10",
                        ];
                        return $gens[$state] ?? ucwords(str_replace('-', ' ', $state));
                    })
                    ->placeholder('-')
                    ->hidden(fn ($state) => $state === null),
                TextEntry::make('target')
                    ->formatStateUsing(fn ($state) => ucwords(str_replace('-', ' ', $state ?? '-')))
                    ->placeholder('-')
                    ->hidden(fn ($state) => $state === null),
                TextEntry::make('effect_chance')
                    ->label('Effect Chance')
                    ->numeric()
                    ->formatStateUsing(fn ($state) => $state !== null ? $state . '%' : '-')
                    ->placeholder('-')
                    ->hidden(fn ($state) => $state === null || $state === 0),
                TextEntry::make('contest_type')
                    ->label('Contest Type')
                    ->badge()
                    ->formatStateUsing(fn ($state) => ucfirst($state ?? '-'))
                    ->placeholder('-')
                    ->hidden(fn ($state) => $state === null),
                TextEntry::make('meta_category')
                    ->label('Category')
                    ->formatStateUsing(fn ($state) => ucwords(str_replace('-', ' ', $state ?? '-')))
                    ->placeholder('-')
                    ->hidden(fn ($state) => $state === null),
                TextEntry::make('ailment')
                    ->formatStateUsing(fn ($state) => ucfirst($state ?? '-'))
                    ->placeholder('-')
                    ->hidden(fn ($state) => $state === null || $state === 'none'),
                TextEntry::make('ailment_chance')
                    ->label('Ailment %')
                    ->numeric()
                    ->formatStateUsing(fn ($state) => $state !== null ? $state . '%' : '-')
                    ->placeholder('-')
                    ->hidden(fn ($state) => $state === null || $state === 0),
                TextEntry::make('flinch_chance')
                    ->label('Flinch %')
                    ->numeric()
                    ->formatStateUsing(fn ($state) => $state !== null ? $state . '%' : '-')
                    ->placeholder('-')
                    ->hidden(fn ($state) => $state === null || $state === 0),
                TextEntry::make('crit_rate')
                    ->label('Crit Rate')
                    ->numeric()
                    ->placeholder('-')
                    ->hidden(fn ($state) => $state === null || $state === 0),
                TextEntry::make('stat_chance')
                    ->label('Stat Change %')
                    ->numeric()
                    ->formatStateUsing(fn ($state) => $state !== null ? $state . '%' : '-')
                    ->placeholder('-')
                    ->hidden(fn ($state) => $state === null || $state === 0),
                TextEntry::make('min_hits')
                    ->label('Min Hits')
                    ->numeric()
                    ->placeholder('-')
                    ->hidden(fn ($state) => $state === null),
                TextEntry::make('max_hits')
                    ->label('Max Hits')
                    ->numeric()
                    ->placeholder('-')
                    ->hidden(fn ($state) => $state === null),
                TextEntry::make('min_turns')
                    ->label('Min Turns')
                    ->numeric()
                    ->placeholder('-')
                    ->hidden(fn ($state) => $state === null),
                TextEntry::make('max_turns')
                    ->label('Max Turns')
                    ->numeric()
                    ->placeholder('-')
                    ->hidden(fn ($state) => $state === null),
                TextEntry::make('drain')
                    ->numeric()
                    ->formatStateUsing(fn ($state) => $state !== null ? $state . '%' : '-')
                    ->placeholder('-')
                    ->hidden(fn ($state) => $state === null || $state === 0),
                TextEntry::make('healing')
                    ->numeric()
                    ->formatStateUsing(fn ($state) => $state !== null ? $state . '%' : '-')
                    ->placeholder('-')
                    ->hidden(fn ($state) => $state === null || $state === 0),
            ]);
    }
}
