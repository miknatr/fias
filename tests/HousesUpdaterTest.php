<?php

use DataSource\DataSource;

class HousesUpdaterTest extends TestAbstract
{
    /** @var DataSource */
    private $reader;

    protected function setUp()
    {
        parent::setUp();
        $results = [
            [
                'HOUSEID'    => 'a64330e3-7a41-41ee-a8a2-41db8693c584',
                'HOUSEGUID'  => 'a64330e3-7a41-41ee-a8a2-41db8693c584',
                'PREVIOUSID' => 'a64330e3-7a41-41ee-a8a2-41db8693c584',
                'AOGUID'     => '77303f7c-452b-4e73-b2b0-cbc59fe636c5',
                'HOUSENUM'   => '02',
                'BUILDNUM'   => '123',
                'STRUCNUM'   => 'нет'
            ],
            [
                'HOUSEID'    => '00000000-0000-0000-0000-000000000000',
                'HOUSEGUID'  => '00000000-0000-0000-0000-000000000000',
                'PREVIOUSID' => '00000000-0000-0000-0000-000000000000',
                'AOGUID'     => '77303f7c-452b-4e73-b2b0-cbc59fe636c5',
                'HOUSENUM'   => '1',
                'BUILDNUM'   => '2',
                'STRUCNUM'   => '3'
            ],
        ];

        $this->reader = $this->getReaderMock($this, [$results]);
    }

    /** @group slow */
    public function testUpdater()
    {
        $countBeforeUpdate = (int) $this->db->execute(
            'SELECT house_count FROM address_objects WHERE id = ?q',
            ['0c5b2444-70a0-4932-980c-b4dc0d3f02b5']
        )->fetchResult();

        $housesConfig = $this->container->getHousesImportConfig();

        $housesConfig['fields']['PREVIOUSID'] = ['name' => 'previous_id', 'type' => 'uuid'];

        $updater = new HousesUpdater($this->db, $housesConfig['table_name'], $housesConfig['fields']);
        $updater->update($this->reader);

        $this->assertEquals(
            '02к123',
            $this->db->execute(
                'SELECT full_number FROM houses WHERE house_id = ?q',
                ['a64330e3-7a41-41ee-a8a2-41db8693c584']
            )->fetchResult()
        );

        $this->assertEquals(
            '1к2с3',
            $this->db->execute(
                'SELECT full_number FROM houses WHERE house_id = ?q',
                ['00000000-0000-0000-0000-000000000000']
            )->fetchResult()
        );

        $this->assertEquals(
            $countBeforeUpdate + 2,
            $this->db->execute(
                'SELECT house_count FROM address_objects WHERE address_id = ?q',
                ['77303f7c-452b-4e73-b2b0-cbc59fe636c5']
            )->fetchResult()
        );
    }
}
