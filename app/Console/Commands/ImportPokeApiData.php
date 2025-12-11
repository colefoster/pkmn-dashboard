<?php

namespace App\Console\Commands;

use App\Services\PokeApiImporter;
use Illuminate\Console\Command;

class ImportPokeApiData extends Command
{
    protected $signature = 'pokeapi:import
                            {--delay=100 : Delay between API requests in milliseconds}
                            {--limit=50 : Number of items per page}
                            {--max= : Maximum number of Pokemon to import}';

    protected $description = 'Import Pokemon data from PokeAPI via Service';

    public function handle(): int
    {
        $delay = (int) $this->option('delay');
        $limit = (int) $this->option('limit');
        $maxPokemon = $this->option('max') ? (int) $this->option('max') : null;

        $importer = new PokeApiImporter($delay);
        $importer->importAll($maxPokemon, $limit);

        return self::SUCCESS;
    }
}
