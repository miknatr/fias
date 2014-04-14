<?php

use ApiAction\PlaceCompletion;

class ApiActionPlaceCompletionTest extends TestAbstract
{
    public function testNotFound()
    {
        $completion = new PlaceCompletion($this->db, 'Никому неизвестное место', 50);
        $this->assertEmpty($completion->run()['places']);
    }

    public function testWithType()
    {
        $completion = new PlaceCompletion($this->db, 'вокзал Паве', 50);
        $result     = $completion->run();

        $this->assertEquals('Павелецкий вокзал', $result['places'][0]['title']);
    }

    public function testWithoutType()
    {
        $completion = new PlaceCompletion($this->db, 'Павел', 50);
        $result     = $completion->run();

        $this->assertEquals('Павелецкий автовокзал', $result['places'][0]['title']);
        $this->assertEquals('Павелецкий вокзал', $result['places'][1]['title']);
    }
}
