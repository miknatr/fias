<?php

namespace Fias\Loader;

class UpdateLoader extends Base
{
    public function loadFile()
    {
        $filesInfo = $this->getLastFileInfo();

        return $this->loadFileFromUrl($filesInfo->getUpdateFileName(), $filesInfo->getUpdateFileUrl());
    }
}
