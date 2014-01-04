<?php

namespace Fias\Tests;

use Fias\Dearchiver;

class DearchiverTest extends \PHPUnit_Framework_TestCase
{
    private $testRarFile;
    private $testTxtFile;
    private $fileFolder;

    protected function setUp()
    {
        $text = 'Test File For Dearchiver';

        $this->fileFolder  = __DIR__ . '/file_folder';
        $this->testTxtFile = $this->fileFolder . '/dearchiverTestFile.txt';
        $this->testRarFile = $this->fileFolder . '/dearchiverTestFile.rar';

        file_put_contents($this->testTxtFile, $text);
        exec('rar a ' . $this->testRarFile . ' ' . $this->testTxtFile, $output, $result);

        if ($result !== 0) {
            throw new \Exception('Ошибка архивации: ' . implode("\n", $output));
        }
    }

    protected function tearDown()
    {
        unlink($this->testRarFile);
        unlink($this->testTxtFile);

        if ($this->extractedFiles) {
            $files = scandir($this->extractedFiles);
            foreach ($files as $file) {
                if ($file == '.' || $file == '..') {
                    continue;
                }

                unlink($this->extractedFiles . '/' . $file);
            }

            rmdir($this->extractedFiles);
        }
    }

    /** @expectedException \Fias\FileException */
    public function testBadFile()
    {
        new Dearchiver('bad_file', $this->fileFolder);
    }

    /** @expectedException \Fias\FileException */
    public function testBadFolder()
    {
        new Dearchiver($this->testRarFile, 'bad_folder');
    }

    private $extractedFiles;

    public function testNormalFile()
    {
        $this->extractedFiles = (new Dearchiver($this->testRarFile, $this->fileFolder))->extract();
        $this->assertEquals(
            md5_file($this->testTxtFile),
            md5_file($this->extractedFiles . '/' . basename($this->testTxtFile))
        );
    }
}
