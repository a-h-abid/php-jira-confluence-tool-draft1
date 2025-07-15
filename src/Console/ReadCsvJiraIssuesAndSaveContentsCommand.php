<?php

namespace AHAbid\JiraItTest\Console;

use AHAbid\JiraItTest\Services\JiraService;
use AHAbid\JiraItTest\Utils\CsvReader;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;

#[AsCommand(
    name: 'jira:read-from-csv-and-save-contents',
    description: 'Get Jira Story IDs from CSV, fetch contents and store in markdown file.',
    help: 'This command allows you to save Jira contents in markdown file...',
)]
class ReadCsvJiraIssuesAndSaveContentsCommand extends Command
{
    public function __invoke(): int
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

        return Command::SUCCESS;
    }
}