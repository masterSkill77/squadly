<?php

namespace App\Services;

use App\Enums\CompetitionClubStatus;
use App\Models\Competition;
use App\Models\CompetitionClub;
use App\Models\Game;
use App\Models\Phase;
use Carbon\Carbon;

class CompetitionService
{
    /**
     * Perform a random draw: shuffle approved clubs and assign them to phases.
     * Returns the draw result for animation purposes.
     */
    public static function draw(Competition $competition): array
    {
        $clubs = $competition->competitionClubs()
            ->where('status', CompetitionClubStatus::Approved)
            ->with('club')
            ->get();

        $shuffled = $clubs->shuffle()->values();
        $phases = $competition->phases()->orderBy('order')->get();

        if ($phases->isEmpty() || $shuffled->isEmpty()) {
            return ['phases' => [], 'draws' => []];
        }

        $draws = [];
        $perPhase = (int) ceil($shuffled->count() / $phases->count());

        foreach ($phases as $i => $phase) {
            $chunk = $shuffled->slice($i * $perPhase, $perPhase);
            $phaseDraws = [];

            foreach ($chunk as $cc) {
                $cc->update(['phase_id' => $phase->id]);
                $phaseDraws[] = [
                    'club_id' => $cc->club_id,
                    'club_name' => $cc->club->name,
                    'club_city' => $cc->club->city,
                ];
            }

            $draws[] = [
                'phase_id' => $phase->id,
                'phase_name' => $phase->name,
                'clubs' => $phaseDraws,
            ];
        }

        return ['phases' => $draws, 'total' => $shuffled->count()];
    }

    /**
     * Generate a knockout bracket for a phase.
     * Clubs are paired randomly: 1v2, 3v4, etc.
     */
    public static function generateKnockout(Phase $phase, array $config = []): void
    {
        $clubIds = CompetitionClub::where('phase_id', $phase->id)
            ->where('status', CompetitionClubStatus::Approved)
            ->pluck('club_id')
            ->shuffle()
            ->toArray();

        if (count($clubIds) < 2) {
            return;
        }

        Game::where('phase_id', $phase->id)->delete();

        $competition = $phase->competition;
        $parallel = $config['parallel_matches'] ?? 1;
        $interval = $config['interval_minutes'] ?? 120;
        $startTime = $config['start_time'] ?? '09:00';
        $daysBetween = $config['days_between'] ?? 7;

        $baseDate = self::buildStartDate($competition->starts_at, $startTime);
        $slotIndex = 0;

        for ($i = 0; $i + 1 < count($clubIds); $i += 2) {
            $scheduledAt = self::calculateSlot($baseDate, $slotIndex, $parallel, $interval, $daysBetween);

            Game::create([
                'phase_id' => $phase->id,
                'home_club_id' => $clubIds[$i],
                'away_club_id' => $clubIds[$i + 1],
                'scheduled_at' => $scheduledAt,
                'status' => 'scheduled',
            ]);

            $slotIndex++;
        }
    }

    /**
     * Generate a round-robin schedule for a given phase.
     */
    public static function generateSchedule(Phase $phase, array $config = []): void
    {
        $clubIds = CompetitionClub::where('phase_id', $phase->id)
            ->where('status', CompetitionClubStatus::Approved)
            ->pluck('club_id')
            ->toArray();

        if (count($clubIds) < 2) {
            return;
        }

        Game::where('phase_id', $phase->id)->delete();

        $competition = $phase->competition;
        $parallel = $config['parallel_matches'] ?? 1;
        $interval = $config['interval_minutes'] ?? 120;
        $startTime = $config['start_time'] ?? '09:00';
        $daysBetween = $config['days_between'] ?? 7;

        $baseDate = self::buildStartDate($competition->starts_at, $startTime);
        $slotIndex = 0;

        for ($i = 0; $i < count($clubIds); $i++) {
            for ($j = $i + 1; $j < count($clubIds); $j++) {
                $scheduledAt = self::calculateSlot($baseDate, $slotIndex, $parallel, $interval, $daysBetween);

                Game::create([
                    'phase_id' => $phase->id,
                    'home_club_id' => $clubIds[$i],
                    'away_club_id' => $clubIds[$j],
                    'scheduled_at' => $scheduledAt,
                    'status' => 'scheduled',
                ]);

                $slotIndex++;
            }
        }
    }

    /**
     * Auto-generate matches based on phase type.
     */
    public static function autoGenerate(Phase $phase, array $config = []): void
    {
        match ($phase->type->value) {
            'knockout', 'single' => self::generateKnockout($phase, $config),
            default => self::generateSchedule($phase, $config),
        };
    }

    /**
     * Build a Carbon date from competition start + time string.
     */
    private static function buildStartDate(Carbon|string|null $date, string $time): Carbon
    {
        $base = $date ? Carbon::parse($date) : now();
        [$h, $m] = explode(':', $time);

        return $base->copy()->setTime((int) $h, (int) $m);
    }

    /**
     * Calculate the scheduled time for a match based on its slot index.
     *
     * With parallel_matches=2, interval=120min, days_between=7:
     *   Slot 0 → Day 1 09:00 (match 1)
     *   Slot 1 → Day 1 09:00 (match 2, parallel)
     *   Slot 2 → Day 1 11:00 (match 3)
     *   Slot 3 → Day 1 11:00 (match 4, parallel)
     *   ...when day is full → Day 8 09:00
     */
    private static function calculateSlot(Carbon $baseDate, int $slotIndex, int $parallel, int $interval, int $daysBetween): Carbon
    {
        // Which time slot within a day (accounting for parallel matches)
        $timeSlot = (int) floor($slotIndex / $parallel);

        // Max time slots per day (assuming 14h window: 09:00 → 23:00)
        $maxSlotsPerDay = max(1, (int) floor(14 * 60 / $interval));

        // Which day and which slot within that day
        $dayIndex = (int) floor($timeSlot / $maxSlotsPerDay);
        $slotInDay = $timeSlot % $maxSlotsPerDay;

        return $baseDate->copy()
            ->addDays($dayIndex * $daysBetween)
            ->addMinutes($slotInDay * $interval);
    }
}
