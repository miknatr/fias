<?php

namespace Fias;

class IntervalGenerator implements Reader
{
    const BASE_TYPE = 1;
    const EVEN_TYPE = 2;
    const ODD_TYPE  = 3;

    private $reader;
    private $startAttribute;
    private $endAttribute;
    private $typeAttribute;

    public function __construct(XmlReader $reader, $startAttribute, $endAttribute, $typeAttribute, $resultAttribute)
    {
        $this->reader = $reader;

        $this->startAttribute  = $startAttribute;
        $this->endAttribute    = $endAttribute;
        $this->typeAttribute   = $typeAttribute;
        $this->resultAttribute = $resultAttribute;
    }

    private $currentPosition;
    private $currentRow;
    private $startPosition;
    private $endPosition;

    public function getRows($maxCount = 1000)
    {
        $result = array();
        $count  = 0;

        // Получать строки мы хотим по указанному количеству,
        // но это накладывается на интервалы, поэтому все сложнее чем в Reader.
        while (($count < $maxCount) || !$this->currentRow) {
            if ($this->currentPosition != $this->endPosition) {
                $result = array_merge($result, $this->getRowsFromInterval($maxCount - $count));
            } else {
                $this->setCurrentRow();
            }
        }

        return $result;
    }

    private function getRowsFromInterval($maxCount)
    {
        $this->ensureMaxCountIsValid($maxCount);

        $result = array();
        $count  = 0;

        while (($count < $maxCount) && ($this->currentPosition <= $this->endPosition)) {
            switch ($this->currentRow[$this->typeAttribute]) {
                case static::BASE_TYPE:
                    $value = $this->currentPosition++;
                    break;
                case static::EVEN_TYPE:
                    $value                 = ($this->currentPosition % 2) == 0 ? $this->currentPosition : $this->currentPosition + 1;
                    $this->currentPosition = ($this->currentPosition % 2) == 0 ? $this->currentPosition + 2 : $this->currentPosition + 1;
                    break;
                case static::ODD_TYPE:
                    $value                 = ($this->currentPosition % 2) == 1 ? $this->currentPosition : $this->currentPosition + 1;
                    $this->currentPosition = ($this->currentPosition % 2) == 1 ? $this->currentPosition + 2 : $this->currentPosition + 1;
                    break;
                default:
                    throw new \Exception('Задан неверный тип работы.');
            }

            $result[] = $this->currentRow[$this->resultAttribute] = $value;
        }

        return $result;
    }

    private function ensureMaxCountIsValid($maxCount)
    {
        if ($maxCount < 1) {
            throw new \LogicException('Неверное значение максимального количества строк: ' . $maxCount);
        }
    }

    private function setCurrentRow()
    {
        $this->currentRow      = $this->reader->getRows(1);
        $this->startPosition   = $this->currentRow[$this->startAttribute];
        $this->currentPosition = $this->currentRow[$this->startAttribute];
        $this->endPosition     = $this->currentRow[$this->endAttribute];

        if ($this->startPosition >= $this->endPosition) {
            throw new \Exception('Начало интервала не может быть больше его окончания');
        }
    }
}
