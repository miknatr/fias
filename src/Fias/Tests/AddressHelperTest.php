<?php

namespace FIas\Tests;

use Fias\AddressHelper;

class AddressHelperTest extends ActionTest
{
    public function testFindAddress()
    {
        $this->assertEquals(
            '29251dcf-00a1-4e34-98d4-5c47484a36d4',
            AddressHelper::findAddress($this->db, 'г москва')
        );

        $this->assertEquals(
            '77303f7c-452b-4e73-b2b0-cbc59fe636c2',
            AddressHelper::findAddress($this->db, 'г москва, ул стахановская')
        );

        $this->assertNull(AddressHelper::findAddress($this->db, 'Ерунда какая-то, а не адрес.'));
    }

    public function testFindHouse()
    {
        $this->assertEquals(
            '841254dc-0074-41fe-99ba-0c8501526c04',
            AddressHelper::findHouse($this->db, 'г москва, ул стахановская, 16с17')
        );

        $this->assertNull(AddressHelper::findHouse($this->db, 'Ерунда какая-то, а не дом'));
    }
}
