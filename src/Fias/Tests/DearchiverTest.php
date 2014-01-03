<?php

namespace Fias\Tests;

use Fias\Dearchiver;
use Fias\Config;

class DearchiverTest extends Base
{
    private $testRarFile;
    private $testTxtFile;

    protected function setUp()
    {
        $text       = 'Test File For Dearchiver';
        $fileFolder = Config::get('config.test')->getParam('file_folder');

        $this->testTxtFile = $fileFolder . '/dearchiverTestFile.txt';
        $this->testRarFile = $fileFolder . '/dearchiverTestFile.rar';

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
    }

    /** @expectedException \Fias\FileException */
    public function testBadFile()
    {
        new Dearchiver(__DIR__ . 'bad_file', Config::get('config.test.php'));
    }

    public function testNormalFile()
    {
        $extracted_files = (new Dearchiver($this->testRarFile, Config::get('config.test')))->extract();
        $this->assertEquals(
            md5_file($this->testTxtFile),
            md5_file($extracted_files . '/' . basename($this->testTxtFile))
        );
    }
}
