<?php

namespace Fias\Tests;

use Fias\DataSource\Xml;
use PHPUnit_Framework_MockObject_Stub_ConsecutiveCalls;

class Helper extends \PHPUnit_Framework_TestCase
{
    public static function invokeMethod($object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));

        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    /**
     * @param \PHPUnit_Framework_TestCase $testCase
     * @param array $results
     * @return Xml
     */
    public static function getReaderMock(\PHPUnit_Framework_TestCase $testCase, array $results)
    {
        $result = new PHPUnit_Framework_MockObject_Stub_ConsecutiveCalls(array_merge($results, array()));
        $reader = $testCase->getMockBuilder('\Fias\DataSource\Xml')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $reader->expects(static::any())
            ->method('getRows')
            ->will($result)
        ;

        return $reader;
    }
}
