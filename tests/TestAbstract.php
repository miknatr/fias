<?php

use DataSource\XmlReader;
use Grace\DBAL\ConnectionAbstract\ConnectionInterface;
use Grace\DBAL\ConnectionFactory;

class TestAbstract extends \PHPUnit_Framework_TestCase
{
    /** @var Container */
    protected $container;

    /** @var  ConnectionInterface */
    protected $db;

    protected function setUp()
    {
        $this->container = new Container();
        static::cleanDatabase();
        $this->db = $this->container->getDb();
    }

    protected static $doesProductionBackupExist = false;

    private static function renameProductionDatabase()
    {
        // То что тут отдельный контейнер это хорошо и правильно. Иначе в контейнере в тесте будет не та БД.
        $container                  = new Container();
        $currentDatabaseName        = $container->getDatabaseName();
        $tempProductionDatabaseName = $currentDatabaseName . '_production_backup';
        $db                         = static::getConnectionForRenaming($container);

        if (!static::doesDatabaseExist($db, $tempProductionDatabaseName)) {
            static::terminateConnectionsToDatabase($db, $currentDatabaseName);
            static::terminateConnectionsToDatabase($db, $tempProductionDatabaseName);
            $db->execute('ALTER DATABASE ?f RENAME TO ?f', array($currentDatabaseName, $tempProductionDatabaseName));
            $db->execute('CREATE DATABASE ?f', array($currentDatabaseName));
        }

        static::$doesProductionBackupExist = true;
        register_shutdown_function(function () {
            static::restoreProductionDatabase();
        });
    }

    private static function doesDatabaseExist(ConnectionInterface $db, $dbName)
    {
        return $db->execute(
            'SELECT datname
            FROM pg_database
            WHERE NOT datistemplate
                AND datname = ?q
            ', array($dbName)
        )->fetchResult();
    }

    private static function terminateConnectionsToDatabase(ConnectionInterface $db, $dbName)
    {
        // Нельзя удалить базу пока к ней есть коннекты. Запрос на удаление зависит от версии постгреса.
        $version = $db->execute('SELECT version()')->fetchResult();
        if (strpos($version, 'PostgreSQL 9.2') !== false) {
            $db->execute(
                'SELECT pg_terminate_backend(pg_stat_activity.pid)
                FROM pg_stat_activity
                WHERE pg_stat_activity.datname = ?q
                    AND pid <> pg_backend_pid()
                ',
                array($dbName)
            );
        } else {
            $db->execute(
                'SELECT pg_terminate_backend(pg_stat_activity.procpid)
                FROM pg_stat_activity
                WHERE pg_stat_activity.datname = ?q
                    AND procpid <> pg_backend_pid()
                ',
                array($dbName)
            );
        }
    }

    /** @var ConnectionInterface */
    protected static $dbForRenaming;

    private static function getConnectionForRenaming(Container $container)
    {
        if (!static::$dbForRenaming) {
            $uri = parse_url($container->getDbUri());

            static::$dbForRenaming = ConnectionFactory::getConnection(array(
                'adapter'  => ($uri['scheme'] == 'mysql') ? 'mysqli' : $uri['scheme'],
                'host'     => $uri['host'],
                'port'     => $uri['port'],
                'user'     => $uri['user'],
                'password' => $uri['pass'],
                'database' => 'postgres',
            ));
        }

        return static::$dbForRenaming;
    }

    private static function cleanDatabase()
    {
        if (!static::$doesProductionBackupExist) {
            static::renameProductionDatabase();
        }

        exec('php ' . __DIR__ . '/../cli/init-db.php');
    }

    private static function restoreProductionDatabase()
    {
        $container                  = new Container();
        $currentDatabaseName        = $container->getDatabaseName();
        $tempProductionDatabaseName = $currentDatabaseName . '_production_backup';
        $db                         = static::getConnectionForRenaming($container);

        if (static::doesDatabaseExist($db, $tempProductionDatabaseName)) {
            static::terminateConnectionsToDatabase($db, $currentDatabaseName);
            static::terminateConnectionsToDatabase($db, $tempProductionDatabaseName);

            $db->execute('DROP DATABASE ?f', array($currentDatabaseName));
            $db->execute('ALTER DATABASE ?f RENAME TO ?f', array($tempProductionDatabaseName, $currentDatabaseName));
        }
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
        $result = new PHPUnit_Framework_MockObject_Stub_ConsecutiveCalls(array_merge($results, array()));
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
