<?php

namespace App\Filament\Resources\Abilities\Pages;

use App\Filament\Resources\Abilities\AbilityResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAbilities extends ListRecords
{
    protected static string $resource = AbilityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
