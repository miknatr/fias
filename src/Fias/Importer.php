<?php

namespace Fias;

class Importer
{
    private $areas;
    private $table;
    private $fields;

    public function __constructor($areas, $table, $fields)
    {
        $this->areas = $areas;
        $this->table = $table;
        $this->fields = $fields;

        $this->checkConstructorParams();
    }

    public function import($fileName)
    {

    }

    private function checkConstructorParams()
    {

    }
}
