<?php

namespace Controller;

use Bravicility\Http\Request;
use Bravicility\Http\Response\JsonResponse;
use Bravicility\Http\Response\Response;
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
            return new Response(400);
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
            return new Response(400);
        }

        $json = (new Validation($this->container->getDb(), $address))->run();

        return new JsonResponse(200, $json);
    }
}
