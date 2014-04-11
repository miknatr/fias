<?php

class DbHelperTest extends TestAbstract
{
    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Ошибка выполнения SQL файла
     */
    public function testRunException()
    {
        DbHelper::runFile($this->container->getDatabaseName(), __DIR__ . '/resources/inCorrectScript.sql');
    }

    public function testRun()
    {
        DbHelper::runFile($this->container->getDatabaseName(), __DIR__ . '/resources/correctScript.sql');
        $this->assertEquals(2, $this->db->execute('SELECT * FROM "correctTable"')->getNumRows());
    }
}
