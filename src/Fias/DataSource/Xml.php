<?php

namespace Fias\DataSource;

use Fias\FileHelper;
use Fias\ImporterException;

class Xml implements DataSource
{
    /** @var \XMLReader */
    private $reader;
    private $nodeName;
    private $attributes = array();
    private $filters    = array();

    public function __construct($pathToFile, $nodeName, array $attributes, array $filters = array())
    {
        $this->nodeName   = $nodeName;
        $this->attributes = $attributes;
        $this->filters    = $filters;

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
        $this->ensureMaxCountIsValid($maxCount);

        $result = array();
        $count  = 0;

        while (($count < $maxCount) && $this->reader->read()) {
            if ($this->checkIsNodeAccepted($this->reader->name)) {
                $result[] = $this->getRowAttributes();
                ++$count;
            }
        }

        return $result;
    }

    private function ensureMaxCountIsValid($maxCount)
    {
        if ($maxCount < 1) {
            throw new \LogicException('Неверное значение максимального количества строк: ' . $maxCount);
        }
    }

    private function checkIsNodeAccepted($node)
    {
        if ($node != $this->nodeName) {
            return false;
        }

        foreach ($this->filters as $attribute => $value) {
            if ($this->reader->getAttribute($attribute) != $value) {
                return false;
            }
        }

        return true;
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
