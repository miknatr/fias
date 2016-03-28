<?php

use DataSource\DataSource;
use Grace\DBAL\ConnectionAbstract\ConnectionInterface;

class Remover
{
    /** @var ConnectionInterface */
    private $db;
    private $table;
    private $keyFieldXml;
    private $keyFieldDatabase;

    public function __construct(ConnectionInterface $db, $table, $keyFieldXml, $keyFieldDatabase)
    {
        $this->db               = $db;
        $this->table            = $table;
        $this->keyFieldXml      = $keyFieldXml;
        $this->keyFieldDatabase = $keyFieldDatabase;
    }

    public function remove(DataSource $reader)
    {
        while ($rows = $reader->getRows()) {
            $this->removeRows($rows);
        }
    }

    private function removeRows(array $rows)
    {
        $ids = [];

        foreach ($rows as $row) {
            if (empty($row[$this->keyFieldXml])) {
                throw new \LogicException('Не найдено поле: ' . $this->keyFieldXml);
            }

            $ids[] = $row[$this->keyFieldXml];
        }

        $this->db->execute('DELETE FROM ?f WHERE ?f IN (?l)', [$this->table, $this->keyFieldDatabase, $ids]);
    }
}
