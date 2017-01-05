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
