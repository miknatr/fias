<?php

namespace Fias\Tests;

use Fias\UpdateLoader;

class UpdateLoaderTest extends Base
{
    public function testLoad()
    {
        $loader = new UpdateLoader();
        $loader->loadFile();
    }
}
