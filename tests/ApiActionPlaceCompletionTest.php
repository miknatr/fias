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

    public function testWithParent()
    {
        $completion = new PlaceCompletion($this->db, 'пулко', 50);
        $result     = $completion->run();

        $this->assertEquals('Пулково аэропорт', $result['places'][0]['title']);
        $this->assertEquals(0, $result['places'][0]['is_complete']);

        $completion = new PlaceCompletion($this->db, 'Пулково аэропорт, терминал', 50);
        $result     = $completion->run();

        $this->assertCount(3, $result['places']);
        $this->assertEquals('Пулково аэропорт, 1 терминал', $result['places'][0]['title']);
        $this->assertEquals(1, $result['places'][0]['is_complete']);

        $completion = new PlaceCompletion($this->db, 'Пулково аэропорт, но', 50);
        $result     = $completion->run();

        $this->assertEquals('Пулково аэропорт, новый терминал', $result['places'][0]['title']);
        $this->assertEquals(1, $result['places'][0]['is_complete']);
    }
}
