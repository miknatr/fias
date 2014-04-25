<?php

use ApiAction\AddressPostalCode;

class ApiActionPostalCodeTest extends TestAbstract
{
    public function testNotFound()
    {
        $mapping = new AddressPostalCode($this->db, 'Точно отсутствующий в базе адрес');
        $this->assertNull($mapping->run());
    }

    public function testAddressToPostalCodeMapping()
    {
        $mapping = new AddressPostalCode($this->db, 'г Москва, ал Старших Бобров');
        $this->assertEquals(123456, $mapping->run());
    }

    public function testAddressToPostalCodeMappingWithHomes()
    {
        // У дома есть свой индекс
        $mapping = new AddressPostalCode($this->db, 'г Москва, ул Стахановская, 16с17');
        $this->assertEquals(654321, $mapping->run());

        // У дома нет своего индекса
        $mapping = new AddressPostalCode($this->db, 'г Москва, ул Стахановская, 16с18');
        $this->assertEquals(123456, $mapping->run());
    }
}
