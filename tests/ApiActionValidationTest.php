<?php

use ApiAction\Validation;

class ApiActionValidationTest extends TestAbstract
{
    public function testNotFound()
    {
        $validate = new Validation($this->db, 'Непонятный адрес');
        $result   = $validate->run();

        $this->assertEmpty($result);
    }

    public function testIncomplete()
    {
        $validate = new Validation($this->db, 'г москва, ул стахановская');
        $result   = $validate->run()[0];

        $this->assertFalse($result['is_complete']);
    }

    public function testValid()
    {
        $validate = new Validation($this->db, 'г москва, ул стахановская, 16с17');
        $result   = $validate->run()[0];

        $this->assertTrue($result['is_complete']);
        $this->assertTrue(in_array('address', $result['tags']));
        $this->assertFalse(in_array('place', $result['tags']));

        $validate = new Validation($this->db, 'Павелецкий автовокзал');
        $result   = $validate->run()[0];

        $this->assertTrue($result['is_complete']);
        $this->assertTrue(in_array('place', $result['tags']));
        $this->assertFalse(in_array('address', $result['tags']));
        $this->assertTrue(in_array('bus_terminal', $result['tags']));
    }

    public function testZeroLevel()
    {
        $validate = new Validation($this->db, 'г москва');
        $result   = $validate->run()[0];

        $this->assertFalse($result['is_complete']);
    }
}
