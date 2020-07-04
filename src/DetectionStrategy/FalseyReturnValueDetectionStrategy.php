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

class FalseyReturnValueDetectionStrategy implements ReturnValueDetectionStrategyInterface
{
    /**
     * Assume a truthy value represents success and a falsey value is a transient failure.
     *
     * @param mixed $value
     * @return bool
     */
    public function isReturnValueTransient($value)
    {
        return !$value;
    }
}
