# Tender Application System Proposal

## Overview
Create a comprehensive tender application form system that pre-fills information from existing contracts and organization data, reducing duplication and streamlining the tender submission process.

## Current Data Available (Can Be Reused)

### ✅ Already in System:
1. **Organization Information**
   - Organization name
   - Domain/email domain
   - User accounts and roles

2. **Contract Data**
   - Contract types (Waking Hours, Sleepovers, Support, etc.)
   - Current rates per contract type
   - Historical rate changes
   - Local authority relationships
   - Contract durations and terms
   - Procurement routes used
   - Tender statuses
   - Framework agreements
   - Evaluation criteria
   - Quality/price weightings
   - Fair work compliance status
   - Community benefits

3. **Operational Data**
   - Number of people supported
   - Number of staff
   - Daytime/sleepover hours
   - Payment history
   - Contract stipulations

4. **Reference Data**
   - All 32 Scottish Local Authorities
   - Procurement routes
   - Tender statuses
   - Reference rates (Scottish Minimum, Real Living Wage, HCA rates)

## Missing Data (Need to Add)

### Organization Profile:
1. **Legal/Registration**
   - Company registration number (Companies House)
   - Care Inspectorate registration number
   - Charity number (if applicable)
   - VAT number
   - Registered address
   - Trading address (if different)

2. **Contact Information**
   - Main contact person
   - Phone number
   - Email address
   - Website

3. **Financial**
   - Annual accounts (upload)
   - Insurance certificates (public liability, employer's liability)
   - Bank details (for payments)

4. **Quality & Compliance**
   - Care Inspectorate rating
   - Last inspection date
   - Policies and procedures (safeguarding, health & safety, etc.)
   - Accreditations/certifications
   - Training records

5. **Operational Capacity**
   - Geographic coverage areas
   - Service types offered
   - Staff qualifications breakdown
   - Languages spoken
   - Specialist expertise areas

6. **References**
   - Previous contract references
   - Client testimonials

## Tender Application Form Structure

### Section 1: Organization Details (Pre-filled from profile)
- Organization name
- Registration numbers
- Contact details
- Addresses

### Section 2: Tender-Specific Information
- Local authority
- Procurement route
- Contract type(s) being tendered for
- Service description
- Number of people/capacity
- Geographic coverage required

### Section 3: Pricing (Pre-filled from current rates)
- Rates per contract type
- Total contract value
- Payment terms
- Price review mechanisms
- Inflation indexation

### Section 4: Quality & Experience
- Care Inspectorate rating (pre-filled)
- Relevant experience (from existing contracts)
- Staff qualifications
- Training programs
- Policies and procedures

### Section 5: Fair Work & Community Benefits
- Fair work compliance (pre-filled from contracts)
- Living wage commitment
- Staff terms and conditions
- Community benefits offered
- Environmental commitments

### Section 6: Operational Details
- Staffing levels
- Hours breakdown (daytime/sleepover)
- Geographic coverage
- Languages/specialist skills

### Section 7: References
- Previous contracts with this LA (auto-populated)
- Other contract references
- Client testimonials

## Benefits of Pre-filling

1. **Reduces Duplication**: Organization details entered once, reused for all tenders
2. **Consistency**: Ensures same information used across all applications
3. **Time Saving**: Faster application process
4. **Accuracy**: Less chance of errors from manual entry
5. **Historical Context**: Can reference previous successful tenders
6. **Rate Consistency**: Ensures rates align with current contracts

## Future API/MCP Server Integration

### Potential Integrations:
1. **Public Contracts Scotland (PCS)**
   - Submit tenders directly
   - Track tender opportunities
   - Receive notifications

2. **Care Inspectorate API**
   - Auto-fetch inspection ratings
   - Verify registration numbers

3. **Companies House API**
   - Verify company details
   - Check financial status

4. **Local Authority Portals**
   - Direct submission to LA tender portals
   - Framework agreement applications

5. **Scotland Excel**
   - Framework application submissions
   - Call-off requests

### MCP Server Benefits:
- Standardized interface for tender data
- Can be used by other systems
- Enables automation
- Supports integration with external tender platforms

## Implementation Plan

### Phase 1: Organization Profile Enhancement
- Add organization profile fields to database
- Create organization profile management page
- Store legal, financial, quality information

### Phase 2: Tender Application Form
- Create tender application form
- Pre-fill from organization profile
- Pre-fill from existing contracts
- Export to PDF/Word for submission

### Phase 3: Tender Management
- Track tender applications
- Link to contracts when awarded
- Store tender documents
- Tender history and analytics

### Phase 4: API/MCP Server
- Design API endpoints
- Create MCP server for tender data
- Documentation for integration

## Database Schema Additions Needed

```sql
-- Organization profile extensions
ALTER TABLE organisations ADD COLUMN company_registration_number VARCHAR(50) NULL;
ALTER TABLE organisations ADD COLUMN care_inspectorate_registration VARCHAR(50) NULL;
ALTER TABLE organisations ADD COLUMN charity_number VARCHAR(50) NULL;
ALTER TABLE organisations ADD COLUMN vat_number VARCHAR(50) NULL;
ALTER TABLE organisations ADD COLUMN registered_address TEXT NULL;
ALTER TABLE organisations ADD COLUMN trading_address TEXT NULL;
ALTER TABLE organisations ADD COLUMN phone VARCHAR(50) NULL;
ALTER TABLE organisations ADD COLUMN website VARCHAR(255) NULL;
ALTER TABLE organisations ADD COLUMN care_inspectorate_rating VARCHAR(50) NULL;
ALTER TABLE organisations ADD COLUMN last_inspection_date DATE NULL;

-- Tender applications table
CREATE TABLE tender_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    organisation_id INT NOT NULL,
    local_authority_id INT NOT NULL,
    procurement_route VARCHAR(100) NULL,
    contract_type_id INT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    status VARCHAR(50) DEFAULT 'draft',
    submitted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    FOREIGN KEY (organisation_id) REFERENCES organisations(id),
    FOREIGN KEY (local_authority_id) REFERENCES local_authorities(id),
    FOREIGN KEY (contract_type_id) REFERENCES contract_types(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);
```

## Next Steps

1. ✅ Fix contract type duplication issue (completed)
2. ⏳ Design organization profile enhancement
3. ⏳ Create tender application form
4. ⏳ Implement pre-filling logic
5. ⏳ Add export functionality
6. ⏳ Design API/MCP server architecture

