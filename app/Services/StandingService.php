<?php

namespace App\Services;

use App\Enums\GameStatus;
use App\Models\Game;
use App\Models\Phase;
use App\Models\Standing;

class StandingService
{
    public static function recalculate(int $phaseId): void
    {
        $phase = Phase::with('competition')->findOrFail($phaseId);
        $competition = $phase->competition;

        $pointsWin = $competition->points_for_win;
        $pointsDraw = $competition->points_for_draw;
        $pointsLoss = $competition->points_for_loss;

        $games = Game::where('phase_id', $phaseId)
            ->where('status', GameStatus::Finished)
            ->get();

        $stats = [];

        foreach ($games as $game) {
            foreach ([$game->home_club_id, $game->away_club_id] as $clubId) {
                if (!isset($stats[$clubId])) {
                    $stats[$clubId] = [
                        'played' => 0, 'won' => 0, 'drawn' => 0, 'lost' => 0,
                        'goals_for' => 0, 'goals_against' => 0, 'points' => 0,
                    ];
                }
            }

            $home = $game->home_club_id;
            $away = $game->away_club_id;

            $stats[$home]['played']++;
            $stats[$away]['played']++;
            $stats[$home]['goals_for'] += $game->home_score;
            $stats[$home]['goals_against'] += $game->away_score;
            $stats[$away]['goals_for'] += $game->away_score;
            $stats[$away]['goals_against'] += $game->home_score;

            if ($game->home_score > $game->away_score) {
                $stats[$home]['won']++;
                $stats[$home]['points'] += $pointsWin;
                $stats[$away]['lost']++;
                $stats[$away]['points'] += $pointsLoss;
            } elseif ($game->home_score < $game->away_score) {
                $stats[$away]['won']++;
                $stats[$away]['points'] += $pointsWin;
                $stats[$home]['lost']++;
                $stats[$home]['points'] += $pointsLoss;
            } else {
                $stats[$home]['drawn']++;
                $stats[$away]['drawn']++;
                $stats[$home]['points'] += $pointsDraw;
                $stats[$away]['points'] += $pointsDraw;
            }
        }

        // Delete old standings for this phase, then bulk insert
        Standing::where('phase_id', $phaseId)->delete();

        foreach ($stats as $clubId => $data) {
            Standing::create([
                'phase_id' => $phaseId,
                'club_id' => $clubId,
                ...$data,
            ]);
        }
    }
}
