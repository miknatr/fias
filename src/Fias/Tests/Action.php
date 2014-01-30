<?php

namespace Fias\Tests;

use Fias\Config;
use Grace\DBAL\ConnectionAbstract\ConnectionInterface;
use Grace\DBAL\ConnectionFactory;

class Action extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConnectionInterface
     */
    protected $db;

    protected function setUp()
    {
        $this->db = ConnectionFactory::getConnection(Config::get('config')->getParam('database'));

        $this->db->execute('START TRANSACTION');
        $this->db->execute('
            CREATE TEMP TABLE address_objects(
                address_id uuid,
                parent_id uuid,
                level integer,
                full_title varchar,
                title varchar,
                prefix varchar,
                house_count integer
            )
        ');

        $rows = array(
            array(
                'address_id'  => '29251dcf-00a1-4e34-98d4-5c47484a36d4',
                'parent_id'   => null,
                'level'       => 0,
                'full_title'  => 'г Москва',
                'title'       => 'Москва',
                'prefix'      => 'г',
                'house_count' => 0,
            ),
            array(
                'address_id'  => '77303f7c-452b-4e73-b2b0-cbc59fe636c2',
                'parent_id'   => '29251dcf-00a1-4e34-98d4-5c47484a36d4',
                'level'       => 1,
                'full_title'  => 'г Москва, ул Стахановская',
                'title'       => 'Стахановская',
                'prefix'      => 'ул',
                'house_count' => 4,
            ),
            array(
                'address_id'  => '77303f7c-452b-4e73-b2b0-cbc59fe636c4',
                'parent_id'   => '29251dcf-00a1-4e34-98d4-5c47484a36d4',
                'level'       => 1,
                'full_title'  => 'г Москва, пр Ставропольский',
                'title'       => 'Ставропольский',
                'prefix'      => 'пр',
                'house_count' => 0,
            ),
            array(
                'address_id'  => '77303f7c-452b-4e73-b2b0-cbc59fe636c5',
                'parent_id'   => '29251dcf-00a1-4e34-98d4-5c47484a36d4',
                'level'       => 1,
                'full_title'  => 'г Москва, пл Сталина',
                'title'       => 'Сталина',
                'prefix'      => 'пл',
                'house_count' => 0,
            ),
            array(
                'address_id'  => '77303f7c-452b-4e73-b2b0-cbc59fe636c6',
                'parent_id'   => '29251dcf-00a1-4e34-98d4-5c47484a36d4',
                'level'       => 1,
                'full_title'  => 'г Москва, ал Старших Бобров',
                'title'       => 'Старших Бобров',
                'prefix'      => 'ал',
                'house_count' => 0,
            ),
            array(
                'address_id'  => '77303f7c-452b-4e73-b2b0-cbc59fe636c7',
                'parent_id'   => '29251dcf-00a1-4e34-98d4-5c47484a36d4',
                'level'       => 1,
                'full_title'  => 'г Москва, ул Машинная',
                'title'       => 'Машинная',
                'prefix'      => 'ул',
                'house_count' => 0,
            ),
        );

        $this->db->execute(
            'INSERT INTO address_objects(?i) VALUES ?v',
            array(array_keys($rows[0]), $rows)
        );

        $this->db->execute('
            CREATE TEMP TABLE houses(
                house_id uuid,
                address_id uuid,
                full_number varchar
            )
        ');

        $rows = array(
            array(
                'full_number' => '16с17',
                'house_id'    => '841254dc-0074-41fe-99ba-0c8501526c04',
                'address_id'  => '77303f7c-452b-4e73-b2b0-cbc59fe636c2'
            ),
            array(
                'full_number' => '16с18',
                'house_id'    => '841254dc-0074-41fe-99ba-0c8501526c05',
                'address_id'  => '77303f7c-452b-4e73-b2b0-cbc59fe636c2'
            ),
            array(
                'full_number' => '23',
                'house_id'    => '841254dc-0074-41fe-99ba-0c8501526c06',
                'address_id'  => '77303f7c-452b-4e73-b2b0-cbc59fe636c2'
            ),
            array(
                'full_number' => '1к1',
                'house_id'    => '841254dc-0074-41fe-99ba-0c8501526c07',
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
}
