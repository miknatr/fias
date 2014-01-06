<?php

namespace Fias;

class UpdateLoader extends Loader
{
    public function loadFile()
    {
        $filesInfo = $this->getLastFileInfo();

        return $this->loadFileFromUrl($filesInfo->getUpdateFileName(), $filesInfo->getUpdateFileUrl());
    }
}
