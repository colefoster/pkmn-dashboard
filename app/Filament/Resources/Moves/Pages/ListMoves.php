<?php

namespace App\Filament\Resources\Moves\Pages;

use App\Filament\Resources\Moves\MoveResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMoves extends ListRecords
{
    protected static string $resource = MoveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
