# OpenApel

**CMS libre pour les associations de parents d'élèves.**

OpenApel donne à chaque association un site public soigné et un back-office simple, sans WordPress ni page Facebook comme seul canal. Une instance = une école. Nom, couleurs, logo, calendrier scolaire : tout se règle dans l'admin, pas dans le code.

> Open-source CMS for French school parent associations. One deploy per school, staff back-office included.

## À qui ça s'adresse

| Public | Accès |
| --- | --- |
| **Familles et visiteurs** | Site public, sans compte |
| **Bénévoles de l'association** | Back-office EasyAdmin (`/admin`) |

Le projet est pensé pour des équipes non techniques : les contenus s'éditent en Markdown (éditeur visuel), les rubriques se montrent ou se masquent, l'identité du site se change depuis « Réglages du site ».

## Fonctionnalités

- **Actualités** — articles publiés ou brouillons, classés par thème
- **Agenda** — événements (fête, réunion, vacances, jour férié…), visibilité publique / masquée / grisée
- **FAQ** — questions fréquentes, filtrables par thème
- **Équipe** — portraits des bénévoles (photo, rôle, bio)
- **Contact** — formulaire public, messages dans l'admin, envoi mail, limitation anti-spam
- **Pages** — bandeaux de rubriques, mentions légales et politique de confidentialité (catalogue figé : le staff édite, il ne crée pas de slugs)
- **Ciblage scolaire** — actualités, événements et FAQ ciblables par niveau ou par classe
- **Identité par instance** — nom, baseline, logo, favicon, couleurs, e-mail, réseaux, bornes d'année scolaire

## Stack

| Couche | Choix |
| --- | --- |
| Backend | PHP 8.3, Symfony 7.4 LTS |
| Base | PostgreSQL 17, Doctrine ORM 3 |
| API | API Platform 4 (lecture publique) |
| Admin | EasyAdmin 4 |
| Frontend | Vue 3, Vite, Vue Router, Pinia, Vuetify 3 |

Routage :

- `/admin/*` — back-office (staff authentifié)
- `/api/*` — API JSON (GET public, écriture réservée au staff)
- `/*` — site public (SPA Vue)

## Prérequis

- PHP 8.3+ avec les extensions habituelles Symfony (`ctype`, `iconv`, `pdo_pgsql`, …)
- [Composer](https://getcomposer.org/)
- PostgreSQL 17
- Node.js 20+ et npm
- Optionnel : [Symfony CLI](https://symfony.com/download) pour le serveur de dev

## Démarrage local

```bash
git clone https://github.com/<org>/OpenApel.git
cd OpenApel

composer install
npm install
```

Copier `.env` vers `.env.local` et renseigner au minimum :

```dotenv
APP_SECRET=<openssl rand -hex 32>
DATABASE_URL="postgresql://USER:PASSWORD@127.0.0.1:5432/openapel?serverVersion=17&charset=utf8"
MAILER_DSN=null://null
```

Puis :

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console app:create-admin-user
```

Deux processus en parallèle :

```bash
# terminal 1 — API + admin + SPA
symfony serve

# terminal 2 — assets Vue (HMR)
npm run dev
```

Le site public est servi sur l'URL Symfony (souvent `https://localhost:8000`). L'admin est sur `/admin`.

Build frontend de production :

```bash
npm run build
```

## Multi-instances

OpenApel n'est pas multi-tenant en base. Chaque école a :

- sa propre base PostgreSQL
- son `.env.local` (domaine, mailer, secrets)
- son répertoire d'uploads
- sa ligne `SiteSettings` (identité visible du site)

Aucune raison sociale n'est figée dans le code.

## Sécurité et RGPD

Les associations gèrent des données de familles, parfois d'enfants. Le projet part de ce constat :

- API publique **en lecture seule** ; l'écriture passe par l'admin authentifié
- formulaire de contact limité (5 envois / 15 min) et CSRF sur les formulaires Twig
- Markdown assaini dans la SPA (`markdown-it` sans HTML brut, puis DOMPurify)
- pages juridiques prévues dans le catalogue (`mentions-legales`, `politique-de-confidentialite`)
- pas d'analytics tiers dans le cœur du projet
- pas d'indexation par les moteurs de recherche ni d'opt-in crawl IA (`robots.txt`, meta noindex, en-tête `X-Robots-Tag`)

À la charge de chaque instance : hébergement, mentions légales réelles, et autorisations parentales avant publication de photos d'enfants.

## Tests

```bash
# PHP
vendor/bin/phpunit

# Frontend
npm run test
npm run lint
```

## Contribuer

Le projet est en développement actif. Les contributions sont les bienvenues : issues, correctifs, retours d'associations.

- une fonctionnalité = une branche `feat/nom-court`
- commits en anglais, [Conventional Commits](https://www.conventionalcommits.org/) (`feat:`, `fix:`, `docs:`, …)
- pas de commit direct sur `main`
- ne jamais versionner `.env.local`, secrets, `vendor/`, `node_modules/`

Le détail des conventions (PHP, Vue, API) est dans [`CLAUDE.md`](./CLAUDE.md).

## Licence

[MIT](./LICENSE)
