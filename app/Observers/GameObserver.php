<?php

namespace App\Observers;

use App\Enums\CompetitionStatus;
use App\Enums\GameStatus;
use App\Enums\PhaseStatus;
use App\Models\Game;
use App\Services\StandingService;

class GameObserver
{
    public function saved(Game $game): void
    {
        $phase = $game->phase;

        if ($game->status === GameStatus::Finished) {
            StandingService::recalculate($phase->id);
        }

        // Auto-update phase status
        $allGames = $phase->games;
        $hasOngoing = $allGames->contains(fn ($g) => in_array($g->status, [GameStatus::Ongoing, GameStatus::Finished]));
        $allFinished = $allGames->isNotEmpty() && $allGames->every(fn ($g) => in_array($g->status, [GameStatus::Finished, GameStatus::Cancelled]));

        if ($allFinished) {
            $phase->update(['status' => PhaseStatus::Finished]);
        } elseif ($hasOngoing) {
            $phase->update(['status' => PhaseStatus::Ongoing]);
        }

        // Auto-update competition status
        $competition = $phase->competition;
        $allPhases = $competition->phases;
        $anyOngoing = $allPhases->contains(fn ($p) => $p->status === PhaseStatus::Ongoing);
        $allPhasesFinished = $allPhases->isNotEmpty() && $allPhases->every(fn ($p) => $p->status === PhaseStatus::Finished);

        if ($allPhasesFinished) {
            $competition->update(['status' => CompetitionStatus::Finished]);
        } elseif ($anyOngoing && $competition->status !== CompetitionStatus::Ongoing) {
            $competition->update(['status' => CompetitionStatus::Ongoing]);
        }
    }
}
