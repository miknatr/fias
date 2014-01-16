<?php

namespace Fias\Loader;

use Fias\FileHelper;

abstract class Base
{
    abstract public function loadFile();

    protected $wsdlUrl;
    protected $fileDirectory;

    public function __construct($wsdlUrl, $fileDirectory)
    {
        $this->wsdlUrl       = $wsdlUrl;
        $this->fileDirectory = $fileDirectory;

        FileHelper::ensureIsDirectory($fileDirectory);
        FileHelper::ensureIsWritable($fileDirectory);
    }

    protected function getLastFileInfo()
    {
        $client    = new \SoapClient($this->wsdlUrl);
        $rawResult = $client->__soapCall('GetLastDownloadFileInfo', array());

        return new SoapResultWrapper($rawResult);
    }

    protected function loadFileFromUrl($fileName, $url)
    {
        $filePath = $this->fileDirectory . '/' . $fileName;
        if (file_exists($filePath)) {
            if ($this->fileIsCorrect($filePath, $url)) {
                return $filePath;
            }

            unlink($filePath);
        }

        $fp = fopen($filePath, 'w');
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_exec($ch);

        curl_close($ch);
        fclose($fp);

        return $filePath;
    }

    protected function fileIsCorrect($filePath, $url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_exec($ch);

        $correctSize = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);

        curl_close($ch);

        return (filesize($filePath) == $correctSize);
    }
}
