<?php

namespace Fias\Tests;

use Fias\Action\Validate;
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

        $this->db->execute('START TRANSACTION');
        $this->db->execute('
            CREATE TEMP TABLE address_objects(
                address_id uuid,
                parent_id uuid,
                level integer,
                full_title varchar
            )
        ');

        $rows = array(
            array(
                'address_id' => '29251dcf-00a1-4e34-98d4-5c47484a36d4',
                'parent_id'  => null,
                'level'      => 0,
                'full_title' => 'г Москва'
            ),
            array(
                'address_id' => '77303f7c-452b-4e73-b2b0-cbc59fe636c2',
                'parent_id'  => '29251dcf-00a1-4e34-98d4-5c47484a36d4',
                'level'      => 1,
                'full_title' => 'г Москва, ул Стахановская'
            ),
        );

        $this->db->execute(
            'INSERT INTO address_objects(?i) VALUES ?v',
            array(array_keys($rows[0]), $rows)
        );

        $this->db->execute('
            CREATE TEMP TABLE houses(
                home_id uuid,
                address_id uuid,
                full_number varchar
            )
        ');

        $rows = array(
            array(
                'full_number' => '16с17',
                'home_id'     => '841254dc-0074-41fe-99ba-0c8501526c04',
                'address_id'  => '77303f7c-452b-4e73-b2b0-cbc59fe636c2'
            ),
            array(
                'full_number' => '16с18',
                'home_id'     => '841254dc-0074-41fe-99ba-0c8501526c04',
                'address_id'  => '77303f7c-452b-4e73-b2b0-cbc59fe636c2'
            ),
        );
        $this->db->execute(
            'INSERT INTO houses(?i) VALUES ?v',
            array(array_keys($rows[0]), $rows)
        );
    }

    protected function tearDown()
    {
        $this->db->execute('ROLLBACK');
    }

    public function testNotFound()
    {
        $validate = new Validate('Непонятный адрес', $this->db);
        $result   = $validate->run();

        $this->isNull($result['id']);
        $this->isFalse($result['is_valid']);
        $this->isFalse($result['is_complete']);
    }

    public function testIncomplete()
    {
        $validate = new Validate('г москва, ул стахановская', $this->db);
        $result   = $validate->run();

        $this->isTrue($result['is_valid']);
        $this->isFalse($result['is_complete']);
        $this->assertEquals('77303f7c-452b-4e73-b2b0-cbc59fe636c2', $result['id']);
    }

    public function testValid()
    {
        $validate = new Validate('г москва, ул стахановская, 16с17', $this->db);
        $result   = $validate->run();

        $this->isTrue($result['is_valid']);
        $this->isFalse($result['is_complete']);
        $this->assertEquals('841254dc-0074-41fe-99ba-0c8501526c04', $result['id']);
    }

    public function testZeroLevel()
    {
        $validate = new Validate('г москва', $this->db);
        $result   = $validate->run();

        $this->isTrue($result['is_valid']);
        $this->isFalse($result['is_complete']);
        $this->assertEquals('29251dcf-00a1-4e34-98d4-5c47484a36d4', $result['id']);
    }
}
