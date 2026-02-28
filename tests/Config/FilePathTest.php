<?php

namespace AHAbid\JiraItTest\Tests\Config;

use AHAbid\JiraItTest\Config\FilePath;
use PHPUnit\Framework\TestCase;

class FilePathTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Define BASE_DIR if not already defined for testing
        if (!defined('BASE_DIR')) {
            define('BASE_DIR', '/test/base/dir');
        }
    }

    public function testInputConstantIsCorrect()
    {
        $expectedPath = BASE_DIR . '/files/input/';
        $this->assertEquals($expectedPath, FilePath::INPUT);
    }

    public function testOutputConstantIsCorrect()
    {
        $expectedPath = BASE_DIR . '/files/output/';
        $this->assertEquals($expectedPath, FilePath::OUTPUT);
    }

    public function testStoriesConstantIsCorrect()
    {
        $expectedPath = BASE_DIR . '/files/stories/';
        $this->assertEquals($expectedPath, FilePath::STORIES);
    }

    public function testIssueStoryPathWithDefaultExtension()
    {
        $issueKey = 'PROJ-123';
        $expectedPath = FilePath::STORIES . '/' . $issueKey . '.md';

        $actualPath = FilePath::issueStoryPath($issueKey);

        $this->assertEquals($expectedPath, $actualPath);
    }

    public function testIssueStoryPathWithCustomExtension()
    {
        $issueKey = 'PROJ-456';
        $extension = '.txt';
        $expectedPath = FilePath::STORIES . '/' . $issueKey . '.txt';

        $actualPath = FilePath::issueStoryPath($issueKey, $extension);

        $this->assertEquals($expectedPath, $actualPath);
    }

    public function testIssueStoryPathWithSuffix()
    {
        $issueKey = 'PROJ-789';
        $suffix = '-comments';
        $expectedPath = FilePath::STORIES . '/' . $issueKey . '-comments.md';

        $actualPath = FilePath::issueStoryPath($issueKey, '.md', $suffix);

        $this->assertEquals($expectedPath, $actualPath);
    }

    public function testIssueStoryPathWithCustomExtensionAndSuffix()
    {
        $issueKey = 'PROJ-999';
        $extension = '.html';
        $suffix = '-backup';
        $expectedPath = FilePath::STORIES . '/' . $issueKey . '-backup.html';

        $actualPath = FilePath::issueStoryPath($issueKey, $extension, $suffix);

        $this->assertEquals($expectedPath, $actualPath);
    }

    public function testIssueStoryPathWithEmptySuffix()
    {
        $issueKey = 'PROJ-111';
        $expectedPath = FilePath::STORIES . '/' . $issueKey . '.md';

        $actualPath = FilePath::issueStoryPath($issueKey, '.md', '');

        $this->assertEquals($expectedPath, $actualPath);
    }
}
