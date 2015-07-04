<?php

namespace Sylius\Api;

class ApiAdapter implements AdapterInterface
{
    /**
     * @var ApiInterface $api
     */
    private $api;

    public function __construct(ApiInterface $api)
    {
        $this->api = $api;
    }

    public function getNumberOfResults(RequestInterface $request)
    {
        $request->setQueryParameters(['page' => 1, 'limit' => 1]);
        $result = $this->api->getPaginated($request);

        return isset($result['total']) ? $result['total'] : 0;
    }

    public function getResults(RequestInterface $request)
    {
        $result = $this->api->getPaginated($request);

        return isset($result['_embedded']['items']) ? $result['_embedded']['items'] : [];
    }
}
