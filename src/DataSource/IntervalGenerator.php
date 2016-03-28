<?php

namespace DataSource;

class IntervalGenerator implements DataSource
{
    const BASE_TYPE = 1;
    const EVEN_TYPE = 2;
    const ODD_TYPE  = 3;

    private $dataSource;
    private $startAttribute;
    private $endAttribute;
    private $typeAttribute;
    private $resultAttribute;

    public function __construct(
        DataSource $dataSource,
        $startAttribute,
        $endAttribute,
        $typeAttribute,
        $resultAttribute
    ) {
        $this->dataSource = $dataSource;

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
        $this->ensureMaxCountIsValid($maxCount);

        $count  = 0;
        $result = [];

        // Первый раз $this->currentRow гарантировано равна null. Поэтому do - while.
        do {
            if (($this->currentPosition <= $this->endPosition) && $this->currentRow) {
                $resultPart = $this->getRowsFromInterval($maxCount - $count);
                $count += count($resultPart);
                $result = array_merge($result, $resultPart);
            } else {
                $this->setCurrentRow();
            }
        } while (($count < $maxCount) && $this->currentRow);

        return $result;
    }

    private function ensureMaxCountIsValid($maxCount)
    {
        if ($maxCount < 1) {
            throw new \LogicException('Неверное значение максимального количества строк: ' . $maxCount);
        }
    }

    private function getRowsFromInterval($maxCount)
    {
        $result = [];
        $count  = 0;

        while (($count < $maxCount) && ($this->currentPosition <= $this->endPosition)) {
            switch ($this->currentRow[$this->typeAttribute]) {
                case static::BASE_TYPE:
                    $value = $this->currentPosition++;
                    break;
                case static::EVEN_TYPE:
                    $value                 = ($this->currentPosition % 2) == 0 ? $this->currentPosition : $this->currentPosition + 1;
                    $this->currentPosition = ($this->currentPosition % 2) == 0 ? $this->currentPosition + 2 : $this->currentPosition + 3;
                    break;
                case static::ODD_TYPE:
                    $value                 = ($this->currentPosition % 2) == 1 ? $this->currentPosition : $this->currentPosition + 1;
                    $this->currentPosition = ($this->currentPosition % 2) == 1 ? $this->currentPosition + 2 : $this->currentPosition + 3;
                    break;
                default:
                    throw new \Exception('Задан неверный тип работы.');
            }

            $this->currentRow[$this->resultAttribute] = $value;

            $result[] = $this->currentRow;
            ++$count;
        }

        return $result;
    }

    private function setCurrentRow()
    {
        $tmpResult = $this->dataSource->getRows(1);
        if (!$tmpResult) {
            $this->currentRow = null;
            return;
        }

        $this->currentRow      = array_shift($tmpResult);
        $this->startPosition   = $this->currentRow[$this->startAttribute];
        $this->currentPosition = $this->currentRow[$this->startAttribute];
        $this->endPosition     = $this->currentRow[$this->endAttribute];

        if ($this->startPosition > $this->endPosition) {
            throw new \Exception('Начало интервала не может быть больше его окончания');
        }
    }
}
