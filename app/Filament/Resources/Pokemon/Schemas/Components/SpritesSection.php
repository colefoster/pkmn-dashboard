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
                    //->belowContent(Schema::center(["Shiny"]))
                    ->hiddenLabel(),

                ImageEntry::make('sprite_back_default')
                    ->hiddenLabel(),
                ImageEntry::make('sprite_back_shiny')
                   //->belowContent(Schema::center(["Shiny"]))

                    ->hiddenLabel(),
            ]);
    }
}
