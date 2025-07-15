<?php

class JiraUrlScanner {
    private $jiraUrl;
    private $username;
    private $apiToken;
    private $targetUrlPattern;
    
    public function __construct($jiraUrl, $username, $apiToken, $targetUrlPattern = 'https://abc.example.com/solutions') {
        $this->jiraUrl = rtrim($jiraUrl, '/');
        $this->username = $username;
        $this->apiToken = $apiToken;
        $this->targetUrlPattern = $targetUrlPattern;
    }
    
    /**
     * Make authenticated API request to JIRA
     */
    private function makeApiRequest($endpoint) {
        $url = $this->jiraUrl . '/rest/api/2/' . ltrim($endpoint, '/');
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Basic ' . base64_encode($this->username . ':' . $this->apiToken),
            'Accept: application/json',
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception("cURL Error: " . $error);
        }
        
        if ($httpCode !== 200) {
            throw new Exception("HTTP Error: " . $httpCode . " - " . $response);
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Search for issues using JQL
     */
    public function searchIssues($jql = '', $maxResults = 100) {
        $endpoint = 'search';
        $params = [
            'jql' => $jql ?: 'project is not empty',
            'maxResults' => $maxResults,
            'fields' => 'summary,description,comment,key,project'
        ];
        
        $endpoint .= '?' . http_build_query($params);
        return $this->makeApiRequest($endpoint);
    }
    
    /**
     * Get all comments for a specific issue
     */
    public function getIssueComments($issueKey) {
        $endpoint = "issue/{$issueKey}/comment";
        return $this->makeApiRequest($endpoint);
    }
    
    /**
     * Extract URLs from text content
     */
    private function extractUrls($text) {
        if (empty($text)) return [];
        
        // Enhanced regex to find URLs
        $urlPattern = '/https?:\/\/(?:[-\w.])+(?::[0-9]+)?(?:\/(?:[\w\/_.])*)?(?:\?(?:[\w&=%.])*)?(?:#(?:[\w.])*)?/i';
        preg_match_all($urlPattern, $text, $matches);
        
        return array_unique($matches[0]);
    }
    
    /**
     * Check if URL matches the target pattern
     */
    private function matchesTargetPattern($url) {
        return stripos($url, $this->targetUrlPattern) !== false;
    }
    
    /**
     * Scan issue content for target URLs
     */
    private function scanIssueContent($issue) {
        $foundUrls = [];
        $issueKey = $issue['key'];
        
        // Check summary
        if (!empty($issue['fields']['summary'])) {
            $urls = $this->extractUrls($issue['fields']['summary']);
            foreach ($urls as $url) {
                if ($this->matchesTargetPattern($url)) {
                    $foundUrls[] = [
                        'url' => $url,
                        'location' => 'summary',
                        'issue' => $issueKey
                    ];
                }
            }
        }
        
        // Check description
        if (!empty($issue['fields']['description'])) {
            $urls = $this->extractUrls($issue['fields']['description']);
            foreach ($urls as $url) {
                if ($this->matchesTargetPattern($url)) {
                    $foundUrls[] = [
                        'url' => $url,
                        'location' => 'description',
                        'issue' => $issueKey
                    ];
                }
            }
        }
        
        // Check comments
        try {
            $comments = $this->getIssueComments($issueKey);
            if (!empty($comments['comments'])) {
                foreach ($comments['comments'] as $comment) {
                    if (!empty($comment['body'])) {
                        $urls = $this->extractUrls($comment['body']);
                        foreach ($urls as $url) {
                            if ($this->matchesTargetPattern($url)) {
                                $foundUrls[] = [
                                    'url' => $url,
                                    'location' => 'comment',
                                    'issue' => $issueKey,
                                    'comment_id' => $comment['id'],
                                    'comment_author' => $comment['author']['displayName'] ?? 'Unknown'
                                ];
                            }
                        }
                    }
                }
            }
        } catch (Exception $e) {
            echo "Warning: Could not fetch comments for {$issueKey}: " . $e->getMessage() . "\n";
        }
        
        return $foundUrls;
    }
    
    /**
     * Main scan function
     */
    public function scanForUrls($jql = '', $maxResults = 100) {
        echo "Starting JIRA URL scan...\n";
        echo "Looking for URLs containing: {$this->targetUrlPattern}\n\n";
        
        try {
            $searchResults = $this->searchIssues($jql, $maxResults);
            $issues = $searchResults['issues'] ?? [];
            $totalResults = $searchResults['total'] ?? 0;
            
            echo "Found {$totalResults} issues to scan\n";
            echo "Processing " . count($issues) . " issues in this batch\n\n";
            
            $allFoundUrls = [];
            $processedCount = 0;
            
            foreach ($issues as $issue) {
                $processedCount++;
                $issueKey = $issue['key'];
                
                echo "Processing [{$processedCount}/" . count($issues) . "] {$issueKey}... ";
                
                $foundUrls = $this->scanIssueContent($issue);
                
                if (!empty($foundUrls)) {
                    $allFoundUrls = array_merge($allFoundUrls, $foundUrls);
                    echo "Found " . count($foundUrls) . " matching URL(s)\n";
                } else {
                    echo "No matching URLs found\n";
                }
                
                // Small delay to avoid overwhelming the API
                usleep(100000); // 0.1 seconds
            }
            
            return $allFoundUrls;
            
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
            return [];
        }
    }
    
    /**
     * Generate report of found URLs
     */
    public function generateReport($foundUrls) {
        if (empty($foundUrls)) {
            echo "\n=== SCAN COMPLETE ===\n";
            echo "No URLs matching the pattern '{$this->targetUrlPattern}' were found.\n";
            return;
        }
        
        echo "\n=== SCAN RESULTS ===\n";
        echo "Found " . count($foundUrls) . " URLs matching the pattern '{$this->targetUrlPattern}':\n\n";
        
        // Group by issue
        $groupedByIssue = [];
        foreach ($foundUrls as $result) {
            $groupedByIssue[$result['issue']][] = $result;
        }
        
        foreach ($groupedByIssue as $issueKey => $urls) {
            echo "Issue: {$issueKey}\n";
            echo "JIRA URL: {$this->jiraUrl}/browse/{$issueKey}\n";
            
            foreach ($urls as $urlData) {
                echo "  - URL: {$urlData['url']}\n";
                echo "    Location: {$urlData['location']}\n";
                
                if ($urlData['location'] === 'comment') {
                    echo "    Comment ID: {$urlData['comment_id']}\n";
                    echo "    Comment Author: {$urlData['comment_author']}\n";
                }
                echo "\n";
            }
            echo "---\n\n";
        }
    }
}

// Configuration
$config = [
    'jira_url' => 'https://your-domain.atlassian.net', // Replace with your JIRA URL
    'username' => 'your-email@example.com',             // Replace with your email
    'api_token' => 'your-api-token',                    // Replace with your API token
    'target_url_pattern' => 'https://abc.example.com/solutions', // URL pattern to search for
    'jql' => 'project = "PROJECT_KEY"',                 // Optional: JQL to filter issues
    'max_results' => 100                                // Maximum number of issues to process
];

// Usage example
try {
    $scanner = new JiraUrlScanner(
        $config['jira_url'],
        $config['username'],
        $config['api_token'],
        $config['target_url_pattern']
    );
    
    // Scan for URLs
    $foundUrls = $scanner->scanForUrls($config['jql'], $config['max_results']);
    
    // Generate report
    $scanner->generateReport($foundUrls);
    
} catch (Exception $e) {
    echo "Fatal Error: " . $e->getMessage() . "\n";
}
