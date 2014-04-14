<?php

namespace ApiAction;

class PlaceCompletion extends CompletionAbstract
{
    private $placeWords = array();
    private $typeId     = null;

    public function run()
    {
        $this->preparePlaceWords();
        $this->extractType();
        $rows = $this->findPlaces();

        return array('places' => $rows);
    }

    private function extractType()
    {
        $type = $this->db->execute(
            'SELECT id, title
            FROM place_types
            WHERE title IN (?l)
            ',
            array($this->placeWords)
        )->fetchOneOrFalse();

        if ($type) {
            unset($this->placeWords[$type['title']]);
            $this->typeId = $type['id'];
        }
    }

    private function findPlaces()
    {
        $pattern = implode(' ', $this->placeWords);
        $sql = "
            SELECT full_title title, 1 is_complete
            FROM places
            WHERE ?p
                AND title ilike '?e%'
            ORDER BY title
            LIMIT ?e"
        ;

        $parentPart = $this->typeId
            ? $this->db->replacePlaceholders('type_id = ?q', array($this->typeId))
            : '1 = 1 '
        ;

        $values = array($parentPart, $pattern, $this->limit);

        return $this->db->execute($sql, $values)->fetchAll();
    }

    private function preparePlaceWords()
    {
        $tmp = explode(' ', strtolower($this->textForCompletion));

        foreach ($tmp as $word) {
            $trimmedWord = trim($word);
            $this->placeWords[$trimmedWord] = $trimmedWord;
        }
    }
}
