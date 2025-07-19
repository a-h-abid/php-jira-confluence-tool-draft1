<?php

namespace AHAbid\JiraItTest\Actions;

use AHAbid\JiraItTest\Exceptions\EmptyContentException;
use AHAbid\JiraItTest\Services\JiraService;

class FetchJiraContentFromJiraCloud
{
    /**
     * @throws EmptyContentException
     */
    public static function execute(string $issueKey): string
    {
        $jiraService = JiraService::build();

        $content = trim($jiraService->getContent($issueKey, true));

        if (empty($content)) {
            throw new EmptyContentException("Content of {$issueKey} is empty.");
        }

        return $content;
    }
}
