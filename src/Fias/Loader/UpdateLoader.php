<?php

namespace Fias\Loader;

class UpdateLoader extends Base
{
    /**
     * @return \Fias\Directory
     */
    public function load()
    {
        $filesInfo = $this->getLastFileInfo();

        return $this->wrap(
            $this->loadFile($filesInfo->getUpdateFileName(), $filesInfo->getUpdateFileUrl())
        );
    }
}
