<?php

namespace AdditionApps\Convoy\Fakes;

use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Support\Testing\Fakes\QueueFake as LaravelQueueFake;

class QueueFake extends LaravelQueueFake
{

	public function queuedJobs(): array
	{
		return $this->jobs;
	}

	public function push($job, $data = '', $queue = null): void
	{
		if($this->isNotification($job)) {
			return;
		}

		if($this->shouldJoinConvoy($job)){
			$this->addToConvoy($job, $queue);
		}
	}

	protected function isNotification($job): bool
	{
		return get_class($job) === SendQueuedNotifications::class;
	}

	protected function shouldJoinConvoy($job): bool
	{
		return ! is_null($job->getConvoyId());
	}

	protected function addToConvoy($job, $queue): void
	{
		$this->jobs[is_object($job) ? get_class($job) : $job][] = [
			'job' => $job,
			'queue' => $queue,
		];
	}


}