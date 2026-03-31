# Squadly

> **Manage less. Play more.**

Application web de gestion de clubs sportifs multi-sports (football, basket, natation, handball…).

## Fonctionnalités

- **Auth & onboarding guidé** — inscription, création de club en 4 étapes, tour guidé
- **Organisation** — gestion du club, sections (multi-sports), équipes
- **Membres** — CRUD complet, rôles (président / coach / membre), profils sportifs dynamiques, assignation aux équipes
- **Dashboard par rôle** — vues personnalisées admin, coach et membre
- **Système d'invitation** — invitation de membres par email avec lien sécurisé

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

## Roadmap V1

- [x] Auth + onboarding guidé
- [x] Organisation (club, sections, équipes)
- [x] Gestion des membres
- [x] Dashboard par rôle
- [ ] Planning & convocations
- [ ] Présences
- [ ] Communication & documents
