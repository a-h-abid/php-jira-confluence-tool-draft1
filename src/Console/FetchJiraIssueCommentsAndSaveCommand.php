<?php

namespace AHAbid\JiraItTest\Console;

use AHAbid\JiraItTest\Actions\FetchJiraCommentsFromJiraCloud;
use AHAbid\JiraItTest\Actions\SaveJiraCommentsToMarkdownFile;
use AHAbid\JiraItTest\Exceptions\EmptyCommentsException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'jira:fetch-and-save-comments',
    description: 'Fetch Comments by Jira Issue Key and Store in markdown file.',
    help: 'This command allows you to save Jira comments in markdown file...',
)]
class FetchJiraIssueCommentsAndSaveCommand extends Command
{
    public function __invoke(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $issueKey = $input->getArgument('issue-key');

        try {
            $output->writeln('Fetching comments for ' . $issueKey);

            $comments = FetchJiraCommentsFromJiraCloud::execute($issueKey);

            SaveJiraCommentsToMarkdownFile::execute($issueKey, $comments);

            $output->writeln('Stored comments for ' . $issueKey);

            $output->writeln([
                '<info>========================</>',
                '<info>Operation Completed!</>',
                '',
            ]);

            return Command::SUCCESS;
        } catch (EmptyCommentsException $e) {
            writeLog("[Command FetchJiraIssueCommentsAndSaveCommand]: {$e->getMessage()}", 'error');

            $output->writeln('<error>Empty comments for ' . $issueKey. '</>');

            return Command::FAILURE;
        }
    }

    protected function configure(): void
    {
        $this
            ->addArgument('issue-key', InputArgument::REQUIRED, 'Jira Issue Key')
        ;
    }
}
