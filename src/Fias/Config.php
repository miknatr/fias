<?php

namespace Fias;

class Config
{
    private $config;

    protected function __construct($pathToFile)
    {
        $this->config = require($pathToFile);
        if (!is_array($this->config)) {
            throw new \Exception('Ошибка загрузки конфигурационного файла: ' . $pathToFile);
        }
    }

    public function getParam($key, $default = null)
    {
        return isset($this->config[$key])
            ? $this->config[$key]
            : $default
        ;
    }

    /** @var  Config[] */
    private static $configCaches;

    public static function get($pathToFile)
    {
        $realPathToFile = realpath($pathToFile);
        if(!$realPathToFile)
        {
            throw new \Exception('Файл не найден: ' . $pathToFile);
        }

        if (!isset(self::$configCaches[$realPathToFile])) {
            self::$configCaches[$realPathToFile] = new Config($realPathToFile);
        }

        return self::$configCaches[$realPathToFile];
    }
}
