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
        $values = array(
            array(12),
            array(18),
            array(180),
        );
        $this->db->execute('INSERT INTO update_log(version_id) VALUES ?v', array($values));

        $this->assertEquals(
            180,
            UpdateLogHelper::getLastVersionId($this->db)
        );
    }
}
