<?php

use Grace\DBAL\ConnectionAbstract\ConnectionInterface;

class HousesImporter extends Importer
{

    public function __construct(ConnectionInterface $db, $table, array $fields)
    {
        parent::__construct($db, $table, $fields, false);
    }

    public function modifyDataAfterImport()
    {
        RawDataHelper::cleanHouses($this->db, $this->table);
        RawDataHelper::updateHousesCount($this->db);
        RawDataHelper::updateNextAddressLevelFlag($this->db);
    }

    protected $rowsPerInsert = 10000;
}
