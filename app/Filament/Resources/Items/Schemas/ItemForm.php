<?php

namespace App\Filament\Resources\Items\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ItemForm
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
                TextInput::make('cost')
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('fling_power')
                    ->numeric(),
                TextInput::make('fling_effect'),
                TextInput::make('category'),
                Textarea::make('effect')
                    ->columnSpanFull(),
                Textarea::make('short_effect')
                    ->columnSpanFull(),
                Textarea::make('flavor_text')
                    ->columnSpanFull(),
                TextInput::make('sprite'),
            ]);
    }
}
