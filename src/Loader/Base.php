<?php

namespace Loader;

use FileSystem\Dearchiver;
use \FileSystem\FileHelper;
use \FileSystem\Directory;

abstract class Base
{
    /** @return Directory */
    abstract public function load();

    protected $wsdlUrl;
    protected $fileDirectory;

    public function __construct($wsdlUrl, $fileDirectory)
    {
        $this->wsdlUrl       = $wsdlUrl;
        $this->fileDirectory = $fileDirectory;

        FileHelper::ensureIsDirectory($fileDirectory);
        FileHelper::ensureIsWritable($fileDirectory);
    }

    /** @var SoapResultWrapper */
    private $fileInfoResult = null;

    public function getLastFileInfo()
    {
        if (!$this->fileInfoResult) {
            $this->fileInfoResult = $this->getLastFileInfoRaw();
        }

        return $this->fileInfoResult;
    }

    private function getLastFileInfoRaw()
    {
        $client    = new \SoapClient($this->wsdlUrl);
        $rawResult = $client->__soapCall('GetLastDownloadFileInfo', []);

        return new SoapResultWrapper($rawResult);
    }

    protected function loadFile($fileName, $url)
    {
        $filePath = $this->fileDirectory . '/' . $fileName;
        if (file_exists($filePath)) {
            if ($this->isFileSizeCorrect($filePath, $url)) {
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

    protected function wrap($path)
    {
        $pathToDirectory = Dearchiver::extract($this->fileDirectory, $path);
        $this->addVersionId($pathToDirectory);

        return new Directory($pathToDirectory);
    }

    private function addVersionId($pathToDirectory)
    {
        $versionId = $this->getLastFileInfo()->getVersionId();
        file_put_contents($pathToDirectory . '/VERSION_ID_' . $versionId, 'Версия: ' . $versionId);
    }

    public function isFileSizeCorrect($filePath, $url)
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
