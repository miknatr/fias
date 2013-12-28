<?php

namespace Fias\Tests;

use Fias\Config;

class ConfigTest extends Base
{
    private $filePath;
    protected function setUp()
    {
        $testConfig = "
        <?php
            return array(
                'string' => 'someString',
            );

        ";

        $this->filePath = $this->createTestConfigFile($testConfig);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Файл не найден: /fake/path
     */
    public function testFileNotFound()
    {
        Config::get('/fake/path');
    }

    public function testGet()
    {
        $config = Config::get($this->filePath);
        $this->assertEquals('someString',   $config->getParam('string', 'fakeString'));
        $this->assertEquals('someString',   $config->getParam('string'));
        $this->assertEquals('defaultValue', $config->getParam('anotherKey', 'defaultValue'));
        $this->assertEquals(null,           $config->getParam('anotherKey', null));
    }

    private function createTestConfigFile($content)
    {
        $fileName = tempnam('let us write to system\'s temporary directory', 'configTest');
        file_put_contents($fileName, $content);
        return $fileName;
    }
}
