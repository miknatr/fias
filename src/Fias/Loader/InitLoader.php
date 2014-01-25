<?php

namespace Fias\Loader;

class InitLoader extends Base
{
    public function load()
    {
        $filesInfo = $this->getLastFileInfo();

        return $this->wrap(
            $this->loadFile($filesInfo->getInitFileName(), $filesInfo->getInitFileUrl())
        );
    }
}
