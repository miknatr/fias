<?php

namespace Fias\Tests;

use Fias\Action\Completion;

class ActionCompletionTest extends Action
{
    public function testNotFound()
    {
        $complete = new Completion($this->db, 'Нави, Главная б', null, 50);
        $result   = $complete->run();

        $this->assertEquals(0, $result['count']);
    }

    public function testAddressCompletion()
    {
        $complete = new Completion($this->db, 'г Москва, Ста', 50);
        $result   = $complete->run();

        $this->assertEquals(4, $result['count']);
        $this->assertEquals('г Москва, пр Ставропольский', $result['rows'][0]);
    }

    public function testHomeCompletion()
    {
        $complete = new Completion($this->db, 'г Москва, ул Стахановская, 1', 2);
        $result   = $complete->run();

        $this->assertEquals(2, $result['count']);
        $this->assertEquals('г Москва, ул Стахановская, 1к1', $result['rows'][0]);
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
