<?php

namespace AdditionApps\Convoy\Contracts;

use Illuminate\Queue\Events\JobProcessed;

interface MonitorContract
{
    public function reportComplete(JobProcessed $event);
}