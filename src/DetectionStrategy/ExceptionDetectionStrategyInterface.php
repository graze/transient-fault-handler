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

interface ExceptionDetectionStrategyInterface
{
    /**
     * True if the exception is indicative of a transient network error; false if the exception indicates a
     * non-transient failure.
     *
     * @param Exception $exception
     * @return bool
     */
    public function isExceptionTransient(Exception $exception);
}
