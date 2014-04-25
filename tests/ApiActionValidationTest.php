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
        $this->assertEquals('address', $result['item_type']);

        $validate = new Validation($this->db, 'Павелецкий автовокзал');
        $result   = $validate->run()[0];

        $this->assertTrue($result['is_complete']);
        $this->assertEquals('place', $result['item_type']);
    }

    public function testZeroLevel()
    {
        $validate = new Validation($this->db, 'г москва');
        $result   = $validate->run()[0];

        $this->assertFalse($result['is_complete']);
    }
}
