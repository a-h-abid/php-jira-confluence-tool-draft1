<?php

namespace AHAbid\JiraItTest\Utils;

/** @package AHAbid\JiraItTest\Utils */
class CsvReader
{
    public static function read($filePath)
    {
        $h = fopen($filePath, 'r');
        if (!$h) {
            throw new \Exception('Error reading file: '. $filePath);
        }

        while (!feof($h)) {
            $row = fgetcsv($h, escape: '\\');
            if (!$row) {
                break;
            }

            yield $row;
        }

        fclose($h);
    }
}