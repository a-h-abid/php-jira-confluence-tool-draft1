<?php

namespace AHAbid\JiraItTest\Console;

use AHAbid\JiraItTest\Actions\FetchJiraContentFromJiraCloud;
use AHAbid\JiraItTest\Actions\SaveJiraContentsToMarkdownFile;
use AHAbid\JiraItTest\Exceptions\EmptyContentException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'jira:fetch-and-save-content',
    description: 'Fetch Content by Jira Issue Key and Store in markdown file.',
    help: 'This command allows you to save Jira content in markdown file...',
)]
class FetchJiraContentAndSaveCommand extends Command
{
    public function __invoke(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $issueKey = $input->getArgument('issue-key');

        try {
            $output->writeln('Fetching for ' . $issueKey);

            $content = FetchJiraContentFromJiraCloud::execute($issueKey);

            SaveJiraContentsToMarkdownFile::execute($issueKey, $content);

            $output->writeln('Stored content for ' . $issueKey);

            $output->writeln([
                '<info>========================</>',
                '<info>Operation Completed!</>',
                '',
            ]);

            return Command::SUCCESS;
        } catch (EmptyContentException $e) {
            writeLog("[Command FetchJiraContentAndSaveCommand]: {$e->getMessage()}", 'error');

            $output->writeln('<error>Empty content for ' . $issueKey. '</>');

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
