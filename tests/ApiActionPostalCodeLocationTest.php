<?php

use ApiAction\PostalCodeLocation;

class ApiActionPostalCodeLocationTest extends TestAbstract
{
    public function testNotFound()
    {
        $mapping = new PostalCodeLocation($this->db, '234325432');
        $this->assertEmpty($mapping->run());
    }

    public function testPostalCodeToAddressMapping()
    {
        $mapping = new PostalCodeLocation($this->db, '123456');
        $parts   = $mapping->run();

        $this->assertCount(1, $parts);
        $this->assertEquals($parts[0]['title'], 'г Москва');

        $mapping = new PostalCodeLocation($this->db, '1234567');
        $parts   = $mapping->run();

        $this->assertCount(2, $parts);
        $this->assertEquals($parts[0]['title'], 'г Москва');
        $this->assertEquals($parts[0]['address_level'], 'region');
        $this->assertEquals($parts[1]['title'], 'р-н Мытищинский');
    }
}
