<?php

namespace Fias;

class InitLoader extends Loader
{
    public function loadFile()
    {
        $filesInfo = $this->getLastFileInfo();

        return $this->loadFileFromUrl($filesInfo->getInitFileName(), $filesInfo->getInitFileUrl());
    }
}
