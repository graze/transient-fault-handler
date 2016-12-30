<?php

namespace Graze\TransientFaultHandler\DetectionStrategy;

use Exception;

interface DetectionStrategyInterface
{
    /**
     * True if the result of the function calls is indicative of a transient network error; false if the result
     * indicates success or a non-transient failure.
     *
     * @param mixed $result
     * @return bool
     */
    public function isResultTransient($result);

    /**
     * True if the exception is indicative of a transient network error; false if the exception indicates a
     * non-transient failure.
     *
     * @param Exception $result
     * @return bool
     */
    public function isExceptionTransient(Exception $result);
}
