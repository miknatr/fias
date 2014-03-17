<?php

namespace Fias\Tests;

use Fias\DbHelper;
use Fias\RawDataHelper;
use Grace\DBAL\ConnectionAbstract\ConnectionInterface;
use Grace\DBAL\ConnectionFactory;

class RawDataHelperTest extends \PHPUnit_Framework_TestCase
{
    /** @var ConnectionInterface */
    private $db;
    private $addressObjectTable;
    private $housesTable;

    protected function setUp()
    {
        $this->db = ConnectionFactory::getConnection(Helper::getGeneralConfig()->getParam('database'));

        $this->addressObjectTable = 'raw_data_address_objects_test';
        $this->housesTable        = 'raw_data_houses_test';
        $importConfig             = Helper::getImportConfig();

        $addressObjectFields               = $importConfig->getParam('address_objects')['fields'];
        $addressObjectFields['level']      = array('name' => 'level', 'type' => 'integer');
        $addressObjectFields['full_title'] = array('name' => 'full_title');


        DbHelper::createTable(
            $this->db,
            $this->addressObjectTable,
            $addressObjectFields
        );

        DbHelper::createTable(
            $this->db,
            $this->housesTable,
            $importConfig->getParam('houses')['fields']
        );

        $addressObjects = array(
            array('5c8b06f1-518e-496e-b683-7bf917e0d70b', '0c5b2444-70a0-4932-980c-b4dc0d3f02b5', NULL, 'Москва', NULL, 'г'),
            array('afdda482-42ae-45d3-9af1-61ac6da41105', '0ecde158-a58f-43af-9707-aa6dd3484b56', '0c5b2444-70a0-4932-980c-b4dc0d3f02b5', 'Тверская', NULL, 'ул'),
        );
        $this->db->execute(
            'INSERT INTO ?f ("id", "address_id", "parent_id", "title", "postal_code", "prefix")
            VALUES ?v',
            array($this->addressObjectTable, $addressObjects)
        );
    }

    protected function tearDown()
    {
        $this->db->execute('DROP TABLE IF EXISTS ?f, ?f', array($this->addressObjectTable, $this->housesTable));
    }

    public function testCleanAddressObjects()
    {
        RawDataHelper::cleanAddressObjects($this->db, $this->addressObjectTable);

        $this->assertEquals(
            'г Москва',
            $this->db->execute(
                "SELECT full_title FROM ?f WHERE id = '5c8b06f1-518e-496e-b683-7bf917e0d70b'",
                array($this->addressObjectTable)
            )->fetchResult()
        );

        $this->assertEquals(
            'г Москва, ул Тверская',
            $this->db->execute(
                "SELECT full_title FROM ?f WHERE id = 'afdda482-42ae-45d3-9af1-61ac6da41105'",
                array($this->addressObjectTable)
            )->fetchResult()
        );

        $this->assertEquals(
            0,
            $this->db->execute(
                "SELECT level FROM ?f WHERE id = '5c8b06f1-518e-496e-b683-7bf917e0d70b'",
                array($this->addressObjectTable)
            )->fetchResult()
        );

        $this->assertEquals(
            1,
            $this->db->execute(
                "SELECT level FROM ?f WHERE id = 'afdda482-42ae-45d3-9af1-61ac6da41105'",
                array($this->addressObjectTable)
            )->fetchResult()
        );
    }
}
