<?php

namespace AHAbid\JiraItTest\Console;

use AHAbid\JiraItTest\Actions\FetchJiraContentFromJiraCloud;
use AHAbid\JiraItTest\Actions\SaveJiraContentsToMarkdownFile;
use AHAbid\JiraItTest\Utils\CsvReader;
use AHAbid\JiraItTest\Config\FilePath;
use AHAbid\JiraItTest\Exceptions\EmptyContentException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'jira:read-from-csv-and-save-contents',
    description: 'Use Jira Story IDs from CSV to fetch contents if not cached already and store in markdown file.',
    help: 'This command allows you to save Jira issues contents in markdown file...',
)]
class ReadCsvJiraIssuesAndSaveContentsCommand extends Command
{
    public function __invoke(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $force = $input->getOption('force');

        foreach (CsvReader::read(FilePath::INPUT . 'jira-stories.csv') as $row) {
            $issueKey = $row[0];
            if ($issueKey == 'Issue key') {
                continue;
            }

            $storedStoryFile = FilePath::issueStoryPath($issueKey);
            if (!$force && file_exists($storedStoryFile)) {
                writeLog("[Command ReadCsvJiraIssuesAndSaveContentsCommand]: Skipping {$issueKey}, file exists", 'warn');
                $output->writeln("Skipping {$issueKey}, file exists");

                continue;
            }

            try {
                $output->writeln('Fetching content for ' . $issueKey);

                $content = FetchJiraContentFromJiraCloud::execute($issueKey);

                SaveJiraContentsToMarkdownFile::execute($issueKey, $content);

                $output->writeln('Stored content for ' . $issueKey);

                sleep(1);
            } catch (EmptyContentException $e) {
                writeLog("[Command FetchJiraContentAndSaveCommand]: {$e->getMessage()}", 'error');
                $output->writeln('<error>Empty content for ' . $issueKey. '</>');
            }
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
