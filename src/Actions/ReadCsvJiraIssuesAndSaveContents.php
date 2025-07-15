<?php

namespace AHAbid\JiraItTest\Actions;

use AHAbid\JiraItTest\Services\JiraService;
use AHAbid\JiraItTest\Utils\CsvReader;

class ReadCsvJiraIssuesAndSaveContents
{
    public static function execute()
    {
        $filePath = BASE_DIR . '/files/input/jira-stories.csv';
        $jiraService = JiraService::build();
        $storePath = BASE_DIR . '/files/stories/';

        foreach (CsvReader::read($filePath) as $row) {
            $issueKey = $row[0];
            if ($issueKey == 'Issue key') {
                continue;
            }

            echo 'Fetching for ' . $issueKey . PHP_EOL;

            $content = trim($jiraService->getContent($issueKey));
            if ($content == '') {
                writeLog("{$issueKey} content is empty, fetched via JiraService.");
                echo 'Empty content for ' . $issueKey . PHP_EOL;
                continue;
            }

            file_put_contents($storePath . '/' . $issueKey . '.md', $content);
            echo 'Stored content for ' . $issueKey . PHP_EOL;
            sleep(1);
        }
    }
}