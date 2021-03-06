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
use Graze\TransientFaultHandler\DetectionStrategy\ExceptionDetectionStrategyInterface;
use Graze\TransientFaultHandler\DetectionStrategy\ReturnValueDetectionStrategyInterface;
use Graze\TransientFaultHandler\RetryStrategy\RetryStrategyInterface;
use Graze\TransientFaultHandler\Test\TestCase;
use Mockery;

class TransientFaultHandlerTest extends TestCase
{
    /** @var ExceptionDetectionStrategyInterface */
    private $exceptionDetectionStrategy;

    /** @var ReturnValueDetectionStrategyInterface */
    private $returnValueDetectionStrategy;

    /** @var RetryStrategyInterface */
    private $retryStrategy;

    /** @var Sleep */
    private $sleep;

    public function setUp()
    {
        parent::setUp();

        $this->exceptionDetectionStrategy = Mockery::mock(ExceptionDetectionStrategyInterface::class);
        $this->returnValueDetectionStrategy = Mockery::mock(ReturnValueDetectionStrategyInterface::class);
        $this->retryStrategy = Mockery::mock(RetryStrategyInterface::class);
        $this->sleep = Mockery::mock(Sleep::class);
    }

    /**
     * Test that a task can be passed as a string holding the namespaced name of a function.
     *
     * A string containing the namespace and name of a function is a "Callable" in PHP. Before PHP 7, calling a string
     * like this using bracket notation would fail, e.g. '\Foo::test'(), and so call_user_func() had to be used. This
     * test protects against this backwards compatibility being removed.
     */
    public function testCallablesPassedAsStringAreExecuted()
    {
        // Mock the detection strategy
        $this->returnValueDetectionStrategy->shouldReceive('isReturnValueTransient')->andReturn(false)->once();

        // Create a handler
        $handler = new TransientFaultHandler(
            $this->exceptionDetectionStrategy,
            $this->returnValueDetectionStrategy,
            $this->retryStrategy,
            $this->sleep
        );

        $result = $handler->execute('\Graze\TransientFaultHandler\Test\Samples::simpleTask');

        // Test that the result of the task is returned by the handler
        $this->assertEquals('success', $result);
    }

    /**
     * Test that retries stop after the task returns a success value.
     *
     * @dataProvider doesNotRetryAfterSuccessDataProvider
     * @param bool[] $isTransientReturnValues
     * @param int $expectedCallCount
     */
    public function testDoesNotRetryAfterSuccess(array $isTransientReturnValues, $expectedCallCount)
    {
        // Mock the detection strategy
        $expectation = $this->returnValueDetectionStrategy->shouldReceive('isReturnValueTransient');
        call_user_func_array([$expectation, 'andReturn'], $isTransientReturnValues);

        // Mock the retry strategy
        $this->retryStrategy->shouldReceive('shouldRetry')->andReturn(true);
        $this->retryStrategy->shouldReceive('calculateBackoffPeriod')->andReturn(0);

        // Mock the Sleep class
        $this->sleep->shouldReceive('milliSleep');

        // Create a task that counts how many times it has been called
        $callCount = 0;
        $task = function () use (&$callCount) {
            $callCount++;
            return 'success';
        };

        // Create a handler
        $handler = new TransientFaultHandler(
            $this->exceptionDetectionStrategy,
            $this->returnValueDetectionStrategy,
            $this->retryStrategy,
            $this->sleep
        );

        $result = $handler->execute($task);

        // Test that the result of the task is returned by the handler
        $this->assertEquals('success', $result);

        // Test that the task was called the expected number of times
        $this->assertEquals($expectedCallCount, $callCount);
    }

    /**
     * @return array
     */
    public function doesNotRetryAfterSuccessDataProvider()
    {
        return [
            [[false], 1],
            [[true, true, true, false], 4],
            [[true, false, true], 2]
        ];
    }

    /**
     * Test that a non-transient exception is rethrown and further attempts are not made.
     *
     * @expectedException Exception
     */
    public function testDoesNotRetryAfterNonTransientException()
    {
        // Mock the detection strategy
        $this->exceptionDetectionStrategy->shouldReceive('isExceptionTransient')->andReturn(false);

        // Mock the retry strategy
        $this->retryStrategy->shouldReceive('shouldRetry')->andReturn(true);
        $this->retryStrategy->shouldReceive('calculateBackoffPeriod')->andReturn(0);

        // Mock the Sleep class
        $this->sleep->shouldReceive('milliSleep');

        // Create a task that counts how many times it has been called
        $callCount = 0;
        $task = function () use (&$callCount) {
            if ($callCount > 0) {
                $this->fail("Task should only be called once");
            }
            $callCount++;
            throw new Exception();
        };

        // Create a handler
        $handler = new TransientFaultHandler(
            $this->exceptionDetectionStrategy,
            $this->returnValueDetectionStrategy,
            $this->retryStrategy,
            $this->sleep
        );

        $handler->execute($task);
    }

    /**
     * Test that transient exceptions are caught and not rethrown while retries are attempted.
     */
    public function testTransientExceptionsIgnored()
    {
        // Mock the detection strategy
        $this->exceptionDetectionStrategy->shouldReceive('isExceptionTransient')->andReturn(true)->once();
        $this->returnValueDetectionStrategy->shouldReceive('isReturnValueTransient')->andReturn(false)->once();

        // Mock the retry strategy
        $this->retryStrategy->shouldReceive('shouldRetry')->andReturn(true);
        $this->retryStrategy->shouldReceive('calculateBackoffPeriod')->andReturn(0);

        // Mock the Sleep class
        $this->sleep->shouldReceive('milliSleep');

        // Create a task that throws an exception then succeeds on the second attempt
        $callCount = 0;
        $task = function () use (&$callCount) {
            if ($callCount > 0) {
                return "success";
            }
            $callCount++;
            throw new Exception();
        };

        // Create a handler
        $handler = new TransientFaultHandler(
            $this->exceptionDetectionStrategy,
            $this->returnValueDetectionStrategy,
            $this->retryStrategy,
            $this->sleep
        );

        $result = $handler->execute($task);

        // Test that the result of the task is returned by the handler
        $this->assertEquals('success', $result);
    }

    /**
     * Test that the handler will keep retrying until the retry strategy returns false.
     */
    public function testRetriesAccordingToRetryStrategy()
    {
        // Mock the detection strategy
        $this->returnValueDetectionStrategy->shouldReceive('isReturnValueTransient')->andReturn(true);

        // Mock the retry strategy
        $this->retryStrategy->shouldReceive('shouldRetry')->andReturn(true, true, true, false);
        $this->retryStrategy->shouldReceive('calculateBackoffPeriod')->andReturn(0);

        // Mock the Sleep class
        $this->sleep->shouldReceive('milliSleep');

        // Create a task that counts how many times it has been called
        $callCount = 0;
        $task = function () use (&$callCount) {
            $callCount++;
        };

        // Create a handler
        $handler = new TransientFaultHandler(
            $this->exceptionDetectionStrategy,
            $this->returnValueDetectionStrategy,
            $this->retryStrategy,
            $this->sleep
        );

        $handler->execute($task);

        // Test that the task was called the expected number of times
        $this->assertEquals(4, $callCount);
    }

    /**
     * Test that the handler will sleep for the length of time specified by the retry strategy.
     *
     * @dataProvider sleepsAccordingToRetryStrategyDataProvider
     * @param int[] $backoffPeriods
     * @param bool[] $shouldRetryReturnValues
     */
    public function testSleepsAccordingToRetryStrategy(array $backoffPeriods, array $shouldRetryReturnValues)
    {
        // Mock the detection strategy
        $this->returnValueDetectionStrategy->shouldReceive('isReturnValueTransient')->andReturn(true);

        // Mock the retry strategy
        $expectation = $this->retryStrategy->shouldReceive('shouldRetry');
        call_user_func_array([$expectation, 'andReturn'], $shouldRetryReturnValues);
        $expectation = $this->retryStrategy->shouldReceive('calculateBackoffPeriod');
        call_user_func_array([$expectation, 'andReturn'], $backoffPeriods);

        // Mock the Sleep class
        $firstFalseOccurrence = array_search(false, $shouldRetryReturnValues);
        for ($i = 0; $i < $firstFalseOccurrence; $i++) {
            $this->sleep->shouldReceive('milliSleep')->with($backoffPeriods[$i])->once();
        }

        // Create a handler
        $handler = new TransientFaultHandler(
            $this->exceptionDetectionStrategy,
            $this->returnValueDetectionStrategy,
            $this->retryStrategy,
            $this->sleep
        );

        $handler->execute(function () {
        });
    }

    /**
     * @return array
     */
    public function sleepsAccordingToRetryStrategyDataProvider()
    {
        return [
            [[0, 1, 2], [true, true, true, false]],
            [[1, 5, 10], [true, true, true, false]],
            [[1000, 5000, 1000000, 5], [true, true, true, false]]
        ];
    }

    /**
     * Test that the retry count starts at zero and is incremented to 1 in the second iteration.
     */
    public function testRetryCountIsIncremented()
    {
        // Mock the detection strategy
        $this->returnValueDetectionStrategy->shouldReceive('isReturnValueTransient')->andReturn(true);

        // Mock the retry strategy
        $this->retryStrategy->shouldReceive('shouldRetry')->andReturn(true, true, false);
        $this->retryStrategy->shouldReceive('calculateBackoffPeriod')->with(0)->andReturn(0);
        $this->retryStrategy->shouldReceive('calculateBackoffPeriod')->with(1)->andReturn(0);

        // Mock the Sleep class
        $this->sleep->shouldReceive('milliSleep');

        // Create a handler
        $handler = new TransientFaultHandler(
            $this->exceptionDetectionStrategy,
            $this->returnValueDetectionStrategy,
            $this->retryStrategy,
            $this->sleep
        );

        $handler->execute(function () {
        });
    }
}
