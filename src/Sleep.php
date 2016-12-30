<?php

namespace Graze\TransientFaultHandler;

class Sleep
{
    /**
     * @param int $milliseconds
     */
    public function milliSleep($milliseconds)
    {
        usleep($milliseconds * 1000);
    }
}
