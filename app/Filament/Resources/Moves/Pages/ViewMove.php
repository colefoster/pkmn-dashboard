<?php

namespace App\Filament\Resources\Moves\Pages;

use App\Filament\Resources\Moves\MoveResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMove extends ViewRecord
{
    protected static string $resource = MoveResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
