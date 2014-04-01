<?php

namespace Fias\Tests;

use Fias\AddressObjectsUpdater;
use Fias\DataSource\DataSource;
use Grace\DBAL\ConnectionAbstract\ConnectionInterface;

class AddressObjectsUpdaterTest extends \PHPUnit_Framework_TestCase
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
                'AOID'        => '5c8b06f1-518e-496e-b683-7bf917e0d70b',
                'AOGUID'      => '0c5b2444-70a0-4932-980c-b4dc0d3f02b5',
                'PREVIOUSID' => '0c5b2444-70a0-4932-980c-b4dc0d3f02b5',
                'PARENTGUID'  => NULL,
                'FORMALNAME'  => 'Москваа',
                'POSTALCODE'  => NULL,
                'SHORTNAME'   => 'г',
            ),
            array(
                'AOID'        => '00000000-0000-0000-0000-000000000000',
                'AOGUID'      => '00000000-0000-0000-0000-000000000000',
                'PREVIOUSID' => '0c5b2444-70a0-4932-980c-b4dc0d3f02b5',
                'PARENTGUID'  => '0c5b2444-70a0-4932-980c-b4dc0d3f02b5',
                'FORMALNAME'  => 'Все вы, питерские, идиоты какие-то',
                'POSTALCODE'  => NULL,
                'SHORTNAME'   => 'пл',
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
        $addressObjectConfig = Helper::getContainer()->getAddressObjectsImportConfig();

        $addressObjectConfig['fields']['PREVIOUSID'] = array('name' => 'previous_id', 'type' => 'uuid');

        $updater = new AddressObjectsUpdater($this->db, $addressObjectConfig['table_name'], $addressObjectConfig['fields']);
        $updater->update($this->reader);

        $this->assertEquals(
            'г Москваа',
            $this->db->execute(
                'SELECT full_title FROM address_objects WHERE address_id = ?q',
                array('0c5b2444-70a0-4932-980c-b4dc0d3f02b5')
            )->fetchResult()
        );

        $this->assertEquals(
            'г Москваа, пл Все вы, питерские, идиоты какие-то',
            $this->db->execute(
                'SELECT full_title FROM address_objects WHERE address_id = ?q',
                array('00000000-0000-0000-0000-000000000000')
            )->fetchResult()
        );
    }
}
