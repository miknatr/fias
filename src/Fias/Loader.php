<?php

namespace Fias;

abstract class Loader
{
    abstract public function loadFile();

    /** @var  Config */
    protected $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @return \stdClass
     */
    protected function getLastFileInfo()
    {
        $client = new \SoapClient($this->config->getParam('wdsl_url'));
        return $client->__soapCall('GetLastDownloadFileInfo', array());
    }

    protected function loadFileFromUrl($fileName, $url)
    {
        set_time_limit(0);

        $filePath = $this->config->getParam('file_folder') . '/' . $fileName;
        if (file_exists($filePath)) {
            if ($this->fileIsCorrect($filePath, $url)) {
                return $filePath;
            } else {
                unlink($filePath);
            }
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

    protected function generateFileName($filesInfo, $fileVariableName)
    {
        $fileName = basename($filesInfo->GetLastDownloadFileInfoResult->$fileVariableName);
        return $filesInfo->GetLastDownloadFileInfoResult->VersionId . '_' . $fileName;
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
