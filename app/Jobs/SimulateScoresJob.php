<?php

namespace App\Jobs;

use App\Enums\GameStatus;
use App\Enums\PhaseType;
use App\Models\Competition;
use App\Models\Game;
use App\Services\QualificationService;
use App\Services\StandingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SimulateScoresJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 300;

    public function __construct(
        public int $competitionId,
    ) {}

    public function handle(): void
    {
        $competition = Competition::findOrFail($this->competitionId);

        // Disable observer to avoid recalculating standings on every single update
        Game::withoutEvents(function () use ($competition) {
            $this->simulate($competition);
        });
    }

    private function simulate(Competition $competition): void
    {
        $sportType = $competition->sport_type;

        $scoreRanges = [
            'football' => ['min' => 0, 'max' => 4],
            'handball' => ['min' => 15, 'max' => 35],
            'basketball' => ['min' => 60, 'max' => 120],
            'volleyball' => ['min' => 0, 'max' => 3],
            'rugby' => ['min' => 0, 'max' => 40],
        ];

        $range = $scoreRanges[$sportType] ?? ['min' => 0, 'max' => 5];

        $phases = $competition->phases()->orderBy('order')->get();

        foreach ($phases as $phase) {
            $games = $phase->games()
                ->where('status', GameStatus::Scheduled)
                ->whereNotNull('home_club_id')
                ->whereNotNull('away_club_id')
                ->get();

            foreach ($games as $game) {
                $homeScore = rand($range['min'], $range['max']);
                $awayScore = rand($range['min'], $range['max']);

                if ($phase->type === PhaseType::Knockout && $game->round !== null) {
                    while ($homeScore === $awayScore) {
                        $awayScore = rand($range['min'], $range['max']);
                    }
                }

                $game->update([
                    'home_score' => $homeScore,
                    'away_score' => $awayScore,
                    'status' => GameStatus::Finished,
                ]);

                if ($phase->type === PhaseType::Knockout && $game->round !== null) {
                    QualificationService::advanceWinner($game);
                }
            }

            // Recalculate standings once per phase (not per game)
            if (in_array($phase->type, [PhaseType::Group, PhaseType::RoundRobin])) {
                StandingService::recalculate($phase->id);
            }
        }
    }
}
