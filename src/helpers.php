<?php

use AHAbid\JiraItTest\Utils\Logger;

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): string|int|bool|null
    {
        if (!isset($_ENV[$key])) {
            return $default;
        }

        if (is_bool($_ENV[$key])) {
            return boolval($_ENV[$key]);
        }

        if (is_numeric($_ENV[$key])) {
            return boolval($_ENV[$key]);
        }

        return (string) $_ENV[$key];
    }
}

if (!function_exists('writeLog')) {
    function writeLog(string $message, string $level = 'info', array $context = []) {
        static $logger = new Logger();

        $logger->write($message, $level, $context);
    }
}