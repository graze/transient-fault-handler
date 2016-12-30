<?php

namespace Graze\TransientFaultHandler\DetectionStrategy;

use Exception;

class ExceptionDetectionStrategy implements DetectionStrategyInterface
{
    /**
     * Assume any result is a positive result
     *
     * @param mixed $result
     * @return bool
     */
    public function isResultTransient($result)
    {
        return false;
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
