<?php

namespace Fias\Tests;

use Fias\UpdateLoader;

class UpdateLoaderTest extends Base
{
    public function testLoad()
    {
        // STOPPER тесты докачки нормальные.
        $loader = new UpdateLoader();
        $this->assertTrue(filesize($loader->loadFile()) > 0);
    }
}
