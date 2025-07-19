<?php

namespace AHAbid\JiraItTest\Config;

class FilePath
{
    public const INPUT = BASE_DIR . '/files/input/';
    public const OUTPUT = BASE_DIR . '/files/output/';
    public const STORIES = BASE_DIR . '/files/stories/';

    public static function issueStoryPath(string $issueKey, string $extension = '.md', string $suffix = ''): string
    {
        return self::STORIES . '/' . $issueKey . $suffix . $extension;
    }
}
