<?php

namespace Fias\Loader;

use Fias\Directory;

class UpdateLoader extends Base
{
    /**
     * @return Directory
     */
    public function load()
    {
        $filesInfo = $this->getLastFileInfo();

        return $this->wrap(
            $this->loadFile($filesInfo->getUpdateFileName(), $filesInfo->getUpdateFileUrl())
        );
    }
}
