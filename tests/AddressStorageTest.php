<?php

class AddressStorageTest extends TestAbstract
{
    /** @var AddressStorage */
    private $storage;

    protected function setUp()
    {
        parent::setUp();
        $this->storage = new AddressStorage($this->db);
    }

    public function testFindAddress()
    {
        $this->assertEquals(
            '29251dcf-00a1-4e34-98d4-5c47484a36d4',
            $this->storage->findAddress('г москва')['address_id']
        );

        $this->assertEquals(
            '77303f7c-452b-4e73-b2b0-cbc59fe636c2',
            $this->storage->findAddress('г москва, ул стахановская')['address_id']
        );

        $this->assertFalse($this->storage->findAddress('Ерунда какая-то, а не адрес.'));
    }

    public function testFindHouse()
    {
        $this->assertEquals(
            '841254dc-0074-41fe-99ba-0c8501526c04',
            $this->storage->findHouse('г москва, ул стахановская, 16с17')['house_id']
        );

        $this->assertFalse($this->storage->findHouse('Ерунда какая-то, а не дом'));
    }
}
