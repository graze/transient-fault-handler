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

namespace Graze\TransientFaultHandler;

use Graze\TransientFaultHandler\DetectionStrategy\ExceptionDetectionStrategyInterface;
use Graze\TransientFaultHandler\DetectionStrategy\FalseyReturnValueDetectionStrategy;
use Graze\TransientFaultHandler\DetectionStrategy\ReturnValueDetectionStrategyInterface;
use Graze\TransientFaultHandler\DetectionStrategy\StaticDetectionStrategy;
use Graze\TransientFaultHandler\RetryStrategy\ExponentialBackoffStrategy;
use Graze\TransientFaultHandler\RetryStrategy\RetryStrategyInterface;
use Psr\Log\LoggerInterface;

class TransientFaultHandlerBuilder
{
    /** @var LoggerInterface */
    private $logger;

    /** @var ExceptionDetectionStrategyInterface */
    private $exceptionDetectionStrategy;

    /** @var ReturnValueDetectionStrategyInterface */
    private $returnValueDetectionStrategy;

    /** @var RetryStrategyInterface */
    private $retryStrategy;

    /**
     * @param LoggerInterface $logger
     * @return TransientFaultHandlerBuilder
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * @param ExceptionDetectionStrategyInterface $exceptionDetectionStrategy
     * @return TransientFaultHandlerBuilder
     */
    public function setExceptionDetectionStrategy(ExceptionDetectionStrategyInterface $exceptionDetectionStrategy)
    {
        $this->exceptionDetectionStrategy = $exceptionDetectionStrategy;
        return $this;
    }

    /**
     * @param ReturnValueDetectionStrategyInterface $returnValueDetectionStrategy
     * @return TransientFaultHandlerBuilder
     */
    public function setReturnValueDetectionStrategy(ReturnValueDetectionStrategyInterface $returnValueDetectionStrategy)
    {
        $this->returnValueDetectionStrategy = $returnValueDetectionStrategy;
        return $this;
    }

    /**
     * @param RetryStrategyInterface $retryStrategy
     * @return TransientFaultHandlerBuilder
     */
    public function setRetryStrategy(RetryStrategyInterface $retryStrategy)
    {
        $this->retryStrategy = $retryStrategy;
        return $this;
    }

    /**
     * @return TransientFaultHandler
     */
    public function build()
    {
        $exceptionDetectionStrategy = $this->exceptionDetectionStrategy ?: new StaticDetectionStrategy();
        $returnValueDetectionStrategy = $this->returnValueDetectionStrategy ?: new FalseyReturnValueDetectionStrategy();
        $retryStrategy = $this->retryStrategy ?: new ExponentialBackoffStrategy();
        $sleep = new Sleep();

        $transientFaultHandler = new TransientFaultHandler(
            $exceptionDetectionStrategy,
            $returnValueDetectionStrategy,
            $retryStrategy,
            $sleep
        );

        if ($this->logger) {
            $transientFaultHandler->setLogger($this->logger);
        }

        return $transientFaultHandler;
    }
}
