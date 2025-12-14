-- Diagnostic script to find duplicate contract links
-- Run this to check for duplicate entries in contract_people table and contracts with person_id

-- Check for duplicate entries in contract_people table (same person + contract combination)
SELECT 
    cp.person_id,
    p.first_name,
    p.last_name,
    cp.contract_id,
    c.title as contract_title,
    c.contract_number,
    COUNT(*) as duplicate_count
FROM contract_people cp
INNER JOIN people p ON cp.person_id = p.id
INNER JOIN contracts c ON cp.contract_id = c.id
GROUP BY cp.person_id, cp.contract_id
HAVING COUNT(*) > 1
ORDER BY duplicate_count DESC, p.last_name, p.first_name;

-- Check contracts that have both person_id set AND entries in contract_people
SELECT 
    c.id as contract_id,
    c.title,
    c.contract_number,
    c.person_id as direct_person_id,
    p1.first_name as direct_first_name,
    p1.last_name as direct_last_name,
    COUNT(cp.id) as contract_people_count,
    GROUP_CONCAT(CONCAT(p2.first_name, ' ', p2.last_name) SEPARATOR ', ') as contract_people_names
FROM contracts c
LEFT JOIN people p1 ON c.person_id = p1.id
LEFT JOIN contract_people cp ON c.id = cp.contract_id
LEFT JOIN people p2 ON cp.person_id = p2.id
WHERE c.person_id IS NOT NULL
GROUP BY c.id, c.title, c.contract_number, c.person_id, p1.first_name, p1.last_name
HAVING COUNT(cp.id) > 0
ORDER BY contract_people_count DESC;

-- Count active contracts per person
SELECT 
    p.id,
    p.first_name,
    p.last_name,
    COUNT(DISTINCT c.id) as active_contract_count
FROM people p
LEFT JOIN contract_people cp ON p.id = cp.person_id
LEFT JOIN contracts c ON cp.contract_id = c.id AND (c.end_date IS NULL OR c.end_date >= CURDATE())
WHERE p.organisation_id = (SELECT id FROM organisations LIMIT 1) -- Replace with your organisation_id
GROUP BY p.id, p.first_name, p.last_name
HAVING active_contract_count > 0
ORDER BY active_contract_count DESC, p.last_name, p.first_name;

-- Total unique active contracts in the system
SELECT 
    COUNT(DISTINCT c.id) as total_unique_active_contracts,
    COUNT(DISTINCT CASE WHEN c.person_id IS NOT NULL THEN c.person_id END) as people_with_direct_links,
    COUNT(DISTINCT cp.person_id) as people_with_contract_people_links
FROM contracts c
LEFT JOIN contract_people cp ON c.id = cp.contract_id
WHERE c.organisation_id = (SELECT id FROM organisations LIMIT 1) -- Replace with your organisation_id
AND (c.end_date IS NULL OR c.end_date >= CURDATE());


