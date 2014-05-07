<?php

use Kola\CoreDatabaseDumpManager;

class DatabaseDumpManager extends CoreDatabaseDumpManager
{
    protected function callCoreDumper($args)
    {
        if ($args != 'clean') {
            throw new \LogicException("There should be only 'clean' now");
        }

        // no parent: we don't depend on core here
    }

    protected function cleanMemcache()
    {
        // no parent: we don't depend on core here
    }
}
