<?php

namespace Fias;

class XmlReader
{
    private $reader;
    private $nodeName;
    private $attributes = array();

    public function __construct($pathToFile, $nodeName, array $attributes)
    {
        $this->nodeName   = $nodeName;
        $this->attributes = $attributes;

        $this->initializeReader($pathToFile);
    }

    public function getRows($count = 1000)
    {
        //STOPPER Тесты и реализация функции
    }

    private function initializeReader($pathToFile)
    {
        FileHelper::ensureIsReadable($pathToFile);

        $this->reader = new \XMLReader();

        $success = $this->reader->open($pathToFile);
        if (!$success)
        {
            throw new ImporterException('Ошибка открытия XML файла по адресу: ' . $pathToFile);
        }
    }
}
