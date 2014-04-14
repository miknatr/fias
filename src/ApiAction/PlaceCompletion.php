<?php

namespace ApiAction;

class PlaceCompletion extends CompletionAbstract implements ApiActionInterface
{
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
