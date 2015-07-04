<?php

/*
 * This file is part of the Lakion package.
 *
 * (c) Lakion
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sylius\Api\Factory;

use Sylius\Api\AdapterInterface;
use Sylius\Api\PaginatorInterface;
use Sylius\Api\RequestInterface;

/**
 * @author Michał Marcinkowski <michal.marcinkowski@lakion.com>
 */
interface PaginatorFactoryInterface
{
    /**
     * @param  AdapterInterface        $adapter
     * @param  RequestFactoryInterface $requestFactory
     * @param  null|RequestInterface   $request
     *
     * @return PaginatorInterface
     */
    public function create(AdapterInterface $adapter, RequestFactoryInterface $requestFactory, RequestInterface $request = null);
}
