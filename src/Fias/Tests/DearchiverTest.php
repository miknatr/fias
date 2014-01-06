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

        $cmd = 'rar a '
            . escapeshellarg($this->testRarFile)
            . ' '
            . escapeshellarg($this->testTxtFile)
        ;
        exec($cmd, $output, $result);

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
        Dearchiver::extract($this->fileFolder, 'bad_file');
    }

    /** @expectedException \Fias\FileException */
    public function testBadFolder()
    {
        Dearchiver::extract('bad_folder', $this->testRarFile);
    }

    private $extractedFiles;

    public function testNormalFile()
    {
        $this->extractedFiles = Dearchiver::extract($this->fileFolder, $this->testRarFile);
        $this->assertEquals(
            md5_file($this->testTxtFile),
            md5_file($this->extractedFiles . '/' . basename($this->testTxtFile))
        );
    }
}
