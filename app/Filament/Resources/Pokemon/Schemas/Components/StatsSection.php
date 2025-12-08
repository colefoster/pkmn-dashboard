<?php

namespace App\Filament\Resources\Pokemon\Schemas\Components;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Support\Enums\FontWeight;

class StatsSection
{
    public static function make(): Fieldset
    {
        return FieldSet::make('Stats')
            ->columns([
                'sm' => 2,
                'md' => 4,
                'lg' => 4,
                'xl' => 7,
            ])
            ->schema([
                TextEntry::make('total_base_stat')
                    ->weight(FontWeight::   Bold)
                    ->label('Total')
                    ->numeric()
                    ->placeholder('-'),

                TextEntry::make('hp_stat')
                    ->label('HP')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('attack_stat')
                    ->label('ATK')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('defense_stat')
                    ->label('DEF')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('special_attack_stat')
                    ->label('SPA')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('special_defense_stat')
                    ->label('SPD')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('speed_stat')
                    ->label('SPE')
                    ->numeric()
                    ->placeholder('-'),
            ]);

    }
}
