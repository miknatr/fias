<?php

namespace Fias;

use Fias\DataSource\DataSource;
use Grace\DBAL\ConnectionAbstract\ConnectionInterface;

class Remover
{
    /** @var ConnectionInterface */
    private $db;
    private $table;
    private $keyField;

    public function __construct(ConnectionInterface $db, $table, $keyField)
    {
        $this->db       = $db;
        $this->table    = $table;
        $this->keyField = $keyField;
    }

    public function remove(DataSource $reader)
    {
        while ($rows = $reader->getRows()) {
            $this->removeRows($rows);
        }
    }

    private function removeRows(array $rows)
    {
        $ids = array();

        foreach($rows as $row) {
            if (empty($row[$this->keyField])) {
                throw new \LogicException('Не найдено поле: ' . $this->keyField);
            }

            $ids[] = $row[$this->keyField];
        }
        // STOPPER отсюда и до обеда.
    }
}
