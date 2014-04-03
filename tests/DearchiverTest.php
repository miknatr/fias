<?php

use FileSystem\Dearchiver;

class DearchiverTest extends TestAbstract
{
    private $testRarFile;
    private $testTxtFile;
    private $fileDirectory;

    protected function setUp()
    {
        $text = 'Test File For Dearchiver';

        $this->fileDirectory = __DIR__ . '/file_directory';
        $this->testTxtFile   = $this->fileDirectory . '/dearchiverTestFile.txt';
        $this->testRarFile   = $this->fileDirectory . '/dearchiverTestFile.rar';

        file_put_contents($this->testTxtFile, $text);

        $cmd = 'rar a '
            . escapeshellarg($this->testRarFile)
            . ' '
            . escapeshellarg($this->testTxtFile)
            . ' 2>&1'
        ;

        exec($cmd, $output, $result);

        if ($result !== 0) {
            throw new \Exception('Ошибка архивации: ' . implode("\n", $output));
        }
    }

    protected function tearDown()
    {
        $this->cleanUpFileDirectory();
    }

    /** @expectedException \FileSystem\FileException */
    public function testBadFile()
    {
        Dearchiver::extract($this->fileDirectory, 'bad_file');
    }

    /** @expectedException \FileSystem\FileException */
    public function testBadDirectory()
    {
        Dearchiver::extract('bad_directory', $this->testRarFile);
    }

    private $extractedFiles;

    public function testNormalFile()
    {
        $this->extractedFiles = Dearchiver::extract($this->fileDirectory, $this->testRarFile);
        $this->assertEquals(
            md5_file($this->testTxtFile),
            md5_file($this->extractedFiles . '/' . basename($this->testTxtFile))
        );
    }
}
