<?php

namespace Fias;

use Fias\DataSource\DataSource;
use Grace\DBAL\ConnectionAbstract\ConnectionInterface;

class HousesUpdater extends Importer
{
    public function __construct(ConnectionInterface $db, $table, array $fields)
    {
        $fields['full_number'] = array('name' => 'full_number');
        parent::__construct($db, $table, $fields, true);
    }

    public function update(DataSource $reader)
    {
        $this->import($reader);
        $this->modifyDataAfterImport();
    }

    public function modifyDataAfterImport()
    {
        RawDataHelper::cleanHouses($this->db, $this->table);

        $this->updateOldRecords();
        $this->addNewRecords();

        RawDataHelper::updateHousesCount($this->db);
    }

    private function updateOldRecords()
    {
        $this->db->execute(
            "UPDATE houses h_old
                SET number = h_new.number,
                 building = h_new.building,
                 structure = h_new.structure,
                 address_id = h_new.address_id,
                 full_number = h_new.full_number
            FROM ?f h_new
            WHERE h_old.id = h_new.id
            AND (
                COALESCE(h_old.number, '') != COALESCE(h_new.number, '')
                OR COALESCE(h_old.building, '') != COALESCE(h_new.building, '')
                OR COALESCE(h_old.structure, '') != COALESCE(h_new.structure, '')
                OR COALESCE(h_old.address_id, '00000000-0000-0000-0000-000000000000') != COALESCE(h_new.address_id, '00000000-0000-0000-0000-000000000000')
            )",
            array($this->table)
        );
    }

    private function addNewRecords()
    {
        $this->db->execute(
            'INSERT INTO houses(id, house_id, address_id, number, building, structure, full_number)
            SELECT h_new.id, h_new.house_id, h_new.address_id, h_new.number, h_new.building, h_new.structure, h_new.full_number
            FROM ?f h_new
            LEFT JOIN houses h_old
                ON h_old.id = h_new.id
            WHERE h_old.id IS NULL
            ',
            array($this->table)
        );
    }
}
