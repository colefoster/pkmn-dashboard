<?php

namespace App\Filament\Resources\Abilities\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AbilityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('api_id')
                    ->required()
                    ->numeric(),
                TextInput::make('name')
                    ->required(),
                Textarea::make('effect')
                    ->columnSpanFull(),
                Textarea::make('short_effect')
                    ->columnSpanFull(),
                Toggle::make('is_main_series')
                    ->required(),
            ]);
    }
}
