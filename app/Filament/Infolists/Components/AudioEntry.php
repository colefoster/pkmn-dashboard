<?php

namespace App\Filament\Infolists\Components;

use Filament\Infolists\Components\Entry;
use Hugomyb\FilamentMediaAction\Actions\MediaAction;

class AudioEntry extends Entry
{
    protected string $view = 'filament.infolists.components.audio-entry';


    public function getSound(string $url) :MediaAction
    {
        return MediaAction::make('cry')
            ->iconButton()
            ->icon('heroicon-o-speaker-wave')
            ->autoplay(true)
            ->media($url);
    }
}
