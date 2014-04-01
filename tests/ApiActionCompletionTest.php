<?php

use ApiAction\Completion;

class ApiActionCompletionTest extends MockDatabaseTest
{
    public function testNotFound()
    {
        $complete = new Completion($this->db, 'Нави, Главная б', null);
        $result   = $complete->run()['addresses'];

        $this->assertCount(0, $result);
    }

    public function testAddressCompletion()
    {
        $complete = new Completion($this->db, 'г Москва, Ста', null);
        $result   = $complete->run()['addresses'];

        $this->assertCount(4, $result);
        $this->assertEquals('г Москва, пр Ставропольский', $result[0]['title']);
        $this->assertEquals(0, $result[0]['is_complete']);
    }

    public function testHomeCompletion()
    {
        $complete = new Completion($this->db, 'г Москва, ул Стахановская, 1', 2);
        $result   = $complete->run()['addresses'];

        $this->assertCount(2, $result);
        $this->assertEquals('г Москва, ул Стахановская, 1к1', $result[0]['title']);
        $this->assertEquals(1, $result[0]['is_complete']);
    }
}
