<?php

use FileSystem\Directory;

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
            $this->directory->getHouseFile()
        );

        $this->assertEquals(
            $this->directory->getPath() . '/AS_DEL_ADDROBJ_20131221_8a0076a7-1f52-4423-8fc6-58dec367832b.XML',
            $this->directory->getDeletedAddressObjectFile()
        );

        $this->assertEquals(
            $this->directory->getPath() . '/AS_DEL_HOUSE_20131221_ea93b12d-129d-46a0-9cfb-429b64a28873.XML',
            $this->directory->getDeletedHouseFile()
        );
    }

    /**
     * @expectedException \FileSystem\FileException
     * @expectedExceptionMessage Файл с префиксом
     */
    public function testFileNotFound()
    {
        $this->directory = new Directory(__DIR__);
        $this->directory->getHouseFile();
    }

    /** @expectedException \FileSystem\FileException */
    public function testDirectoryNotFound()
    {
        new Directory('badDir');
    }
}
