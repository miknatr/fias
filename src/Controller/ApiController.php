<?php

namespace Controller;

use Bravicility\Http\Request;
use Bravicility\Http\Response\JsonResponse;
use ApiAction\Completion;
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
        if ($limit > Completion::MAX_LIMIT) {
            return $this->makeErrorResponse('Превышен допустимый лимит на количество записей.');
        }

        $json = (new Completion($this->container->getDb(), $request->get('address'), $limit))->run();

        return new JsonResponse(200, $json);
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

        return new JsonResponse(200, $json);
    }

    private function makeErrorResponse($message)
    {
        return new JsonResponse(400, array('error_message' => $message));
    }
}
