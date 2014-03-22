<?php

namespace Fias\Tests;

use Fias\Config;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    private $filePath;

    protected function setUp()
    {
        $this->filePath = __DIR__ . '/resources/configTest.php';
        $testConfig     = "<?php
            return array(
                'string' => 'someString',
            );
        ";

        file_put_contents($this->filePath, $testConfig);
    }

    protected function tearDown()
    {
        unlink($this->filePath);
    }

    /** @expectedException \Fias\FileException */
    public function testFileNotFound()
    {
        Config::get('fakeConfig');
    }

    public function testGet()
    {
        $this->assertEquals('someString', Config::get($this->filePath)->getParam('string'));
    }

    /** @expectedException \Fias\ConfigException */
    public function testGetException()
    {
        Config::get($this->filePath)->getParam('fakeKey');
    }
}
