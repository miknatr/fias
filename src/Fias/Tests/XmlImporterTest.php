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

    protected function setUp()
    {
        $this->db = ConnectionFactory::getConnection(Config::get('config')->getParam('database'));
    }

    /**
     * @expectedException \Fias\ImporterException
     */
    public function testBadTable()
    {
        new XmlImporter($this->db, 'bad_table', array());
    }
}
