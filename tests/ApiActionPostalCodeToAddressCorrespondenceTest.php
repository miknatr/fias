<?php

use ApiAction\PostalCodeToAddressCorrespondence;

class ApiActionPostalCodeToAddressCorrespondenceTest extends TestAbstract
{
    public function testNotFound()
    {
        $correspondence = new PostalCodeToAddressCorrespondence($this->db, '234325432');
        $this->assertEmpty($correspondence->run());
    }

    public function testPostalCodeToAddressCorrespondence()
    {
        $correspondence = new PostalCodeToAddressCorrespondence($this->db, '123456');
        $parts          = $correspondence->run();

        $this->assertCount(1, $parts);
        $this->assertEquals($parts[0]['title'], 'г Москва');

        $correspondence = new PostalCodeToAddressCorrespondence($this->db, '1234567');
        $parts          = $correspondence->run();

        $this->assertCount(2, $parts);
        $this->assertEquals($parts[0]['title'], 'г Москва');
        $this->assertEquals($parts[1]['title'], 'р-н Мытищенский');
    }
}
