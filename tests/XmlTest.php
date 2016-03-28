<?php

use DataSource\XmlReader;

class XmlTest extends \PHPUnit_Framework_TestCase
{
    /** @var XmlReader */
    private $reader;

    protected function setUp()
    {
        $filters = [
            ['field' => 'available', 'type' => 'eq', 'value' => 1],
            ['field' => 'madeIn', 'type' => 'in', 'value' => ['USA', 'China', 'Germany', 'Rwanda']],
            ['field' => 'title', 'type' => 'in', 'value' => []],
            ['field' => 'id', 'type' => 'nin', 'value' => [6]],
            [
                'field' => 'id',
                'type'  => 'hash',
                'value' => [1 => true, 2 => true, 3 => true, 4 => true, 5 => true, 6 => true, 7 => false]
            ],
        ];

        $this->reader = new XmlReader(
            __DIR__ . '/resources/readerTest.xml',
            'Computer',
            [
                'id',
                'madeIn',
                'fakeAttribute'
            ],
            $filters
        );
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
