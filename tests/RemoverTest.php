<?php

use DataSource\DataSource;

class RemoverTest extends TestAbstract
{
    private $table;
    /** @var DataSource */
    private $reader;

    protected function setUp()
    {
        parent::setUp();
        $this->db    = $this->container->getDb();
        $this->table = 'test_table';

        $results = array();
        for ($i = 1; $i < 200; ++$i) {
            $results[] = array('xmlId' => $i);
        }
        $this->reader = $this->getReaderMock($this, array($results));

        $this->db->execute('DROP TABLE IF EXISTS ?f', array($this->table));
        $this->db->execute('CREATE TEMP TABLE ?f (id integer)', array($this->table));
        $this->db->execute('INSERT INTO ?f SELECT generate_series(0,499)', array($this->table));
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Не найдено
     */
    public function testRemoveWithBadParams()
    {
        $remover = new Remover($this->db, $this->table, 'fakeKey', 'keyFake');
        $remover->remove($this->reader);
    }

    public function testRemove()
    {
        $this->assertEquals('500', $this->db->execute('SELECT COUNT(*) FROM ?f', array($this->table))->fetchResult());

        $remover = new Remover($this->db, $this->table, 'xmlId', 'id');
        $remover->remove($this->reader);

        $this->assertEquals('301', $this->db->execute('SELECT COUNT(*) FROM ?f', array($this->table))->fetchResult());
    }
}
