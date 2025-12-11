<?php

namespace App\Console\Commands;

use App\Services\PokeApiImporter;
use Illuminate\Console\Command;

class ImportPokeApiData extends Command
{
    protected $signature = 'pokeapi:import
                            {--delay=100 : Delay between API requests in milliseconds}
                            {--limit=50 : Number of items per page}
                            {--max= : Maximum number of Pokemon to import}
                            {--types : Import Types}
                            {--abilities : Import Abilities}
                            {--moves : Import Moves}
                            {--items : Import Items}
                            {--species : Import Pokemon Species}
                            {--evolution-chains : Import Evolution Chains}
                            {--pokemon : Import Pokemon}';

    protected $description = 'Import Pokemon data from PokeAPI via Service (selective import)';

    public function handle(): int
    {
        $delay = (int) $this->option('delay');
        $limit = (int) $this->option('limit');
        $maxPokemon = $this->option('max') ? (int) $this->option('max') : null;

        // Determine which types to import
        $importTypes = [];
        if ($this->option('types')) $importTypes[] = 'types';
        if ($this->option('abilities')) $importTypes[] = 'abilities';
        if ($this->option('moves')) $importTypes[] = 'moves';
        if ($this->option('items')) $importTypes[] = 'items';
        if ($this->option('species')) $importTypes[] = 'species';
        if ($this->option('evolution-chains')) $importTypes[] = 'evolution_chains';
        if ($this->option('pokemon')) $importTypes[] = 'pokemon';

        $importer = new PokeApiImporter($delay);
        $importer->importAll($maxPokemon, $limit, $importTypes);

        return self::SUCCESS;
    }
}
