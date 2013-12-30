<?php

namespace Fias;

class UpdateLoader extends Loader
{
    public function loadFile()
    {
        // TODO добавить проверку, что бы в случае, если нового обновления не вышло, не загружать этот файл.
        $fileInfo = $this->getLastFileInfo();
        set_time_limit(0);
        $fileName = tempnam($this->config->getParam('file_folder'), 'updateLoader');
        $fp = fopen ($fileName, 'w+');
        $ch = curl_init($fileInfo->GetLastDownloadFileInfoResult->FiasDeltaXmlUrl);
        curl_setopt($ch, CURLOPT_TIMEOUT, 50);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);
    }
}
