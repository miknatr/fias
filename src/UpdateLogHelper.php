<?php

use Grace\DBAL\ConnectionAbstract\ConnectionInterface;

class UpdateLogHelper
{
    public static function addVersionIdToLog(ConnectionInterface $db, $versionId)
    {
        $db->execute('INSERT INTO update_log(version_id) VALUES (?q)', [$versionId]);
    }

    public static function getLastVersionId(ConnectionInterface $db)
    {
        return (int) $db->execute('SELECT MAX(version_id) FROM update_log')->fetchResult();
    }
}
