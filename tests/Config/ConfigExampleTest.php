<?php

namespace AHAbid\JiraItTest\Tests\Config;

use PHPUnit\Framework\TestCase;

class ConfigExampleTest extends TestCase
{
    public function testConfigExampleContainsExpectedKeys(): void
    {
        $configPath = __DIR__ . '/../../config.example.php';

        $this->assertFileExists($configPath);

        $config = require $configPath;

        $this->assertIsArray($config);

        $expectedKeys = [
            'JIRA_URL',
            'JIRA_USERNAME',
            'JIRA_API_TOKEN',
            'SOLUTIONS_URL_LINK_PATTERN',
        ];

        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $config);
            $this->assertNotSame('', $config[$key]);
        }
    }
}
