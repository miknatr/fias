<?php

namespace ApiAction;

use Grace\DBAL\ConnectionAbstract\ConnectionInterface;

class PostalCodeLocation implements ApiActionInterface
{
    /** @var ConnectionInterface */
    private $db;
    private $postalCode;

    public function __construct(ConnectionInterface $db, $postalCode)
    {
        $this->db         = $db;
        $this->postalCode = $postalCode;
    }

    public function run()
    {
        $count = $this->getAddressCount();
        if (!$count) {
            return [];
        }

        $stack = $this->getBiggestSuitableStack();

        return $this->filterAddressStack($stack, $count);
    }

    private function getAddressCount($pattern = null)
    {
        $values = [$this->postalCode];
        $sql    = 'SELECT COUNT(*) FROM address_objects WHERE postal_code = ?q';

        if ($pattern) {
            $values[] = $pattern;
            $sql .= " AND full_title LIKE '?e%'";
        }

        return $this->db->execute($sql, $values)->fetchResult();
    }

    private function getBiggestSuitableStack()
    {
        // Берем наименьший адрес и через родительские связки строим стек.
        return $this->db->execute(
            "WITH RECURSIVE
                address_stack(parent_id, title, full_title, level, address_level) AS (
                        SELECT parent_id, prefix || ' ' || title, full_title, level, address_level
                        FROM address_objects ao
                        WHERE address_id IN (
                            SELECT address_id
                            FROM address_objects
                            WHERE postal_code = ?q
                            ORDER BY level
                            LIMIT 1
                        )
                    UNION ALL
                        SELECT r.parent_id, r.prefix || ' ' || r.title, r.full_title, r.level, r.address_level
                        FROM address_objects r
                        INNER JOIN address_stack ads
                            ON ads.parent_id = r.address_id

                )
                SELECT parent_id, a.title, full_title, level, al.code address_level
                FROM address_stack a
                INNER JOIN address_object_levels al
                    ON al.id = address_level
                ORDER BY level
            ",
            [$this->postalCode]
        )->fetchAll();
    }

    private function filterAddressStack(array $partsForChecking, $totalCount, array $currentStack = [])
    {
        $part       = array_shift($partsForChecking);
        $levelCount = $this->getAddressCount($part['full_title']);

        if ($levelCount == $totalCount) {
            $currentStack[] = ['title' => $part['title'], 'address_level' => $part['address_level']];
        }

        if (!$partsForChecking || ($levelCount != $totalCount)) {
            return $currentStack;
        }

        return $this->filterAddressStack($partsForChecking, $totalCount, $currentStack);
    }
}
