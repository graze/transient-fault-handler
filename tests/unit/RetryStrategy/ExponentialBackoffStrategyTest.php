<?php

/**
 * This file is part of graze/transient-fault-handler.
 *
 * Copyright (c) 2017 Nature Delivered Ltd. <https://www.graze.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license https://github.com/graze/transient-fault-handler/blob/master/LICENSE.md
 * @link https://github.com/graze/transient-fault-handler
 */

namespace Graze\TransientFaultHandler\RetryStrategy;

use Graze\TransientFaultHandler\Test\CartesianProduct;
use Graze\TransientFaultHandler\Test\TestCase;
use InvalidArgumentException;

class ExponentialBackoffStrategyTest extends TestCase
{
    /**
     * @dataProvider backoffPeriodDataProvider
     * @param int $maxRetries
     * @param bool $multiplier
     * @param int $minBackoff
     * @param int|null $maxBackoff
     * @param int $retryCount
     */
    public function testBackoffPeriodIsAboveMinimum($maxRetries, $multiplier, $minBackoff, $maxBackoff, $retryCount)
    {
        $strategy = new ExponentialBackoffStrategy($maxRetries);
        $strategy->setMultiplier($multiplier);
        $strategy->setMinBackoff($minBackoff);
        $strategy->setMaxBackoff($maxBackoff);

        $this->assertGreaterThanOrEqual($minBackoff, $strategy->calculateBackoffPeriod($retryCount));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testMinBackoffAboveMaxBackoff()
    {
        $strategy = new ExponentialBackoffStrategy(1);
        $strategy->setMinBackoff(5);
        $strategy->setMaxBackoff(4);
    }

    /**
     * Test that the backoff period is zero for the first retry when firstFastRetry is set to true.
     *
     * @dataProvider backoffPeriodDataProvider
     * @param int $maxRetries
     * @param bool $multiplier
     * @param int $minBackoff
     * @param int|null $maxBackoff
     */
    public function testFirstFastRetry($maxRetries, $multiplier, $minBackoff, $maxBackoff)
    {
        $strategy = new ExponentialBackoffStrategy($maxRetries);
        $strategy->setFirstFastRetry(true);
        $strategy->setMultiplier($multiplier);
        $strategy->setMinBackoff($minBackoff);
        $strategy->setMaxBackoff($maxBackoff);

        $this->assertEquals(0, $strategy->calculateBackoffPeriod(0));
    }

    /**
     * @return array
     */
    public function backoffPeriodDataProvider()
    {
        $maxRetries = [0, 1, 10];
        $multiplier = [1000];
        $minBackoff = [0, 10];
        $maxBackoff = [10, 1000];
        $retryCount = [0, 10];

        $cartesianProduct = new CartesianProduct();
        return $cartesianProduct->build([$maxRetries, $multiplier, $minBackoff, $maxBackoff, $retryCount]);
    }
}
