<?php

namespace Fias\Tests;

use Fias\Action\Completion;

class ActionCompletionTest extends Action
{
    public function testNotFound()
    {
        $complete = new Completion($this->db, 'Нави, Главная б', null, 50);
        $result   = $complete->run();

        $this->assertCount(0, $result);
    }

    public function testAddressCompletion()
    {
        $complete = new Completion($this->db, 'г Москва, Ста', 50);
        $result   = $complete->run();

        $this->assertCount(4, $result);
        $this->assertEquals('г Москва, пр Ставропольский', $result[0]['title']);
        $this->assertEquals(0, $result[0]['is_complete']);
    }

    public function testHomeCompletion()
    {
        $complete = new Completion($this->db, 'г Москва, ул Стахановская, 1', 2);
        $result   = $complete->run();

        $this->assertCount(2, $result);
        $this->assertEquals('г Москва, ул Стахановская, 1к1', $result[0]['title']);
        $this->assertEquals(1, $result[0]['is_complete']);
    }

    /**
     * @expectedException \Fias\Action\HttpException
     * @expectedExceptionCode 400
     */
    public function testLimitOverflow()
    {
        new Completion($this->db, 'г Москва, ул Стахановская, 1', Completion::MAX_LIMIT+1);
    }
}
