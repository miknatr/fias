<?php

class UpdateLogHelperLogTest extends TestAbstract
{
    public function testAddVersionIdToLog()
    {
        UpdateLogHelper::addVersionIdToLog($this->db, 100000);

        $this->assertEquals(
            100000,
            $this->db->execute('SELECT MAX(version_id) FROM update_log')->fetchResult()
        );
    }

    public function testGetLastVersionId()
    {
        $values = [
            [12],
            [18],
            [180],
        ];
        $this->db->execute('INSERT INTO update_log(version_id) VALUES ?v', [$values]);

        $this->assertEquals(
            180,
            UpdateLogHelper::getLastVersionId($this->db)
        );
    }
}
