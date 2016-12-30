<?php

namespace Graze\TransientFaultHandler\RetryStrategy;

use Graze\TransientFaultHandler\Test\CartesianProduct;
use Graze\TransientFaultHandler\Test\TestCase;
use InvalidArgumentException;

class ExponentialBackoffStrategyTest extends TestCase
{
    /**
     * @dataProvider backoffPeriodDataProvider
     * @param int $maxRetries
     * @param bool $firstFastRetry
     * @param bool $multiplier
     * @param int $minBackoff
     * @param int|null $maxBackoff
     * @param int $retryCount
     */
    public function testBackoffPeriodIsAboveMinimum($maxRetries, $firstFastRetry, $multiplier, $minBackoff, $maxBackoff, $retryCount)
    {
        $strategy = new ExponentialBackoffStrategy($maxRetries, $firstFastRetry, $multiplier, $minBackoff, $maxBackoff);
        $this->assertGreaterThanOrEqual($minBackoff, $strategy->getBackoffPeriod($retryCount));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testMinBackoffAboveMaxBackoff()
    {
        new ExponentialBackoffStrategy(1, false, 1000, 5, 4);
    }

    /**
     * @return array
     */
    public function backoffPeriodDataProvider()
    {
        $maxRetries = [0, 1, 10];
        $firstFastRetry = [true, false];
        $multiplier = [1000];
        $minBackoff = [0, 10];
        $maxBackoff = [10, 1000, null];
        $retryCount = [0, 10];

        $cartesianProduct = new CartesianProduct();
        return $cartesianProduct->build([$maxRetries, $firstFastRetry, $multiplier, $minBackoff, $maxBackoff, $retryCount]);
    }
}