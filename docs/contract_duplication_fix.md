# Contract Duplication Issue - Fix Summary

## Problem
When viewing `http://localhost:8888/person-view.php?id=3`, contracts were being displayed twice, causing the contract count to be doubled.

## Root Cause
The issue was in the `Person::getContracts()` method in `/src/models/Person.php` (lines 140-154).

### The Bug
The SQL query was using `SELECT DISTINCT` along with fields from the `contract_people` junction table:

```sql
SELECT DISTINCT
    c.*,
    ct.name as contract_type_name,
    la.name as local_authority_name,
    cp.start_date as person_start_date,
    cp.end_date as person_end_date,
    cp.notes as contract_notes
FROM contract_people cp
INNER JOIN contracts c ON cp.contract_id = c.id
...
WHERE cp.person_id = ?
```

### Why This Caused Duplicates
The `DISTINCT` keyword applies to the **entire result set**. If a person has multiple entries in the `contract_people` table for the same contract (e.g., different start/end dates, different local authorities, or different notes), `DISTINCT` would return multiple rows because those additional fields differ.

**Example scenario:**
- Person ID 3 works on Contract ID 5 from 2020-2021
- Person ID 3 works on Contract ID 5 again from 2022-2023

Result: Two rows returned for the same contract, even though it's the same contract!

## The Fix
Changed the query to use `GROUP BY c.id` instead of `SELECT DISTINCT`, and used aggregate functions for the fields from `contract_people`:

```sql
SELECT 
    c.*,
    ct.name as contract_type_name,
    la.name as local_authority_name,
    MIN(cp.start_date) as person_start_date,
    MAX(cp.end_date) as person_end_date,
    GROUP_CONCAT(cp.notes SEPARATOR '; ') as contract_notes
FROM contract_people cp
INNER JOIN contracts c ON cp.contract_id = c.id
...
WHERE cp.person_id = ?
GROUP BY c.id, ct.name, la.name
```

### What Changed
- **`SELECT DISTINCT`** → **`GROUP BY c.id`** - Groups results by contract ID
- **`cp.start_date`** → **`MIN(cp.start_date)`** - Gets the earliest start date
- **`cp.end_date`** → **`MAX(cp.end_date)`** - Gets the latest end date
- **`cp.notes`** → **`GROUP_CONCAT(cp.notes SEPARATOR '; ')`** - Combines all notes

This ensures that each contract appears only once, while still preserving the relevant information from multiple `contract_people` entries.

## Testing
After applying this fix, you should see each contract listed only once on the person view page, and the contract count should be accurate.

## File Modified
- `/src/models/Person.php` - Line 140-154 (the first SQL query in the `getContracts()` method)
