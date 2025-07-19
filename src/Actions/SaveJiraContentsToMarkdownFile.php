<?php

namespace AHAbid\JiraItTest\Actions;

use AHAbid\JiraItTest\Config\FilePath;
use AHAbid\JiraItTest\Exceptions\EmptyContentException;

class SaveJiraContentsToMarkdownFile
{
    /**
     * @throws EmptyContentException
     */
    public static function execute(string $issueKey, string $content)
    {
        if (empty($content)) {
            throw new EmptyContentException("Content of {$issueKey} is empty.");
        }

        file_put_contents(FilePath::STORIES . $issueKey . '.md', $content);
    }
}
