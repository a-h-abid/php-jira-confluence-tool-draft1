<?php

namespace AHAbid\JiraItTest;

use Dotenv\Dotenv;
use Symfony\Component\Console\Application;

class Bootstrap
{
    public static function load($baseDir = '.')
    {
        define('BASE_DIR', $baseDir);

        $configPath = $baseDir . '/config.php';
        if (file_exists($configPath)) {
            $config = require $configPath;
            if (is_array($config)) {
                foreach ($config as $key => $value) {
                    $_ENV[$key] = $value;
                }
            }
        }

        $dotenv = Dotenv::createImmutable($baseDir . '/');
        $dotenv->safeLoad();

        $application = new Application();

        $application->add(new \AHAbid\JiraItTest\Console\FetchJiraContentAndSaveCommand());
        $application->add(new \AHAbid\JiraItTest\Console\FetchJiraIssueCommentsAndSaveCommand());
        $application->add(new \AHAbid\JiraItTest\Console\ReadCsvJiraIssuesAndSaveContentsCommand());

        $application->run();
    }
}
