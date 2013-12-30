<?php

namespace Fias;

abstract class Loader
{
    abstract public function loadFile();

    /** @var  Config */
    protected $config;

    public function __construct()
    {
        $this->config = Config::get('config');
    }

    /**
     * @return \stdClass
     */
    protected function getLastFileInfo() {
        $client = new \SoapClient($this->config->getParam('file_loader_url') . '?WSDL');
        return $client->__soapCall('GetLastDownloadFileInfo', array());
    }
}
