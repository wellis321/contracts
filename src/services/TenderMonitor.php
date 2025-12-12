<?php
/**
 * Tender Monitor Service
 * Monitors Public Contracts Scotland API for new tender opportunities
 */
class TenderMonitor {
    
    /**
     * Check Public Contracts Scotland API for new opportunities
     */
    public static function checkPCSAPI($keywords = '', $cpvCode = '85000000', $dateFrom = null) {
        if ($dateFrom === null) {
            // Default to last 30 days (wider range to catch more opportunities)
            $dateFrom = date('m-Y', strtotime('-30 days'));
        }
        
        $apiUrl = "https://api.publiccontractsscotland.gov.uk/v1/Notices";
        $params = [
            'dateFrom' => $dateFrom,
            'noticeType' => 2, // Contract Notice
            'outputType' => 0  // JSON
        ];
        
        if (!empty($keywords)) {
            $params['keywords'] = $keywords;
        }
        
        if (!empty($cpvCode)) {
            $params['cpvCode'] = $cpvCode;
        }
        
        $url = $apiUrl . '?' . http_build_query($params);
        
        try {
            $response = self::fetchAPI($url);
            if ($response) {
                $data = json_decode($response, true);
                
                // Log API response for debugging
                error_log("PCS API Response: " . json_encode([
                    'url' => $url,
                    'response_keys' => $data ? array_keys($data) : [],
                    'has_releases' => isset($data['releases']),
                    'releases_count' => isset($data['releases']) ? count($data['releases']) : 0
                ]));
                
                return $data;
            }
        } catch (Exception $e) {
            error_log("PCS API Error: " . $e->getMessage() . " | URL: " . $url);
        }
        
        return null;
    }
    
    /**
     * Fetch from API
     */
    private static function fetchAPI($url) {
        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Social Care Contracts Management/1.0');
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200) {
                return $response;
            } else {
                throw new Exception("API returned HTTP $httpCode");
            }
        } else {
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'header' => "User-Agent: Social Care Contracts Management/1.0\r\n",
                    'timeout' => 30
                ]
            ]);
            
            $response = @file_get_contents($url, false, $context);
            if ($response !== false) {
                return $response;
            }
        }
        
        return false;
    }
    
    /**
     * Process API response and create opportunities
     */
    public static function processAPIResponse($apiData, $organisationId) {
        if (empty($apiData)) {
            error_log("TenderMonitor: Empty API data received");
            return [];
        }
        
        // The PCS API might return data in different formats
        // Try different possible structures
        $releases = [];
        if (isset($apiData['releases'])) {
            $releases = $apiData['releases'];
        } elseif (isset($apiData['data'])) {
            $releases = is_array($apiData['data']) ? $apiData['data'] : [];
        } elseif (is_array($apiData) && isset($apiData[0])) {
            // Might be a direct array of releases
            $releases = $apiData;
        }
        
        if (empty($releases)) {
            error_log("TenderMonitor: No releases found in API response. Response structure: " . json_encode(array_keys($apiData)));
            return [];
        }
        
        error_log("TenderMonitor: Processing " . count($releases) . " releases");
        
        $created = [];
        $db = getDbConnection();
        
        foreach ($releases as $release) {
            try {
                $tender = $release['tender'] ?? [];
                $parties = $release['parties'] ?? [];
                
                // Extract data
                $title = $tender['title'] ?? 'Untitled Tender';
                $description = $tender['description'] ?? '';
                $value = self::extractValue($tender);
                $deadline = self::extractDeadline($tender);
                $publishedDate = $release['date'] ?? null;
                
                // Find local authority
                $localAuthorityId = self::findLocalAuthority($parties, $db);
                
                // Check if already exists
                $tenderId = $release['id'] ?? null;
                if ($tenderId) {
                    $stmt = $db->prepare("SELECT id FROM tender_opportunities WHERE tender_reference = ? AND organisation_id = ?");
                    $stmt->execute([$tenderId, $organisationId]);
                    if ($stmt->fetch()) {
                        continue; // Already exists
                    }
                }
                
                // Create opportunity
                $opportunityData = [
                    'local_authority_id' => $localAuthorityId ?? 1, // Default to first if not found
                    'title' => $title,
                    'description' => $description,
                    'tender_reference' => $tenderId,
                    'source' => 'public_contracts_scotland',
                    'source_url' => self::buildPCSURL($tenderId),
                    'published_date' => $publishedDate ? date('Y-m-d', strtotime($publishedDate)) : null,
                    'submission_deadline' => $deadline ?: date('Y-m-d', strtotime('+30 days')),
                    'estimated_value' => $value,
                    'status' => 'open'
                ];
                
                // Only include contract_type_id if we can determine it
                // For now, leave it null and let users set it manually
                
                $opportunityId = TenderOpportunity::create($opportunityData);
                
                $created[] = $opportunityId;
            } catch (Exception $e) {
                error_log("Error processing tender: " . $e->getMessage());
            }
        }
        
        return $created;
    }
    
    /**
     * Extract value from tender data
     */
    private static function extractValue($tender) {
        $value = $tender['value']['amount'] ?? null;
        if ($value) {
            return floatval($value);
        }
        return null;
    }
    
    /**
     * Extract deadline from tender data
     */
    private static function extractDeadline($tender) {
        $deadline = $tender['tenderPeriod']['endDate'] ?? null;
        if ($deadline) {
            return date('Y-m-d', strtotime($deadline));
        }
        return null;
    }
    
    /**
     * Find local authority from parties data
     */
    private static function findLocalAuthority($parties, $db) {
        foreach ($parties as $party) {
            $name = $party['name'] ?? '';
            if (empty($name)) continue;
            
            // Try to match with our local authorities
            $stmt = $db->prepare("SELECT id FROM local_authorities WHERE name LIKE ? LIMIT 1");
            $stmt->execute(["%$name%"]);
            $result = $stmt->fetch();
            if ($result) {
                return $result['id'];
            }
        }
        return null;
    }
    
    /**
     * Build PCS URL from tender ID
     */
    private static function buildPCSURL($tenderId) {
        return "https://www.publiccontractsscotland.gov.uk/search/show/search_view.aspx?ID=" . urlencode($tenderId);
    }
    
    /**
     * Run monitoring check for all active monitors
     */
    public static function runMonitoringCheck() {
        $db = getDbConnection();
        
        // Get all active monitoring preferences
        $stmt = $db->query("
            SELECT * FROM tender_monitoring_preferences 
            WHERE is_active = 1
        ");
        $monitors = $stmt->fetchAll();
        
        $results = [];
        
        foreach ($monitors as $monitor) {
            try {
                $keywords = $monitor['keywords'] ? explode(',', $monitor['keywords']) : [];
                $keyword = !empty($keywords) ? trim($keywords[0]) : 'social care';
                
                $cpvCodes = json_decode($monitor['cpv_codes'] ?? '[]', true);
                $cpvCode = !empty($cpvCodes) ? $cpvCodes[0] : '85000000';
                
                error_log("TenderMonitor: Checking monitor {$monitor['id']} with keyword='$keyword', cpvCode='$cpvCode'");
                
                // Check API
                $apiData = self::checkPCSAPI($keyword, $cpvCode);
                
                if ($apiData) {
                    // Process and create opportunities
                    $created = self::processAPIResponse($apiData, $monitor['organisation_id']);
                    
                    error_log("TenderMonitor: Monitor {$monitor['id']} processed, created " . count($created) . " opportunities");
                    
                    if (!empty($created)) {
                        // Create notifications
                        self::createNotifications($monitor, $created);
                        
                        // Update monitor
                        $stmt = $db->prepare("
                            UPDATE tender_monitoring_preferences 
                            SET last_checked_at = NOW(),
                                opportunities_found = opportunities_found + ?
                            WHERE id = ?
                        ");
                        $stmt->execute([count($created), $monitor['id']]);
                    } else {
                        // Still update last_checked_at even if no new opportunities
                        $stmt = $db->prepare("
                            UPDATE tender_monitoring_preferences 
                            SET last_checked_at = NOW()
                            WHERE id = ?
                        ");
                        $stmt->execute([$monitor['id']]);
                    }
                    
                    $results[$monitor['id']] = [
                        'monitor_id' => $monitor['id'],
                        'opportunities_found' => count($created),
                        'created_ids' => $created,
                        'api_response_received' => true
                    ];
                } else {
                    error_log("TenderMonitor: Monitor {$monitor['id']} - No API data received");
                    $results[$monitor['id']] = [
                        'monitor_id' => $monitor['id'],
                        'opportunities_found' => 0,
                        'api_response_received' => false,
                        'error' => 'No API response received'
                    ];
                }
            } catch (Exception $e) {
                error_log("Monitor error for monitor {$monitor['id']}: " . $e->getMessage());
                $results[$monitor['id']] = ['error' => $e->getMessage()];
            }
        }
        
        if (empty($monitors)) {
            error_log("TenderMonitor: No active monitors found");
        }
        
        return $results;
    }
    
    /**
     * Create notifications for found opportunities
     */
    private static function createNotifications($monitor, $opportunityIds) {
        $db = getDbConnection();
        
        foreach ($opportunityIds as $opportunityId) {
            // Get opportunity details
            $opportunity = TenderOpportunity::findById($opportunityId);
            if (!$opportunity) continue;
            
            // Create notification
            $stmt = $db->prepare("
                INSERT INTO tender_notifications (
                    organisation_id, user_id, monitoring_preference_id,
                    tender_opportunity_id, notification_type, title, message
                ) VALUES (?, ?, ?, ?, 'new_opportunity', ?, ?)
            ");
            
            $title = "New Tender Opportunity: " . $opportunity['title'];
            $message = "A new tender opportunity matching your criteria has been found: " . $opportunity['title'] . 
                      " for " . ($opportunity['local_authority_name'] ?? 'Unknown Authority');
            
            $stmt->execute([
                $monitor['organisation_id'],
                $monitor['user_id'],
                $monitor['id'],
                $opportunityId,
                $title,
                $message
            ]);
            
            // Send email if configured
            if ($monitor['notification_method'] === 'email' || $monitor['notification_method'] === 'both') {
                self::sendEmailNotification($monitor, $opportunity);
            }
        }
    }
    
    /**
     * Send email notification
     */
    private static function sendEmailNotification($monitor, $opportunity) {
        $email = $monitor['email_address'];
        if (empty($email)) {
            // Get user email
            $db = getDbConnection();
            $stmt = $db->prepare("SELECT email FROM users WHERE id = ?");
            $stmt->execute([$monitor['user_id']]);
            $user = $stmt->fetch();
            $email = $user['email'] ?? null;
        }
        
        if (!$email) {
            return false;
        }
        
        $subject = "New Tender Opportunity: " . $opportunity['title'];
        $body = "
            <h2>New Tender Opportunity Found</h2>
            <p>A new tender opportunity matching your monitoring criteria has been found:</p>
            <ul>
                <li><strong>Title:</strong> " . htmlspecialchars($opportunity['title']) . "</li>
                <li><strong>Local Authority:</strong> " . htmlspecialchars($opportunity['local_authority_name'] ?? 'Unknown') . "</li>
                <li><strong>Deadline:</strong> " . ($opportunity['submission_deadline'] ? date(DATE_FORMAT, strtotime($opportunity['submission_deadline'])) : 'Not specified') . "</li>
                " . ($opportunity['estimated_value'] ? "<li><strong>Estimated Value:</strong> £" . number_format($opportunity['estimated_value'], 2) . "</li>" : "") . "
            </ul>
            <p>
                <a href=\"" . url('tender-opportunities.php') . "\" style=\"background: #2563eb; color: white; padding: 0.75rem 1.5rem; text-decoration: none; border-radius: 0.375rem; display: inline-block;\">
                    View Opportunity
                </a>
            </p>
        ";
        
        // Use PHP mail() or configure SMTP
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: " . APP_NAME . " <noreply@" . parse_url(APP_URL, PHP_URL_HOST) . ">\r\n";
        
        return @mail($email, $subject, $body, $headers);
    }
}

