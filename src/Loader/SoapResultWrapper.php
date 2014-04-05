<?php

namespace Loader;

class SoapResultWrapper
{
    private $versionId;
    private $updateFileUrl;
    private $initFileUrl;

    public function __construct(\stdClass $rawResult)
    {
        $rawResult           = $rawResult->GetLastDownloadFileInfoResult;
        $this->versionId     = $rawResult->VersionId;
        $this->initFileUrl   = $rawResult->FiasCompleteXmlUrl;
        $this->updateFileUrl = $rawResult->FiasDeltaXmlUrl;
    }

    public function getVersionId()
    {
        return $this->versionId;
    }

    public function getUpdateFileUrl()
    {
        return $this->updateFileUrl;
    }

    public function getInitFileUrl()
    {
        return $this->initFileUrl;
    }

    public function getUpdateFileName()
    {
        $fileName = basename($this->updateFileUrl);
        return $this->versionId . '_' . $fileName;
    }

    public function getInitFileName()
    {
        $fileName = basename($this->initFileUrl);
        return $this->versionId . '_' . $fileName;
    }
}
