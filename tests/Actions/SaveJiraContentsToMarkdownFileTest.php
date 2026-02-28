<?php

namespace AHAbid\JiraItTest\Tests\Actions;

use AHAbid\JiraItTest\Actions\SaveJiraContentsToMarkdownFile;
use AHAbid\JiraItTest\Config\FilePath;
use AHAbid\JiraItTest\Exceptions\EmptyContentException;
use PHPUnit\Framework\TestCase;

class SaveJiraContentsToMarkdownFileTest extends TestCase
{
    private string $testStoriesDir;

    protected function setUp(): void
    {
        parent::setUp();

        // Define BASE_DIR if not already defined
        if (!defined('BASE_DIR')) {
            define('BASE_DIR', sys_get_temp_dir() . '/test_base_' . uniqid());
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

    public function testSavesContentToMarkdownFile()
    {
        $issueKey = 'TEST-123';
        $content = '# Test Content\n\nThis is test markdown content.';

        SaveJiraContentsToMarkdownFile::execute($issueKey, $content);

        $expectedFile = FilePath::STORIES . $issueKey . '.md';
        $this->assertFileExists($expectedFile);

        $savedContent = file_get_contents($expectedFile);
        $this->assertEquals($content, $savedContent);
    }

    public function testThrowsExceptionForEmptyContent()
    {
        $issueKey = 'TEST-456';

        $this->expectException(EmptyContentException::class);
        $this->expectExceptionMessage("Content of {$issueKey} is empty.");

        SaveJiraContentsToMarkdownFile::execute($issueKey, '');
    }

    public function testSavesContentWithSpecialCharacters()
    {
        $issueKey = 'TEST-789';
        $content = "# Special Characters Test\n\n* Bullet point\n* Another bullet\n\n**Bold** and *italic*\n\n```php\necho 'code';\n```";

        SaveJiraContentsToMarkdownFile::execute($issueKey, $content);

        $expectedFile = FilePath::STORIES . $issueKey . '.md';
        $this->assertFileExists($expectedFile);

        $savedContent = file_get_contents($expectedFile);
        $this->assertEquals($content, $savedContent);
    }

    public function testOverwritesExistingFile()
    {
        $issueKey = 'TEST-999';
        $oldContent = 'Old content';
        $newContent = 'New content';

        $file = FilePath::STORIES . $issueKey . '.md';

        // Create file with old content
        file_put_contents($file, $oldContent);
        $this->assertEquals($oldContent, file_get_contents($file));

        // Save new content
        SaveJiraContentsToMarkdownFile::execute($issueKey, $newContent);

        // Verify new content
        $savedContent = file_get_contents($file);
        $this->assertEquals($newContent, $savedContent);
    }

    public function testSavesContentWithUnicodeCharacters()
    {
        $issueKey = 'TEST-111';
        $content = "# Unicode Test\n\nHello 世界 🌍\n\nΓεια σου κόσμε";

        SaveJiraContentsToMarkdownFile::execute($issueKey, $content);

        $expectedFile = FilePath::STORIES . $issueKey . '.md';
        $this->assertFileExists($expectedFile);

        $savedContent = file_get_contents($expectedFile);
        $this->assertEquals($content, $savedContent);
    }
}
