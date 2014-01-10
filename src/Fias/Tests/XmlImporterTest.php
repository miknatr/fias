<?php

namespace Fias\Tests;

use Fias\Config;
use Fias\XmlImporter;
use Grace\DBAL\ConnectionAbstract\ConnectionInterface;
use Grace\DBAL\ConnectionFactory;

class XmlImporterTest extends \PHPUnit_Framework_TestCase
{
    /** @var ConnectionInterface */
    private $db;
    private $table;
    private $fields = array();

    protected function setUp()
    {
        $this->db     = ConnectionFactory::getConnection(Config::get('config')->getParam('database'));
        $this->table  = 'xml_importer_test_table';
        $this->fields = array('one', 'two', 'three');

        $this->db->execute('DROP TABLE IF EXISTS xml_importer_test_table');
        $this->db->execute('CREATE TEMP TABLE xml_importer_test_table(one varchar, two varchar, three varchar)');
    }

    /**
     * @expectedException \Fias\ImporterException
     * @expectedExceptionMessage таблица или список
     */
    public function testBadParams()
    {
        new XmlImporter($this->db, 'bad_table', $this->fields, array());
    }

    /**
     * @expectedException \Fias\ImporterException
     * @expectedExceptionMessage таблица
     */
    public function testEmptyTable()
    {
        new XmlImporter($this->db, '', $this->fields, array());
    }

    /**
     * @expectedException \Fias\ImporterException
     * @expectedExceptionMessage поля
     */
    public function testEmptyFields()
    {
        new XmlImporter($this->db, $this->table, array());
    }

    public function testImport()
    {
        $reader = $this->getReaderMock();
        $fields = array(
            'madeIn' => 'two',
            'id'     => 'one',
            'title'  => 'three',
        );

        $importer = new XmlImporter($this->db, $this->table, $fields);
        $importer->import($reader);

        $this->assertEquals(
            6,
            $this->db->execute('SELECT COUNT(*) as count FROM ?F', array($this->table))->fetchOneOrFalse()['count']
        );

        $this->assertEquals(
            'USA',
            $this->db->execute("SELECT two FROM ?F WHERE one = '2'", array($this->table))->fetchOneOrFalse()['two']
        );

        $this->assertEquals(
            'Tulip',
            $this->db->execute("SELECT three FROM ?F WHERE one = '6'", array($this->table))->fetchOneOrFalse()['three']
        );
    }

    private function getReaderMock()
    {
        $result1 = array(
            array('id' => 1, 'madeIn' => 'China', 'title' => 'Phone'),
            array('id' => 2, 'madeIn' => 'USA', 'title' => 'Chicken wings'),
            array('id' => 3, 'madeIn' => 'Russia', 'title' => 'Topol-M'),
        );

        $result2 = array(
            array('id' => 4, 'madeIn' => 'France', 'title' => 'Wine'),
            array('id' => 5, 'madeIn' => 'Germany', 'title' => 'Audi'),
            array('id' => 6, 'madeIn' => 'Denmark', 'title' => 'Tulip'),
        );

        $result = $this->onConsecutiveCalls($result1, $result2, array());
        $reader = $this->getMockBuilder('\Fias\XmlReader')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $reader->expects($this->any())
            ->method('getRows')
            ->will($result)
        ;

        return $reader;
    }
}
