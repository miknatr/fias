<?php

namespace Fias\Tests;

use Fias\UpdateLoader;
use Fias\Config;

class UpdateLoaderTest extends \PHPUnit_Framework_TestCase
{
    /** @var Config */
    private $config;

    protected function setUp()
    {
        $this->config = Config::get('config');
        $fileFolder   = $this->config->getParam('file_folder');

        if (!is_dir($fileFolder)) {
            mkdir($fileFolder);
        }

        $information = $this->getInformationAboutCurrentUpdateFile();
        @unlink($fileFolder . '/' . $information['version'] . '_fias_delta_xml.rar');
    }

    public function testLoad()
    {
        $loader = new UpdateLoader($this->config->getParam('wsdl_url'), __DIR__ . '/file_folder');
        $this->assertEquals($this->getInformationAboutCurrentUpdateFile()['file_size'], filesize($loader->loadFile()));
    }

    public function testReWritingBadFile()
    {
        file_put_contents(
            $this->config->getParam('file_folder')
            . '/'
            . $this->getInformationAboutCurrentUpdateFile()['version']
            . '_fias_delta_xml.rar',
            'Really bad file'
        );

        $loader = new UpdateLoader($this->config->getParam('wsdl_url'), __DIR__ . '/file_folder');
        $this->assertEquals($this->getInformationAboutCurrentUpdateFile()['file_size'], filesize($loader->loadFile()));
    }

    public function testNoRewritingGoodFile()
    {
        $loader   = new UpdateLoader($this->config->getParam('wsdl_url'), __DIR__ . '/file_folder');
        $filePath = $loader->loadFile();

        $this->assertTrue(
            Helper::invokeMethod(
                $loader,
                'fileIsCorrect',
                array(
                    $filePath,
                    $this->getInformationAboutCurrentUpdateFile()['url']
                )
            )
        );
    }

    private $updateInformation = array();

    private function getInformationAboutCurrentUpdateFile()
    {
        if (!$this->updateInformation) {
            $client    = new \SoapClient($this->config->getParam('wsdl_url'));
            $filesInfo = $client->__soapCall('GetLastDownloadFileInfo', array());

            $ch = curl_init($filesInfo->GetLastDownloadFileInfoResult->FiasDeltaXmlUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_exec($ch);

            $this->updateInformation = array(
                'url'       => $filesInfo->GetLastDownloadFileInfoResult->FiasDeltaXmlUrl,
                'version'   => $filesInfo->GetLastDownloadFileInfoResult->VersionId,
                'file_size' => curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD)
            );

            curl_close($ch);
        }

        return $this->updateInformation;
    }
}
