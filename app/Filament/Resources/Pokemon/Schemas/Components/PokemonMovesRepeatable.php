<?php

namespace App\Filament\Resources\Pokemon\Schemas\Components;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;

class PokemonMovesRepeatable
{
    public static function make(): RepeatableEntry
    {
        return RepeatableEntry::make('moves')
            ->hiddenLabel()
            ->schema([
                TextEntry::make('name')
                    ->hiddenLabel()
                    ->size(TextSize::Large)
                    ->weight(FontWeight::Bold)
                    ->formatStateUsing(fn($state) => str_replace(' ', '-', ucwords(str_replace('-', ' ', $state)))),
                TextEntry::make('type.name')
                    ->label(fn($state): string => 'Type')
                    ->badge()
                    ->color(fn($state): string => $state)
                    ->placeholder('-'),
                TextEntry::make('damage_class')
                    ->label('Class')
                    ->badge()
                    ->color(fn($state): string => $state)
                    ->placeholder('-'),
                TextEntry::make('power')
                    ->label('Power')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('pp')
                    ->label('PP')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('accuracy')
                    ->label('Accuracy')
                    ->numeric()
                    ->formatStateUsing(fn($state) => $state . "%")
                    ->placeholder('-'),
                TextEntry::make('learn_method')
                    ->label('Learn Method')
                    ->getStateUsing(fn($record) => $record->pivot->learn_method)
                    ->badge()
                    ->placeholder('-'),
                TextEntry::make('level_learned_at')
                    ->label('Level')
                    ->getStateUsing(fn($record) => $record->pivot->level_learned_at ?: null)
                    ->numeric()
                    ->placeholder('-'),
            ])
            ->columns(6);
    }
}
