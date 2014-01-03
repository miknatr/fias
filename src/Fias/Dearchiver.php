<?php

namespace Fias;

class Dearchiver
{
    private $config;
    private $pathToFile;

    public function __construct($pathToFile, Config $config)
    {
        $this->pathToFile = $pathToFile;
        $this->config     = $config;
        $this->checkFile();
    }

    public function extract()
    {
        // STOPPER уточнить у Миши  по поводу реализации.
    }

    private function checkFile()
    {
        if(!is_readable($this->pathToFile)) {
            throw new FileException('Файл недоступен для чтения: ' . $this->pathToFile);
        }
    }
}
