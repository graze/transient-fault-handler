<?php

namespace Graze\TransientFaultHandler\RetryStrategy;

use Graze\TransientFaultHandler\Test\TestCase;

class AbstractRetryStrategyTest extends TestCase
{
    /**
     * @dataProvider shouldReplyDataProvider
     * @param int $maxRetries
     * @param int $retryCount
     * @param bool $expected
     */
    public function testShouldReply($maxRetries, $retryCount, $expected)
    {
        $stub = $this->getMockForAbstractClass(AbstractRetryStrategy::class, [$maxRetries]);
        $this->assertEquals($expected, $stub->shouldRetry($retryCount));
    }

    /**
     * @return array
     */
    public function shouldReplyDataProvider()
    {
        return [
            [3, 0, true],
            [3, 1, true],
            [3, 3, false],
            [0, 0, false]
        ];
    }
}
