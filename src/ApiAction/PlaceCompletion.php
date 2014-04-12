<?php
// STOPPER код дублируется с просто Completion. Стоит сделать с этим что-то.
namespace ApiAction;

use Grace\DBAL\ConnectionAbstract\ConnectionInterface;

class PlaceCompletion implements ApiActionInterface
{
    const MAX_LIMIT = 50;

    /** @var ConnectionInterface */
    private $db;
    private $place;
    private $limit;

    public function __construct(ConnectionInterface $db, $place, $limit)
    {
        $this->db      = $db;
        $this->place = $place;
        $this->limit   = $limit ?: static::MAX_LIMIT;
    }

    public function run()
    {
        $typeId = $this->getType();
        $this->findPlaces($typeId);
    }

    private function getType()
    {
        return 42;
    }

    private function findPlaces()
    {

    }
}
