# Test Seed Data

## Overview
The `seed_test_data.sql` file creates realistic test data for development and demonstration purposes. It represents what a real social care organisation might enter into the system.

## What Gets Created

### Organisation: Highland Care Services
- **Domain**: `highlandcare.test`
- **Complete organisation profile** with all fields filled

### Users (5 users, all password: `password`)
1. **Sarah MacLeod** - Organisation Admin (`admin@highlandcare.test`)
2. **James Campbell** - Finance Manager (`finance@highlandcare.test`)
3. **Fiona MacDonald** - Inverness Team Manager (`manager.inverness@highlandcare.test`)
4. **David Stewart** - Aberdeen Team Manager (`manager.aberdeen@highlandcare.test`)
5. **Emma Robertson** - Support Worker (`support.worker@highlandcare.test`)

### Team Structure
- **2 Areas**: Highland Area, Aberdeen Area
- **4 Teams**: 
  - Inverness Support Team (under Highland Area)
  - Nairn Support Team (under Highland Area)
  - Aberdeen City Team (under Aberdeen Area)
  - Aberdeenshire Rural Team (under Aberdeen Area)

### People Being Supported (5 people)
1. **John Smith** - Multiple contracts over time (showing contract progression)
2. **Mary Johnson** - Single ongoing contract
3. **Robert Brown** - Recently moved to Aberdeen (cross-authority tracking)
4. **Sarah Williams** - Learning disabilities support
5. **Michael Taylor** - Complex needs, contract expiring soon

### Contracts (7 contracts)
1. **John Smith - Initial** (2022-2024, ended) - Shows historical contract
2. **John Smith - Current** (2024-2026, active) - Shows contract renewal
3. **Mary Johnson** (2023-2025, active) - Ongoing support hours
4. **Robert Brown** (2024-2026, active) - New contract in Aberdeen
5. **Bulk Contract** (2023-2025, active) - 8 people, Nairn area
6. **Sarah Williams** (2024-2025, active) - Personal care
7. **Michael Taylor** (2022-2025, active, expiring soon) - Complex support

### Rates
- Rates set for Highland, Aberdeen City, and Aberdeenshire
- Historical rate changes (showing rate increase over time)
- Different rates per local authority

### Payments
- Monthly payment history for active contracts
- Different payment frequencies (Monthly)
- Mix of Tender and Self-Directed Support payments

### Tender Applications (3 applications)
1. **Under Review** - Highland complex needs support (submitted, awaiting decision)
2. **Draft** - Aberdeen support hours (being prepared)
3. **Submitted** - Aberdeenshire personal care (submitted, awaiting response)

## How to Use

### Prerequisites
1. Run `schema.sql` to create database structure
2. Run all migration files in order:
   - `migration_teams_hierarchy.sql`
   - `migration_custom_team_roles.sql`
   - `migration_organisation_profile.sql`
   - `migration_tender_applications.sql`
   - Any other migrations

### Running the Seed Data
```sql
-- In phpMyAdmin or MySQL client:
SOURCE /path/to/seed_test_data.sql;

-- Or copy and paste the entire file contents
```

### Logging In
- **Admin Account**: `admin@highlandcare.test` / `password`
- **Finance Account**: `finance@highlandcare.test` / `password`
- **Team Manager**: `manager.inverness@highlandcare.test` / `password`

### What You'll See
- Complete organisation profile (ready for tender applications)
- Team hierarchy with users assigned
- People with contract history
- Contracts at various stages (active, expired, expiring soon)
- Rate history showing changes over time
- Payment records
- Tender applications in different statuses

## Test Scenarios Covered

1. **Contract Progression**: John Smith has an old contract and a new one
2. **Cross-Authority**: Robert Brown moved from Highland to Aberdeen
3. **Bulk Contracts**: Contract for 8 people
4. **Expiring Contracts**: Michael Taylor's contract expires soon
5. **Tender Workflow**: Applications at different stages
6. **Team Access Control**: Different users see different contracts based on team
7. **Rate Variations**: Different rates for different local authorities
8. **Payment History**: Multiple payments showing financial tracking

## Notes
- All test users have password: `password`
- Dates are set to show realistic timelines
- Contract numbers follow pattern: HCS-YYYY-XXX
- Tender references follow local authority patterns
- The seed data is idempotent (safe to run multiple times)

