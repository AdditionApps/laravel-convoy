<?php

namespace AdditionApps\Convoy\Support;

use AdditionApps\Convoy\Contracts\ConvoyRepositoryContract;
use AdditionApps\Convoy\Contracts\MonitorContract;
use AdditionApps\Convoy\DataTransferObjects\ConvoyData;
use Illuminate\Mail\SendQueuedMailable;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Support\Arr;

class Monitor implements MonitorContract
{
    /** @var \AdditionApps\Convoy\Contracts\ConvoyRepositoryContract */
    protected $convoyRepository;

    /** @var \AdditionApps\Convoy\Support\Events */
    protected $convoyEvents;

    public function __construct(
        ConvoyRepositoryContract $convoyRepository,
        Events $convoyEvents
    ) {
        $this->convoyRepository = $convoyRepository;
        $this->convoyEvents = $convoyEvents;
    }

    public function reportComplete(JobProcessed $event): void
    {
        $job = $this->getJob($event);

        if ($this->jobIsNotInConvoy($job)) {
            return;
        }

        $convoy = $this->convoyRepository->updateAfterJobCompleted(
            $job->getConvoyId(), $job->getConvoyMemberId()
        );

        if ($this->shouldDeleteConvoy($convoy)) {
            $this->convoyRepository->delete($convoy->id);
        }

        $this->convoyEvents->fire($convoy);
    }

    protected function getJob($event)
    {
        $data = unserialize(Arr::get($event->job->payload(), 'data.command'));

        switch (true) {
            case $data instanceof SendQueuedMailable:
                return $data->mailable;
            default:
                return $data;
        }
    }

    protected function jobIsNotInConvoy($job): bool
    {
        return ! property_exists($job, 'convoyId')
            || ! property_exists($job, 'convoyMemberId')
            || is_null($job->getConvoyId())
            || is_null($job->getConvoyMemberId());
    }

    protected function shouldDeleteConvoy(ConvoyData $convoy): bool
    {
        if ($convoy->totalProcessed === $convoy->total) {
            return true;
        }

        // TODO
        // potentially have a per-convoy config option to retain
        // convoys when completed or when jobs on convoy failed.

        return false;
    }

    public function reportFailed(JobFailed $event): void
    {
        $job = $this->getJob($event);

        if ($this->jobIsNotInConvoy($job)) {
            return;
        }

        $convoy = $this->convoyRepository->updateAfterJobFailed(
            $job->getConvoyId(), $job->getConvoyMemberId()
        );

        if ($this->shouldDeleteConvoy($convoy)) {
            $this->convoyRepository->delete($convoy->id);
        }

        $this->convoyEvents->fire($convoy);
    }
}
