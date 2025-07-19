<?php

namespace AHAbid\JiraItTest;

use Dotenv\Dotenv;
use Symfony\Component\Console\Application;

class Bootstrap
{
    public static function load($baseDir = '.')
    {
        define('BASE_DIR', $baseDir);

        $dotenv = Dotenv::createImmutable($baseDir . '/');
        $dotenv->load();

        $application = new Application();

        $application->add(new \AHAbid\JiraItTest\Console\FetchJiraContentAndSaveCommand());
        $application->add(new \AHAbid\JiraItTest\Console\FetchJiraIssueCommentsAndSaveCommand());
        $application->add(new \AHAbid\JiraItTest\Console\ReadCsvJiraIssuesAndSaveContentsCommand());

        $application->run();
    }
}
