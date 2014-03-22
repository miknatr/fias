<?php

namespace Fias\Tests;

use Fias\Loader\UpdateLoader;
use Fias\Config;

class UpdateLoaderTest extends \PHPUnit_Framework_TestCase
{
    /** @var Config */
    private $config;
    private $fileDirectory;

    protected function setUp()
    {
        $this->config        = Helper::getGeneralConfig();
        $this->fileDirectory = __DIR__ . '/file_directory';

        if (!is_dir($this->fileDirectory)) {
            mkdir($this->fileDirectory);
        }

        $information = $this->getInformationAboutCurrentUpdateFile();
        @unlink($this->fileDirectory . '/' . $information['version'] . '_fias_delta_xml.rar');
    }

    protected function tearDown()
    {
        Helper::cleanUpFileDirectory();
    }

    public function testLoad()
    {
        $loader     = new UpdateLoader($this->config->getParam('wsdl_url'), $this->fileDirectory);
        $filesCount = count(scandir($loader->load()->getPath()));
        $this->assertGreaterThan(16, $filesCount);
    }

    public function testReWritingBadFile()
    {
        $message  = 'Really bad file';
        $filePath = $this->fileDirectory
            . '/'
            . $this->getInformationAboutCurrentUpdateFile()['version']
            . '_fias_delta_xml.rar'
        ;

        file_put_contents($filePath, $message);

        $loader = new UpdateLoader($this->config->getParam('wsdl_url'), $this->fileDirectory);
        $loader->load();

        $this->assertTrue(strlen($message) != filesize($filePath));
    }

    public function testNoRewritingGoodFile()
    {
        $loader = new UpdateLoader($this->config->getParam('wsdl_url'), $this->fileDirectory);
        $loader->load();

        $filePath = $this->fileDirectory
            . '/'
            . $this->getInformationAboutCurrentUpdateFile()['version']
            . '_fias_delta_xml.rar'
        ;

        $this->assertTrue(
            Helper::invokeMethod(
                $loader,
                'fileIsCorrect',
                array(
                    $filePath,
                    $this->getInformationAboutCurrentUpdateFile()['url'],
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
                'file_size' => curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD),
            );

            curl_close($ch);
        }

        return $this->updateInformation;
    }
}
