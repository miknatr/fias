<?php

namespace Fias\Loader;

class InitLoader extends Base
{
    public function loadFile()
    {
        $filesInfo = $this->getLastFileInfo();

        return $this->loadFileFromUrl($filesInfo->getInitFileName(), $filesInfo->getInitFileUrl());
    }
}
