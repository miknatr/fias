<?php

namespace Fias\Tests;

use Fias\Dearchiver;
use Fias\Config;

class DearchiverTest extends Base
{
    /** @expectedException \Fias\FileException */
    public function testBadFile()
    {
        new Dearchiver(__DIR__ . 'badfile', Config::get('config.test.php'));
    }
}
