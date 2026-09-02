# CLAUDE.md

Ce fichier fournit à Claude Code le contexte nécessaire pour travailler efficacement sur ce projet.

## Présentation du projet

CMS pour une association de parents d'élèves. L'objectif est de pouvoir déployer plusieurs instances (une par école) avec une configuration par environnement.

**Fonctionnalités principales :**
- Actualités / articles
- Événements / calendrier
- Documents téléchargeables
- Pages statiques (qui sommes-nous, contact, etc.)
- Formulaire de contact
- Galerie photos

**Utilisateurs :**
- **Staff (bénévoles de l'asso)** : authentification requise, accès au back-office EasyAdmin
- **Public (parents, visiteurs)** : pas d'authentification, consultation uniquement

## Stack technique

| Couche | Technologie | Version |
|--------|-------------|---------|
| Runtime PHP | PHP 8.3 | installation locale |
| Framework backend | Symfony | 7.4 LTS |
| Langage backend | PHP | 8.3 |
| Base de données | PostgreSQL | 17 |
| ORM | Doctrine ORM | 3.x |
| API | API Platform | 4.x |
| Back-office | EasyAdmin | 4.x |
| Frontend public | Vue.js (SPA) | 3.x (Composition API) |
| Build tool frontend | Vite | dernière stable |
| Routing SPA | Vue Router | 4.x |
| State management | Pinia | 3.x |

**Bundles Symfony clés :**
- `api-platform/core` — API REST auto-générée
- `easycorp/easyadmin-bundle` — back-office staff
- `vich/uploader-bundle` — upload de fichiers (photos, documents)
- `liip/imagine-bundle` — redimensionnement d'images
- `league/commonmark` — parsing Markdown côté serveur
- `symfony/security-bundle` — auth staff
- `symfony/mailer` — envoi formulaire contact

**Libs frontend clés :**
- `markdown-it` + `dompurify` — rendu Markdown sécurisé côté SPA
- `axios` ou `fetch` natif pour les appels API
- `@fortawesome/vue-fontawesome` + `@fortawesome/fontawesome-svg-core` + `@fortawesome/free-solid-svg-icons` — icônes via composant `<FontAwesomeIcon>`

## Architecture

```
asso-cms/
├── assets/               # Frontend Vue 3 SPA
│   ├── vue/
│   │   ├── components/
│   │   ├── pages/
│   │   ├── stores/       # Pinia
│   │   ├── router/
│   │   └── api/          # Client API
│   ├── styles/
│   └── app.js
├── config/               # Config Symfony
├── src/                  # Code PHP
│   ├── Controller/
│   │   ├── Admin/        # Controllers EasyAdmin
│   │   └── Api/          # Endpoints custom hors API Platform
│   ├── Entity/
│   ├── Repository/
│   ├── Service/
│   └── State/            # State providers/processors API Platform
├── templates/            # Templates Twig (admin + base SPA)
├── migrations/           # Migrations Doctrine
├── tests/
└── vite.config.js
```

**Routing global :**
- `/admin/*` → EasyAdmin (Twig, staff authentifié)
- `/api/*` → API Platform (JSON, public en lecture)
- `/*` → SPA Vue 3 (catch-all, sert `index.html`)

## Conventions de code

### PHP / Symfony

- **PHP 8.4 strict** : `declare(strict_types=1);` en tête de chaque fichier
- **Typage** : tous les paramètres, retours et propriétés typés. Pas de `mixed` sauf cas exceptionnel justifié.
- **Attributs PHP** plutôt qu'annotations (jamais de docblock pour la config)
- **Constructor property promotion** systématique
- **Readonly** sur les propriétés immuables (DTOs, value objects)
- **Énums natifs** pour les statuts, types, rôles
- Pas de logique métier dans les controllers : déléguer aux services
- Nommage : `PascalCase` pour les classes, `camelCase` pour les méthodes et propriétés
- Services en injection de dépendances par constructeur (pas de `ContainerAware`)
- Repositories : utiliser `QueryBuilder` pour les requêtes complexes, pas de DQL en string

### API Platform

- Déclarer les ressources via attributs PHP (`#[ApiResource]`)
- Utiliser des **groupes de sérialisation** explicites (`article:read`, `article:write`)
- Définir les **opérations** explicitement (pas de CRUD automatique en bloc)
- Endpoints publics en GET, écriture réservée au staff via `security: "is_granted('ROLE_STAFF')"`
- Pagination activée par défaut (30 items par page)
- Filtres déclarés via attributs (`SearchFilter`, `DateFilter`, etc.)

### Doctrine

- Migrations versionnées, **jamais de modification directe du schéma**
- Une migration par changement logique (pas de migrations fourre-tout)
- Relations : préférer `LAZY` par défaut, `EAGER` uniquement si justifié
- Index explicites sur les colonnes filtrées/triées fréquemment
- UUID v7 pour les identifiants publics, ID auto-incrémenté en interne

### Vue 3

- **Composition API exclusivement** (pas d'Options API)
- `<script setup lang="ts">` (TypeScript activé)
- Un composant par fichier, nom en `PascalCase.vue`
- Props et émits **typés explicitement** via `defineProps<T>()` et `defineEmits<T>()`
- Stores Pinia avec syntaxe `setup` (pas la syntaxe options)
- Pas de logique dans les templates au-delà de ce qui est trivial : extraire en `computed`
- Les appels API passent **toujours** par les modules dans `assets/vue/api/`, jamais directement dans les composants
- Icônes via le composant `<FontAwesomeIcon :icon="['fas', 'icon-name']" />` — enregistrer les icônes utilisées dans `assets/vue/plugins/fontawesome.ts`, pas d'import `library.add` dispersé dans les composants

### CSS

- Utiliser **Vuetify 3** (Material Design component library)
- Composants design system dans `assets/vue/components/ui/`
- Pas de CSS inline sauf valeurs dynamiques calculées

### Git

- Commits en **anglais**, format conventional commits : `feat:`, `fix:`, `refactor:`, `docs:`, `chore:`, `test:`
- Une fonctionnalité = une branche `feat/nom-court`
- Pas de commit direct sur `main`
- Messages de commit clairs, présent de l'indicatif : `add article entity`, pas `added` ni `adding`

## Commandes utiles

### Symfony

```bash
# Console Symfony
php bin/console <commande>

# Créer une migration
php bin/console make:migration

# Exécuter les migrations
php bin/console doctrine:migrations:migrate --no-interaction

# Vider le cache
php bin/console cache:clear

# Créer un user staff
php bin/console app:create-staff-user
```

### Composer / NPM

```bash
# Installer une dépendance PHP
composer require <package>

# Installer une dépendance JS
npm install <package>

# Build frontend dev (watch)
npm run dev

# Build frontend prod
npm run build
```

### Tests

```bash
# PHPUnit
bin/phpunit

# Avec couverture
bin/phpunit --coverage-html var/coverage

# Vitest (frontend)
npm run test
```

### Qualité de code

```bash
# PHPStan (niveau max)
vendor/bin/phpstan analyse

# PHP-CS-Fixer
vendor/bin/php-cs-fixer fix

# ESLint
npm run lint

# Prettier
npm run format
```

## Règles spécifiques pour Claude

### Avant d'agir

1. **Toujours lire** les fichiers concernés avant de proposer une modification
2. Si une entité ou un service existe déjà, **réutiliser** plutôt que recréer
3. Vérifier les conventions en lisant un fichier similaire existant avant d'en créer un nouveau
4. Pour toute nouvelle fonctionnalité touchant la BDD : créer l'entité ET la migration

### Ce qu'il faut faire

- Proposer des solutions **idiomatiques Symfony 7.x** (pas de patterns Symfony 4 obsolètes)
- Utiliser les **nouveautés PHP 8.4** quand pertinent (property hooks, asymmetric visibility, etc.)
- Écrire du code **testable** : injection de dépendances, pas de statiques cachées
- Ajouter des **tests** pour toute nouvelle logique métier (PHPUnit côté PHP, Vitest côté Vue)
- Documenter les **décisions non évidentes** par un commentaire bref expliquant le "pourquoi" (pas le "quoi")
- Côté API : toujours penser aux **groupes de sérialisation** et aux **permissions**
- Côté Vue : toujours penser au **loading state** et au **error state** des appels API

### Ce qu'il faut éviter

- ❌ Annotations Doctrine/Symfony (utiliser les attributs PHP)
- ❌ `array` non typé en signature (préférer un DTO ou typer le contenu)
- ❌ Logique métier dans les controllers
- ❌ Requêtes Doctrine dans les controllers (passer par un repository ou service)
- ❌ `dump()`, `dd()`, `console.log()` laissés dans le code
- ❌ Commits avec `node_modules/`, `vendor/`, `var/`, `.env.local`
- ❌ Modification du schéma BDD sans migration
- ❌ Composants Vue avec plus de 200 lignes (à découper)
- ❌ Variables d'environnement en dur dans le code (utiliser `.env` + `$_ENV` côté PHP, `import.meta.env` côté Vite)
- ❌ Désactivation de la sécurité CSRF ou de la sanitization sans justification écrite

### En cas de doute

- Si la demande est ambiguë : **demander** plutôt que supposer
- Si plusieurs approches sont possibles : **proposer un choix** avec les avantages/inconvénients de chacune
- Si une dépendance manque pour faire ce qui est demandé : **le signaler** avant d'installer
- Si une modification a un impact large (refacto, migration de données) : **planifier** par étapes et faire valider avant d'exécuter

## Sécurité

- **Jamais** de secret en clair dans le code ni dans les fichiers versionnés
- Les secrets de prod sont gérés via les secrets Symfony (`bin/console secrets:set`)
- CSRF actif sur tous les formulaires Twig (EasyAdmin)
- L'API publique est en **lecture seule** sans auth, écriture réservée à `ROLE_STAFF`
- Sanitization du HTML rendu depuis Markdown via `league/commonmark` côté serveur + `DOMPurify` côté client
- Upload de fichiers : whitelist d'extensions strict, validation MIME côté serveur, stockage hors du document root
- En-têtes de sécurité (CSP, HSTS, X-Frame-Options) configurés au niveau Caddy

## RGPD

L'asso gère des données potentiellement liées à des mineurs et à des familles. Vigilance accrue :

- Pas de tracking analytics tiers (Google Analytics, etc.) sans bandeau de consentement
- Logs serveur sans IP en clair après 24h (anonymisation)
- Page "mentions légales" et "politique de confidentialité" obligatoires (entités `Page`)
- Formulaire de contact : pas de stockage en BDD au-delà du nécessaire, ou suppression auto après X jours
- Photos des enfants : **toujours** vérifier l'autorisation parentale avant publication (à gérer côté process, mais prévoir un champ "autorisation reçue" sur les médias concernés)

## Multi-instances

Le projet est conçu pour être déployé en plusieurs instances indépendantes (une par école/asso). Conséquences :

- **Une base PostgreSQL par instance** (pas de multi-tenant logique en BDD)
- Configuration par variables d'environnement (`.env.local` par déploiement)
- Routage par sous-domaine ou vhost (`asso1.domaine.fr`, `asso2.domaine.fr`)
- Répertoire d'uploads séparé par instance au déploiement
- Pas de référence en dur à un nom d'asso dans le code : l'identité du site (nom, baseline, logo, favicon, contact, réseaux, couleurs, bornes d'année scolaire) est stockée en BDD dans l'entité singleton `SiteSettings` (1 ligne, `id = 1`), éditable via "Réglages du site" dans EasyAdmin. Elle est exposée par `SiteSettingsProvider` (cache), `GET /api/site_settings` côté SPA et la fonction Twig `site_settings()` côté templates. Les dates `schoolYearStart` / `schoolYearEnd` ne conservent que le jour et le mois (l'année scolaire en cours est calculée à la volée).

## Référence rapide des choix de conception

| Question | Choix | Raison |
|----------|-------|--------|
| Runtime PHP | PHP local standard | Simplicité de développement |
| ORM vs requêtes brutes | Doctrine ORM | Standard Symfony, migrations gérées |
| API auto vs custom | API Platform | Gain de temps, OpenAPI auto |
| Back-office | EasyAdmin | Rapide, suffisant pour un CMS basique |
| Auth public | Aucune | Pas demandé, simplification |
| Auth staff | Form login Symfony classique | Simple, robuste, CSRF natif |
| Multi-langue | Non | Mono FR, peut être ajouté plus tard |
| Éditeur contenu | Markdown (EasyMDE côté admin) | Simple, portable, versionnable |
| Rendu Markdown | Côté serveur (CommonMark) | SEO, sécurité, perf SPA |
| Style frontend | Vuetify 3 | Connaissance préalable, composants Material Design prêts à l'emploi |
| TypeScript Vue | Oui | Robustesse, autocomplétion |

---

*Dernière mise à jour : à maintenir au fil du projet. Si une convention évolue, mettre à jour ce fichier dans le même commit.*
