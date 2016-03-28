<?php

use DataSource\IntervalGenerator;

class IntervalGeneratorTest extends TestAbstract
{
    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage количества
     */
    public function testBadCount()
    {
        $reader = $this->getGenerator();
        $reader->getRows(0);
    }

    public function testGeneratedRow()
    {
        $reader = $this->getGenerator(
            [
                [['title' => 'Title 1', 'start' => 5, 'end' => 9, 'type' => 1]],
            ]
        );

        $rows = $reader->getRows();

        $this->assertEquals(5, count($rows));
        $this->assertEquals('Title 1', $rows[3]['title']);
        $this->assertEquals(5, $rows[0]['result']);
        $this->assertEquals(9, $rows[4]['result']);
    }

    public function testGetRowsWithCount()
    {
        $reader = $this->getGenerator(
            [
                [['title' => 'Title 1', 'start' => 5, 'end' => 9, 'type' => 1]],
                [['title' => 'Title 2', 'start' => 10, 'end' => 14, 'type' => 2]],
                [['title' => 'Title 3', 'start' => 15, 'end' => 20, 'type' => 3]],
            ]
        );

        // Выборка прервана на getRowsFromInterval
        $result1 = $reader->getRows(3);
        // Выборка должна прерваться в конце второй строки
        $result2 = $reader->getRows(5);
        // Тут только по 3-й строке
        $result3 = $reader->getRows();

        // Смотрим количество
        $this->assertEquals(3, count($result1));
        $this->assertEquals(5, count($result2));
        $this->assertEquals(3, count($result3));

        // Проверяем значения на стыках
        $this->assertEquals(7, $result1[2]['result']);
        $this->assertEquals(8, $result2[0]['result']);
        $this->assertEquals(14, $result2[4]['result']);
        $this->assertEquals(15, $result3[0]['result']);
    }

    /**
     * @dataProvider provider
     */
    public function testGeneration($correctResult, $readerData)
    {
        $reader = $this->getGenerator([$readerData]);
        $this->assertEquals($correctResult, count($reader->getRows()));
    }

    public function provider()
    {
        return [
            [
                3,
                [
                    ['title' => 'Title', 'start' => 9, 'end' => 15, 'type' => 2],
                ],
            ],
            [
                4,
                [
                    ['title' => 'Title', 'start' => 10, 'end' => 16, 'type' => 2],
                ],
            ],
            [
                3,
                [
                    ['title' => 'Title', 'start' => 8, 'end' => 14, 'type' => 3],
                ],
            ],
            [
                4,
                [
                    ['title' => 'Title', 'start' => 9, 'end' => 15, 'type' => 3],
                ],
            ],
        ];
    }

    public function getGenerator($results = [])
    {
        return new IntervalGenerator($this->getReaderMock($this, $results), 'start', 'end', 'type', 'result');
    }
}
