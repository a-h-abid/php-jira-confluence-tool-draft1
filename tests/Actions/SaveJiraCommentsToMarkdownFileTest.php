<?php

namespace AHAbid\JiraItTest\Tests\Actions;

use AHAbid\JiraItTest\Actions\SaveJiraCommentsToMarkdownFile;
use AHAbid\JiraItTest\Config\FilePath;
use AHAbid\JiraItTest\DTO\JiraComment;
use AHAbid\JiraItTest\Exceptions\EmptyCommentsException;
use DateTime;
use PHPUnit\Framework\TestCase;

class SaveJiraCommentsToMarkdownFileTest extends TestCase
{
    private string $testStoriesDir;

    protected function setUp(): void
    {
        parent::setUp();

        // Define BASE_DIR if not already defined
        if (!defined('BASE_DIR')) {
            define('BASE_DIR', sys_get_temp_dir() . '/test_base_comments_' . uniqid());
        }

        // Create test stories directory
        $this->testStoriesDir = BASE_DIR . '/files/stories';
        if (!is_dir($this->testStoriesDir)) {
            mkdir($this->testStoriesDir, 0777, true);
        }
    }

    protected function tearDown(): void
    {
        // Clean up test files
        if (is_dir($this->testStoriesDir)) {
            $files = glob($this->testStoriesDir . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }

        parent::tearDown();
    }

    public function testSavesSingleCommentToMarkdownFile()
    {
        $issueKey = 'TEST-123';
        $comment = new JiraComment(
            createdAt: new DateTime('2024-01-15 10:30:00.123456+0000'),
            body: 'This is a test comment',
            authorName: 'John Doe',
            authorEmail: 'john.doe@example.com'
        );

        SaveJiraCommentsToMarkdownFile::execute($issueKey, [$comment]);

        $expectedFile = FilePath::STORIES . $issueKey . '-comments.md';
        $this->assertFileExists($expectedFile);

        $savedContent = file_get_contents($expectedFile);
        $this->assertStringContainsString('---', $savedContent);
        $this->assertMatchesRegularExpression('/Created: 2024-01-15 10:30:00\.\d+\+0000/', $savedContent);
        $this->assertStringContainsString('Author: John Doe <john.doe@example.com>', $savedContent);
        $this->assertStringContainsString('This is a test comment', $savedContent);
    }

    public function testSavesMultipleCommentsToMarkdownFile()
    {
        $issueKey = 'TEST-456';
        $comments = [
            new JiraComment(
                createdAt: new DateTime('2024-01-15 10:30:00.123456+0000'),
                body: 'First comment',
                authorName: 'John Doe',
                authorEmail: 'john.doe@example.com'
            ),
            new JiraComment(
                createdAt: new DateTime('2024-01-16 11:45:00.654321+0000'),
                body: 'Second comment',
                authorName: 'Jane Smith',
                authorEmail: 'jane.smith@example.com'
            ),
        ];

        SaveJiraCommentsToMarkdownFile::execute($issueKey, $comments);

        $expectedFile = FilePath::STORIES . $issueKey . '-comments.md';
        $this->assertFileExists($expectedFile);

        $savedContent = file_get_contents($expectedFile);
        $this->assertStringContainsString('First comment', $savedContent);
        $this->assertStringContainsString('Second comment', $savedContent);
        $this->assertStringContainsString('John Doe', $savedContent);
        $this->assertStringContainsString('Jane Smith', $savedContent);

        // Count separators (should be 2 for 2 comments)
        $separatorCount = substr_count($savedContent, "---\n\n");
        $this->assertEquals(2, $separatorCount);
    }

    public function testThrowsExceptionForEmptyComments()
    {
        $issueKey = 'TEST-789';

        $this->expectException(EmptyCommentsException::class);
        $this->expectExceptionMessage("Comments of {$issueKey} is empty.");

        SaveJiraCommentsToMarkdownFile::execute($issueKey, []);
    }

    public function testSavesCommentWithNullEmail()
    {
        $issueKey = 'TEST-999';
        $comment = new JiraComment(
            createdAt: new DateTime('2024-01-15 10:30:00.123456+0000'),
            body: 'Comment without email',
            authorName: 'Anonymous User'
        );

        SaveJiraCommentsToMarkdownFile::execute($issueKey, [$comment]);

        $expectedFile = FilePath::STORIES . $issueKey . '-comments.md';
        $this->assertFileExists($expectedFile);

        $savedContent = file_get_contents($expectedFile);
        $this->assertStringContainsString('Author: Anonymous User <>', $savedContent);
    }

    public function testSavesCommentWithMarkdownFormatting()
    {
        $issueKey = 'TEST-111';
        $comment = new JiraComment(
            createdAt: new DateTime('2024-01-15 10:30:00.123456+0000'),
            body: "# Heading\n\n* Bullet 1\n* Bullet 2\n\n**Bold text**",
            authorName: 'Test User',
            authorEmail: 'test@example.com'
        );

        SaveJiraCommentsToMarkdownFile::execute($issueKey, [$comment]);

        $expectedFile = FilePath::STORIES . $issueKey . '-comments.md';
        $this->assertFileExists($expectedFile);

        $savedContent = file_get_contents($expectedFile);
        $this->assertStringContainsString('# Heading', $savedContent);
        $this->assertStringContainsString('* Bullet 1', $savedContent);
        $this->assertStringContainsString('**Bold text**', $savedContent);
    }

    public function testOverwritesExistingCommentsFile()
    {
        $issueKey = 'TEST-222';
        $file = FilePath::STORIES . $issueKey . '-comments.md';

        // Create file with old content
        file_put_contents($file, 'Old comments content');

        $comment = new JiraComment(
            createdAt: new DateTime('2024-01-15 10:30:00.123456+0000'),
            body: 'New comment',
            authorName: 'New Author',
            authorEmail: 'new@example.com'
        );

        SaveJiraCommentsToMarkdownFile::execute($issueKey, [$comment]);

        $savedContent = file_get_contents($file);
        $this->assertStringNotContainsString('Old comments content', $savedContent);
        $this->assertStringContainsString('New comment', $savedContent);
        $this->assertStringContainsString('New Author', $savedContent);
    }

    public function testFormatsCommentsCorrectly()
    {
        $issueKey = 'TEST-333';
        $comment = new JiraComment(
            createdAt: new DateTime('2024-01-15 10:30:00.123456+0000'),
            body: 'Test body',
            authorName: 'Test Author',
            authorEmail: 'test@example.com'
        );

        SaveJiraCommentsToMarkdownFile::execute($issueKey, [$comment]);

        $expectedFile = FilePath::STORIES . $issueKey . '-comments.md';
        $savedContent = file_get_contents($expectedFile);

        // Verify the exact format
        $this->assertMatchesRegularExpression('/^---\n\n/', $savedContent);
        $this->assertMatchesRegularExpression('/Created: \d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\.\d+\+\d{4}\n/', $savedContent);
        $this->assertMatchesRegularExpression('/Author: .+ <.+>\n/', $savedContent);
    }
}
