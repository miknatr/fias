<?php

namespace Fias;

use Bravicility\Container\DbContainerTrait;
use Bravicility\Container\LoggingContainerTrait;
use Bravicility\Container\RouterContainerTrait;

class Container
{
    use DbContainerTrait;
    use RouterContainerTrait;
    use LoggingContainerTrait;

    protected $config = array();

    public function __construct()
    {
        $this->config = parse_ini_file(__DIR__ . '/../../config/config.ini', true);
        $this->ensureParameters($this->config, array('app.host', 'app.file_directory', 'app.wsdl_url', 'db.uri'));

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
        return __DIR__ . '/../..';
    }

    public function getHost()
    {
        return $this->config['app.host'];
    }
}
