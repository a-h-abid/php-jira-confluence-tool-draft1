<?php

namespace AHAbid\JiraItTest\Console;

use AHAbid\JiraItTest\Services\JiraService;
use AHAbid\JiraItTest\Utils\CsvReader;
use AHAbid\JiraItTest\Config\FilePath;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'jira:read-from-csv-and-save-contents',
    description: 'Get Jira Story IDs from CSV, fetch contents and store in markdown file.',
    help: 'This command allows you to save Jira contents in markdown file...',
)]
class ReadCsvJiraIssuesAndSaveContentsCommand extends Command
{

    public function __invoke(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $force = $input->getOption('force');

        $jiraService = JiraService::build();

        foreach (CsvReader::read(FilePath::INPUT . 'jira-stories.csv') as $row) {
            $issueKey = $row[0];
            if ($issueKey == 'Issue key') {
                continue;
            }

            $storedStoryFile = FilePath::STORIES . $issueKey . '.md';
            if (!$force && file_exists($storedStoryFile)) {
                $output->writeln('Skipping fetch as file exists for ' . $issueKey);
                continue;
            }

            $output->writeln('Fetching for ' . $issueKey);

            $content = trim($jiraService->getContent($issueKey));
            if ($content == '') {
                writeLog("{$issueKey} content is empty, fetched via JiraService.");
                $output->writeln('Empty content for ' . $issueKey);
                continue;
            }

            file_put_contents($storedStoryFile, $content);
            $output->writeln('Stored content for ' . $issueKey);
            sleep(1);
        }

        $output->writeln([
            '<info>========================</>',
            '<info>Operation Completed!</>',
            '',
        ]);

        return Command::SUCCESS;
    }

    protected function configure(): void
    {
        $this
            ->addOption('force', null, InputOption::VALUE_NONE, 'Force to re-fetch contents from Jira Cloud', 1)
        ;
    }
}