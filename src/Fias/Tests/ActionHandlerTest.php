<?php

namespace Fias\Tests;

use Fias\Config;
use Fias\Action\Handler;
use Grace\DBAL\ConnectionAbstract\ConnectionInterface;
use Grace\DBAL\ConnectionFactory;

class ActionHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConnectionInterface
     */
    private $db;

    protected function setUp()
    {
        $this->db = ConnectionFactory::getConnection(Config::get('config')->getParam('database'));
    }

    /**
     * @expectedException \Fias\Action\Exception
     * @expectedExceptionCode 404
     */
    public function testNotFound()
    {
        Handler::handle('/wrong/destination', $this->db);
    }

    /**
     * @expectedException \Fias\Action\Exception
     * @expectedExceptionCode 400
     */
    public function testBadParams()
    {
        Handler::handle('/complete', $this->db);
    }

}
