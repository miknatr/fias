<?php

class ImporterTest extends TestAbstract
{
    private $table;

    protected function setUp()
    {
        parent::setUp();
        $this->table = 'test_table';
    }

    /**
     * @expectedException \ImporterException
     * @expectedExceptionMessage таблица
     */
    public function testEmptyTable()
    {
        new Importer($this->db, '', ['one', 'two', 'three']);
    }

    /**
     * @expectedException \ImporterException
     * @expectedExceptionMessage поля
     */
    public function testEmptyFields()
    {
        new Importer($this->db, 'some_table_name', []);
    }

    public function testImport()
    {
        $results = [
            [
                ['id' => 1, 'madeIn' => 'China', 'title' => 'Phone'],
                ['id' => 2, 'madeIn' => 'USA', 'title' => 'Chicken wings'],
                ['id' => 3, 'madeIn' => 'Russia', 'title' => 'Topol-M'],
            ],
            [
                ['id' => 4, 'madeIn' => 'France', 'title' => 'Wine'],
                ['id' => 5, 'madeIn' => 'Germany', 'title' => 'Audi'],
                ['id' => 6, 'madeIn' => 'Denmark', 'title' => 'Tulip'],
            ],
        ];

        $reader = $this->getReaderMock($this, $results);
        $fields = [
            'madeIn' => ['name' => 'two'],
            'id'     => ['name' => 'one'],
            'title'  => ['name' => 'three'],
        ];

        $importer  = new Importer($this->db, $this->table, $fields);
        $tableName = $importer->import($reader);

        $this->assertEquals(
            6,
            $this->db->execute('SELECT COUNT(*) count FROM ?F', [$tableName])->fetchResult()
        );

        $this->assertEquals(
            'USA',
            $this->db->execute("SELECT two FROM ?F WHERE one = '2'", [$tableName])->fetchResult()
        );

        $this->assertEquals(
            'Tulip',
            $this->db->execute("SELECT three FROM ?F WHERE one = '6'", [$tableName])->fetchResult()
        );
    }
}
