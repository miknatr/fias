<?php

namespace Fias;

class InitLoader extends Loader
{
    public function loadFile()
    {
        $filesInfo = $this->getLastFileInfo();
        $fileName  = $this->generateFileName($filesInfo, 'FiasCompleteXmlUrl');

        return $this->loadFileFromUrl($fileName, $filesInfo->GetLastDownloadFileInfoResult->FiasCompleteXmlUrl);
    }
}
