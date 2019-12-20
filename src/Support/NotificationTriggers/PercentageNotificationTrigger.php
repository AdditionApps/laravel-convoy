<?php

namespace AdditionApps\Convoy\Support\NotificationTriggers;

use AdditionApps\Convoy\Contracts\NotificationTriggerContract;
use AdditionApps\Convoy\DataTransferObjects\ConvoyData;

class PercentageNotificationTrigger implements NotificationTriggerContract
{
    /** @var \AdditionApps\Convoy\DataTransferObjects\ConvoyData */
    protected $convoy;

    /** @var float */
    protected $percentComplete;

    /** @var int */
    protected $triggerValue;

    public function __construct(ConvoyData $convoy, int $triggerValue)
    {
        $this->convoy = $convoy;
        $this->percentComplete = (float) $convoy->percentProcessed * 100;
        $this->triggerValue = $triggerValue;
    }

    public function isTriggered(): bool
    {
        if ($this->beforeFirstTriggerPoint() || $this->approachingNextTriggerPoint()) {
            return false;
        }

        return true;
    }

    private function beforeFirstTriggerPoint(): bool
    {
        return $this->percentComplete < $this->triggerValue;
    }

    private function approachingNextTriggerPoint(): bool
    {
        return ($this->calculateRemainder()) > (100 / $this->convoy->total);
    }

    private function calculateRemainder(): int
    {
        return $this->percentComplete % $this->triggerValue;
    }
}
