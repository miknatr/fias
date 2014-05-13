<?php

namespace Controller;

use ApiAction\AddressPostalCode;
use ApiAction\PlaceCompletion;
use ApiAction\PostalCodeLocation;
use Bravicility\Http\Request;
use Bravicility\Http\Response\JsonpResponse;
use Bravicility\Http\Response\JsonResponse;
use ApiAction\AddressCompletion;
use ApiAction\Validation;
use Bravicility\Http\Response\Response;
use Bravicility\Router\RouteNotFoundException;
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
     * @route GET /api/complete.json
     * @route GET /api/complete.jsonp
     */
    public function complete(Request $request)
    {
        $maxLimit = $this->container->getMaxCompletionLimit();

        $maxAddressLevel = $request->get('max_address_level');
        $regions         = $request->get('regions', array());
        $pattern         = $request->get('pattern', '');
        $limit           = $request->get('limit', $maxLimit);

        if ($limit > $maxLimit) {
            return $this->makeErrorResponse($request, 'Превышен допустимый лимит на количество записей. Лимит должен быть не более: ' . $maxLimit);
        }

        $places = (new PlaceCompletion($this->container->getDb(), $pattern, $limit))->run();
        foreach ($places as $key => $devNull) {
            $places[$key]['item_type'] = 'place';
        }


        $addresses = (new AddressCompletion(
            $this->container->getDb(),
            $pattern,
            $limit,
            $maxAddressLevel,
            $regions
        ))->run();
        foreach ($addresses as $key => $devNull) {
            $addresses[$key]['item_type'] = 'address';
        }

        $result = array('items' => array_merge($places, $addresses));

        return $this->makeResponse($request, $result);
    }

    /**
     * @route GET /api/validate
     * @route GET /api/validate.json
     * @route GET /api/validate.jsonp
     */
    public function validate(Request $request)
    {
        $pattern = $request->get('pattern');
        if (!$pattern) {
            return $this->makeErrorResponse($request, 'Отсутствует обязательный параметр: pattern.');
        }

        $result = array('items' => (new Validation($this->container->getDb(), $pattern))->run());

        return $this->makeResponse($request, $result);
    }

    /**
     * @route GET /api/address_postal_code
     * @route GET /api/address_postal_code.json
     * @route GET /api/address_postal_code.jsonp
     */
    public function postalCode(Request $request)
    {
        $address = $request->get('address', '');
        if (!$address) {
            return $this->makeErrorResponse($request, 'Не указан адрес для поиска индекса.');
        }

        $result = (new AddressPostalCode($this->container->getDb(), $address))->run();

        return $this->makeResponse($request, array('postal_code' => $result));
    }

    /**
     * @route GET /api/postal_code_location
     * @route GET /api/postal_code_location.json
     * @route GET /api/postal_code_location.jsonp
     */
    public function postalCodeLocation(Request $request)
    {
        $postalCode = $request->get('postal_code', '');
        if (!$postalCode) {
            return $this->makeErrorResponse($request, 'Не заданы индекс для поиска адреса.');
        }

        $result = array('address_parts' => (new PostalCodeLocation($this->container->getDb(), $postalCode))->run());

        return $this->makeResponse($request, $result);
    }

    private function makeErrorResponse(Request $request, $message)
    {
        return $this->makeResponse($request, array('error_message' => $message), 400);
    }

    private function makeResponse(Request $request, array $values, $status = 200)
    {
        $format = $this->getFormat($request);

        switch ($format) {
            case 'json':
                $response = new JsonResponse($status, $values);
                break;
            case 'jsonp':
                $callback = $request->get('callback');

                if (!$callback) {
                    return new Response(400, 'Не указан обязательный параметр callback.');
                }
                $response = new JsonpResponse($values, $callback);
                break;
            default:
                throw new RouteNotFoundException;
        }

        return $response;
    }

    private function getFormat(Request $request)
    {

        $pathParts = explode('/', rtrim($request->getUrlPath(), '/'));
        $lastPart  = explode('.', array_pop($pathParts));

        return !empty($lastPart[1]) ? $lastPart[1] : 'json';
    }
}
