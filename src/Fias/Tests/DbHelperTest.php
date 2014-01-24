<?php

namespace Fias\Tests;

use Fias\Config;
use Fias\DbHelper;
use Grace\DBAL\ConnectionAbstract\ConnectionInterface;
use Grace\DBAL\ConnectionFactory;

class DbHelperTest extends \PHPUnit_Framework_TestCase
{
    /** @var ConnectionInterface */
    private $db;
    protected function setUp()
    {
        $config   = Config::get('config');
        $this->db = ConnectionFactory::getConnection($config->getParam('database'));
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Ошибка выполнения SQL файла
     */
    public function testRunException()
    {
        DbHelper::runFile('fias', __DIR__ . '/resources/inCorrectScript.sql');
    }

    public function testRun()
    {
        DbHelper::runFile('fias', __DIR__ . '/resources/correctScript.sql');
        $this->assertEquals(2, $this->db->execute('SELECT * FROM "correctTable"')->getNumRows());
    }
}
