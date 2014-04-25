<?php

namespace Controller;

use ApiAction\PostalCode;
use ApiAction\PlaceCompletion;
use ApiAction\PostalCodeLocation;
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
        $maxLimit = $this->container->getMaxCompletionLimit();

        $maxDepth      = $request->get('max_depth');
        $addressLevels = $request->get('address_levels', array());
        $regions       = $request->get('regions', array());
        $pattern       = $request->get('pattern', '');
        $limit         = $request->get('limit', $maxLimit);

        if ($limit > $maxLimit) {
            return $this->makeErrorResponse('Превышен допустимый лимит на количество записей. Лимит должен быть не более: ' . $maxLimit);
        }

        $places = (new PlaceCompletion($this->container->getDb(), $pattern, $limit))->run();
        foreach ($places as $key => $devNull) {
            $places[$key]['type'] = 'place';
        }


        $addresses = (new AddressCompletion(
            $this->container->getDb(),
            $pattern,
            $limit,
            $maxDepth,
            $addressLevels,
            $regions
        ))->run();
        foreach ($addresses as $key => $devNull) {
            $addresses[$key]['type'] = 'address';
        }

        $result = array('items' => array_merge($places, $addresses));

        return $this->makeResponse($result);
    }

    /**
     * @route GET /api/validate
     */
    public function validate(Request $request)
    {
        $pattern = $request->get('pattern');
        if (!$pattern) {
            return $this->makeErrorResponse('Отсутствует обязательный параметр: pattern.');
        }

        $json = (new Validation($this->container->getDb(), $pattern))->run();

        return $this->makeResponse($json);
    }

    /**
     * @route GET /api/postal_code/
     */
    public function postalCode(Request $request)
    {
        $address = $request->get('address', '');
        if (!$address) {
            return $this->makeErrorResponse('Не указан адрес для поиска индекса.');
        }

        $result = (new PostalCode($this->container->getDb(), $address))->run();

        return $this->makeResponse(array('postal_code' => $result));
    }

    /**
     * @route GET /api/postal_code_location
     */
    public function postalCodeLocation(Request $request)
    {
        $postalCode = $request->get('postal_code', '');
        if (!$postalCode) {
            return $this->makeErrorResponse('Не заданы индекс для поиска адреса.');
        }

        $result = array('addresses' => (new PostalCodeLocation($this->container->getDb(), $postalCode))->run());

        return $this->makeResponse($result);
    }

    private function makeErrorResponse($message)
    {
        return $this->makeResponse(array('error_message' => $message), 400);
    }

    private function makeResponse(array $values, $status = 200)
    {
        $response = new JsonResponse($status, $values);
        $response->addHeader('Access-Control-Allow-Origin: *');

        return $response;
    }
}
