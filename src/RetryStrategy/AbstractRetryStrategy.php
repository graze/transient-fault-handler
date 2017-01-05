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

abstract class AbstractRetryStrategy implements RetryStrategyInterface
{
    /** @var int */
    protected $maxRetries;

    /**
     * AbstractRetryStrategy constructor.
     *
     * @param int $maxRetries
     */
    public function __construct($maxRetries = 1)
    {
        $this->maxRetries = $maxRetries;
    }

    /**
     * True if the retry count is less than the maximum allowed number of retries; false otherwise.
     *
     * @param int $retryCount
     * @return bool
     */
    public function shouldRetry($retryCount)
    {
        return ($retryCount < $this->maxRetries);
    }
}
