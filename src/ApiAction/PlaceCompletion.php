<?php

namespace ApiAction;

class PlaceCompletion extends CompletionAbstract
{
    private $placeWords = array();

    public function run()
    {
        $this->preparePlaceWords();
        $typeId = $this->extractType();
        $rows   = $this->findPlaces($typeId);

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

        if (!$type) {
            return null;
        }

        unset($this->placeWords[$type['title']]);
        return $type['id'];
    }

    private function findPlaces($typeId = null)
    {
        $pattern = implode(' ', $this->placeWords);
        $sql     = "
            SELECT full_title title, 1 is_complete
            FROM places
            WHERE ?p
                AND title ilike '?e%'
            ORDER BY title
            LIMIT ?e"
        ;

        $typePart = $typeId
            ? $this->db->replacePlaceholders('type_id = ?q', array($typeId))
            : '1 = 1 '
        ;

        $values = array($typePart, $pattern, $this->limit);

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
