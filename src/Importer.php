<?php

use DataSource\DataSource;
use Grace\DBAL\ConnectionAbstract\ConnectionInterface;

class Importer
{
    private $fields = [];

    /** @var ConnectionInterface */
    protected $db;
    protected $table;

    public function __construct(ConnectionInterface $db, $table, array $fields, $isTemp = true)
    {
        if (!$table) {
            throw new ImporterException('Не задана таблица для импорта');
        }

        if (!$fields) {
            throw new ImporterException('Не заданы поля для импорта.');
        }

        $this->db     = $db;
        $this->fields = $fields;
        $this->table  = $table;

        if ($isTemp) {
            $this->table .= '_xml_importer';
            DbHelper::createTable($this->db, $this->table, $this->fields, $isTemp);
        }
    }

    protected $rowsPerInsert = 1000;

    public function import(DataSource $reader)
    {
        $i = 0;
        while ($rows = $reader->getRows($this->rowsPerInsert)) {
            $this->db->execute($this->getQuery($rows[0]), [$rows]);
            ++$i;
            if (($i % 100) == 0) {
                $this->db->getLogger()->reset();
            }
        }

        return $this->table;
    }

    private $sqlHeader;

    private function getQuery($rowExample)
    {
        if (!$this->sqlHeader) {
            $fields = [];
            foreach ($rowExample as $attribute => $devNull) {
                $fields[] = $this->fields[$attribute]['name'];
            }

            $headerPart = $this->db->replacePlaceholders('INSERT INTO ?f(?i) VALUES ', [$this->table, $fields]);

            $this->sqlHeader = $headerPart . ' ?v';
        }

        return $this->sqlHeader;
    }
}
