<?php

use ApiAction\PostalCode;

class ApiActionPostalCodeTest extends TestAbstract
{
    public function testNotFound()
    {
        $mapping = new PostalCode($this->db, 'Точно отсутствующий в базе адрес');
        $this->assertNull($mapping->run());
    }

    public function testAddressToPostalCodeMapping()
    {
        $mapping = new PostalCode($this->db, 'г Москва, ал Старших Бобров');
        $this->assertEquals(123456, $mapping->run());
    }

    public function testAddressToPostalCodeMappingWithHomes()
    {
        // У дома есть свой индекс
        $mapping = new PostalCode($this->db, 'г Москва, ул Стахановская, 16с17');
        $this->assertEquals(654321, $mapping->run());

        // У дома нет своего индекса
        $mapping = new PostalCode($this->db, 'г Москва, ул Стахановская, 16с18');
        $this->assertEquals(123456, $mapping->run());
    }
}
