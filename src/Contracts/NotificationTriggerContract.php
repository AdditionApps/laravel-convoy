<?php

namespace AdditionApps\Convoy\Contracts;

interface NotificationTriggerContract
{
    public function isTriggered(): bool;
}