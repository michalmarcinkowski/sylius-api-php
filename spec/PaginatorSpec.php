<?php

/*
 * This file is part of the Lakion package.
 *
 * (c) Lakion
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\Sylius\Api;

use PhpSpec\ObjectBehavior;
use Sylius\Api\AdapterInterface;
use Sylius\Api\Factory\RequestFactoryInterface;
use Sylius\Api\RequestInterface;

/**
 * @author Michał Marcinkowski <michal.marcinkowski@lakion.com>
 */
class PaginatorSpec extends ObjectBehavior
{
    function let(
        AdapterInterface $adapter,
        RequestFactoryInterface $requestFactory,
        RequestInterface $request
    ) {
        $requestFactory->create()->shouldBeCalled()->willReturn($request);
        $request->getQueryParameters()->shouldBeCalled()->willReturn([]);
        $request->setQueryParameters(['limit' => 10])->shouldBeCalled();
        $this->beConstructedWith($adapter, $requestFactory);
    }

    function it_sets_request_limit_to_10_if_none_given(
        $adapter,
        $requestFactory,
        RequestInterface $request
    ) {
        $requestFactory->create()->shouldNotBeCalled();
        $request->getQueryParameters()->shouldBeCalled()->willReturn([]);
        $request->setQueryParameters(['limit' => 10])->shouldBeCalled();
        $this->beConstructedWith($adapter, $requestFactory, $request);
        $this->shouldHaveType('Sylius\Api\Paginator');
    }

    function it_does_not_set_request_limit_to_10_if_other_is_defined(
        $adapter,
        $requestFactory,
        RequestInterface $request
    ) {
        $requestFactory->create()->shouldNotBeCalled();
        $request->getQueryParameters()->shouldBeCalled()->willReturn(['limit' => 20]);
        $request->setQueryParameters(['limit' => 10])->shouldNotBeCalled();
        $this->beConstructedWith($adapter, $requestFactory, $request);
        $this->shouldHaveType('Sylius\Api\Paginator');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Sylius\Api\Paginator');
    }

    function it_implements_paginator_interface()
    {
        $this->shouldImplement('Sylius\Api\PaginatorInterface');
    }

    function it_has_limit_10_by_default($adapter, $request)
    {
        $adapter->getNumberOfResults($request)->willReturn(20);
        $adapter->getResults($request)
            ->willReturn(array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j'))
            ->shouldBeCalled();
        $this->getCurrentPageResults()->shouldHaveCount(10);
    }

    function its_limit_can_be_specified($adapter, RequestInterface $request)
    {
        $adapter->getNumberOfResults([])->willReturn(30);
        $adapter->getResults(['page' => 1, 'limit' => 15], [])
            ->willReturn(array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o'))
            ->shouldBeCalled();
        $this->getCurrentPageResults()->shouldHaveCount(15);
    }

    function it_validates_that_limit_is_int($adapter, $requestFactory, RequestInterface $request1, RequestInterface $request2, RequestInterface $request3)
    {
        $request1->getQueryParameters()->shouldBeCalled()->willReturn(['limit' => '1']);
        $request2->getQueryParameters()->shouldBeCalled()->willReturn(['limit' => new \stdClass()]);
        $request3->getQueryParameters()->shouldBeCalled()->willReturn(['limit' => 1.5]);
        $this->shouldThrow('InvalidArgumentException')->during('__construct', [$adapter, $requestFactory, $request1]);
        $this->shouldThrow('InvalidArgumentException')->during('__construct', [$adapter, $requestFactory, $request2]);
        $this->shouldThrow('InvalidArgumentException')->during('__construct', [$adapter, $requestFactory, $request3]);
    }

    function it_gets_current_page_results($adapter)
    {
        $adapter->getNumberOfResults([])->willReturn(3);
        $adapter->getResults(['page' => 1, 'limit' => 10], [])->willReturn(array('a', 'b', 'c'))->shouldBeCalled();
        $this->getCurrentPageResults()->shouldReturn(array('a', 'b', 'c'));
    }

    function it_caches_results_for_current_page($adapter)
    {
        $adapter->getNumberOfResults([])->willReturn(3);
        $adapter->getResults(['page' => 1, 'limit' => 10], [])->shouldBeCalledTimes(1);
        $adapter->getResults(['page' => 1, 'limit' => 10], [])->willReturn(array('a', 'b', 'c'));
        $this->getCurrentPageResults()->shouldReturn(array('a', 'b', 'c'));

        $adapter->getResults(['page' => 1, 'limit' => 10], [])->willReturn(array('d', 'e', 'f'));
        $this->getCurrentPageResults()->shouldReturn(array('a', 'b', 'c'));
    }

    function it_moves_to_the_next_page($adapter)
    {
        $adapter->getNumberOfResults([])->willReturn(8);
        $adapter->getResults(['page' => 1, 'limit' => 5], [])->willReturn(array('a', 'b', 'c', 'b', 'e'));
        $adapter->getResults(['page' => 2, 'limit' => 5], [])->willReturn(array('f', 'g', 'h'));

        $this->getCurrentPageResults()->shouldReturn(array('a', 'b', 'c', 'b', 'e'));
        $this->nextPage();
        $this->getCurrentPageResults()->shouldReturn(array('f', 'g', 'h'));
    }

    function it_moves_to_the_previous_page($adapter)
    {
        $adapter->getNumberOfResults([])->willReturn(8);
        $adapter->getResults(['page' => 1, 'limit' => 5], [])->shouldBeCalledTimes(2);
        $adapter->getResults(['page' => 1, 'limit' => 5], [])->willReturn(array('a', 'b', 'c', 'b', 'e'));
        $adapter->getResults(['page' => 2, 'limit' => 5], [])->willReturn(array('f', 'g', 'h'));

        $this->getCurrentPageResults()->shouldReturn(array('a', 'b', 'c', 'b', 'e'));
        $this->nextPage();
        $this->getCurrentPageResults()->shouldReturn(array('f', 'g', 'h'));
        $this->previousPage();
        $this->getCurrentPageResults()->shouldReturn(array('a', 'b', 'c', 'b', 'e'));
    }

    function it_returns_false_if_there_is_no_previous_page()
    {
        $this->hasPreviousPage()->shouldReturn(false);
    }

    function it_throws_exception_when_can_not_move_to_the_previous_page()
    {
        $this->shouldThrow('LogicException')->during('previousPage', []);
    }

    function it_returns_true_if_there_is_previous_page($adapter)
    {
        $adapter->getNumberOfResults([])->willReturn(25);
        $this->nextPage();
        $this->hasPreviousPage()->shouldReturn(true);
    }

    function it_gets_number_of_results($adapter)
    {
        $adapter->getNumberOfResults([])->willReturn(5);
        $this->getNumberOfResults()->shouldReturn(5);
    }

    function it_returns_false_if_there_is_no_next_page($adapter)
    {
        $adapter->getNumberOfResults([])->willReturn(8);
        $this->hasNextPage()->shouldReturn(false);
    }

    function it_returns_true_if_there_is_next_page($adapter)
    {
        $adapter->getNumberOfResults([])->willReturn(25);
        $this->hasNextPage()->shouldReturn(true);
    }

    function it_throws_exception_when_can_not_move_to_the_next_page($adapter)
    {
        $adapter->getNumberOfResults([])->willReturn(8);
        $this->shouldThrow('LogicException')->during('nextPage', []);
    }
}
