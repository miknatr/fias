<?php

namespace Controller;

use ApiAction\AddressToPostalCodeCorrespondence;
use ApiAction\PlaceCompletion;
use ApiAction\PostalCodeToAddressCorrespondence;
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

        $places = (new PlaceCompletion($this->container->getDb(), $request->get('address'), $limit))->run();
        if ($places) {
            foreach ($places as $key => $devNull) {
                $places[$key]['type'] = 'place';
            }
        }

        $addresses = (new AddressCompletion($this->container->getDb(), $request->get('address'), $limit))->run();
        if ($addresses) {
            foreach ($addresses as $key => $devNull) {
                $addresses[$key]['type'] = 'address';
            }
        }

        $result = array('items' => array_merge($places, $addresses));

        return $this->makeResponse($result);
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

    /**
     * @route GET /api/correspondence
     */
    public function correspondence(Request $request)
    {
        $address = $request->get('address');
        if ($address) {
            $result = (new AddressToPostalCodeCorrespondence($this->container->getDb(), $address))->run();
            return $this->makeResponse($result);
        }

        $postalCode = $request->get('postal_code');
        if ($postalCode) {
            $result = array('addresses' => (new PostalCodeToAddressCorrespondence($this->container->getDb(), $postalCode))->run());
            return $this->makeResponse($result);
        }

        return $this->makeErrorResponse('Не заданы параметры для поиска соответствия.');
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
