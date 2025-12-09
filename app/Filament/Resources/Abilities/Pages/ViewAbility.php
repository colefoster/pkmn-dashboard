<?php

namespace App\Filament\Resources\Abilities\Pages;

use App\Filament\Resources\Abilities\AbilityResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAbility extends ViewRecord
{
    protected static string $resource = AbilityResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
