<?php

namespace AHAbid\JiraItTest\Actions;

use AHAbid\JiraItTest\Exceptions\EmptyContentException;
use AHAbid\JiraItTest\Services\JiraService;

class FetchJiraContentFromJiraCloud
{
    private static $jiraService;

    /**
     * @throws EmptyContentException
     */
    public static function execute(string $issueKey): string
    {
        $jiraService = self::getJiraService();

        $content = trim($jiraService->getContent($issueKey, true));

        if (empty($content)) {
            throw new EmptyContentException("Content of {$issueKey} is empty.");
        }

        return $content;
    }

    public static function clearInstance()
    {
        self::$jiraService = null;
    }

    private static function getJiraService()
    {
        if (empty(self::$jiraService)) {
            self::$jiraService = JiraService::build();
        }

        return self::$jiraService;
    }
}
