<?php

namespace Fias;

class Dearchiver
{
    private $config;
    private $pathToFile;

    public function __construct($pathToFile, Config $config)
    {
        $this->pathToFile = $pathToFile;
        $this->config     = $config;

        $this->checkFile();
    }

    public function extract()
    {
        $folder = $this->config->getParam('file_folder')
            . '/'
            . explode('_', basename($this->pathToFile))[0]
            . '_'
            . date('YmdHis')
        ;

        mkdir($folder);
        exec('unrar e ' . $this->pathToFile . ' ' . $folder, $output, $result);
        if ($result !== 0) {
            throw new \Exception('Ошибка разархивации: ' . implode("\n", $output));
        }

        return $folder;
    }

    private function checkFile()
    {
        if (!is_readable($this->pathToFile)) {
            throw new FileException('Файл недоступен для чтения: ' . $this->pathToFile);
        }
    }
}
