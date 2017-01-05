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
