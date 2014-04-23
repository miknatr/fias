<?php

use ApiAction\AddressToPostalCodeCorrespondence;

class ApiActionAddressToPostalCodeCorrespondenceTest extends TestAbstract
{
    public function testNotFound()
    {
        $correspondence = new AddressToPostalCodeCorrespondence($this->db, 'Точно отсутствующий в базе адрес');
        $this->assertNull($correspondence->run()['postal_code']);
    }

    public function testAddressToPostalCodeCorrespondence()
    {
        $correspondence = new AddressToPostalCodeCorrespondence($this->db, 'г Москва, ал Старших Бобров');
        $this->assertEquals(123456, $correspondence->run()['postal_code']);
    }

    public function testAddressToPostalCodeCorrespondenceWithHomes()
    {
        // У дома есть свой индекс
        $correspondence = new AddressToPostalCodeCorrespondence($this->db, 'г Москва, ул Стахановская, 16с17');
        $this->assertEquals(654321, $correspondence->run()['postal_code']);

        // У дома нет своего индекса
        $correspondence = new AddressToPostalCodeCorrespondence($this->db, 'г Москва, ул Стахановская, 16с18');
        $this->assertEquals(123456, $correspondence->run()['postal_code']);
    }
}
