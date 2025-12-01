<?php

namespace App\Console\Commands;

use App\Services\TravelService;
use Illuminate\Console\Command;

class ProcessTravelsCommand extends Command
{
    // Назва, яку ми будемо використовувати для крона
    protected $signature = 'game:process-travels';

    // Опис команди
    protected $description = 'Checks all active player travels and completes those that have arrived.';

    protected TravelService $travelService;

    public function __construct(TravelService $travelService)
    {
        parent::__construct();
        $this->travelService = $travelService;
    }

    /**
     * Виконання консольної команди.
     */
    public function handle(): int
    {
        $this->info('Starting travel processing...');

        $this->travelService->processTravels();

        $this->info('Travel processing completed.');

        return Command::SUCCESS;
    }
}
