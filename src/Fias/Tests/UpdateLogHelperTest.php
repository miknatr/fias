<?php

namespace Fias\Tests;

use Fias\UpdateLogHelper;
use Grace\DBAL\ConnectionAbstract\ConnectionInterface;
use Grace\DBAL\ConnectionFactory;

class UpdateLogHelperLogTest extends \PHPUnit_Framework_TestCase
{
    /** @var ConnectionInterface */
    private $db;

    protected function setUp()
    {
        $this->db = ConnectionFactory::getConnection(Helper::getGeneralConfig()->getParam('database'));
        $this->db->start();
    }

    protected function tearDown()
    {
        $this->db->rollback();
    }

    public function testAddVersionIdToLog()
    {
        UpdateLogHelper::addVersionIdToLog($this->db, 100000);

        $this->assertEquals(
            100000,
            $this->db->execute('SELECT MAX(version_id) FROM update_log')->fetchResult()
        );
    }

    public function testGetLastVersionId()
    {
        $values = array(
            array(12),
            array(18),
            array(180),
        );
        $this->db->execute('INSERT INTO update_log(version_id) VALUES ?v', array($values));

        $this->assertEquals(
            180,
            UpdateLogHelper::getLastVersionId($this->db)
        );
    }
}
