<?php

namespace Fias;

class UpdateLoader extends Loader
{
    public function loadFile()
    {
        $filesInfo = $this->getLastFileInfo();
        $fileName  = $this->generateFileName($filesInfo, 'FiasDeltaXmlUrl');

        return $this->loadFileFromUrl($fileName, $filesInfo->GetLastDownloadFileInfoResult->FiasDeltaXmlUrl);
    }
}
