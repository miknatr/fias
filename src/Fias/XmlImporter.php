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

    public function import(XMLReader $reader)
    {

    }

    private function checkParams()
    {
        if (!$this->table) {
            throw new ImporterException('Не задана таблица для импорта');
        }

        if (!$this->fields) {
            throw new ImporterException('Не заданы поля для импорта.');
        }

        try {
            $this->db->execute('SELECT ?i FROM ?f LIMIT 1', array($this->fields, $this->table));
        } catch ( QueryException $e ) {
            throw new ImporterException('Задана неверная таблица или список полей.');
        }
    }
}
