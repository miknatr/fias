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

    public function import(XMLReader $reader)
    {
        while ($rows = $reader->getRows()) {
            $this->db->execute($this->getQuery($rows[0]), array($rows));
        }
    }

    private $sqlHeader;

    private function getQuery($rowExample)
    {
        if (!$this->sqlHeader) {
            $fields = array();
            foreach($rowExample as $attribute => $devNull) {
                $fields[] = $this->fields[$attribute];
            }

            $this->sqlHeader = $this->db->replacePlaceholders('INSERT INTO ?f(?i) VALUES ', array($this->table, $fields)) . ' ?v';
        }

        return $this->sqlHeader;
    }
}
