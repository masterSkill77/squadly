# Squadly
> "Manage less. Play more."

Application web de gestion de clubs sportifs multi-sports (football, basket, natation, handball…).
Cible : admins de club, coachs, joueurs/membres.

---

## Stack technique

| Couche | Technologie |
|--------|-------------|
| Backend | Laravel 13 |
| Frontend | Vue 3 (Composition API) |
| Bridge | Inertia.js |
| Style | Tailwind CSS |
| Base de données | PostgreSQL |
| Auth | Laravel Breeze (preset Inertia + Vue) |
| Rôles & permissions | Spatie Laravel Permission |
| Stockage fichiers | Spatie Laravel Media Library |
| Notifications email | Laravel Notifications + Mailpit (dev) |

## Packages Laravel installés

- `spatie/laravel-permission` — rôles & permissions
- `spatie/laravel-medialibrary` — gestion de fichiers/médias
- `spatie/laravel-sluggable` — génération de slugs
- `inertiajs/inertia-laravel` — bridge Laravel ↔ Vue
- `tightenco/ziggy` — routes Laravel côté JS
- `laravel/breeze` (dev) — scaffolding auth

---

## Conventions

- Langue du code : anglais (variables, fonctions, migrations, commentaires)
- Langue de l'UI : français
- Style : Tailwind CSS — pas de CSS custom sauf exception
- Composants Vue : Composition API (`<script setup>`)
- Fichiers courts : découper en sous-composants, éviter les fichiers monolithiques
- Pas d'API REST séparée — tout passe par Inertia
- Toasts de confirmation sur chaque action (flash messages via Inertia shared data)
- Pas de Co-Authored-By dans les commits

---

## Rôles (via Spatie Permission)

| Rôle | Label UI | Périmètre | Affichage fiche membre |
|------|----------|-----------|----------------------|
| `admin_club` | Président | Club entier | N/A (c'est le propriétaire) |
| `coach` | Coach | Ses équipes encadrées | Pas de profil sportif, carte "Équipes encadrées" (bleu) |
| `membre` | Membre | Son profil + son planning | Profils sportifs dynamiques + carte "Équipes" (vert) |

Permissions Spatie : `manage_club`, `manage_members`, `manage_teams`, `manage_sections`, `manage_events`, `manage_convocations`, `manage_attendance`, `manage_documents`, `manage_finances`, `view_dashboard`, `respond_convocation`, `view_own_profile`

---

## Modèles principaux (implémentés)

- `Club` — owner_id, name, slug, city, logo_path
- `Section` — club_id, sport_type, sport_template (JSON)
- `Team` — section_id, name, age_category, season
- `User` — email, password, has_completed_onboarding (via Breeze + HasRoles)
- `MemberProfile` — user_id, club_id, first_name, last_name, birth_date, phone, photo (Spatie Media)
- `MemberSectionProfile` — user_id, section_id, sport_profile (JSON dynamique)
- `TeamMember` — pivot user_id ↔ team_id

## Modèles à venir

- `Event` — team_id, type (training/match/other), titre, lieu, dates
- `Convocation` — event_id, user_id, status (pending/confirmed/declined)
- `Attendance` — event_id, user_id, status (present/absent/justified)
- `Document` — user_id, club_id, type, media, saison, expires_at, status

---

## Modules V1 (état d'avancement)

1. ✅ **Auth + onboarding guidé** — Breeze, création de club en 4 étapes, tour guidé
2. ✅ **Organisation** — CRUD Club, Sections (ajout sport), Teams (ajout/edit/delete)
3. ✅ **Membres** — CRUD complet, rôle coach/membre, profils sport dynamiques, assignation équipes
4. ⬜ **Planning & convocations** — événements, convocations, réponses joueurs
5. ⬜ **Présences** — feuille d'appel, taux de présence, historique
6. ✅ **Dashboard par rôle** — vues admin / coach / membre, sidebar verticale collapsible
7. ⬜ **Communication & documents** — annonces, upload fichiers, alertes docs

---

## Structure des pages

- `/` — Landing page (Welcome.vue)
- `/login` — Connexion (split layout + image communauté)
- `/register` — Inscription → redirige vers `/onboarding`
- `/onboarding` — Wizard 4 étapes (club → sports → équipes → récap)
- `/dashboard` — Tableau de bord par rôle (admin/coach/membre)
- `/club` — Gestion du club (sections, équipes, ajout sport)
- `/members` — Liste des membres avec recherche
- `/members/{id}` — Fiche membre (coach: équipes encadrées / membre: profils sportifs)

## Services

- `SportTemplateService` — templates sport centralisés (football, basketball, handball, natation, rugby, volleyball)

---

## Spec complète
Voir `docs/spec-v1.md`
