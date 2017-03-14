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

interface RetryStrategyInterface
{
    /**
     * Given the number of retries so far, return whether subsequent retries should be attempted.
     *
     * @param int $retryCount
     * @return bool
     */
    public function shouldRetry($retryCount);

    /**
     * Return the number of ms to wait before retrying the task.
     *
     * @param int $retryCount
     * @return int
     */
    public function calculateBackoffPeriod($retryCount);
}
