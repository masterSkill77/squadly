<?php

use App\Http\Controllers\ClubController;
use App\Http\Controllers\CoachEventController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\CoachTeamController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\TeamController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
});

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/onboarding', [OnboardingController::class, 'index'])->name('onboarding');
    Route::post('/onboarding', [OnboardingController::class, 'store'])->name('onboarding.store');
    Route::post('/onboarding/complete', [OnboardingController::class, 'complete'])->name('onboarding.complete');
    Route::get('/club', [ClubController::class, 'show'])->name('club.show');
    Route::post('/sections', [SectionController::class, 'store'])->name('sections.store');
    Route::delete('/sections/{section}', [SectionController::class, 'destroy'])->name('sections.destroy');
    Route::post('/teams', [TeamController::class, 'store'])->name('teams.store');
    Route::put('/teams/{team}', [TeamController::class, 'update'])->name('teams.update');
    Route::delete('/teams/{team}', [TeamController::class, 'destroy'])->name('teams.destroy');

    Route::get('/members', [MemberController::class, 'index'])->name('members.index');
    Route::post('/members', [MemberController::class, 'store'])->name('members.store');
    Route::get('/members/{member}', [MemberController::class, 'show'])->name('members.show');
    Route::put('/members/{member}', [MemberController::class, 'update'])->name('members.update');
    Route::delete('/members/{member}', [MemberController::class, 'destroy'])->name('members.destroy');
    Route::put('/members/{member}/sport-profile', [MemberController::class, 'updateSportProfile'])->name('members.sport-profile');
    Route::put('/members/{member}/teams', [MemberController::class, 'updateTeams'])->name('members.teams');

    Route::get('/events', [EventController::class, 'index'])->name('events.index');
    Route::post('/events', [EventController::class, 'store'])->name('events.store');
    Route::put('/events/{event}', [EventController::class, 'update'])->name('events.update');
    Route::delete('/events/{event}', [EventController::class, 'destroy'])->name('events.destroy');

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

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
