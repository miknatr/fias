<?php

use ApiAction\AddressCompletion;

class ApiActionAddressCompletionTest extends TestAbstract
{
    public function testNotFound()
    {
        $complete = new AddressCompletion($this->db, 'Нави, Главная б', 50);
        $this->assertCount(0, $complete->run());
    }

    public function testAddressCompletion()
    {
        $complete = new AddressCompletion($this->db, 'г Москва, Ста', 50);
        $result   = $complete->run();

        $this->assertCount(4, $result);
        $this->assertEquals('г Москва, пр Ставропольский', $result[0]['title']);
        $this->assertEquals(0, $result[0]['is_complete']);
    }

    public function testHomeCompletion()
    {
        $complete = new AddressCompletion($this->db, 'г Москва, ул Стахановская, 1', 2);
        $result   = $complete->run();

        $this->assertCount(2, $result);
        $this->assertEquals('г Москва, ул Стахановская, 1к1', $result[0]['title']);
        $this->assertEquals(1, $result[0]['is_complete']);
    }

    public function testMaxAddressLevel()
    {
        $complete = new AddressCompletion($this->db, 'г Москва, Ста', 50, 'region');
        $this->assertEmpty($complete->run());

        $complete = new AddressCompletion($this->db, 'Моск', 50, 'region');
        $result   = $complete->run();
        $this->assertCount(1, $result);
        $this->assertTrue($result[0]['is_complete']);

        $complete = new AddressCompletion($this->db, 'Моск', 50, 'street');
        $result   = $complete->run();
        $this->assertCount(1, $result);
        $this->assertFalse($result[0]['is_complete']);

        $complete = new AddressCompletion($this->db, 'г Москва, ул Стахановская, 1', 2, 'building');
        $result   = $complete->run();

        $this->assertCount(2, $result);
    }

    public function testRegion()
    {
        $complete = new AddressCompletion($this->db, 'Моск', 50, null, [78]);
        $this->assertEmpty($complete->run());

        $complete = new AddressCompletion($this->db, 'Моск', 50, null, [77]);
        $this->assertCount(1, $complete->run());
    }

    public function testTags()
    {
        $completion = new AddressCompletion($this->db, 'Моск', 50);
        $result     = $completion->run();

        $this->assertEquals(['address'], $result[0]['tags']);
    }
}
