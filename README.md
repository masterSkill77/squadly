<p align="center">
  <img src="public/squadly-logo-light.svg" width="320" alt="Squadly Logo">
</p>

<p align="center">
  <strong>Manage less. Play more.</strong><br>
  Application web de gestion de clubs sportifs multi-sports.
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-13-FF2D20?logo=laravel&logoColor=white" alt="Laravel">
  <img src="https://img.shields.io/badge/Vue.js-3-4FC08D?logo=vuedotjs&logoColor=white" alt="Vue.js">
  <img src="https://img.shields.io/badge/Inertia.js-2-9553E9?logo=inertia&logoColor=white" alt="Inertia.js">
  <img src="https://img.shields.io/badge/Tailwind_CSS-4-06B6D4?logo=tailwindcss&logoColor=white" alt="Tailwind CSS">
  <img src="https://img.shields.io/badge/PostgreSQL-16-4169E1?logo=postgresql&logoColor=white" alt="PostgreSQL">
</p>

---

## Présentation

Squadly est une plateforme tout-en-un pensée pour simplifier la gestion quotidienne des clubs sportifs, quel que soit le sport pratiqué : football, basketball, handball, natation, rugby, volleyball…

L'application s'adresse à trois profils :
- **Les présidents de club** qui administrent l'ensemble de la structure (membres, sections, équipes, finances)
- **Les coachs** qui gèrent leurs équipes, planifient les entraînements et matchs, et suivent la présence des joueurs
- **Les membres / joueurs** qui consultent leur planning, répondent aux convocations et accèdent à leurs documents

Squadly remplace les fichiers Excel, les groupes WhatsApp et les échanges d'emails éparpillés par une interface unique, claire et collaborative.

## Fonctionnalités

- **Auth & onboarding guidé** — inscription, création de club en 4 étapes, tour guidé
- **Organisation** — gestion du club, sections (multi-sports), équipes
- **Membres** — CRUD complet, rôles (président / coach / membre), profils sportifs dynamiques, assignation aux équipes
- **Dashboard par rôle** — vues personnalisées admin, coach et membre
- **Système d'invitation** — invitation de membres par email avec lien sécurisé
- **Compétitions** — championnats, tournois, classements en temps réel, calendrier des matchs, stats joueurs

## Stack technique

| Couche | Technologie |
|--------|-------------|
| Backend | Laravel 13 |
| Frontend | Vue 3 (Composition API) |
| Bridge | Inertia.js |
| Style | Tailwind CSS |
| Base de données | PostgreSQL |
| Auth | Laravel Breeze |
| Rôles & permissions | Spatie Laravel Permission |
| Stockage fichiers | Spatie Laravel Media Library (Cloudflare R2) |

## Prérequis

- PHP 8.2+
- Node.js 18+
- PostgreSQL
- Composer

## Installation

```bash
# Cloner le projet
git clone <repo-url> squadly
cd squadly

# Installer les dépendances
composer install
npm install

# Configurer l'environnement
cp .env.example .env
php artisan key:generate

# Configurer la base de données dans .env puis :
php artisan migrate --seed

# Lancer l'application
composer run dev
```

L'application sera accessible sur `http://localhost:8000`.

## Structure du projet

```
app/
├── Http/Controllers/    # Contrôleurs Inertia
├── Models/              # Eloquent models
├── Services/            # Services métier (SportTemplateService…)
└── Notifications/       # Notifications email

resources/js/
├── Components/          # Composants Vue réutilisables
├── Layouts/             # Layouts (AppLayout, AuthLayout…)
└── Pages/               # Pages Inertia (Dashboard, Club, Members…)
```

## Rôles

| Rôle | Label UI | Périmètre |
|------|----------|-----------|
| `admin_club` | Président | Club entier |
| `coach` | Coach | Ses équipes encadrées |
| `membre` | Membre | Son profil + son planning |
| `organizer_admin` | Organisateur | Son organisation & compétitions |
| `organizer_staff` | Staff | Saisie des scores uniquement |

