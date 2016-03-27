<?php

namespace ApiAction;

use Grace\DBAL\ConnectionAbstract\ConnectionInterface;
use PlaceStorage;

// TODO IS-1258 Связь мест с адресами в ФИАС.
class PlaceCompletion implements ApiActionInterface
{
    /** @var ConnectionInterface */
    private $db;
    private $limit;
    private $textForCompletion;

    public function __construct(ConnectionInterface $db, $textForCompletion, $limit)
    {
        $this->db    = $db;
        $this->limit = $limit;

        $this->textForCompletion = $textForCompletion;
    }

    public function run()
    {
        $placeParts = $this->splitPlaceTitle($this->textForCompletion);
        $parentId   = null;

        if (!empty($placeParts['parent_place'])) {
            $storage  = new PlaceStorage($this->db);
            $parentId = $storage->findPlace($placeParts['parent_place'])['id'];
            if (!$parentId) {
                return [];
            }
        }

        $placeWords = $this->splitPatternToWords($placeParts['pattern']);
        $type       = $this->extractType($placeWords);

        if ($type) {
            unset($placeWords[$type['title']]);
        }

        $rows = $this->findPlaces($placeWords, $type, $parentId);

        return $rows;
    }

    private function splitPlaceTitle($title)
    {
        $tmp = explode(',', $title);

        return [
            'pattern'      => strtolower(trim(array_pop($tmp))),
            'parent_place' => implode(',', $tmp),
        ];
    }

    private function extractType(array $words)
    {
        $type = $this->db->execute(
            'SELECT id, title
            FROM place_types
            WHERE title IN (?l)
            ',
            [$words]
        )->fetchOneOrFalse();

        return $type ?: [];
    }

    private function findPlaces(array $placeWords, array $type, $parentId)
    {
        $pattern = implode(' ', $placeWords);
        $sql     = "
            SELECT full_title title, (CASE WHEN have_children THEN 0 ELSE 1 END)  is_complete, pt.system_name type_system_name
            FROM places p
            INNER JOIN place_types pt
                ON p.type_id = pt.id
            WHERE ?p
            ORDER BY p.title
            LIMIT ?e"
        ;

        $whereParts = [$this->db->replacePlaceholders("p.title ilike '?e%'", [$pattern])];

        if ($type) {
            $whereParts[] = $this->db->replacePlaceholders('type_id = ?q', [$type['id']]);
        }

        if ($parentId) {
            $whereParts[] = $this->db->replacePlaceholders('p.parent_id = ?q', [$parentId]);
        }

        $values = ['(' . implode($whereParts, ') AND (') . ')', $this->limit];

        $items = $this->db->execute($sql, $values)->fetchAll();
        if ($items) {
            foreach ($items as $key => $item) {
                $items[$key]['is_complete'] = $item['is_complete'] ? true : false;
                $items[$key]['tags']        = ['place', $item['type_system_name']];
            }
        }

        return $items;
    }

    private function splitPatternToWords($pattern)
    {
        $tmp    = explode(' ', $pattern);
        $result = [];

        foreach ($tmp as $word) {
            $trimmedWord          = trim($word);
            $result[$trimmedWord] = $trimmedWord;
        }

        return $result;
    }
}
