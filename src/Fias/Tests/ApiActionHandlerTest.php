<?php

namespace Fias\Tests;

use Fias\ApiAction\Handler;
use Grace\DBAL\ConnectionAbstract\ConnectionInterface;
use Grace\DBAL\ConnectionFactory;

class ApiActionHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConnectionInterface
     */
    private $db;

    protected function setUp()
    {
        $this->db = ConnectionFactory::getConnection(Helper::getGeneralConfig()->getParam('database'));
    }

    /**
     * @expectedException \Fias\ApiAction\HttpException
     * @expectedExceptionCode 404
     */
    public function testNotFound()
    {
        Handler::handleRequest('/api/wrong/destination', array(), $this->db);
    }

    /**
     * @expectedException \Fias\ApiAction\HttpException
     * @expectedExceptionCode 400
     */
    public function testBadParams()
    {
        Handler::handleRequest('/api/complete/', array('limit' => 70), $this->db);
    }

}
