<?php

use DataSource\DataSource;
use Grace\DBAL\ConnectionAbstract\ConnectionInterface;

class HousesUpdaterTest extends \PHPUnit_Framework_TestCase
{
    /** @var ConnectionInterface */
    private $db;
    /** @var DataSource */
    private $reader;

    protected function setUp()
    {
        $this->db = Helper::getContainer()->getDb();
        $this->db->start();

        $results = array(
            array(
                'HOUSEID'    => 'a64330e3-7a41-41ee-a8a2-41db8693c584',
                'HOUSEGUID'  => 'a64330e3-7a41-41ee-a8a2-41db8693c584',
                'PREVIOUSID' => 'a64330e3-7a41-41ee-a8a2-41db8693c584',
                'AOGUID'     => '0c5b2444-70a0-4932-980c-b4dc0d3f02b5',
                'HOUSENUM'   => '02',
                'BUILDNUM'   => '123',
                'STRUCNUM'   => 'нет'
            ),
            array(
                'HOUSEID'    => '00000000-0000-0000-0000-000000000000',
                'HOUSEGUID'  => '00000000-0000-0000-0000-000000000000',
                'PREVIOUSID' => '00000000-0000-0000-0000-000000000000',
                'AOGUID'     => '0c5b2444-70a0-4932-980c-b4dc0d3f02b5',
                'HOUSENUM'   => '1',
                'BUILDNUM'   => '2',
                'STRUCNUM'   => '3'
            ),
        );

        $this->reader = Helper::getReaderMock($this, array($results));
    }

    protected function tearDown()
    {
        $this->db->rollback();
    }

    /** @group slow */
    public function testUpdater()
    {
        $countBeforeUpdate = (int) $this->db->execute(
            'SELECT house_count FROM address_objects WHERE id = ?q',
            array('0c5b2444-70a0-4932-980c-b4dc0d3f02b5')
        )->fetchResult();

        $housesConfig = Helper::getContainer()->getHousesImportConfig();

        $housesConfig['fields']['PREVIOUSID'] = array('name' => 'previous_id', 'type' => 'uuid');

        $updater = new HousesUpdater($this->db, $housesConfig['table_name'], $housesConfig['fields']);
        $updater->update($this->reader);

        $this->assertEquals(
            '02к123',
            $this->db->execute(
                'SELECT full_number FROM houses WHERE house_id = ?q',
                array('a64330e3-7a41-41ee-a8a2-41db8693c584')
            )->fetchResult()
        );

        $this->assertEquals(
            '1к2с3',
            $this->db->execute(
                'SELECT full_number FROM houses WHERE house_id = ?q',
                array('00000000-0000-0000-0000-000000000000')
            )->fetchResult()
        );

        $this->assertEquals(
            $countBeforeUpdate + 2,
            $this->db->execute(
                'SELECT house_count FROM address_objects WHERE address_id = ?q',
                array('0c5b2444-70a0-4932-980c-b4dc0d3f02b5')
            )->fetchResult()
        );
    }
}
