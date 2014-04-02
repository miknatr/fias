<?php

use Bravicility\Container\DbContainerTrait;
use Bravicility\Container\LoggingContainerTrait;
use Bravicility\Container\RouterContainerTrait;

class Container
{
    use DbContainerTrait;
    use RouterContainerTrait;
    use LoggingContainerTrait;

    protected $config       = array();
    protected $importConfig = array();

    public function __construct()
    {
        $this->config = parse_ini_file(__DIR__ . '/../config/config.ini', true);
        $this->ensureParameters($this->config, array('app.host', 'app.file_directory', 'app.wsdl_url', 'db.uri'));

        $this->importConfig = require(__DIR__ . '/../config/import.php');
        $this->loadDbConfig($this->config);
        $this->loadRouterConfig($this->config, $this->getRootDirectory());
        $this->loadLoggingConfig($this->config, $this->getRootDirectory());
    }

    protected function ensureParameters(array $config, array $parameterNames)
    {
        $undefinedMessages = array();
        foreach ($parameterNames as $name) {
            if (!isset($config[$name])) {
                $undefinedMessages[] = "Config parameter {$name} is not defined";
            }
        }

        if (count($undefinedMessages) > 0) {
            throw new \LogicException(implode("\n", $undefinedMessages));
        }
    }

    public function getDbUri()
    {
        return $this->config['db.uri'];
    }

    public function getRootDirectory()
    {
        return __DIR__ . '/..';
    }

    public function getHost()
    {
        return $this->config['app.host'];
    }

    public function getWsdlUrl()
    {
        return $this->config['app.wsdl_url'];
    }

    public function getFileDirectory()
    {
        return $this->config['app.file_directory'];
    }

    public function getDatabaseName()
    {
        $parts = explode('/', $this->config['db.uri']);

        return array_pop($parts);
    }

    public function getHousesImportConfig()
    {
        return $this->importConfig['houses'];
    }

    public function getAddressObjectsImportConfig()
    {
        return $this->importConfig['address_objects'];
    }
}
