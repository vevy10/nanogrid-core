# Nanogrid Core

Symfony backend API for managing decentralized nanogrid sites, equipment, households, and operational incidents.

## Overview

This project is a backend-oriented prototype inspired by rural electrification operations. It models the core resources required to supervise decentralized nanogrid deployments and day-to-day field operations:

- `Site`
- `Equipment`
- `Household`
- `Incident`

The current implementation focuses on:

- a clean local development environment
- a consistent domain model
- Docker-based infrastructure
- REST API endpoints for core operational resources
- JSON error handling for API routes
- functional API tests with PHPUnit

## Tech Stack

- PHP `8.5`
- Symfony `8.1`
- PostgreSQL `16`
- Doctrine ORM
- Doctrine Migrations
- Docker Compose
- Mailpit
- PHPUnit

## Project Structure

- `src/Entity`: Doctrine domain entities
- `src/Controller/Api`: REST API controllers
- `src/Repository`: Doctrine repositories
- `migrations`: database migrations
- `tests/Api`: functional API tests
- `docker-compose.yml`: local infrastructure definition

## Local Setup

### Requirements

- Docker Desktop
- PHP `8.5`
- Composer
- Symfony CLI

### Environment

Create a local environment override in `.env.local`:

```env
DATABASE_URL="postgresql://symfony:ChangeMe123!@127.0.0.1:5433/nanogrid_db?serverVersion=16&charset=utf8"
MAILER_DSN="smtp://127.0.0.1:1025"
```

### Start Infrastructure

```bash
docker compose up -d
docker compose ps
```

Services:

- PostgreSQL: `127.0.0.1:5433`
- Mailpit SMTP: `127.0.0.1:1025`
- Mailpit UI: `http://127.0.0.1:8025`

### Install Dependencies

```bash
composer install
```

### Run Migrations

```bash
php bin/console doctrine:migrations:migrate
```

### Start the API

```bash
symfony server:start -d
```

API base URL:

```text
http://127.0.0.1:8000
```

## Current Domain Model

### Site

Represents a nanogrid deployment site.

Key fields:

- `name`
- `code`
- `region`
- `status`
- `commissionedAt`

### Equipment

Represents technical assets installed on a site.

Key fields:

- `name`
- `serialNumber`
- `type`
- `status`
- `installedAt`
- `lastSeenAt`

Relationship:

- many equipment items belong to one site

### Household

Represents a household connected to a site.

Key fields:

- `reference`
- `ownerName`
- `phoneNumber`
- `connectionStatus`
- `connectedAt`

Relationship:

- many households belong to one site

### Incident

Represents operational issues reported on a site, optionally linked to specific equipment.

Key fields:

- `title`
- `description`
- `severity`
- `status`
- `reportedAt`
- `resolvedAt`

Relationships:

- every incident belongs to one site
- an incident may optionally be linked to one equipment item

## Available API Endpoints

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

### Incidents

- `GET /api/incidents`
- `GET /api/incidents/{id}`
- `POST /api/incidents`

### Households

- `GET /api/households`
- `GET /api/households/{id}`
- `POST /api/households`

## Example Requests

### Create a Site

```bash
curl -X POST http://127.0.0.1:8000/api/sites \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Ambanja Sud 04",
    "code": "AMB-S04",
    "region": "Diana",
    "status": "active",
    "commissionedAt": "2026-06-01T09:00:00+00:00"
  }'
```

### Update a Site

```bash
curl -X PUT http://127.0.0.1:8000/api/sites/1 \
  -H "Content-Type: application/json" \
  -d '{
    "status": "maintenance",
    "region": "Sava"
  }'
```

### Create Equipment

```bash
curl -X POST http://127.0.0.1:8000/api/equipment \
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

### Create Incident

```bash
curl -X POST http://127.0.0.1:8000/api/incidents \
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

### Create Household

```bash
curl -X POST http://127.0.0.1:8000/api/households \
  -H "Content-Type: application/json" \
  -d '{
    "reference": "HH-AMB-001",
    "ownerName": "Jean Rakoto",
    "phoneNumber": "+261340000001",
    "connectionStatus": "connected",
    "connectedAt": "2026-08-15T09:00:00+00:00",
    "siteId": 1
  }'
```

## Testing

Run all tests:

```bash
php bin/phpunit
```

Run specific API test suites:

```bash
php bin/phpunit tests/Api/SiteControllerTest.php
php bin/phpunit tests/Api/EquipmentControllerTest.php
php bin/phpunit tests/Api/IncidentControllerTest.php
php bin/phpunit tests/Api/HouseholdControllerTest.php
```

The test environment uses a dedicated PostgreSQL test database configured through `.env.test`.

## Development Notes

- `.env` and `.env.dev` are committed as shared project defaults.
- `.env.local` is local-only and must not be committed.
- `.env.test` is used for the dedicated test environment.
- PostgreSQL is exposed on port `5433` to avoid conflicts with a local PostgreSQL instance on `5432`.
- API routes return JSON responses, including JSON-formatted error responses for API exceptions.
- The current API uses handcrafted JSON normalization to keep the response contract explicit and avoid exposing Doctrine entities directly.

## Next Steps

- add `PUT /api/incidents/{id}`
- add `PUT /api/households/{id}`
- automate `updatedAt` updates with Doctrine lifecycle hooks or subscribers
- improve API error formatting consistency
- add fixtures or dedicated seed commands
- introduce DTOs or dedicated response mappers
- prepare CI workflow for automated test execution
