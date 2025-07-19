<?php

namespace AHAbid\JiraItTest\Console;

use AHAbid\JiraItTest\Actions\FetchJiraCommentsFromJiraCloud;
use AHAbid\JiraItTest\Actions\SaveJiraCommentsToMarkdownFile;
use AHAbid\JiraItTest\Utils\CsvReader;
use AHAbid\JiraItTest\Config\FilePath;
use AHAbid\JiraItTest\Exceptions\EmptyCommentsException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'jira:read-from-csv-and-save-comments',
    description: 'Use Jira Story IDs from CSV to fetch comments if not cached already and store in markdown file.',
    help: 'This command allows you to save Jira issues comments in markdown file...',
)]
class ReadCsvJiraIssuesAndSaveCommentsCommand extends Command
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
                writeLog("[Command ReadCsvJiraIssuesAndSaveCommentsCommand]: Skipping {$issueKey}, file exists", 'warn');
                $output->writeln("Skipping {$issueKey}, file exists");

                continue;
            }

            try {
                $output->writeln('Fetching comments for ' . $issueKey);

                $comments = FetchJiraCommentsFromJiraCloud::execute($issueKey);

                SaveJiraCommentsToMarkdownFile::execute($issueKey, $comments);

                $output->writeln('Stored comments for ' . $issueKey);

                usleep(300);
            } catch (EmptyCommentsException $e) {
                writeLog("[Command ReadCsvJiraIssuesAndSaveCommentsCommand]: {$e->getMessage()}", 'error');
                $output->writeln('<error>Empty comments for ' . $issueKey. '</>');
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
            ->addOption('force', null, InputOption::VALUE_NONE, 'Force to re-fetch comments from Jira Cloud')
        ;
    }
}
