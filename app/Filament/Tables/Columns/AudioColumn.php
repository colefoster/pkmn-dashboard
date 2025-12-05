<?php

namespace App\Filament\Tables\Columns;

use Filament\Tables\Columns\Column;
use Hugomyb\FilamentMediaAction\Actions\MediaAction;

class AudioColumn extends Column
{
    protected string $view = 'filament.tables.columns.audio-column';

    public function getSound(string $url)
    {
        return MediaAction::make('cry')
            ->iconButton()
            ->icon('heroicon-o-speaker-wave')
            ->autoplay()
            ->media($url);
    }
}
