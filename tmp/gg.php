#!/usr/bin/php
<?php

/**
 * JIRA API Interaction Script (CLI Version)
 *
 * This script fetches a JIRA issue (Story) and its comments to find specific URLs.
 * It looks for a URL in the issue's description and the latest matching URL in the comments.
 *
 * @version 2.0
 * @author  Your Name
 *
 * Usage: php jira_fetch_cli.php PROJ-123
 */

// --- Configuration ---
// Replace with your JIRA instance details.
define('JIRA_URL', 'https://your-jira-instance.atlassian.net'); // e.g., https://example.atlassian.net
define('JIRA_API_USERNAME', 'your-email@example.com'); // Your JIRA email address
define('JIRA_API_TOKEN', 'YOUR_JIRA_API_TOKEN'); // Your JIRA API Token

// The URL pattern to search for.
define('URL_PATTERN', '/https://abc\.example\.com\/solutions\S+/');

// --- CLI Output Colors ---
define('COLOR_RESET', "\033[0m");
define('COLOR_RED', "\033[0;31m");
define('COLOR_GREEN', "\033[0;32m");
define('COLOR_YELLOW', "\033[0;33m");
define('COLOR_BLUE', "\033[0;34m");
define('COLOR_CYAN', "\033[0;36m");
define('COLOR_BOLD', "\033[1m");


/**
 * Fetches data from the JIRA API using cURL.
 *
 * @param string $url The full URL to the JIRA API endpoint.
 * @return array|null The decoded JSON response as an associative array, or null on failure.
 */
function fetch_from_jira_api($url)
{
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
    ]);
    curl_setopt($ch, CURLOPT_USERPWD, JIRA_API_USERNAME . ':' . JIRA_API_TOKEN);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch)) {
        echo COLOR_RED . "cURL Error: " . curl_error($ch) . COLOR_RESET . "\n";
        curl_close($ch);
        return null;
    }

    if ($http_code >= 400) {
        echo COLOR_RED . "HTTP Error: " . $http_code . ". Response: " . $response . COLOR_RESET . "\n";
        curl_close($ch);
        return null;
    }

    curl_close($ch);

    return json_decode($response, true);
}

/**
 * Finds all matching URLs in a given text.
 *
 * @param string $text The text to search within.
 * @return array An array of found URLs.
 */
function find_urls_in_text($text)
{
    preg_match_all(URL_PATTERN, $text, $matches);
    return $matches[0] ?? [];
}

/**
 * Processes a JIRA issue to find the required URLs.
 *
 * @param string $issue_id The JIRA issue ID (e.g., "PROJ-123").
 */
function process_jira_issue($issue_id)
{
    if (empty($issue_id)) {
        echo COLOR_RED . "Please provide a JIRA Issue ID." . COLOR_RESET . "\n";
        return;
    }

    echo COLOR_BOLD . COLOR_BLUE . "Processing JIRA Issue: " . $issue_id . COLOR_RESET . "\n";
    echo "--------------------------------------------------\n";

    // --- 1. Fetch the main issue content ---
    $issue_url = JIRA_URL . '/rest/api/3/issue/' . $issue_id;
    $issue_data = fetch_from_jira_api($issue_url);

    if (!$issue_data) {
        echo COLOR_RED . "Failed to fetch issue data." . COLOR_RESET . "\n";
        return;
    }

    // Extract description from the issue data
    $description = $issue_data['fields']['description']['content'][0]['content'][0]['text'] ?? 'No description found.';
    
    echo COLOR_CYAN . "\n--- Issue Description ---\n" . COLOR_RESET;
    echo $description . "\n";

    // Find URL in the description
    $description_urls = find_urls_in_text($description);
    if (!empty($description_urls)) {
        echo COLOR_GREEN . "\n[SUCCESS] Found URL in description: " . $description_urls[0] . COLOR_RESET . "\n";
    } else {
        echo COLOR_YELLOW . "\n[INFO] No matching URL found in the description." . COLOR_RESET . "\n";
    }

    // --- 2. Fetch the issue comments ---
    $comments_url = JIRA_URL . '/rest/api/3/issue/' . $issue_id . '/comment';
    $comments_data = fetch_from_jira_api($comments_url);

    if (!$comments_data) {
        echo COLOR_RED . "Failed to fetch comments." . COLOR_RESET . "\n";
        return;
    }

    // --- 3. Search for the latest URL in comments ---
    $latest_comment_url = null;
    $latest_comment_date = 0;

    echo COLOR_CYAN . "\n--- Searching Comments ---\n" . COLOR_RESET;

    if (empty($comments_data['comments'])) {
        echo COLOR_YELLOW . "[INFO] No comments found for this issue." . COLOR_RESET . "\n";
    } else {
        foreach ($comments_data['comments'] as $comment) {
            $comment_text = '';
            // Comments can have a complex structure, so we loop through content blocks
            if (isset($comment['body']['content'])) {
                foreach ($comment['body']['content'] as $content_block) {
                    if (isset($content_block['content'])) {
                        foreach ($content_block['content'] as $text_part) {
                             if (isset($text_part['text'])) {
                                $comment_text .= $text_part['text'] . ' ';
                             }
                        }
                    }
                }
            }

            $comment_urls = find_urls_in_text($comment_text);

            if (!empty($comment_urls)) {
                $comment_created_timestamp = strtotime($comment['created']);
                // Check if this comment is newer
                if ($comment_created_timestamp > $latest_comment_date) {
                    $latest_comment_date = $comment_created_timestamp;
                    // We take the first URL found in the latest comment
                    $latest_comment_url = $comment_urls[0];
                }
            }
        }

        if ($latest_comment_url) {
            echo COLOR_GREEN . "[SUCCESS] Latest URL found in comments: " . $latest_comment_url . COLOR_RESET . "\n";
            echo COLOR_YELLOW . "[INFO] From a comment made on: " . date('Y-m-d H:i:s', $latest_comment_date) . COLOR_RESET . "\n";
        } else {
            echo COLOR_YELLOW . "[INFO] No matching URL found in any of the comments." . COLOR_RESET . "\n";
        }
    }
    echo "--------------------------------------------------\n";
}

// --- Main Execution ---
// Ensure the script is run from the command line
if (php_sapi_name() !== 'cli') {
    die("This script is intended for command-line use only.");
}

// Check for the required argument
if ($argc < 2) {
    echo COLOR_BOLD . "JIRA Story URL Fetcher (CLI)\n" . COLOR_RESET;
    echo "Usage: php " . basename(__FILE__) . " <JIRA_ISSUE_ID>\n";
    echo "Example: php " . basename(__FILE__) . " PROJ-123\n";
    exit(1);
}

$issue_id = trim($argv[1]);
process_jira_issue($issue_id);
