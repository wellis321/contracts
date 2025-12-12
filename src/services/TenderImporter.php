<?php
/**
 * Tender Importer Service
 * Imports tender opportunities from external sources (Public Contracts Scotland, etc.)
 */
class TenderImporter {
    
    /**
     * Import opportunity from Public Contracts Scotland URL
     */
    public static function importFromPCS($url) {
        // Fetch the page
        $html = self::fetchUrl($url);
        if (!$html) {
            throw new Exception('Could not fetch the tender notice page.');
        }
        
        // Parse the HTML
        $dom = new DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new DOMXPath($dom);
        
        $data = [
            'source' => 'public_contracts_scotland',
            'source_url' => $url,
            'tender_reference' => self::extractPCSReference($xpath, $dom),
            'title' => self::extractPCSTitle($xpath, $dom),
            'description' => self::extractPCSDescription($xpath, $dom),
            'published_date' => self::extractPCSPublishedDate($xpath, $dom),
            'submission_deadline' => self::extractPCSDeadline($xpath, $dom),
            'estimated_value' => self::extractPCSValue($xpath, $dom),
            'local_authority_id' => self::extractPCSLocalAuthority($xpath, $dom),
            'geographic_coverage' => self::extractPCSGeographicCoverage($xpath, $dom)
        ];
        
        return $data;
    }
    
    /**
     * Extract tender reference from PCS page
     */
    private static function extractPCSReference($xpath, $dom) {
        // Try multiple selectors for reference number
        $selectors = [
            "//span[contains(text(), 'Reference')]/following-sibling::text()[1]",
            "//td[contains(text(), 'Reference')]/following-sibling::td[1]",
            "//div[contains(@class, 'reference')]",
            "//strong[contains(text(), 'Reference')]/following-sibling::text()[1]"
        ];
        
        foreach ($selectors as $selector) {
            $nodes = $xpath->query($selector);
            if ($nodes->length > 0) {
                $text = trim($nodes->item(0)->textContent);
                if (!empty($text)) {
                    return $text;
                }
            }
        }
        
        // Try to extract from URL
        if (preg_match('/\/notice\/([^\/]+)/', $_SERVER['REQUEST_URI'] ?? '', $matches)) {
            return $matches[1];
        }
        
        return null;
    }
    
    /**
     * Extract title from PCS page
     */
    private static function extractPCSTitle($xpath, $dom) {
        $selectors = [
            "//h1",
            "//h2[contains(@class, 'title')]",
            "//div[contains(@class, 'title')]",
            "//title"
        ];
        
        foreach ($selectors as $selector) {
            $nodes = $xpath->query($selector);
            if ($nodes->length > 0) {
                $text = trim($nodes->item(0)->textContent);
                if (!empty($text) && strlen($text) > 10) {
                    return $text;
                }
            }
        }
        
        return 'Imported Tender Opportunity';
    }
    
    /**
     * Extract description from PCS page
     */
    private static function extractPCSDescription($xpath, $dom) {
        $selectors = [
            "//div[contains(@class, 'description')]",
            "//div[contains(@class, 'summary')]",
            "//p[contains(@class, 'description')]",
            "//div[@id='description']"
        ];
        
        foreach ($selectors as $selector) {
            $nodes = $xpath->query($selector);
            if ($nodes->length > 0) {
                $text = trim($nodes->item(0)->textContent);
                if (!empty($text)) {
                    return $text;
                }
            }
        }
        
        return null;
    }
    
    /**
     * Extract published date from PCS page
     */
    private static function extractPCSPublishedDate($xpath, $dom) {
        $selectors = [
            "//span[contains(text(), 'Published')]/following-sibling::text()[1]",
            "//td[contains(text(), 'Published')]/following-sibling::td[1]",
            "//div[contains(@class, 'published-date')]"
        ];
        
        foreach ($selectors as $selector) {
            $nodes = $xpath->query($selector);
            if ($nodes->length > 0) {
                $text = trim($nodes->item(0)->textContent);
                $date = self::parseDate($text);
                if ($date) {
                    return $date;
                }
            }
        }
        
        return null;
    }
    
    /**
     * Extract submission deadline from PCS page
     */
    private static function extractPCSDeadline($xpath, $dom) {
        $keywords = ['Deadline', 'Closing', 'Submission', 'Return', 'Due'];
        
        foreach ($keywords as $keyword) {
            $selectors = [
                "//span[contains(text(), '$keyword')]/following-sibling::text()[1]",
                "//td[contains(text(), '$keyword')]/following-sibling::td[1]",
                "//div[contains(text(), '$keyword')]"
            ];
            
            foreach ($selectors as $selector) {
                $nodes = $xpath->query($selector);
                if ($nodes->length > 0) {
                    $text = trim($nodes->item(0)->textContent);
                    $date = self::parseDate($text);
                    if ($date) {
                        return $date;
                    }
                }
            }
        }
        
        return null;
    }
    
    /**
     * Extract estimated value from PCS page
     */
    private static function extractPCSValue($xpath, $dom) {
        $selectors = [
            "//span[contains(text(), 'Value')]/following-sibling::text()[1]",
            "//td[contains(text(), 'Value')]/following-sibling::td[1]",
            "//div[contains(text(), '£')]"
        ];
        
        foreach ($selectors as $selector) {
            $nodes = $xpath->query($selector);
            if ($nodes->length > 0) {
                $text = trim($nodes->item(0)->textContent);
                $value = self::parseCurrency($text);
                if ($value) {
                    return $value;
                }
            }
        }
        
        return null;
    }
    
    /**
     * Extract local authority from PCS page
     */
    private static function extractPCSLocalAuthority($xpath, $dom) {
        $db = getDbConnection();
        
        // Try to find authority name in page
        $selectors = [
            "//span[contains(text(), 'Authority')]/following-sibling::text()[1]",
            "//td[contains(text(), 'Authority')]/following-sibling::td[1]",
            "//div[contains(@class, 'authority')]"
        ];
        
        foreach ($selectors as $selector) {
            $nodes = $xpath->query($selector);
            if ($nodes->length > 0) {
                $text = trim($nodes->item(0)->textContent);
                // Try to match with our local authorities
                $stmt = $db->prepare("SELECT id FROM local_authorities WHERE name LIKE ? LIMIT 1");
                $stmt->execute(["%$text%"]);
                $result = $stmt->fetch();
                if ($result) {
                    return $result['id'];
                }
            }
        }
        
        return null;
    }
    
    /**
     * Extract geographic coverage from PCS page
     */
    private static function extractPCSGeographicCoverage($xpath, $dom) {
        $keywords = ['Location', 'Geographic', 'Coverage', 'Area'];
        
        foreach ($keywords as $keyword) {
            $selectors = [
                "//span[contains(text(), '$keyword')]/following-sibling::text()[1]",
                "//td[contains(text(), '$keyword')]/following-sibling::td[1]"
            ];
            
            foreach ($selectors as $selector) {
                $nodes = $xpath->query($selector);
                if ($nodes->length > 0) {
                    $text = trim($nodes->item(0)->textContent);
                    if (!empty($text)) {
                        return $text;
                    }
                }
            }
        }
        
        return null;
    }
    
    /**
     * Fetch URL content
     */
    private static function fetchUrl($url) {
        // Validate URL
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }
        
        // Use cURL if available, otherwise file_get_contents
        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $html = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200) {
                return $html;
            }
        } else {
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36\r\n",
                    'timeout' => 30
                ]
            ]);
            
            $html = @file_get_contents($url, false, $context);
            if ($html !== false) {
                return $html;
            }
        }
        
        return false;
    }
    
    /**
     * Parse date string to Y-m-d format
     */
    private static function parseDate($dateString) {
        if (empty($dateString)) {
            return null;
        }
        
        // Try common date formats
        $formats = ['d/m/Y', 'd-m-Y', 'Y-m-d', 'd M Y', 'd F Y', 'Y/m/d'];
        
        foreach ($formats as $format) {
            $date = DateTime::createFromFormat($format, trim($dateString));
            if ($date) {
                return $date->format('Y-m-d');
            }
        }
        
        // Try strtotime as fallback
        $timestamp = strtotime($dateString);
        if ($timestamp !== false) {
            return date('Y-m-d', $timestamp);
        }
        
        return null;
    }
    
    /**
     * Parse currency string to decimal
     */
    private static function parseCurrency($currencyString) {
        if (empty($currencyString)) {
            return null;
        }
        
        // Remove currency symbols and extract number
        $cleaned = preg_replace('/[£,\s]/', '', $currencyString);
        
        // Extract number (handles ranges like "£100,000 - £200,000")
        if (preg_match('/(\d+(?:\.\d+)?)/', $cleaned, $matches)) {
            return floatval($matches[1]);
        }
        
        return null;
    }
    
    /**
     * Search Public Contracts Scotland (simplified - returns search URL)
     */
    public static function getPCSSearchUrl($keywords = '', $localAuthority = '') {
        $baseUrl = 'https://www.publiccontractsscotland.gov.uk/search';
        $params = [];
        
        if (!empty($keywords)) {
            $params['keywords'] = urlencode($keywords);
        }
        
        if (!empty($localAuthority)) {
            $params['authority'] = urlencode($localAuthority);
        }
        
        // Add category filter for social care
        $params['category'] = '85000000'; // Health and social work services
        
        if (!empty($params)) {
            return $baseUrl . '?' . http_build_query($params);
        }
        
        return $baseUrl;
    }
}

