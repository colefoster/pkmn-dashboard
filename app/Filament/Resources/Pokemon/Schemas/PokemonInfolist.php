<?php

namespace App\Filament\Resources\Pokemon\Schemas;

use App\Filament\Resources\Pokemon\Schemas\Components\PokemonMovesRepeatable;
use App\Livewire\PokemonMovesTable;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PokemonInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Sprites')
                    ->heading('')
                    ->hiddenLabel()
                    ->columns([
                        'sm' => 4,
                        'md' => 4,
                        'lg' => 2,
                        'xl' => 4,
                        '2xl' => 4,
                    ])
                    ->schema([
                        ImageEntry::make('sprite_front_default')
                            ->hiddenLabel(),
                        ImageEntry::make('sprite_front_shiny')
                            ->hiddenLabel(),

                        ImageEntry::make('sprite_back_default')
                            ->hiddenLabel(),
                        ImageEntry::make('sprite_back_shiny')
                            ->hiddenLabel(),
                    ]),
                Section::make('Info')
                    ->columns([
                        'sm' => 4,
                        'md' => 4,
                        'lg' => 2,
                        'xl' => 4,
                    ])
                    ->schema([
                        TextEntry::make('species.name')
                            ->label('Species')
                            ->placeholder('-'),
                        TextEntry::make('height')
                            ->numeric()
                            ->formatStateUsing(fn($state) => number_format($state / 10, 1) . " m")
                            ->placeholder('-'),

                        TextEntry::make('base_experience')
                            ->numeric()
                            ->formatStateUsing(fn($state) => $state . " points")
                            ->placeholder('-')
                            ->label('Base XP'),
                        TextEntry::make('weight')
                            ->numeric()
                            ->formatStateUsing(fn($state) => number_format($state / 10, 1) . " kg")
                            ->placeholder('-'),
                    ]),

                Livewire::make(PokemonMovesTable::class, fn($record) => ['pokemon' => $record])
                    ->columnSpanFull()
            ])
            ->columns([
                'sm' => 1,
                'md' => 1
            ]);
    }
}
