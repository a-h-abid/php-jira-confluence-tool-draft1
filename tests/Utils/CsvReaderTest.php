<?php

namespace AHAbid\JiraItTest\Tests\Utils;

use AHAbid\JiraItTest\Utils\CsvReader;
use PHPUnit\Framework\TestCase;

class CsvReaderTest extends TestCase
{
    private string $testCsvFile;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testCsvFile = sys_get_temp_dir() . '/test_' . uniqid() . '.csv';
    }

    protected function tearDown(): void
    {
        if (file_exists($this->testCsvFile)) {
            unlink($this->testCsvFile);
        }
        parent::tearDown();
    }

    public function testCanReadSimpleCsvFile()
    {
        $csvContent = "header1,header2,header3\nvalue1,value2,value3\nvalue4,value5,value6";
        file_put_contents($this->testCsvFile, $csvContent);

        $rows = [];
        foreach (CsvReader::read($this->testCsvFile) as $row) {
            $rows[] = $row;
        }

        $this->assertCount(3, $rows);
        $this->assertEquals(['header1', 'header2', 'header3'], $rows[0]);
        $this->assertEquals(['value1', 'value2', 'value3'], $rows[1]);
        $this->assertEquals(['value4', 'value5', 'value6'], $rows[2]);
    }

    public function testCanReadCsvFileWithQuotes()
    {
        $csvContent = "name,description\n\"John Doe\",\"A person with, comma\"\n\"Jane Smith\",\"Another person\"";
        file_put_contents($this->testCsvFile, $csvContent);

        $rows = [];
        foreach (CsvReader::read($this->testCsvFile) as $row) {
            $rows[] = $row;
        }

        $this->assertCount(3, $rows);
        $this->assertEquals(['name', 'description'], $rows[0]);
        $this->assertEquals(['John Doe', 'A person with, comma'], $rows[1]);
        $this->assertEquals(['Jane Smith', 'Another person'], $rows[2]);
    }

    public function testReturnsGeneratorForMemoryEfficiency()
    {
        $csvContent = "col1,col2\nval1,val2";
        file_put_contents($this->testCsvFile, $csvContent);

        $result = CsvReader::read($this->testCsvFile);

        $this->assertInstanceOf(\Generator::class, $result);
    }

    public function testThrowsExceptionForNonExistentFile()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Error reading file:');

        $generator = CsvReader::read('/path/to/nonexistent/file.csv');
        // Need to iterate to trigger the exception
        iterator_to_array($generator);
    }

    public function testCanReadEmptyCsvFile()
    {
        file_put_contents($this->testCsvFile, '');

        $rows = [];
        foreach (CsvReader::read($this->testCsvFile) as $row) {
            $rows[] = $row;
        }

        $this->assertCount(0, $rows);
    }

    public function testCanReadCsvWithSingleRow()
    {
        $csvContent = "single,row,data";
        file_put_contents($this->testCsvFile, $csvContent);

        $rows = [];
        foreach (CsvReader::read($this->testCsvFile) as $row) {
            $rows[] = $row;
        }

        $this->assertCount(1, $rows);
        $this->assertEquals(['single', 'row', 'data'], $rows[0]);
    }
}
