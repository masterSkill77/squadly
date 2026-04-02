# Squadly V2 — Prompt Claude Code
## Module : Gestion des compétitions

---

## Contexte du projet

Squadly est une app de gestion de clubs sportifs multi-sports (Laravel 11 + Vue 3 + Inertia.js + Tailwind CSS).

La V1 est en production et couvre :
- Gestion des membres, équipes, sections (multi-sports)
- Planning & convocations
- Présences
- Dashboard par rôle (admin_club, coach, membre)
- Documents & communication

La V2 ajoute un nouveau acteur — **l'Organisateur** — et toute la gestion des compétitions.

---

## Nouveau rôle : Organisateur

Un organisateur est une entité distincte d'un club (ligue, fédération, association sportive).
Il a son propre compte Squadly et son propre dashboard.

### Modèle `Organizer`

```php
organizers (
  id uuid PK,
  name string,
  logo_url string nullable,
  sport_type string,        -- foot, basket, natation...
  contact_email string,
  city string nullable,
  created_by uuid FK users,
  created_at, updated_at
)

organizer_users (           -- membres de l'organisation
  organizer_id uuid FK,
  user_id uuid FK,
  role enum('admin','staff'),
  created_at
)
```

### Permissions organisateur (via Spatie Permission)

Ajoute ces rôles :
- `organizer_admin` — accès complet à son organisation
- `organizer_staff` — saisie des scores et résultats uniquement

---

## Modèles V2 à créer

### `Competition`

```php
competitions (
  id uuid PK,
  organizer_id uuid FK,
  name string,              -- ex: "Championnat National 2025"
  sport_type string,
  season string,            -- ex: "2024-2025"
  format enum('league','cup','group+knockout','custom'),
  status enum('draft','open','ongoing','finished'),
  description text nullable,
  rules jsonb nullable,     -- règles spécifiques (points victoire, etc.)
  starts_at date,
  ends_at date,
  created_at, updated_at
)
```

### `Phase`

```php
phases (
  id uuid PK,
  competition_id uuid FK,
  name string,              -- ex: "Poule A", "Demi-finales", "Finale"
  type enum('group','knockout','round_robin','single'),
  order integer,            -- ordre d'affichage
  status enum('pending','ongoing','finished'),
  created_at, updated_at
)
```

### `CompetitionClub` (inscriptions)

```php
competition_clubs (
  id uuid PK,
  competition_id uuid FK,
  club_id uuid FK,
  phase_id uuid FK nullable, -- groupe/poule affecté
  status enum('pending','approved','rejected'),
  registered_at timestamp,
  approved_at timestamp nullable
)
```

### `Match`

```php
matches (
  id uuid PK,
  phase_id uuid FK,
  home_club_id uuid FK clubs,
  away_club_id uuid FK clubs,
  scheduled_at timestamp,
  location string nullable,
  status enum('scheduled','ongoing','finished','cancelled'),
  home_score integer nullable,
  away_score integer nullable,
  notes text nullable,
  created_at, updated_at
)
```

### `PlayerStat`

```php
player_stats (
  id uuid PK,
  match_id uuid FK,
  user_id uuid FK,
  club_id uuid FK,
  goals integer default 0,
  assists integer default 0,
  yellow_cards integer default 0,
  red_cards integer default 0,
  minutes_played integer nullable,
  extra jsonb nullable,     -- stats spécifiques au sport (rebonds basket, etc.)
  created_at, updated_at
)
```

### `Standing` (classement — calculé automatiquement)

```php
standings (
  id uuid PK,
  phase_id uuid FK,
  club_id uuid FK,
  played integer default 0,
  won integer default 0,
  drawn integer default 0,
  lost integer default 0,
  goals_for integer default 0,
  goals_against integer default 0,
  points integer default 0,
  updated_at timestamp
)
```

---

## Logique métier à implémenter

### Calcul automatique du classement

À chaque fois qu'un score est saisi ou modifié, recalculer automatiquement le classement de la phase concernée.

```php
// Règles par défaut (configurables via competition.rules JSON)
Victoire = 3 points
Nul = 1 point
Défaite = 0 point
Différence de buts = goals_for - goals_against
```

Implémente un `Observer` sur le modèle `Match` :

```php
class MatchObserver {
  public function saved(Match $match) {
    if ($match->status === 'finished') {
      StandingService::recalculate($match->phase_id);
    }
  }
}
```

### Inscription d'un club à une compétition

1. Le club soumet une demande d'inscription
2. L'organisateur approuve ou rejette
3. Une fois approuvé, le club apparaît dans la compétition
4. L'organisateur affecte le club à une phase/poule

### Notification automatique

Envoyer un email (via le mailer Laravel configuré) :
- Au club quand son inscription est approuvée/rejetée
- Aux clubs participants quand un match est programmé
- Aux clubs quand un score est saisi

---

## Vues & pages Inertia + Vue à créer

### Dashboard Organisateur

**Route** : `/organizer/dashboard`

Widgets :
- Compétitions actives (statut, nombre de clubs inscrits)
- Prochains matchs à programmer
- Inscriptions en attente de validation
- Classements en temps réel

### Gestion des compétitions

- `/organizer/competitions` — liste des compétitions
- `/organizer/competitions/create` — créer une compétition (wizard 3 étapes : infos → phases → clubs)
- `/organizer/competitions/{id}` — détail compétition
- `/organizer/competitions/{id}/phases` — gérer les phases
- `/organizer/competitions/{id}/clubs` — gérer les inscriptions
- `/organizer/competitions/{id}/matches` — calendrier des matchs
- `/organizer/competitions/{id}/standings` — classements

### Saisie des scores

- `/organizer/matches/{id}/score` — formulaire simple : score domicile / extérieur + stats joueurs

### Vue publique (accessible sans connexion)

- `/competitions/{id}` — page publique d'une compétition (classement + calendrier)
- `/competitions/{id}/matches` — tous les matchs
- `/competitions/{id}/standings` — classement par phase

### Vue club (dans le dashboard club existant)

Ajouter un onglet **Compétitions** dans le dashboard club :
- Compétitions auxquelles le club participe
- Ses prochains matchs
- Son classement dans chaque compétition
- Ses stats joueurs

---

## Routes à ajouter dans `routes/web.php`

```php
// Organisateur
Route::middleware(['auth', 'role:organizer_admin|organizer_staff'])
  ->prefix('organizer')
  ->group(function () {
    Route::get('/dashboard', [OrganizerDashboardController::class, 'index']);
    Route::resource('competitions', CompetitionController::class);
    Route::resource('competitions.phases', PhaseController::class);
    Route::resource('competitions.clubs', CompetitionClubController::class);
    Route::resource('phases.matches', MatchController::class);
    Route::patch('matches/{match}/score', [MatchController::class, 'updateScore']);
  });

// Public
Route::get('/competitions/{competition}', [PublicCompetitionController::class, 'show']);
Route::get('/competitions/{competition}/standings', [PublicCompetitionController::class, 'standings']);

// Club — compétitions auxquelles il participe
Route::middleware(['auth'])->group(function () {
  Route::get('/club/competitions', [ClubCompetitionController::class, 'index']);
});
```

---

## Services à créer

```php
// app/Services/StandingService.php
class StandingService {
  public static function recalculate(string $phaseId): void {
    // 1. Récupère tous les matchs finished de la phase
    // 2. Regroupe par club
    // 3. Calcule V/N/D/Pts/BF/BC pour chaque club
    // 4. Upsert dans standings
  }
}

// app/Services/CompetitionService.php
class CompetitionService {
  public static function generateSchedule(Phase $phase): void {
    // Génère automatiquement le calendrier round-robin
    // pour une phase de type group ou round_robin
  }
}
```

---

## Composants Vue à créer

```
resources/js/Components/Competition/
  ├── StandingsTable.vue      -- tableau de classement
  ├── MatchCard.vue           -- carte d'un match (score, date, lieu)
  ├── MatchCalendar.vue       -- calendrier des matchs
  ├── ScoreForm.vue           -- formulaire saisie score
  ├── PlayerStatsForm.vue     -- stats joueurs après un match
  └── CompetitionBadge.vue    -- badge statut compétition

resources/js/Pages/Organizer/
  ├── Dashboard.vue
  ├── Competitions/
  │   ├── Index.vue
  │   ├── Create.vue          -- wizard 3 étapes
  │   ├── Show.vue
  │   ├── Phases.vue
  │   ├── Clubs.vue
  │   ├── Matches.vue
  │   └── Standings.vue
  └── Matches/
      └── Score.vue

resources/js/Pages/Public/
  └── Competition/
      ├── Show.vue
      └── Standings.vue
```

---

## Migrations à créer (dans l'ordre)

```
create_organizers_table
create_organizer_users_table
create_competitions_table
create_phases_table
create_competition_clubs_table
create_matches_table
create_player_stats_table
create_standings_table
```

---

## Seeders à créer

```php
OrganizerSeeder       -- 1 organisateur de démo
CompetitionSeeder     -- 1 compétition avec 2 phases, 4 clubs, 6 matchs avec scores
```

---

## Ordre de développement recommandé

1. Migrations + Modèles + Relations
2. OrganizerSeeder + CompetitionSeeder
3. StandingService (logique métier centrale)
4. MatchObserver (recalcul auto classement)
5. Controllers (Organizer)
6. Pages Vue (dashboard → compétitions → matchs → classement)
7. Vue publique compétition
8. Intégration dans dashboard club existant
9. Notifications email

---

## Stack & conventions (rappel)

- Laravel 11 + Vue 3 (Composition API `<script setup>`) + Inertia.js
- Tailwind CSS — pas de CSS custom
- Spatie Laravel Permission pour les rôles
- SQLite en dev → PostgreSQL en prod (Railway)
- Langue UI : français / Langue code : anglais
- UUIDs pour tous les IDs
