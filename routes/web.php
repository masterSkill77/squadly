<?php

use App\Http\Controllers\ClubController;
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
    Route::post('/club', [ClubController::class, 'update'])->name('club.update');
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

    Route::get('/coach/tactique', fn () => \Inertia\Inertia::render('Coach/Tactics'))->name('coach.tactics');
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

require __DIR__.'/auth.php';
