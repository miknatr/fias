<?php

use Browser\TestBrowserTrait;

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
        $tooBigLimit = $this->container->getMaxCompletionLimit() + 1;
        $this->describe('Проверяем отсечение слишком большого лимита.')
            ->loadPage('/api/complete/?pattern=Москва&limit=' . $tooBigLimit, 400)
            ->describe('Проверяем автоподстановку для адресного объекта.')
            ->loadPage('/api/complete/?pattern=Моск', 200)
            ->ensureResponse(json_decode($this->curResponse->getContent())->items[0]->title == 'Московский вокзал')
            ->ensureResponse(json_decode($this->curResponse->getContent())->items[1]->title == 'г Москва')
            ->describe('Проверяем автоподстановку для дома.')
            ->loadPage('/api/complete/?pattern=г Москва, ул Стахановская, 1')
            ->ensureResponse(json_decode($this->curResponse->getContent())->items[0]->title == 'г Москва, ул Стахановская, 1к1')
            ->describe('Проверяем автоподстановку для места')
            ->loadPage('/api/complete/?pattern=Паве')
            ->ensureResponse(count(json_decode($this->curResponse->getContent())->items) == 2)
        ;
    }

    public function testValidate()
    {
        $this->describe('Проверяем выдачу ошибки при отсутствии необходимого параметра')
            ->loadPage('/api/validate/', 400)
            ->describe('Проверяем валидацию корректного адресного объекта')
            ->loadPage('/api/validate/?pattern=г Москва', 200)
            ->ensureResponse(json_decode($this->curResponse->getContent())->is_valid && !json_decode($this->curResponse->getContent())->is_complete)
            ->describe('Проверяем валидацию некорректного адресного объекта')
            ->loadPage('/api/validate/?pattern=г Мосва', 200)
            ->ensureResponse(!json_decode($this->curResponse->getContent())->is_valid && !json_decode($this->curResponse->getContent())->is_complete)
            ->describe('Проверяем валидацию корректного дома')
            ->loadPage('/api/validate/?pattern=г Москва, ул Стахановская, 1к1', 200)
            ->ensureResponse(json_decode($this->curResponse->getContent())->is_valid && json_decode($this->curResponse->getContent())->is_complete)
            ->describe('Проверяем валидацию некорректного дома')
            ->loadPage('/api/validate/?pattern=г Москва, ул Стахановская, 1324326с1', 200)
            ->ensureResponse(!json_decode($this->curResponse->getContent())->is_valid && !json_decode($this->curResponse->getContent())->is_complete)
        ;
    }

    public function testMapping()
    {
        $this->describe('Проверяем выдачу ошибки при отсутствии необходимого параметра')
            ->loadPage('/api/address_postal_code', 400)
            ->loadPage('/api/postal_code_location', 400)
            ->describe('Проверяем выдачу корректного почтового индекса')
            ->loadPage('/api/address_postal_code/?address=г Москва, ул Стахановская', 200)
            ->ensureResponse(json_decode($this->curResponse->getContent())->postal_code == 123456)
            ->describe('Проверяем выдачу адресов')
            ->loadPage('/api/postal_code_location/?postal_code=123456', 200)
            ->ensureResponse(count(json_decode($this->curResponse->getContent())->addresses) == 1)
        ;
    }

    public function testNotFound()
    {
        $this->describe('Проверяем выдачу 404 ошибки при ошибочном uri')
            ->loadPage('/totally/wrong/destination', 404)
        ;
    }
}
