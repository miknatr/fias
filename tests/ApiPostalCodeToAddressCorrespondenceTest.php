<?php

use ApiAction\PostalCodeToAddressCorrespondence;

class ApiActionPostalCodeToAddressCorrespondenceTest extends TestAbstract
{
    public function testNotFound()
    {
        $correspondence = new PostalCodeToAddressCorrespondence($this->db, '234325432');
        $this->assertEmpty($correspondence->run()['addresses']);
    }

    public function testPostalCodeToAddressCorrespondence()
    {
        $correspondence = new PostalCodeToAddressCorrespondence($this->db, '123456');
        $this->assertCount(2, $correspondence->run()['addresses']);
    }
}
