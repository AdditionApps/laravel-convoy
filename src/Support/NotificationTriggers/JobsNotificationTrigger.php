<?php

namespace AdditionApps\Convoy\Support\NotificationTriggers;

use AdditionApps\Convoy\Contracts\NotificationTriggerContract;
use AdditionApps\Convoy\DataTransferObjects\ConvoyData;

class JobsNotificationTrigger implements NotificationTriggerContract
{
    /** @var \AdditionApps\Convoy\DataTransferObjects\ConvoyData */
    protected $convoy;
    /** @var int */
    protected $triggerValue;

    public function __construct(ConvoyData $convoy, int $triggerValue)
    {
        $this->convoy = $convoy;
        $this->triggerValue = $triggerValue;
    }

    public function isTriggered(): bool
    {
        return $this->convoy->totalProcessed % $this->triggerValue === 0;
    }
}
