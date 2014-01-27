<?php

namespace Fias\Tests;

use Fias\DataSource\Xml;

class XmlTest extends \PHPUnit_Framework_TestCase
{
    /** @var Xml */
    private $reader;

    protected function setUp()
    {
        $filters = array(
            array('field' => 'available', 'type' => 'eq', 'value' => 1),
            array('field' => 'madeIn', 'type' => 'in', 'value' => array('USA', 'China', 'Germany', 'Rwanda')),
            array('field' => 'title', 'type' => 'in', 'value' => array()),
            array('field' => 'id', 'type' => 'nin', 'value' => array(6)),
            array(
                'field' => 'id',
                'type'  => 'hash',
                'value' => array(1 => true, 2 => true, 3 => true, 4 => true, 5 => true, 6 => true, 7 => false)
            ),
        );

        $this->reader = new Xml(
            __DIR__ . '/resources/readerTest.xml',
            'Computer',
            array(
                'id',
                'madeIn',
                'fakeAttribute'
            ),
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
