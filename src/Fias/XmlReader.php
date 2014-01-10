<?php

namespace Fias;

class XmlReader
{
    /** @var \XMLReader */
    private $reader;
    private $nodeName;
    private $attributes = array();

    public function __construct($pathToFile, $nodeName, array $attributes)
    {
        $this->nodeName   = $nodeName;
        $this->attributes = $attributes;

        $this->initializeReader($pathToFile);
    }

    private function initializeReader($pathToFile)
    {
        FileHelper::ensureIsReadable($pathToFile);

        $this->reader = new \XMLReader();

        $success = $this->reader->open($pathToFile);
        if (!$success) {
            throw new ImporterException('Ошибка открытия XML файла по адресу: ' . $pathToFile);
        }
    }

    public function getRows($maxCount = 1000)
    {
        if ($maxCount < 1) {
            throw new \LogicException('Неверное значение максимального количества строк: ' . $maxCount);
        }

        $result = array();
        $count  = 0;

        while ($this->reader->read() && ($count < $maxCount)) {
            if ($this->reader->name == $this->nodeName) {
                $result[] = $this->getRowAttributes();
                ++$count;
            }
        }

        return $result;
    }

    private function getRowAttributes()
    {
        $result = array();

        foreach ($this->attributes as $attribute) {
            // Если атрибут отсутствует, в $result[$attribute] окажется null.
            $result[$attribute] = $this->reader->getAttribute($attribute);
        }

        return $result;
    }
}
