<?php

namespace AHAbid\JiraItTest\Actions;

use AHAbid\JiraItTest\DTO\JiraComment;
use AHAbid\JiraItTest\Exceptions\EmptyCommentsException;
use AHAbid\JiraItTest\Services\JiraService;

class FetchJiraCommentsFromJiraCloud
{
    private static $jiraService;

    /**
     * @return JiraComment[]
     * @throws EmptyContentException
     */
    public static function execute(string $issueKey): array
    {
        $jiraService = self::getJiraService();

        $comments = $jiraService->getComments($issueKey, true);

        if (empty($comments)) {
            throw new EmptyCommentsException("Comments of {$issueKey} is empty.");
        }

        return $comments;
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
