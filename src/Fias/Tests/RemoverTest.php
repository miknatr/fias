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

        $this->db->execute('CREATE TEMP TABLE ?f (id integer)', array($this->table));
        $this->db->execute('INSERT INTO ?f SELECT generate_series(0,499)', array($this->table));
    }

    protected function tearDown()
    {
        $this->db->execute('DROP TABLE IF EXISTS ?f', array($this->table));
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
        $this->assertEquals('500', $this->db->execute('SELECT COUNT(*) FROM ?f', array($this->table))->fetchResult());

        $remover = new Remover($this->db, $this->table, 'id');
        $remover->remove($this->reader);

        $this->assertEquals('301', $this->db->execute('SELECT COUNT(*) FROM ?f', array($this->table))->fetchResult());
    }
}
