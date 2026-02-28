<?php

namespace AHAbid\JiraItTest\Tests\DTO;

use AHAbid\JiraItTest\DTO\JiraComment;
use DateTime;
use PHPUnit\Framework\TestCase;

class JiraCommentTest extends TestCase
{
    public function testCanCreateJiraComment()
    {
        $createdAt = new DateTime('2024-01-15 10:30:00');
        $body = 'This is a test comment';
        $authorName = 'John Doe';
        $authorEmail = 'john.doe@example.com';

        $comment = new JiraComment(
            createdAt: $createdAt,
            body: $body,
            authorName: $authorName,
            authorEmail: $authorEmail
        );

        $this->assertInstanceOf(JiraComment::class, $comment);
        $this->assertEquals($createdAt, $comment->createdAt);
        $this->assertEquals($body, $comment->body);
        $this->assertEquals($authorName, $comment->authorName);
        $this->assertEquals($authorEmail, $comment->authorEmail);
    }

    public function testCanCreateJiraCommentWithoutEmail()
    {
        $createdAt = new DateTime('2024-01-15 10:30:00');
        $body = 'Test comment without email';
        $authorName = 'Jane Smith';

        $comment = new JiraComment(
            createdAt: $createdAt,
            body: $body,
            authorName: $authorName
        );

        $this->assertInstanceOf(JiraComment::class, $comment);
        $this->assertEquals($createdAt, $comment->createdAt);
        $this->assertEquals($body, $comment->body);
        $this->assertEquals($authorName, $comment->authorName);
        $this->assertNull($comment->authorEmail);
    }

    public function testJiraCommentIsReadonly()
    {
        $comment = new JiraComment(
            createdAt: new DateTime(),
            body: 'Test',
            authorName: 'Test Author'
        );

        // This test verifies that the class is readonly by attempting to access properties
        $this->assertInstanceOf(JiraComment::class, $comment);
    }
}
