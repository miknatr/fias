<?php

namespace Fias\Tests;

use Fias\IntervalGenerator;

class IntervalGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /** @var IntervalGenerator */
    private $reader;

    protected function setUp()
    {
        $results = array(
            array(
                array('title' => 'Title 1', 'start' => 5, 'end' => 9, 'type' => 1),
            ),
            array(
                array('title' => 'Title 2', 'start' => 10, 'end' => 14, 'type' => 2),
            ),
            array(
                array('title' => 'Title 3', 'start' => 15, 'end' => 19, 'type' => 3),
            ),
        );

        $this->reader = new IntervalGenerator(Helper::getReaderMock($this, $results), 'start', 'end', 'type', 'result');
    }

    public function testRead()
    {
        $rows = $this->reader->getRows();

        $this->assertEquals(2, count($rows));
        $this->assertEquals('USA', $rows[1]['madeIn']);
        $this->assertEquals(null, $rows[0]['fakeAttribute']);
        $this->assertTrue(!isset($rows[0]['title']));
    }

    public function testReadWithCount()
    {
        $this->assertEquals(1, count($this->reader->getRows(1)));
        $this->assertEquals(1, count($this->reader->getRows(1)));
        $this->assertEquals(0, count($this->reader->getRows(1)));
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage количества
     */
    public function testBadCount()
    {
        $this->reader->getRows(0);
    }
}
