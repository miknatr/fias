<?php

use DataSource\XmlReader;
use Grace\DBAL\ConnectionAbstract\ConnectionInterface;

class TestAbstract extends \PHPUnit_Framework_TestCase
{
    /** @var Container */
    protected $container;

    /** @var  ConnectionInterface */
    protected $db;

    /** @var DatabaseDumpManager */
    protected static $dumpManager;

    protected function setUp()
    {
        $this->container = new Container();

        if (!static::$dumpManager) {
            static::$dumpManager = new DatabaseDumpManager(null, $this->container->getDbUri());
        }

        static::$dumpManager->restore('init', function () {
            static::$dumpManager->clean(false);
            exec('php ' . __DIR__ . '/../cli/init-db.php');
        });

        $this->db = $this->container->getDb();
    }

    public static function cleanUpFileDirectory()
    {
        static::removeFilesInDirectory(__DIR__ . '/file_directory');
    }

    private static function removeFilesInDirectory($directoryPath)
    {
        $files = scandir($directoryPath);
        foreach ($files as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }

            $filePath = $directoryPath . '/' . $file;

            if (is_dir($filePath)) {
                static::removeFilesInDirectory($filePath);
                rmdir($filePath);
            } else {
                unlink($filePath);
            }
        }
    }

    /**
     * @param \PHPUnit_Framework_TestCase $testCase
     * @param array $results
     * @return XmlReader
     */
    public function getReaderMock(\PHPUnit_Framework_TestCase $testCase, array $results)
    {
        $result = new PHPUnit_Framework_MockObject_Stub_ConsecutiveCalls(array_merge($results, []));
        $reader = $testCase->getMockBuilder('\DataSource\XmlReader')
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
