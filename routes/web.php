<?php

use App\Http\Controllers\ClubCompetitionController;
use App\Http\Controllers\ClubController;
use App\Http\Controllers\CompetitionClubController;
use App\Http\Controllers\CompetitionController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\CoachAttendanceController;
use App\Http\Controllers\CoachConvocationController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\CoachEventController;
use App\Http\Controllers\ConvocationController;
use App\Http\Controllers\MemberConvocationController;
use App\Http\Controllers\MemberEventController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\CoachTeamController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\OrganizerDashboardController;
use App\Http\Controllers\PhaseController;
use App\Http\Controllers\PublicClubController;
use App\Http\Controllers\PublicCompetitionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\TeamController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    $clubs = \App\Models\Club::with(['sections.teams' => fn ($q) => $q->withCount('members'), 'sections'])
        ->withCount('memberProfiles')
        ->latest()
        ->limit(8)
        ->get()
        ->map(fn ($c) => [
            'id' => $c->id,
            'name' => $c->name,
            'slug' => $c->slug,
            'city' => $c->city,
            'logo_url' => $c->getFirstMediaUrl('logo') ?: null,
            'members_count' => $c->member_profiles_count,
            'sports' => $c->sections->pluck('sport_type')->unique()->values(),
            'teams' => $c->sections->flatMap->teams->map(fn ($t) => [
                'name' => $t->name,
                'sport' => $t->section->sport_type,
                'members_count' => $t->members_count,
            ])->values(),
        ]);

    $stats = [
        'clubs' => \App\Models\Club::count(),
        'members' => \App\Models\MemberProfile::count(),
        'teams' => \App\Models\Team::count(),
        'competitions' => \App\Models\Competition::count(),
    ];

    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'clubs' => $clubs,
        'stats' => $stats,
    ]);
});

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/search', SearchController::class)->name('search');
    Route::get('/onboarding', [OnboardingController::class, 'index'])->name('onboarding');
    Route::post('/onboarding', [OnboardingController::class, 'store'])->name('onboarding.store');
    Route::post('/onboarding/complete', [OnboardingController::class, 'complete'])->name('onboarding.complete');
    Route::get('/club', [ClubController::class, 'show'])->name('club.show');
    Route::post('/club', [ClubController::class, 'update'])->name('club.update');
    Route::post('/sections', [SectionController::class, 'store'])->name('sections.store');
    Route::delete('/sections/{section}', [SectionController::class, 'destroy'])->name('sections.destroy');
    Route::post('/teams', [TeamController::class, 'store'])->name('teams.store');
    Route::put('/teams/{team}', [TeamController::class, 'update'])->name('teams.update');
    Route::delete('/teams/{team}', [TeamController::class, 'destroy'])->name('teams.destroy');

    Route::get('/members', [MemberController::class, 'index'])->name('members.index');
    Route::get('/members/export/csv', [MemberController::class, 'exportCsv'])->name('members.export.csv');
    Route::get('/members/export/pdf', [MemberController::class, 'exportPdf'])->name('members.export.pdf');
    Route::post('/members', [MemberController::class, 'store'])->name('members.store');
    Route::get('/members/{member}', [MemberController::class, 'show'])->name('members.show');
    Route::put('/members/{member}', [MemberController::class, 'update'])->name('members.update');
    Route::delete('/members/{member}', [MemberController::class, 'destroy'])->name('members.destroy');
    Route::put('/members/{member}/sport-profile', [MemberController::class, 'updateSportProfile'])->name('members.sport-profile');
    Route::put('/members/{member}/teams', [MemberController::class, 'updateTeams'])->name('members.teams');

    Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
    Route::post('/documents', [DocumentController::class, 'store'])->name('documents.store');
    Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');
    Route::get('/documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');

    Route::get('/annonces', [AnnouncementController::class, 'index'])->name('announcements.index');
    Route::post('/annonces', [AnnouncementController::class, 'store'])->name('announcements.store');
    Route::delete('/annonces/{announcement}', [AnnouncementController::class, 'destroy'])->name('announcements.destroy');

    Route::get('/events', [EventController::class, 'index'])->name('events.index');
    Route::post('/events', [EventController::class, 'store'])->name('events.store');
    Route::put('/events/{event}', [EventController::class, 'update'])->name('events.update');
    Route::delete('/events/{event}', [EventController::class, 'destroy'])->name('events.destroy');

    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/attendance/event/{event}', [AttendanceController::class, 'show'])->name('attendance.show');
    Route::post('/attendance/event/{event}', [AttendanceController::class, 'store'])->name('attendance.store');

    Route::get('/convocations/event/{event}', [ConvocationController::class, 'show'])->name('convocations.show');
    Route::post('/convocations/event/{event}', [ConvocationController::class, 'store'])->name('convocations.store');

    Route::get('/mes-evenements', [MemberEventController::class, 'index'])->name('membre.events');
    Route::get('/mes-convocations', [MemberConvocationController::class, 'index'])->name('membre.convocations');
    Route::put('/mes-convocations/{convocation}', [MemberConvocationController::class, 'respond'])->name('membre.convocations.respond');

    // Route::get('/coach/tactique', fn () => \Inertia\Inertia::render('Coach/Tactics'))->name('coach.tactics'); // V2
    Route::get('/coach/effectifs', [CoachTeamController::class, 'index'])->name('coach.effectifs');
    Route::get('/coach/team/{team}', [CoachTeamController::class, 'show'])->name('coach.team');
    Route::post('/coach/team/{team}/players', [CoachTeamController::class, 'addPlayer'])->name('coach.team.add-player');
    Route::get('/coach/team/{team}/player/{user}', [CoachTeamController::class, 'showPlayer'])->name('coach.team.player');
    Route::put('/coach/team/{team}/player/{user}', [CoachTeamController::class, 'updatePlayer'])->name('coach.team.player.update');
    Route::put('/coach/team/{team}/player/{user}/sport-profile', [CoachTeamController::class, 'updatePlayerSportProfile'])->name('coach.team.player.sport-profile');

    Route::get('/coach/events', [CoachEventController::class, 'index'])->name('coach.events');
    Route::post('/coach/events', [CoachEventController::class, 'store'])->name('coach.events.store');
    Route::put('/coach/events/{event}', [CoachEventController::class, 'update'])->name('coach.events.update');
    Route::delete('/coach/events/{event}', [CoachEventController::class, 'destroy'])->name('coach.events.destroy');

    Route::get('/coach/attendance', [CoachAttendanceController::class, 'index'])->name('coach.attendance');
    Route::get('/coach/attendance/event/{event}', [CoachAttendanceController::class, 'show'])->name('coach.attendance.show');
    Route::post('/coach/attendance/event/{event}', [CoachAttendanceController::class, 'store'])->name('coach.attendance.store');

    Route::get('/coach/convocations/event/{event}', [CoachConvocationController::class, 'show'])->name('coach.convocations.show');
    Route::post('/coach/convocations/event/{event}', [CoachConvocationController::class, 'store'])->name('coach.convocations.store');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread', [NotificationController::class, 'unread'])->name('notifications.unread');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Organizer routes
Route::middleware(['auth', 'role:organizer_admin|organizer_staff'])
    ->prefix('organizer')
    ->name('organizer.')
    ->group(function () {
        Route::get('/dashboard', [OrganizerDashboardController::class, 'index'])->name('dashboard');
        Route::post('/setup', [OrganizerDashboardController::class, 'store'])->name('setup');
        Route::resource('competitions', CompetitionController::class);
        Route::get('/competitions/{competition}/phases', [PhaseController::class, 'index'])->name('competitions.phases.index');
        Route::post('/competitions/{competition}/phases', [PhaseController::class, 'store'])->name('competitions.phases.store');
        Route::put('/competitions/{competition}/phases/{phase}', [PhaseController::class, 'update'])->name('competitions.phases.update');
        Route::delete('/competitions/{competition}/phases/{phase}', [PhaseController::class, 'destroy'])->name('competitions.phases.destroy');
        Route::post('/competitions/{competition}/phases/{phase}/generate-schedule', [PhaseController::class, 'generateSchedule'])->name('competitions.phases.generate-schedule');
        Route::get('/competitions/{competition}/draw', [CompetitionController::class, 'draw'])->name('competitions.draw');
        Route::post('/competitions/{competition}/draw', [CompetitionController::class, 'performDraw'])->name('competitions.perform-draw');
        Route::post('/competitions/{competition}/auto-generate', [CompetitionController::class, 'autoGenerate'])->name('competitions.auto-generate');
        Route::get('/competitions/{competition}/clubs', [CompetitionClubController::class, 'index'])->name('competitions.clubs.index');
        Route::post('/competitions/{competition}/clubs', [CompetitionClubController::class, 'store'])->name('competitions.clubs.store');
        Route::post('/competitions/{competition}/clubs/approve-all', [CompetitionClubController::class, 'approveAll'])->name('competitions.clubs.approve-all');
        Route::post('/competitions/{competition}/clubs/register-all', [CompetitionClubController::class, 'registerAll'])->name('competitions.clubs.register-all');
        Route::post('/competitions/{competition}/clubs/bulk-assign', [CompetitionClubController::class, 'bulkAssignPhase'])->name('competitions.clubs.bulk-assign');
        Route::put('/competitions/{competition}/clubs/{competition_club}', [CompetitionClubController::class, 'update'])->name('competitions.clubs.update');
        Route::delete('/competitions/{competition}/clubs/{competition_club}', [CompetitionClubController::class, 'destroy'])->name('competitions.clubs.destroy');
        Route::get('/competitions/{competition}/bracket', [GameController::class, 'bracket'])->name('competitions.bracket');
        Route::get('/competitions/{competition}/matches', [GameController::class, 'index'])->name('competitions.matches.index');
        Route::post('/competitions/{competition}/matches', [GameController::class, 'store'])->name('competitions.matches.store');
        Route::get('/matches/{game}/score', [GameController::class, 'score'])->name('matches.score');
        Route::patch('/matches/{game}/score', [GameController::class, 'updateScore'])->name('matches.update-score');
        Route::put('/matches/{game}', [GameController::class, 'update'])->name('matches.update');
        Route::delete('/matches/{game}', [GameController::class, 'destroy'])->name('matches.destroy');
        Route::post('/competitions/{competition}/simulate-scores', [GameController::class, 'simulateScores'])->name('competitions.simulate-scores');
    });

// Public club pages
Route::get('/clubs', [PublicClubController::class, 'index'])->name('clubs.index');
Route::get('/clubs/{club:slug}', [PublicClubController::class, 'show'])->name('clubs.show');

// Public competition pages
Route::get('/competitions', [PublicCompetitionController::class, 'index'])->name('competitions.index');
Route::get('/competitions/{competition}', [PublicCompetitionController::class, 'show'])->name('competitions.show');
Route::get('/competitions/{competition}/standings', [PublicCompetitionController::class, 'standings'])->name('competitions.standings');
Route::get('/competitions/{competition}/bracket', [PublicCompetitionController::class, 'bracket'])->name('competitions.bracket');

// Club competition pages
Route::middleware('auth')->group(function () {
    Route::get('/club/competitions', [ClubCompetitionController::class, 'index'])->name('club.competitions');
    Route::get('/club/competitions/{competition}', [ClubCompetitionController::class, 'show'])->name('club.competitions.show');
    Route::post('/competitions/{competition}/register', [CompetitionClubController::class, 'register'])->name('competitions.register');
});

require __DIR__.'/auth.php';
