<?php

use DataSource\DataSource;
use Grace\DBAL\ConnectionAbstract\ConnectionInterface;

class HousesUpdater extends Importer
{
    public function __construct(ConnectionInterface $db, $table, array $fields)
    {
        $fields['full_number'] = ['name' => 'full_number'];
        parent::__construct($db, $table, $fields, true);
    }

    public function update(DataSource $reader)
    {
        $this->import($reader);
        $this->modifyDataAfterImport();
    }

    public function modifyDataAfterImport()
    {
        $this->createTemporaryIndexes();

        RawDataHelper::cleanHouses($this->db, $this->table);

        $this->removeOldRecords();
        $this->addNewRecords();

        RawDataHelper::updateHousesCount($this->db);
        RawDataHelper::updateNextAddressLevelFlag($this->db);
    }

    private function removeOldRecords()
    {
        $this->db->execute(
            'DELETE FROM ?f:temp_table: h
            USING (
                SELECT DISTINCT h.address_id
                FROM ?f:temp_table: h
                LEFT JOIN address_objects ao
                    ON ao.address_id = h.address_id
                WHERE ao.id IS NULL
            ) a
            WHERE a.address_id = h.address_id
            ',
            ['temp_table' => $this->table]
        );

        $this->db->execute(
            'DELETE FROM houses h_old
            USING ?f h_new
            WHERE (h_old.house_id = h_new.house_id OR h_old.id = h_new.previous_id)
            ',
            [$this->table]
        );
    }

    private function addNewRecords()
    {
        $this->db->execute(
            'INSERT INTO houses(id, house_id, address_id, number, building, structure, full_number)
            SELECT h_new.id, h_new.house_id, h_new.address_id, h_new.number, h_new.building, h_new.structure, h_new.full_number
            FROM ?f h_new
            ',
            [$this->table]
        );
    }

    private function createTemporaryIndexes()
    {
        $sql = 'CREATE INDEX tmp_'  . rand() . '_idx ON ?f USING BTREE(building)';
        $this->db->execute($sql, [$this->table]);

        $sql = 'CREATE INDEX tmp_2' . rand() . '_idx ON ?f USING BTREE(structure)';
        $this->db->execute($sql, [$this->table]);

        $sql = 'CREATE INDEX tmp_3' . rand() . '_idx ON ?f USING BTREE(house_id)';
        $this->db->execute($sql, [$this->table]);

        $sql = 'CREATE INDEX tmp_4' . rand() . '_idx ON ?f USING BTREE(previous_id)';
        $this->db->execute($sql, [$this->table]);
    }
}
