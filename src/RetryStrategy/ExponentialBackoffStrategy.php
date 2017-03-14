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

use InvalidArgumentException;

class ExponentialBackoffStrategy extends AbstractRetryStrategy implements RetryStrategyInterface
{
    /** @var bool */
    protected $firstFastRetry;

    /** @var int */
    protected $minBackoff;

    /** @var int */
    protected $maxBackoff;

    /** @var int */
    protected $multiplier;

    /**
     * ExponentialBackoffStrategy constructor.
     *
     * @param int $maxRetries Maximum number of allowed retries before giving up
     * @param bool $firstFastRetry Whether to retry immediately in the first instance (or after minBackoff if non-zero)
     * @param int $multiplier The upper bound for the backoff period will be multiplied by this. A value of 1000 will
     * give backoff periods that increase in the order of seconds, e.g. 1s, 2s, 4s, ....
     * @param int $minBackoff The minimum allowed time in ms between retries
     * @param int|null $maxBackoff The maximum allowed time in ms between retries
     */
    public function __construct($maxRetries = 2, $firstFastRetry = false, $multiplier = 1000, $minBackoff = 0, $maxBackoff = null)
    {
        parent::__construct($maxRetries);

        if ($maxBackoff && $minBackoff > $maxBackoff) {
            throw new InvalidArgumentException("Minimum backoff period must be less than or equal to maximum backoff period");
        }

        $this->firstFastRetry = $firstFastRetry;
        $this->multiplier = $multiplier;
        $this->minBackoff = $minBackoff;
        $this->maxBackoff = $maxBackoff;
    }

    /**
     * @param int $retryCount
     * @return int
     */
    public function calculateBackoffPeriod($retryCount)
    {
        $offset = $this->firstFastRetry ? $this->multiplier : 0;
        $backoff = $this->minBackoff + rand(0, $this->multiplier * pow(2, $retryCount) - $offset);

        if ($this->maxBackoff) {
            $backoff = min($backoff, $this->maxBackoff);
        }

        return $backoff;
    }
}
