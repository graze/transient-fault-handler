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

namespace Graze\TransientFaultHandler\DetectionStrategy;

use Exception;

/**
 * This return value detection strategy ignores the return value and always returns the same answer, which can be
 * configured using the constructor argument.
 */
class StaticDetectionStrategy implements ExceptionDetectionStrategyInterface, ReturnValueDetectionStrategyInterface
{
    /**
     * The value to return from the isExceptionTransient function
     *
     * @var bool
     */
    private $returnTransient;

    /**
     * StaticExceptionDetectionStrategy constructor.
     *
     * @param bool $returnTransient The value to always return when detecting transient errors
     */
    public function __construct($returnTransient = true)
    {
        $this->returnTransient = $returnTransient;
    }

    /**
     * @param Exception $exception
     * @return bool
     */
    public function isExceptionTransient(Exception $exception)
    {
        return $this->returnTransient;
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public function isReturnValueTransient($value)
    {
        return $this->returnTransient;
    }
}
