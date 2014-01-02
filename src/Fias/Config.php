<?php

namespace Fias;

class Config
{
    private $config;

    protected function __construct($pathToFile)
    {
        $this->config = require($pathToFile);
        if (!is_array($this->config)) {
            throw new \LogicException('Ошибка загрузки конфигурационного файла: ' . $pathToFile);
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

    public static function get($name)
    {
        $name       = basename($name);
        $pathToFile = ROOT_DIR . 'config/' . $name . '.php';

        if (!is_file($pathToFile)) {
            throw new FileNotFoundException('Файл не найден: ' . $pathToFile);
        }

        if (!isset(static::$configCaches[$pathToFile])) {
            static::$configCaches[$pathToFile] = new Config($pathToFile);
        }

        return static::$configCaches[$pathToFile];
    }
}
