<?php

namespace Sylius\Api;

use Sylius\Api\Factory\RequestFactoryInterface;

class Paginator implements PaginatorInterface
{
    /**
     * @var AdapterInterface $adapter
     */
    private $adapter;
    /**
     * @var int
     */
    private $currentPage = 1;
    /**
     * @var int
     */
    private $lastPage;
    /**
     * @var array
     */
    private $currentResults;
    /**
     * @var int
     */
    private $numberOfResults = -1;
    /**
     * @var array $queryParameters
     */
    private $queryParameters;
    /**
     * @var array $uriParameters
     */
    private $uriParameters;

    /**
     * @param AdapterInterface        $adapter
     * @param RequestFactoryInterface $requestFactory
     * @param null|RequestInterface   $request
     */
    public function __construct(AdapterInterface $adapter, RequestFactoryInterface $requestFactory, RequestInterface $request = null)
    {
        if (null === $request) {
            $request = $requestFactory->create();
        }
        $queryParameters = $request->getQueryParameters();
        $queryParameters['limit'] = isset($queryParameters['limit']) ? $queryParameters['limit'] : 10;
        if (!is_int($queryParameters['limit'])) {
            throw new \InvalidArgumentException('Page limit must an integer!');
        }
        $request->setQueryParameters($queryParameters);
        $this->adapter = $adapter;
        $this->request = $request;
        $this->lastPage = (int) ceil($this->getNumberOfResults() / $queryParameters['limit']);
    }

    public function getCurrentPageResults()
    {
        if (!$this->isResultCached()) {
            $this->queryParameters['page'] = $this->currentPage;
            $this->currentResults = $this->adapter->getResults($this->request);
        }

        return $this->currentResults;
    }

    private function isResultCached()
    {
        return (null !== $this->currentResults);
    }

    public function previousPage()
    {
        if (!$this->hasPreviousPage()) {
            throw new \LogicException('There is no previous page.');
        }
        $this->currentPage--;
        $this->currentResults = null;
    }

    public function nextPage()
    {
        if (!$this->hasNextPage()) {
            throw new \LogicException('There is no next page.');
        }
        $this->currentPage++;
        $this->currentResults = null;
    }

    public function hasPreviousPage()
    {
        return (1 < $this->currentPage);
    }

    public function hasNextPage()
    {
        return ($this->currentPage < $this->lastPage);
    }

    public function getNumberOfResults()
    {
        if (-1 === $this->numberOfResults) {
            $this->numberOfResults = $this->adapter->getNumberOfResults(clone $this->request);
        }

        return $this->numberOfResults;
    }

    /**
     * @return int
     */
    public function getCurrentPage()
    {
        return $this->currentPage;
    }
}
