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
        new XmlImporter($this->db, $this->table, array(), array());
    }
}
