<?php

namespace Graze\TransientFaultHandler;

use Psr\Log\LoggerAwareInterface;

interface TransientFaultHandlerInterface extends LoggerAwareInterface
{
    /**
     * @param callable $task
     * @return mixed
     */
    public function execute(callable $task);
}
