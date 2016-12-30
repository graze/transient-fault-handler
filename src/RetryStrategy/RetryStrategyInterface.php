<?php

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
    public function getBackoffPeriod($retryCount);
}
