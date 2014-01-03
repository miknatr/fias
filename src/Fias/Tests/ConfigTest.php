<?php

namespace Fias\Tests;

use Fias\Config;

class ConfigTest extends Base
{
    private $fileName;
    private $filePath;

    protected function setUp()
    {
        $this->fileName = md5(time());
        $this->filePath = ROOT_DIR . 'config/' . $this->fileName . '.php';
        $testConfig     = "
        <?php
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
        $config = Config::get($this->fileName);

        $this->assertEquals('someString',   $config->getParam('string', 'fakeString'));
        $this->assertEquals('someString',   $config->getParam('string'));
        $this->assertEquals('defaultValue', $config->getParam('anotherKey', 'defaultValue'));
        $this->assertEquals(null,           $config->getParam('anotherKey', null));
    }
}
