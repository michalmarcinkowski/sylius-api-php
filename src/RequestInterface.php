<?php

/*
 * This file is part of the Lakion package.
 *
 * (c) Lakion
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sylius\Api;

/**
 * @author Michał Marcinkowski <michal.marcinkowski@lakion.com>
 */
interface RequestInterface
{
    public function getId();
    public function setId($id);
    public function getUriParameters();
    public function setUriParameters(array $uriParameters);
    public function getQueryParameters();
    public function setQueryParameters(array $uriParameters);
}
