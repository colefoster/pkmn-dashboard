<?php

namespace App\Filament\Resources\Pokemon\Schemas\Components;

use Filament\Infolists\Components\ImageEntry;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SpritesSection
{
    public static function make(): FieldSet
    {
        return FieldSet::make('Sprites')
            ->columns([
                'sm' => 2,
                'md' => 2,
                'lg' => 2,
                'xl' => 2,
                '2xl' => 2,
            ])
            ->schema([
                ImageEntry::make('sprite_front_default')
                    ->hiddenLabel(),

            ]);
    }
}
