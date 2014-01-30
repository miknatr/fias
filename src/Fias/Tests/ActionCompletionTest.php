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
        $complete = new Completion($this->db, 'г Москва, Ста', '29251dcf-00a1-4e34-98d4-5c47484a36d4', 50);
        $result   = $complete->run();

        $this->assertEquals(4, $result['count']);
        $this->assertEquals('пр Ставропольский', $result['rows'][0]['title']);
        $this->assertEquals(0, $result['rows'][0]['is_complete']);
    }

    public function testHomeCompletion()
    {
        $complete = new Completion($this->db, 'г Москва, Стахановская, 1', '77303f7c-452b-4e73-b2b0-cbc59fe636c2', 2);
        $result   = $complete->run();

        $this->assertEquals(2, $result['count']);
        $this->assertEquals('1к1', $result['rows'][0]['title']);
        $this->assertEquals(1, $result['rows'][0]['is_complete']);
    }
}
