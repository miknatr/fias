<?php

namespace Fias;

class UpdateLoader extends Loader
{
    public function loadFile()
    {
        // TODO добавить проверку, что бы в случае, если нового обновления не вышло, не загружать этот файл.
        $filesInfo = $this->getLastFileInfo();
        $fileName  = $this->generateFileName($filesInfo, 'FiasDeltaXmlUrl');
        return $this->loadFileFromUrl($fileName, $filesInfo->GetLastDownloadFileInfoResult->FiasDeltaXmlUrl);
    }
}
