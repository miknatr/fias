<?php

namespace Loader;

use FileSystem\Directory;

class InitLoader extends Base
{
    /**
     * @return Directory
     */
    public function load()
    {
        $filesInfo = $this->getLastFileInfo();

        return $this->wrap(
            $this->loadFile($filesInfo->getInitFileName(), $filesInfo->getInitFileUrl())
        );
    }
}
