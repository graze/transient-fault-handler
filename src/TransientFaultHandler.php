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

use Exception;
use Graze\TransientFaultHandler\DetectionStrategy\DefaultDetectionStrategy;
use Graze\TransientFaultHandler\DetectionStrategy\DetectionStrategyInterface;
use Graze\TransientFaultHandler\RetryStrategy\ExponentialBackoffStrategy;
use Graze\TransientFaultHandler\RetryStrategy\RetryStrategyInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * This class can be used to repeatedly retry a task that is prone to fail due to transient (i.e. temporary) network
 * errors. The retry strategy defines how long to wait in between each retry attempt and how many retry attempts to make
 * before failing. The detection strategy will decide if the task failed, and whether that failure was due to a
 * transient network problem or not. If not, the task will not be retried.
 */
class TransientFaultHandler implements TransientFaultHandlerInterface
{
    use LoggerAwareTrait;

    /** @var DetectionStrategyInterface */
    private $detectionStrategy;

    /** @var RetryStrategyInterface */
    private $retryStrategy;

    /** @var Sleep */
    private $sleep;

    /**
     * TransientFaultHandler constructor.
     *
     * @param DetectionStrategyInterface $detectionStrategy
     * @param RetryStrategyInterface $retryStrategy
     * @param Sleep $sleep
     */
    public function __construct(
        DetectionStrategyInterface $detectionStrategy,
        RetryStrategyInterface $retryStrategy,
        Sleep $sleep
    ) {
        $this->detectionStrategy = $detectionStrategy;
        $this->retryStrategy = $retryStrategy;
        $this->sleep = $sleep;
    }

    /**
     * Retry the task according to the retry strategy until it succeeds, experiences a non-transient failure, or the
     * retry strategy reaches a stopping condition.
     *
     * @param callable $task
     * @return mixed The return value of the task will be returned.
     * @throws Exception If the task throws an exception that is deemed non-transient by the detection strategy, the
     * exception will be rethrown. If the task throws an exception during the final retry attempt, the exception will be
     * rethrown.
     */
    public function execute(callable $task)
    {
        $retryCount = 0;

        do {
            $result = null;
            $exception = null;

            try {
                $result = $task();
                $transient = $this->detectionStrategy->isResultTransient($result);

                // If the result does not indicate a transient error, return to the user
                if (!$transient) {
                    return $result;
                }
            } catch (Exception $e) {
                $exception = $e;
                $transient = $this->detectionStrategy->isExceptionTransient($e);

                // If the exception is not transient, rethrow to the user
                if (!$transient) {
                    throw $e;
                }
            }

            $retry = $this->retryStrategy->shouldRetry($retryCount);

            if (!$retry) {
                break;
            }

            $backoffPeriod = $this->retryStrategy->getBackoffPeriod($retryCount);
            $retryCount++;

            if ($this->logger) {
                $this->logger->debug("Task failed, retrying [$retryCount] in {$backoffPeriod}ms");
            }

            $this->sleep->milliSleep($backoffPeriod);
        } while ($retry);

        // If the last retry raised an exception, rethrow it
        if ($exception) {
            throw $exception;
        }

        return $result;
    }

    /**
     * Return an instance of TransientFaultHandler. A detection strategy and retry strategy can optionally be set
     * otherwise defaults are chosen.
     *
     * @param DetectionStrategyInterface $detectionStrategy
     * @param RetryStrategyInterface $retryStrategy
     *
     * @return static
     */
    public static function factory(
        DetectionStrategyInterface $detectionStrategy = null,
        RetryStrategyInterface $retryStrategy = null
    ) {
        if (!$detectionStrategy) {
            $detectionStrategy = new DefaultDetectionStrategy();
        }

        if (!$retryStrategy) {
            $retryStrategy = new ExponentialBackoffStrategy();
        }

        return new static($detectionStrategy, $retryStrategy, new Sleep());
    }
}
