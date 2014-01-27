<?php

namespace Fias\Tests;

use Fias\Directory;

class DirectoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var Directory */
    private $directory;

    protected function setUp()
    {
        $this->directory = new Directory(__DIR__ . '/resources/directoryTest');
    }

    public function testGet()
    {
        $this->assertEquals(
            $this->directory->getPath() . '/AS_ADDROBJ_20131221_5316e71a-a8d8-49df-b17c-66d3a981906a.XML',
            $this->directory->getAddressObjectFile()
        );

        $this->assertEquals(
            $this->directory->getPath() . '/AS_HOUSE_20131221_bccfd0d0-af7a-49db-8401-df23dc3d2efa.XML',
            $this->directory->getHousesFile()
        );
    }

    /**
     * @expectedException \Fias\FileException
     * @expectedExceptionMessage Файл с префиксом
     */
    public function testFileNotFound()
    {
        $this->directory = new Directory(__DIR__);
        $this->directory->getHousesFile();
    }

    /** @expectedException \Fias\FileException */
    public function testDirectoryNotFound()
    {
        new Directory('badDir');
    }
}
