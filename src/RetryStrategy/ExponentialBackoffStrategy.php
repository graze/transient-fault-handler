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
    protected $firstFastRetry = false;

    /** @var int */
    protected $minBackoff = 0;

    /** @var int */
    protected $maxBackoff;

    /** @var int */
    protected $multiplier = 1000;

    /**
     * Whether to retry immediately in the first instance (or after minBackoff if set).
     *
     * @param bool $firstFastRetry
     */
    public function setFirstFastRetry($firstFastRetry)
    {
        $this->firstFastRetry = $firstFastRetry;
    }

    /**
     * The minimum allowed time in ms between retries.
     *
     * @param int $minBackoff
     */
    public function setMinBackoff($minBackoff)
    {
        if ($this->maxBackoff && $minBackoff > $this->maxBackoff) {
            throw new InvalidArgumentException("Minimum backoff period must be less than or equal to maximum backoff period");
        }

        $this->minBackoff = $minBackoff;
    }

    /**
     * The maximum allowed time in ms between retries.
     *
     * @param int $maxBackoff
     */
    public function setMaxBackoff($maxBackoff)
    {
        if ($maxBackoff < $this->minBackoff) {
            throw new InvalidArgumentException("Maximum backoff period must be more than or equal to minimum backoff period");
        }

        $this->maxBackoff = $maxBackoff;
    }

    /**
     * The upper bound for the backoff period will be multiplied by this. A value of 1000 will give backoff periods
     * that increase in the order of seconds, i.e. 1s, 2s, 4s, ....
     *
     * @param int $multiplier
     */
    public function setMultiplier($multiplier)
    {
        $this->multiplier = $multiplier;
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
