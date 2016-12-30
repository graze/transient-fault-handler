<?php

namespace Graze\TransientFaultHandler;

use Exception;
use Graze\TransientFaultHandler\DetectionStrategy\DetectionStrategyInterface;
use Graze\TransientFaultHandler\RetryStrategy\RetryStrategyInterface;
use Psr\Log\LoggerInterface;

class TransientFaultHandlerBuilder
{
    /** @var LoggerInterface */
    private $logger;

    /** @var DetectionStrategyInterface */
    private $detectionStrategy;

    /** @var RetryStrategyInterface */
    private $retryStrategy;

    /**
     * TransientFaultHandlerBuilder constructor.
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param DetectionStrategyInterface $detectionStrategy
     * @return TransientFaultHandlerBuilder
     */
    public function setDetectionStrategy(DetectionStrategyInterface $detectionStrategy)
    {
        $this->detectionStrategy = $detectionStrategy;
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
     * @throws Exception
     */
    public function build()
    {
        if (!$this->detectionStrategy) {
            throw new Exception("No detection strategy set");
        }

        if (!$this->retryStrategy) {
            throw new Exception("No retry strategy set");
        }

        $sleep = new Sleep();
        $transientFaultHandler = new TransientFaultHandler($this->detectionStrategy, $this->retryStrategy, $sleep);
        $transientFaultHandler->setLogger($this->logger);
        return $transientFaultHandler;
    }
}
