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
| Notifications push | OneSignal (V2) |

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
- Pas d'API REST séparée — tout passe par Inertia

---

## Structure du club (hiérarchie)

```
Club
 └── Section (un sport : foot, basket, natation…)
      └── Équipe (seniors, U17, féminines…)
           └── Membre (joueur ou staff)
```

Un membre a un **profil de base commun** + un **profil spécifique par section** (stocké en JSON).
Un membre peut appartenir à plusieurs sections du même club.

---

## Rôles (via Spatie Permission)

| Rôle | Périmètre |
|------|-----------|
| `admin_club` | Club entier |
| `coach` | Son équipe uniquement |
| `membre` | Son profil + son planning |

Permissions additionnelles (flags JSON sur le modèle user) :
- `can_manage_finances`
- `can_manage_documents`
- `can_validate_transfers` (V2)

---

## Modèles principaux

- `Club` — nom, logo, ville
- `Section` — club_id, sport_type, sport_template (JSON)
- `Team` — section_id, nom, catégorie d'âge, saison
- `User` — email, password (via Breeze)
- `MemberProfile` — user_id, club_id, prénom, nom, date de naissance, photo
- `MemberSectionProfile` — user_id, section_id, sport_profile (JSON)
- `TeamMember` — pivot user ↔ team
- `UserRole` — user_id, role, scope_type, scope_id, extra_perms (JSON)
- `Event` — team_id, type (training/match/other), titre, lieu, dates
- `Convocation` — event_id, user_id, status (pending/confirmed/declined)
- `Attendance` — event_id, user_id, status (present/absent/justified)
- `Document` — user_id, club_id, type, media, saison, expires_at, status

---

## Modules V1 (ordre de développement)

1. **Auth + onboarding guidé** — Breeze, création de club en 4 étapes
2. **Membres & équipes** — CRUD complet, profils sport, multi-sections
3. **Planning & convocations** — événements, convocations, réponses joueurs
4. **Présences** — feuille d'appel, taux de présence, historique
5. **Dashboard par rôle** — vues différentes admin / coach / membre
6. **Communication & documents** — annonces, upload fichiers, alertes docs

---

## Onboarding guidé (création de club)

Wizard en 4 étapes après inscription :
1. Infos du club (nom, logo, ville)
2. Sports pratiqués → crée les sections avec template associé
3. Créer les équipes (nom, catégorie d'âge, saison)
4. Inviter les membres (email + rôle assigné)

---

## Templates par sport (exemples)

Stockés en JSON dans `sections.sport_template`. Champs affichés dynamiquement dans les formulaires.

```json
{
  "football": {
    "fields": [
      { "key": "position", "label": "Poste", "type": "select", "options": ["Gardien", "Défenseur", "Milieu", "Attaquant"] },
      { "key": "dominant_foot", "label": "Pied dominant", "type": "select", "options": ["Gauche", "Droit", "Les deux"] },
      { "key": "jersey_number", "label": "Numéro de maillot", "type": "number" },
      { "key": "shoe_size", "label": "Pointure", "type": "number" }
    ]
  },
  "basketball": {
    "fields": [
      { "key": "position", "label": "Poste", "type": "select", "options": ["Meneur", "Arrière", "Ailier", "Ailier fort", "Pivot"] },
      { "key": "jersey_number", "label": "Numéro de maillot", "type": "number" },
      { "key": "dominant_hand", "label": "Main dominante", "type": "select", "options": ["Gauche", "Droite"] }
    ]
  }
}
```

---

## Hors scope V1 → V2

- Cotisations et paiements en ligne
- Statistiques et performances joueurs
- Transferts entre clubs
- Budget du club
- Dashboard super-admin (multi-clubs / SaaS)
- App mobile native
- Chat individuel
