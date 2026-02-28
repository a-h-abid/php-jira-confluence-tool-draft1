<?php

namespace AHAbid\JiraItTest\Tests\Utils;

use AHAbid\JiraItTest\Utils\Logger;
use PHPUnit\Framework\TestCase;

class LoggerTest extends TestCase
{
    private string $logPath;
    private string $logFile;

    protected function setUp(): void
    {
        parent::setUp();
        // Logger uses __DIR__ . '/../../logs/', so we need to create a temporary logs directory
        $this->logPath = sys_get_temp_dir() . '/test_logs_' . uniqid();
        mkdir($this->logPath, 0777, true);

        $this->logFile = $this->logPath . '/log-' . date('Ymd') . '.log';
    }

    protected function tearDown(): void
    {
        // Clean up log files
        if (file_exists($this->logFile)) {
            unlink($this->logFile);
        }
        if (is_dir($this->logPath)) {
            rmdir($this->logPath);
        }

        // Clean up logs directory created by Logger (in src/../logs)
        $actualLogFile = __DIR__ . '/../../logs/log-' . date('Ymd') . '.log';
        if (file_exists($actualLogFile)) {
            // Just verify it exists, but don't delete it as it might be used by the application
        }

        parent::tearDown();
    }

    public function testLoggerCreatesLogFile()
    {
        $logger = new Logger();
        $logger->write('Test message');

        // Logger creates file in __DIR__ . '/../../logs/'
        $logFile = __DIR__ . '/../../logs/log-' . date('Ymd') . '.log';

        $this->assertFileExists($logFile);
    }

    public function testLoggerWritesMessageWithInfoLevel()
    {
        $logger = new Logger();
        $message = 'Test info message ' . uniqid();
        $logger->write($message, 'info');

        $logFile = __DIR__ . '/../../logs/log-' . date('Ymd') . '.log';
        $content = file_get_contents($logFile);

        $this->assertStringContainsString('INFO:', $content);
        $this->assertStringContainsString($message, $content);
    }

    public function testLoggerWritesMessageWithErrorLevel()
    {
        $logger = new Logger();
        $message = 'Test error message ' . uniqid();
        $logger->write($message, 'error');

        $logFile = __DIR__ . '/../../logs/log-' . date('Ymd') . '.log';
        $content = file_get_contents($logFile);

        $this->assertStringContainsString('ERROR:', $content);
        $this->assertStringContainsString($message, $content);
    }

    public function testLoggerWritesMessageWithWarningLevel()
    {
        $logger = new Logger();
        $message = 'Test warning message ' . uniqid();
        $logger->write($message, 'warning');

        $logFile = __DIR__ . '/../../logs/log-' . date('Ymd') . '.log';
        $content = file_get_contents($logFile);

        $this->assertStringContainsString('WARNING:', $content);
        $this->assertStringContainsString($message, $content);
    }

    public function testLoggerWritesMessageWithContext()
    {
        $logger = new Logger();
        $message = 'Test message with context ' . uniqid();
        $context = ['key1' => 'value1', 'key2' => 'value2'];
        $logger->write($message, 'info', $context);

        $logFile = __DIR__ . '/../../logs/log-' . date('Ymd') . '.log';
        $content = file_get_contents($logFile);

        $this->assertStringContainsString($message, $content);
        $this->assertStringContainsString('"key1":"value1"', $content);
        $this->assertStringContainsString('"key2":"value2"', $content);
    }

    public function testLoggerWritesMessageWithEmptyContext()
    {
        $logger = new Logger();
        $message = 'Test message without context ' . uniqid();
        $logger->write($message, 'info', []);

        $logFile = __DIR__ . '/../../logs/log-' . date('Ymd') . '.log';
        $content = file_get_contents($logFile);

        $this->assertStringContainsString($message, $content);
        $this->assertStringContainsString('[]', $content);
    }

    public function testLoggerIncludesTimestamp()
    {
        $logger = new Logger();
        $message = 'Test timestamp message ' . uniqid();
        $logger->write($message);

        $logFile = __DIR__ . '/../../logs/log-' . date('Ymd') . '.log';
        $content = file_get_contents($logFile);

        // Check for timestamp format [YYYY-MM-DD HH:MM:SS.microseconds]
        $this->assertMatchesRegularExpression('/\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\.\d+\]/', $content);
    }

    public function testLoggerDefaultsToInfoLevel()
    {
        $logger = new Logger();
        $message = 'Test default level ' . uniqid();
        $logger->write($message);

        $logFile = __DIR__ . '/../../logs/log-' . date('Ymd') . '.log';
        $content = file_get_contents($logFile);

        $this->assertStringContainsString('INFO:', $content);
    }
}
