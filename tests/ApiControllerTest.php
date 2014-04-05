<?php

use Browser\TestBrowserTrait;
use ApiAction\Completion;

class ApiControllerTest extends TestAbstract
{
    use TestBrowserTrait;

    public function setUp()
    {
        parent::setUp();
        $this->prepareHttpClient('http://' . $this->container->getHost());
    }

    public function testComplete()
    {
        $tooBigLimit = Completion::MAX_LIMIT + 1;
        $this->describe('Проверяем отсечение слишком большого лимита.')
            ->loadPage('/api/complete/?address=Москва&limit=' . $tooBigLimit, 400)
            ->describe('Проверяем автоподстановку для адресного объекта.')
            ->loadPage('/api/complete/?address=Моск', 200)
            ->ensureResponse(json_decode($this->curResponse->getContent())->addresses[0]->title == 'г Москва')
            ->describe('Проверяем автоподстановку для дома.')
            ->loadPage('/api/complete/?address=г Москва, ул Стахановская, 1')
            ->ensureResponse(json_decode($this->curResponse->getContent())->addresses[0]->title == 'г Москва, ул Стахановская, 1к1')
        ;
    }

    public function testValidate()
    {
        $this->describe('Проверяем выдачу ошибки при отсутствии необходимого параметра')
            ->loadPage('/api/validate/', 400)
            ->describe('Проверяем валидацию корректного адресного объекта')
            ->loadPage('/api/validate/?address=г Москва', 200)
            ->ensureResponse(json_decode($this->curResponse->getContent())->is_valid && !json_decode($this->curResponse->getContent())->is_complete)
            ->describe('Проверяем валидацию некорректного адресного объекта')
            ->loadPage('/api/validate/?address=г Мосва', 200)
            ->ensureResponse(!json_decode($this->curResponse->getContent())->is_valid && !json_decode($this->curResponse->getContent())->is_complete)
            ->describe('Проверяем валидацию корректного дома')
            ->loadPage('/api/validate/?address=г Москва, ул Стахановская, 1к1', 200)
            ->ensureResponse(json_decode($this->curResponse->getContent())->is_valid && json_decode($this->curResponse->getContent())->is_complete)
            ->describe('Проверяем валидацию некорректного дома')
            ->loadPage('/api/validate/?address=г Москва, ул Стахановская, 1324326с1', 200)
            ->ensureResponse(!json_decode($this->curResponse->getContent())->is_valid && !json_decode($this->curResponse->getContent())->is_complete)
        ;
    }

    public function testNotFound()
    {
        $this->describe('Проверяем выдачу 404 ошибки при ошибочном uri')
            ->loadPage('/totally/wrong/destination', 404)
        ;
    }
}
