<?php

namespace Fias\Tests;

use Fias\Config;
use Grace\DBAL\ConnectionAbstract\ConnectionInterface;
use Grace\DBAL\ConnectionFactory;

class ActionValidateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConnectionInterface
     */
    private $db;

    protected function setUp()
    {
        $this->db = ConnectionFactory::getConnection(Config::get('config')->getParam('database'));
    }


    public function testNotFound()
    {
        $this->markTestIncomplete('Реализовать!');
    }

    public function testIncomplete()
    {
        $this->markTestIncomplete('Реализовать!');
    }

    public function testValid()
    {
        $this->markTestIncomplete('Реализовать!');
    }
}
