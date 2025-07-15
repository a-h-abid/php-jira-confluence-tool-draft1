<?php

namespace AHAbid\JiraItTest\Utils;

use DateTime;

class Logger
{
    private string $logPath;
    private $logFileHandle;

    public function __construct()
    {
        $this->logPath = __DIR__ . '/../../logs/log-' . date('Ymd');
        $this->logFileHandle = fopen($this->logPath . '.log', 'a+');
    }

    public function __destruct()
    {
        fclose($this->logFileHandle);
    }

    public function write(string $message, string $level = 'info', array $context = [])
    {
        $datetime = '[' . (new DateTime())->format('Y-m-d H:i:s.u') . ']';
        $contextJson = empty($context) ? '[]' : json_encode($context);
        $level = strtoupper($level);

        $logContent = "{$datetime} {$level}: {$message} {$contextJson}" . PHP_EOL;

        fwrite($this->logFileHandle, $logContent);
    }
}