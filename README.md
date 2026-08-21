# Nanogrid Core

Backend Symfony pour piloter des sites nanogrid, leurs équipements, les foyers raccordés, les incidents opérationnels, les contrats de maintenance et les comptes utilisateurs.

## Vue d'ensemble

Ce projet modélise les ressources principales d'une exploitation nanogrid orientée terrain :

- `Site`
- `Equipment`
- `Household`
- `Incident`
- `ContractPlan`
- `SiteContract`
- `User`

Le backend couvre actuellement :

- une infrastructure locale Docker avec PostgreSQL et Mailpit
- un modèle de domaine Doctrine cohérent
- des APIs JSON pour les ressources métier
- une gestion JSON des erreurs API
- une authentification par session
- la protection des routes d'écriture
- des tests fonctionnels PHPUnit sur les APIs principales

## Stack technique

- PHP `8.5`
- Symfony `8.1`
- PostgreSQL `16`
- Doctrine ORM
- Doctrine Migrations
- Symfony Security
- Docker Compose
- Mailpit
- PHPUnit

## Structure du projet

- `src/Entity` : entités Doctrine
- `src/Controller/Api` : contrôleurs HTTP API
- `src/Service/Api` : logique applicative et normalisation JSON
- `src/Repository` : repositories Doctrine
- `src/EventListener` : gestion des erreurs API
- `src/EventSubscriber` : événements applicatifs
- `migrations` : migrations SQL versionnées
- `tests/Api` : tests fonctionnels API
- `docker-compose.yml` : infrastructure locale

## Installation locale

### Prérequis

- Docker Desktop
- PHP `8.5`
- Composer
- Symfony CLI

### Variables d'environnement

Créer `.env.local` :

```env
DATABASE_URL="postgresql://symfony:ChangeMe123!@127.0.0.1:5433/nanogrid_db?serverVersion=16&charset=utf8"
MAILER_DSN="smtp://127.0.0.1:1025"
```

### Démarrage de l'infrastructure

```bash
docker compose up -d
docker compose ps
```

Services disponibles :

- PostgreSQL : `127.0.0.1:5433`
- Mailpit SMTP : `127.0.0.1:1025`
- Mailpit UI : `http://127.0.0.1:8025`

### Installation des dépendances

```bash
composer install
```

### Migration de la base

```bash
php bin/console doctrine:migrations:migrate
```

### Lancement de l'API

```bash
symfony server:start -d
```

Base URL locale :

```text
http://127.0.0.1:8000
```

## Modèle métier actuel

### Site

Représente un site nanogrid.

Champs clés :

- `name`
- `code`
- `region`
- `status`
- `commissionedAt`

Relations :

- un site possède plusieurs équipements
- un site possède plusieurs foyers
- un site possède plusieurs incidents
- un site peut avoir plusieurs contrats dans le temps

Note :

- la réponse API des sites expose `activeContract` quand un contrat actif existe

### Equipment

Représente un équipement technique installé sur un site.

Champs clés :

- `name`
- `serialNumber`
- `type`
- `status`
- `installedAt`
- `lastSeenAt`

Relation :

- plusieurs équipements appartiennent à un site

### Household

Représente un foyer raccordé à un site.

Champs clés :

- `reference`
- `ownerName`
- `phoneNumber`
- `connectionStatus`
- `connectedAt`

Relation :

- plusieurs foyers appartiennent à un site

### Incident

Représente un incident métier ou technique.

Champs clés :

- `title`
- `description`
- `severity`
- `status`
- `reportedAt`
- `resolvedAt`

Relations :

- chaque incident appartient à un site
- un incident peut être lié à un équipement

### ContractPlan

Représente une offre de maintenance.

Champs clés :

- `name`
- `code`
- `annualPrice`
- `freePreventiveVisitsPerYear`
- `additionalVisitCost`
- `curativeInterventionCost`
- `consumableReplacementCost`
- `annualConsumableCoverageLimit`
- `phoneSupportIncluded`
- `status`

Exemples actuels :

- `NO_CONTRACT`
- `STANDARD`
- `PREMIUM`

### SiteContract

Représente la souscription d'un site à une offre de maintenance.

Champs clés :

- `startDate`
- `endDate`
- `status`

Relations :

- un contrat est lié à un `Site`
- un contrat est lié à un `ContractPlan`

Règle métier exposée :

- `isActive` est calculé selon le statut et la période du contrat

### User

Représente un utilisateur applicatif.

Champs clés :

- `email`
- `password`
- `roles`
- `fullName`
- `status`

Comportement :

- mot de passe hashé par Symfony
- authentification par session

## Authentification et sécurité

Le backend utilise actuellement une authentification par session.

Endpoints d'authentification :

- `POST /api/register`
- `POST /api/login`
- `GET /api/me`
- `POST /api/logout`

Règles actuelles :

- les routes `GET` restent publiques
- les routes `POST` et `PUT` principales sont protégées par `ROLE_USER`
- l'utilisateur doit se connecter avant d'appeler les routes d'écriture

Routes protégées actuellement :

- `POST /api/sites`
- `PUT /api/sites/{id}`
- `POST /api/equipment`
- `PUT /api/equipment/{id}`
- `POST /api/households`
- `POST /api/incidents`
- `POST /api/site-contracts`
- `PUT /api/site-contracts/{id}`

## Endpoints disponibles

### Auth

- `POST /api/register`
- `POST /api/login`
- `GET /api/me`
- `POST /api/logout`

### Sites

- `GET /api/sites`
- `GET /api/sites/{id}`
- `POST /api/sites`
- `PUT /api/sites/{id}`

### Equipment

- `GET /api/equipment`
- `GET /api/equipment/{id}`
- `POST /api/equipment`
- `PUT /api/equipment/{id}`

### Households

- `GET /api/households`
- `GET /api/households/{id}`
- `POST /api/households`

### Incidents

- `GET /api/incidents`
- `GET /api/incidents/{id}`
- `POST /api/incidents`

### Contract Plans

- `GET /api/contract-plans`
- `GET /api/contract-plans/{id}`

### Site Contracts

- `GET /api/site-contracts`
- `GET /api/site-contracts/{id}`
- `POST /api/site-contracts`
- `PUT /api/site-contracts/{id}`

## Exemples de requêtes

### Register

```bash
curl -X POST http://127.0.0.1:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@nanogrid.local",
    "password": "ChangeMe123!",
    "fullName": "Admin Nanogrid"
  }'
```

### Login

```bash
curl -c var/cookies.txt -X POST http://127.0.0.1:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@nanogrid.local",
    "password": "ChangeMe123!"
  }'
```

### Me

```bash
curl -b var/cookies.txt http://127.0.0.1:8000/api/me
```

### Logout

```bash
curl -b var/cookies.txt -X POST http://127.0.0.1:8000/api/logout
```

### Créer un site

```bash
curl -b var/cookies.txt -X POST http://127.0.0.1:8000/api/sites \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Ambanja Sud 04",
    "code": "AMB-S04",
    "region": "Diana",
    "status": "active",
    "commissionedAt": "2026-06-01T09:00:00+00:00"
  }'
```

### Créer un équipement

```bash
curl -b var/cookies.txt -X POST http://127.0.0.1:8000/api/equipment \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Distribution Panel D4",
    "serialNumber": "PANEL-D4-004",
    "type": "panel",
    "status": "active",
    "installedAt": "2026-07-01T10:00:00+00:00",
    "lastSeenAt": "2026-08-20T10:15:00+00:00",
    "siteId": 2
  }'
```

### Créer un incident

```bash
curl -b var/cookies.txt -X POST http://127.0.0.1:8000/api/incidents \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Battery overheating detected",
    "description": "Temperature threshold exceeded on battery bank A1.",
    "severity": "high",
    "status": "open",
    "siteId": 1,
    "equipmentId": 1,
    "reportedAt": "2026-08-20T10:30:00+00:00"
  }'
```

### Créer un contrat site

```bash
curl -b var/cookies.txt -X POST http://127.0.0.1:8000/api/site-contracts \
  -H "Content-Type: application/json" \
  -d '{
    "siteId": 2,
    "contractPlanId": 3,
    "startDate": "2026-09-01T00:00:00+00:00",
    "endDate": "2027-08-31T23:59:59+00:00",
    "status": "active"
  }'
```

## Postman

Collection recommandée :

- `AUTH`
- `SITES`
- `EQUIPMENT`
- `HOUSEHOLDS`
- `INCIDENTS`
- `CONTRACT PLANS`
- `SITE CONTRACTS`

Variable d'environnement :

- `base_url = http://127.0.0.1:8000`

Ordre de test recommandé :

1. `AUTH / POST - Login`
2. `AUTH / GET - Me`
3. requêtes `GET`
4. requêtes `POST` et `PUT`
5. `AUTH / POST - Logout`

Important :

- les cookies doivent être activés dans Postman
- les requêtes d'écriture nécessitent une session active

## Tests

Exécuter toute la suite :

```bash
php bin/phpunit
```

Suites API disponibles :

```bash
php bin/phpunit tests/Api/SiteControllerTest.php
php bin/phpunit tests/Api/EquipmentControllerTest.php
php bin/phpunit tests/Api/IncidentControllerTest.php
php bin/phpunit tests/Api/HouseholdControllerTest.php
```

Le projet utilise une base de test dédiée via `.env.test`.

## Notes de développement

- `.env` et `.env.dev` sont versionnés comme configuration partagée
- `.env.local` reste local et ne doit pas être commit
- `.env.test` est utilisé pour l'environnement de test
- PostgreSQL est exposé sur `5433` pour éviter les conflits avec un PostgreSQL local sur `5432`
- les réponses API sont explicitement normalisées dans les services applicatifs
- les erreurs API sont renvoyées en JSON
- la date métier de référence pendant les derniers tests manuels était le `21 août 2026`

## Prochaines améliorations possibles

- ajouter `PUT /api/incidents/{id}`
- ajouter `PUT /api/households/{id}`
- ajouter des tests fonctionnels pour `register`, `login`, `logout` et `me`
- introduire `ROLE_ADMIN`
- restreindre certaines routes à l'administration
- ajouter un endpoint métier dédié à la couverture contractuelle d'un site
- ajouter des fixtures ou commandes de seed
- préparer une CI pour lancer les tests automatiquement
