<?php

namespace App\Jobs;

use App\Models\Game;
use App\Models\Phase;
use App\Services\CompetitionService;
use App\Services\StandingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateScheduleJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 300;

    public function __construct(
        public int $phaseId,
        public array $config = [],
    ) {}

    public function handle(): void
    {
        $phase = Phase::findOrFail($this->phaseId);

        Game::withoutEvents(function () use ($phase) {
            CompetitionService::autoGenerate($phase, $this->config);
        });

        StandingService::initialize($phase->id);
        $phase->update(['status' => 'ongoing']);
    }
}
