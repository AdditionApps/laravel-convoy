<?php

namespace AdditionApps\Convoy\Support;

use AdditionApps\Convoy\Contracts\NotificationTriggerContract;
use AdditionApps\Convoy\DataTransferObjects\ConvoyData;
use AdditionApps\Convoy\Events\ConvoyCompleted;
use AdditionApps\Convoy\Events\ConvoyUpdated;
use AdditionApps\Convoy\Exceptions\ConvoyException;
use Illuminate\Support\Arr;

class Events
{
    public function fire(ConvoyData $convoy): void
    {
        if ($this->allJobsProcessed($convoy)) {
            event($this->getConvoyCompletedEvent($convoy));

            return;
        }

        if (is_null(Arr::get($convoy->config, 'notify'))) {
            return;
        }

        $this->checkTriggers($convoy);
    }

    protected function allJobsProcessed(ConvoyData $convoy): bool
    {
        return $convoy->total === $convoy->totalProcessed;
    }

    protected function getConvoyCompletedEvent(ConvoyData $convoy)
    {
        if ($class = Arr::get($convoy->config, 'events.completed')) {
            return new $class($convoy);
        }

        return new ConvoyCompleted($convoy);
    }

    protected function checkTriggers(ConvoyData $convoy): void
    {
        collect(Arr::get($convoy->config, 'notify'))
            ->each(function ($value, $type) use ($convoy) {
                if ($this->notification($convoy, $type, $value)->isTriggered()) {
                    event($this->getConvoyUpdatedEvent($convoy));

                    return false;
                }
            });
    }

    protected function notification(
        ConvoyData $convoy,
        string $type,
        int $value
    ): NotificationTriggerContract {
        $className = ucfirst($type).'NotificationTrigger';
        $class = "AdditionApps\\Convoy\\Support\\NotificationTriggers\\{$className}";

        if (! class_exists($class)) {
            ConvoyException::missingNotificationTriggerClass($class);
        }

        return new $class($convoy, $value);
    }

    protected function getConvoyUpdatedEvent(ConvoyData $convoy)
    {
        if ($class = Arr::get($convoy->config, 'events.updated')) {
            return new $class($convoy);
        }

        return new ConvoyUpdated($convoy);
    }
}
