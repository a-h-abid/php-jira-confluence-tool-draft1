<?php

namespace AHAbid\JiraItTest\Actions;

use AHAbid\JiraItTest\Config\FilePath;
use AHAbid\JiraItTest\DTO\JiraComment;
use AHAbid\JiraItTest\Exceptions\EmptyCommentsException;

class SaveJiraCommentsToMarkdownFile
{
    /**
     * @param JiraComment[] $comments
     * @throws EmptyCommentsException
     */
    public static function execute(string $issueKey, array $comments)
    {
        if (empty($comments)) {
            throw new EmptyCommentsException("Comments of {$issueKey} is empty.");
        }

        $commentsMarkDown = '';

        foreach ($comments as $comment) {
            $commentsMarkDown .= "---\n\n";
            $commentsMarkDown .= "Created: " . $comment->createdAt->format('Y-m-d H:i:s.vO') . "\n";
            $commentsMarkDown .= "Author: " . $comment->authorName . " <{$comment->authorEmail}>\n";
            $commentsMarkDown .= "\n";
            $commentsMarkDown .= "{$comment->body}\n";
            $commentsMarkDown .= "\n";
        }

        file_put_contents(FilePath::STORIES . $issueKey . '-comments.md', $commentsMarkDown);
    }
}
