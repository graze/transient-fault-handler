<?php

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
