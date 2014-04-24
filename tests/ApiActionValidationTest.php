<?php

use ApiAction\Validation;

class ApiActionValidationTest extends TestAbstract
{
    public function testNotFound()
    {
        $validate = new Validation($this->db, 'Непонятный адрес');
        $result   = $validate->run();

        $this->assertFalse($result['is_valid']);
        $this->assertFalse($result['is_complete']);
    }

    public function testIncomplete()
    {
        $validate = new Validation($this->db, 'г москва, ул стахановская');
        $result   = $validate->run();

        $this->assertFalse($result['is_complete']);
        $this->assertTrue($result['is_valid']);
    }

    public function testValid()
    {
        $validate = new Validation($this->db, 'г москва, ул стахановская, 16с17');
        $result   = $validate->run();

        $this->assertTrue($result['is_complete']);
        $this->assertTrue($result['is_valid']);
        $this->assertEquals('address', $result['item_type']);

        $validate = new Validation($this->db, 'Павелецкий автовокзал');
        $result   = $validate->run();

        $this->assertTrue($result['is_complete']);
        $this->assertTrue($result['is_valid']);
        $this->assertEquals('place', $result['item_type']);
    }

    public function testZeroLevel()
    {
        $validate = new Validation($this->db, 'г москва');
        $result   = $validate->run();

        $this->assertFalse($result['is_complete']);
        $this->assertTrue($result['is_valid']);
    }
}
