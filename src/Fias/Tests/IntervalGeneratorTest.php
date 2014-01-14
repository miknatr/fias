<?php

namespace Fias\Tests;

use Fias\IntervalGenerator;

class IntervalGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /** @var IntervalGenerator */
    private $reader;

    protected function setUp()
    {
        $mock = $this->getMock('Fias\\XmlReader');
        $this->reader = new IntervalGenerator($mock, 'start', 'end', 'type', 'result');
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
