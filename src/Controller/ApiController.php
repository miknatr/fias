<?php

namespace Controller;

use Bravicility\Http\Request;
use Bravicility\Http\Response\JsonResponse;
use ApiAction\AddressCompletion;
use ApiAction\Validation;
use Container;

class ApiController
{
    /** @var Container */
    protected $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @route GET /api/complete
     */
    public function complete(Request $request)
    {
        $limit = $request->get('limit');
        if ($limit > AddressCompletion::MAX_LIMIT) {
            return $this->makeErrorResponse('Превышен допустимый лимит на количество записей.');
        }

        $json = (new AddressCompletion($this->container->getDb(), $request->get('address'), $limit))->run();

        return $this->makeResponse($json);
    }

    /**
     * @route GET /api/validate
     */
    public function validate(Request $request)
    {
        $address = $request->get('address');
        if (!$address) {
            return $this->makeErrorResponse('Отсутствует обязательный параметр: адрес.');
        }

        $json = (new Validation($this->container->getDb(), $address))->run();

        return $this->makeResponse($json);
    }

    private function makeErrorResponse($message)
    {
        return $this->makeResponse(array('error_message' => $message), 400);
    }

    private function makeResponse($json, $status = 200)
    {
        $response = new JsonResponse($status, $json);
        $response->addHeader('Access-Control-Allow-Origin: *');

        return $response;
    }
}
