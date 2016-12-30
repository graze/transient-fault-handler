<?php

namespace Graze\TransientFaultHandler\DetectionStrategy;

use Exception;

class DefaultDetectionStrategy implements DetectionStrategyInterface
{
    /**
     * Assume a truthy value represents success and a falsey value is a transient failure.
     *
     * @param mixed $result
     * @return bool
     */
    public function isResultTransient($result)
    {
        return !$result;
    }

    /**
     * Assume all exceptions indicate a transient network error.
     *
     * @param Exception $result
     * @return bool
     */
    public function isExceptionTransient(Exception $result)
    {
        return true;
    }
}
