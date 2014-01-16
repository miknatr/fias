<?php

namespace Fias\Tests;

use Fias\Config;
use Fias\Importer;
use Grace\DBAL\ConnectionAbstract\ConnectionInterface;
use Grace\DBAL\ConnectionFactory;

class ImporterTest extends \PHPUnit_Framework_TestCase
{
    /** @var ConnectionInterface */
    private $db;
    private $table;

    protected function setUp()
    {
        $this->db     = ConnectionFactory::getConnection(Config::get('config')->getParam('database'));
        $this->table  = 'xml_importer_test_table';
    }

    /**
     * @expectedException \Fias\ImporterException
     * @expectedExceptionMessage таблица
     */
    public function testEmptyTable()
    {
        new Importer($this->db, '', array('one', 'two', 'three'), array());
    }

    /**
     * @expectedException \Fias\ImporterException
     * @expectedExceptionMessage поля
     */
    public function testEmptyFields()
    {
        new Importer($this->db, 'some_table_name', array());
    }

    public function testImport()
    {
        $results = array(
            array(
                array('id' => 1, 'madeIn' => 'China', 'title' => 'Phone'),
                array('id' => 2, 'madeIn' => 'USA', 'title' => 'Chicken wings'),
                array('id' => 3, 'madeIn' => 'Russia', 'title' => 'Topol-M'),
            ),
            array(
                array('id' => 4, 'madeIn' => 'France', 'title' => 'Wine'),
                array('id' => 5, 'madeIn' => 'Germany', 'title' => 'Audi'),
                array('id' => 6, 'madeIn' => 'Denmark', 'title' => 'Tulip'),
            ),
        );

        $reader = Helper::getReaderMock($this, $results);
        $fields = array(
            'madeIn' => array('name' => 'two'),
            'id'     => array('name' => 'one'),
            'title'  => array('name' => 'three'),
        );

        $importer  = new Importer($this->db, $this->table, $fields);
        $tableName = $importer->import($reader);

        $this->assertEquals(
            6,
            $this->db->execute('SELECT COUNT(*) as count FROM ?F', array($tableName))->fetchOneOrFalse()['count']
        );

        $this->assertEquals(
            'USA',
            $this->db->execute("SELECT two FROM ?F WHERE one = '2'", array($tableName))->fetchOneOrFalse()['two']
        );

        $this->assertEquals(
            'Tulip',
            $this->db->execute("SELECT three FROM ?F WHERE one = '6'", array($tableName))->fetchOneOrFalse()['three']
        );
    }
}
