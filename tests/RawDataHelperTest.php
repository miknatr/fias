<?php

use Grace\DBAL\ConnectionAbstract\ConnectionInterface;

class RawDataHelperTest extends \PHPUnit_Framework_TestCase
{
    /** @var ConnectionInterface */
    private $db;
    private $addressObjectTable;
    private $housesTable;

    protected function setUp()
    {
        $this->db = Helper::getContainer()->getDb();

        $this->addressObjectTable = 'raw_data_address_objects_test';
        $this->housesTable        = 'raw_data_houses_test';
        $container                = Helper::getContainer();

        $addressObjectFields               = $container->getAddressObjectsImportConfig()['fields'];
        $addressObjectFields['level']      = array('name' => 'level', 'type' => 'integer');
        $addressObjectFields['full_title'] = array('name' => 'full_title');

        DbHelper::createTable(
            $this->db,
            $this->addressObjectTable,
            $addressObjectFields
        );

        $housesFields                = $container->getHousesImportConfig()['fields'];
        $housesFields['full_number'] = array('name' => 'full_number');

        DbHelper::createTable(
            $this->db,
            $this->housesTable,
            $housesFields
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

        $houses = array(
            array( 'a64330e3-7a41-41ee-a8a2-41db8693c584', 'a64330e3-7a41-41ee-a8a2-41db8693c584', 'afdda482-42ae-45d3-9af1-61ac6da41105', '02', '1', 'нет'),
            array( 'b3ace9e8-dead-4e2c-9c56-e524aef28082', '4fd3b082-34bf-4ad9-8f27-c5c92952554c', 'afdda482-42ae-45d3-9af1-61ac6da41105', '02a', '02a', null),
        );
        $this->db->execute(
            'INSERT INTO ?f ("id", "house_id", "address_id", "number", "structure", "building")
            VALUES ?v',
            array($this->housesTable, $houses)
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

    public function testCleanHouses()
    {
        RawDataHelper::cleanHouses($this->db, $this->housesTable);

        $this->assertEquals(
            1,
            $this->db->execute(
                'SELECT COUNT(*) FROM ?f WHERE building IS NULL AND id = ?q',
                array($this->housesTable, 'a64330e3-7a41-41ee-a8a2-41db8693c584')
            )->fetchResult()
        );

        $this->assertEquals(
            1,
            $this->db->execute(
                'SELECT COUNT(*) FROM ?f WHERE building IS NULL AND structure IS NULL AND id = ?q',
                array($this->housesTable, 'b3ace9e8-dead-4e2c-9c56-e524aef28082')
            )->fetchResult()
        );

        $this->assertEquals(
            1,
            $this->db->execute(
                'SELECT COUNT(*) FROM ?f WHERE full_number = ?q  AND id = ?q',
                array($this->housesTable, '02с1', 'a64330e3-7a41-41ee-a8a2-41db8693c584')
            )->fetchResult()
        );
    }
}
