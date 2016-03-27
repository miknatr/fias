<?php

use DataSource\DataSource;
use Grace\DBAL\ConnectionAbstract\ConnectionInterface;

class AddressObjectsUpdater extends Importer
{
    public function __construct(ConnectionInterface $db, $table, array $fields)
    {
        parent::__construct($db, $table, $fields, true);
    }

    public function update(DataSource $reader)
    {
        $this->import($reader);
        $this->modifyDataAfterImport();
    }

    public function modifyDataAfterImport()
    {
        $this->updateOldRecords();
        $this->addNewRecords();

        RawDataHelper::cleanAddressObjects($this->db);
    }

    private function updateOldRecords()
    {
        $this->db->execute(
            "UPDATE address_objects ao_old
                SET title = ao_new.title,
                 postal_code = ao_new.postal_code,
                 prefix = ao_new.prefix,
                 parent_id = ao_new.parent_id
            FROM ?f ao_new
            WHERE (ao_old.address_id = ao_new.address_id OR ao_old.id = ao_new.previous_id)
            AND (
                COALESCE(ao_old.title, '') != COALESCE(ao_new.title, '')
                OR COALESCE(ao_old.postal_code, 0) != COALESCE(ao_new.postal_code, 0)
                OR COALESCE(ao_old.prefix, '') != COALESCE(ao_new.prefix, '')
                OR COALESCE(ao_old.parent_id, '00000000-0000-0000-0000-000000000000') != COALESCE(ao_new.parent_id, '00000000-0000-0000-0000-000000000000')
            )",
            [$this->table]
        );
    }

    private function addNewRecords()
    {
        $this->db->execute(
            'INSERT INTO address_objects(id, address_id, parent_id, title, postal_code, prefix)
            SELECT ao_new.id, ao_new.address_id, ao_new.parent_id, ao_new.title, ao_new.postal_code, ao_new.prefix
            FROM ?f ao_new
            LEFT JOIN address_objects ao_old
                ON (ao_old.address_id = ao_new.address_id OR ao_old.id = ao_new.previous_id)
            WHERE ao_old.id IS NULL
            ',
            [$this->table]
        );
    }
}
