<?php

namespace Fias;

use Grace\DBAL\ConnectionAbstract\ConnectionInterface;
use Grace\DBAL\Exception\QueryException;

class XmlImporter
{
    /** @var ConnectionInterface */
    private $db;
    private $table;
    private $fields = array();

    public function __construct(ConnectionInterface $db, $table, array $fields)
    {
        $this->db     = $db;
        $this->table  = $table;
        $this->fields = $fields;

        $this->checkParams();
    }

    public function import($fileName)
    {

    }

    private function checkParams()
    {
        $this->checkTable();
        $this->checkFields();
    }

    private function checkTable()
    {
        try {
            $this->db->execute('SELECT 1 FROM ?f LIMIT 1', array($this->table));
        } catch ( QueryException $e ) {
            throw new ImporterException('Некорректное имя таблицы: ' . $this->table);
        }
    }

    private function checkFields()
    {

    }
}
