<?php

namespace Fias;

use Grace\DBAL\ConnectionAbstract\ConnectionInterface;

class AddressObjectsImporter extends Importer
{

    public function __construct(ConnectionInterface $db, $table, array $fields)
    {
        parent::__construct($db, $table, $fields, false);
    }

    public function modifyDataAfterImport()
    {

    }
}
