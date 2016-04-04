<?php

use Bravicility\Container\DbContainerTrait;
use Bravicility\Container\LoggingContainerTrait;
use Bravicility\Container\RouterContainerTrait;
use Loader\InitLoader;
use Loader\UpdateLoader;

class Container
{
    use DbContainerTrait;
    use RouterContainerTrait;
    use LoggingContainerTrait;

    private $config       = [];
    private $importConfig = [];

    public function __construct()
    {
        $this->config = parse_ini_file(__DIR__ . '/../config.ini', true);
        $this->ensureParameters(
            $this->config,
            ['app.host', 'app.file_directory', 'app.wsdl_url', 'app.max_completion_limit', 'db.uri']
        );

        $this->importConfig = require(__DIR__ . '/../import.php');
        $this->loadDbConfig($this->config);
        $this->loadRouterConfig($this->config, $this->getRootDirectory());
        $this->loadLoggingConfig($this->config, $this->getRootDirectory());
    }

    protected function ensureParameters(array $config, array $parameterNames)
    {
        $undefinedMessages = [];
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

    public function getMaxCompletionLimit()
    {
        return $this->config['app.max_completion_limit'];
    }

    public function getDatabaseName()
    {
        $parts = explode('/', $this->config['db.uri']);

        return array_pop($parts);
    }

    public function getDatabaseSourcesDirectory()
    {
        return __DIR__ . '/../database';
    }

    public function getHousesImportConfig()
    {
        return $this->importConfig['houses'];
    }

    public function getAddressObjectsImportConfig()
    {
        return $this->importConfig['address_objects'];
    }

    public function getUpdateLoader()
    {
        return new UpdateLoader($this->getWsdlUrl(), $this->getFileDirectory());
    }

    public function getInitLoader(){
        return new InitLoader($this->getWsdlUrl(), $this->getFileDirectory());
    }
}
