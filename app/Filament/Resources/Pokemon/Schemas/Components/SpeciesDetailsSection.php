<?php

namespace App\Filament\Resources\Pokemon\Schemas\Components;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Support\Colors\Color;

class SpeciesDetailsSection
{
    public static function make(): FieldSet
    {
        return FieldSet::make('Species Details')
            ->columns([
                'sm' => 2,
                'md' => 3,
                'lg' => 2,
                'xl' => 4,
            ])
            ->schema([
                TextEntry::make('species.name')
                    ->label('Species')
                    ->formatStateUsing(fn ($state) => ucwords($state))
                    ->placeholder('-'),
                TextEntry::make('height')
                    ->numeric()
                    ->formatStateUsing(fn($state) => number_format($state / 10, 1) . " m")
                    ->placeholder('-'),


                TextEntry::make('weight')
                    ->numeric()
                    ->formatStateUsing(fn($state) => number_format($state / 10, 1) . " kg")
                    ->placeholder('-'),
                TextEntry::make('base_experience')
                    ->numeric()
                    ->formatStateUsing(fn($state) => $state . " points")
                    ->placeholder('-')
                    ->label('Base XP'),
                TextEntry::make('species.generation')
                    ->label('Generation')
                    ->formatStateUsing(fn($state) => ucwords(str_replace('-', ' ', $state)))
                    ->badge()
                    ->placeholder('-'),
                TextEntry::make('species.habitat')
                    ->label('Habitat')
                    ->formatStateUsing(fn($state) => ucwords( $state))
                    ->placeholder('-'),
                TextEntry::make('species.color')
                    ->label('Color')
                    ->formatStateUsing(fn($state) => ucwords($state))
                    ->badge()
                    ->color(fn($state) => match(strtolower($state)) {
                        'black' => Color::Slate,
                        'blue' => Color::Blue,
                        'brown' => Color::Amber,
                        'gray' => Color::Gray,
                        'green' => Color::Green,
                        'pink' => Color::Pink,
                        'purple' => Color::Purple,
                        'red' => Color::Red,
                        'white' => Color::Zinc,
                        'yellow' => Color::Yellow,
                        default => Color::Neutral,
                    })
                    ->placeholder('-'),
                TextEntry::make('species.shape')
                    ->label('Shape')
                    ->formatStateUsing(fn($state) => ucwords($state))
                    ->placeholder('-'),
                TextEntry::make('species.capture_rate')
                    ->label('Capture Rate')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('species.base_happiness')
                    ->label('Base Happiness')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('species.hatch_counter')
                    ->label('Hatch Counter')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('species.gender_rate')
                    ->label('Gender Rate')
                    ->numeric()
                    ->formatStateUsing(function ($state) {
                        if ($state === -1) return 'Genderless';
                        if ($state === null) return '-';
                        $femaleChance = ($state / 8) * 100;
                        return number_format($femaleChance, 1) . '% ♀';
                    })
                    ->placeholder('-'),
                IconEntry::make('species.is_baby')
                    ->label('Baby')
                    ->boolean(),
                IconEntry::make('species.is_legendary')
                    ->label('Legendary')
                    ->boolean(),
                IconEntry::make('species.is_mythical')
                    ->label('Mythical')
                    ->boolean(),
            ]);
    }
}
