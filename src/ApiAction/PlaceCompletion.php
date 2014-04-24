<?php

namespace ApiAction;

use Grace\DBAL\ConnectionAbstract\ConnectionInterface;
use PlaceStorage;

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
            $parentId = $storage->findPlace($placeParts['parent_place']);
            if (!$parentId) {
                return array();
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

        return array(
            'pattern'      => strtolower(trim(array_pop($tmp))),
            'parent_place' => implode(',', $tmp),
        );
    }

    private function extractType(array $words)
    {
        $type = $this->db->execute(
            'SELECT id, title
            FROM place_types
            WHERE title IN (?l)
            ',
            array($words)
        )->fetchOneOrFalse();

        return $type ?: array();
    }

    private function findPlaces(array $placeWords, array $type, $parentId)
    {
        $pattern = implode(' ', $placeWords);
        $sql     = "
            SELECT full_title title, (CASE WHEN have_children THEN 0 ELSE 1 END)  is_complete
            FROM places
            WHERE ?p
            ORDER BY title
            LIMIT ?e"
        ;

        $whereParts = array($this->db->replacePlaceholders("title ilike '?e%'", array($pattern)));

        if ($type) {
            $whereParts[] = $this->db->replacePlaceholders('type_id = ?q', array($type['id']));
        }

        if ($parentId) {
            $whereParts[] = $this->db->replacePlaceholders('parent_id = ?q', array($parentId));
        }

        $values = array(implode($whereParts, ' AND '), $this->limit);

        $items = $this->db->execute($sql, $values)->fetchAll();
        if ($items) {
            foreach ($items as $key => $item) {
                $items[$key]['is_complete'] = $item['is_complete'] ? true : false;
            }
        }

        return $items;
    }

    private function splitPatternToWords($pattern)
    {
        $tmp    = explode(' ', $pattern);
        $result = array();

        foreach ($tmp as $word) {
            $trimmedWord          = trim($word);
            $result[$trimmedWord] = $trimmedWord;
        }

        return $result;
    }
}
