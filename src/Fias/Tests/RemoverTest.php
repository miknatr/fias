<?php

namespace Fias\Tests;

use Fias\DataSource\DataSource;
use Fias\Remover;
use Grace\DBAL\ConnectionAbstract\ConnectionInterface;
use Grace\DBAL\ConnectionFactory;

class RemoverTest extends \PHPUnit_Framework_TestCase
{
    /** @var ConnectionInterface */
    private $db;
    private $table;
    /** @var DataSource */
    private $reader;

    protected function setUp()
    {
        $this->db    = ConnectionFactory::getConnection(Helper::getConfig()->getParam('database'));
        $this->table = 'test_table';

        $results = array();
        for ($i = 1; $i < 200; ++$i) {
            $results[] = array('id' => $i);
        }

        $this->reader = Helper::getReaderMock($this, array($results));
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Не найдено
     */
    public function testRemoveWithBadParams()
    {
        $remover = new Remover($this->db, $this->table, 'fakeKey');
        $remover->remove($this->reader);
    }

    public function testRemove()
    {
        $remover = new Remover($this->db, $this->table, 'id');
        $remover->remove($this->reader);
    }
}
