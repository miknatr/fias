<?php

use Loader\UpdateLoader;

class UpdateLoaderTest extends TestAbstract
{
    private $fileDirectory;

    protected function setUp()
    {
        parent::setUp();
        $this->fileDirectory = __DIR__ . '/file_directory';

        if (!is_dir($this->fileDirectory)) {
            mkdir($this->fileDirectory);
        }

        $information = $this->getInformationAboutCurrentUpdateFile();
        @unlink($this->fileDirectory . '/' . $information['version'] . '_fias_delta_xml.rar');
    }

    protected function tearDown()
    {
        $this->cleanUpFileDirectory();
    }

    public function testLoad()
    {
        $loader     = new UpdateLoader($this->container->getWsdlUrl(), $this->fileDirectory);
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

        $loader = new UpdateLoader($this->container->getWsdlUrl(), $this->fileDirectory);
        $loader->load();

        $this->assertTrue(strlen($message) != filesize($filePath));
    }

    public function testNoRewritingGoodFile()
    {
        $loader = new UpdateLoader($this->container->getWsdlUrl(), $this->fileDirectory);
        $loader->load();

        $filePath = $this->fileDirectory
            . '/'
            . $this->getInformationAboutCurrentUpdateFile()['version']
            . '_fias_delta_xml.rar'
        ;

        $this->assertTrue($loader->isFileSizeCorrect($filePath, $this->getInformationAboutCurrentUpdateFile()['url']));
    }

    private $updateInformation = [];

    private function getInformationAboutCurrentUpdateFile()
    {
        if (!$this->updateInformation) {
            try {
                $client = new \SoapClient($this->container->getWsdlUrl());
                $filesInfo = $client->__soapCall('GetLastDownloadFileInfo', []);
            } catch (SoapFault $e) {
                $this->markTestSkipped($e->getMessage());
                return []; // IDE calmer, markTestSkipped throws an exception
            }

            $ch = curl_init($filesInfo->GetLastDownloadFileInfoResult->FiasDeltaXmlUrl);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_NOBODY, true);

            curl_exec($ch);

            $this->updateInformation = [
                'url'       => $filesInfo->GetLastDownloadFileInfoResult->FiasDeltaXmlUrl,
                'version'   => $filesInfo->GetLastDownloadFileInfoResult->VersionId,
                'file_size' => curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD),
            ];

            curl_close($ch);
        }

        return $this->updateInformation;
    }
}
