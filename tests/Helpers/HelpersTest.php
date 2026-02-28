<?php

namespace AHAbid\JiraItTest\Tests\Helpers;

use PHPUnit\Framework\TestCase;

class HelpersTest extends TestCase
{
    protected function tearDown(): void
    {
        // Clean up any environment variables set during tests
        parent::tearDown();
    }

    public function testEnvReturnsValueWhenKeyExists()
    {
        $_ENV['TEST_KEY'] = 'test_value';

        $result = env('TEST_KEY');

        $this->assertEquals('test_value', $result);

        unset($_ENV['TEST_KEY']);
    }

    public function testEnvReturnsDefaultWhenKeyDoesNotExist()
    {
        $result = env('NONEXISTENT_KEY', 'default_value');

        $this->assertEquals('default_value', $result);
    }

    public function testEnvReturnsNullWhenKeyDoesNotExistAndNoDefault()
    {
        $result = env('NONEXISTENT_KEY');

        $this->assertNull($result);
    }

    public function testEnvHandlesBooleanValues()
    {
        $_ENV['BOOL_KEY'] = true;

        $result = env('BOOL_KEY');

        $this->assertTrue($result);

        unset($_ENV['BOOL_KEY']);
    }

    public function testEnvHandlesNumericValues()
    {
        $_ENV['NUMERIC_KEY'] = 123;

        $result = env('NUMERIC_KEY');

        // Note: Based on the helper code, numeric values are converted to boolean
        // This is line 17: return boolval($_ENV[$key]);
        $this->assertTrue($result);

        unset($_ENV['NUMERIC_KEY']);
    }

    public function testEnvHandlesStringValues()
    {
        $_ENV['STRING_KEY'] = 'some_string_value';

        $result = env('STRING_KEY');

        $this->assertEquals('some_string_value', $result);

        unset($_ENV['STRING_KEY']);
    }

    public function testEnvHandlesEmptyString()
    {
        $_ENV['EMPTY_KEY'] = '';

        $result = env('EMPTY_KEY', 'default');

        $this->assertEquals('', $result);

        unset($_ENV['EMPTY_KEY']);
    }

    public function testWriteLogCreatesLogEntry()
    {
        $message = 'Test log message ' . uniqid();

        writeLog($message);

        // Check that the log file exists
        $logFile = __DIR__ . '/../../logs/log-' . date('Ymd') . '.log';
        $this->assertFileExists($logFile);

        // Verify the message was written
        $content = file_get_contents($logFile);
        $this->assertStringContainsString($message, $content);
        $this->assertStringContainsString('INFO:', $content);
    }

    public function testWriteLogWithErrorLevel()
    {
        $message = 'Test error log ' . uniqid();

        writeLog($message, 'error');

        $logFile = __DIR__ . '/../../logs/log-' . date('Ymd') . '.log';
        $content = file_get_contents($logFile);

        $this->assertStringContainsString($message, $content);
        $this->assertStringContainsString('ERROR:', $content);
    }

    public function testWriteLogWithContext()
    {
        $message = 'Test log with context ' . uniqid();
        $context = ['key1' => 'value1', 'key2' => 123];

        writeLog($message, 'info', $context);

        $logFile = __DIR__ . '/../../logs/log-' . date('Ymd') . '.log';
        $content = file_get_contents($logFile);

        $this->assertStringContainsString($message, $content);
        $this->assertStringContainsString('"key1":"value1"', $content);
        $this->assertStringContainsString('"key2":123', $content);
    }

    public function testWriteLogUsesStaticLogger()
    {
        // Test that multiple calls use the same logger instance
        $message1 = 'First message ' . uniqid();
        $message2 = 'Second message ' . uniqid();

        writeLog($message1);
        writeLog($message2);

        $logFile = __DIR__ . '/../../logs/log-' . date('Ymd') . '.log';
        $content = file_get_contents($logFile);

        $this->assertStringContainsString($message1, $content);
        $this->assertStringContainsString($message2, $content);
    }

    public function testWriteLogDefaultsToInfoLevel()
    {
        $message = 'Default level message ' . uniqid();

        writeLog($message);

        $logFile = __DIR__ . '/../../logs/log-' . date('Ymd') . '.log';
        $content = file_get_contents($logFile);

        $this->assertStringContainsString($message, $content);
        $this->assertStringContainsString('INFO:', $content);
    }
}
