<?php

namespace AdditionApps\Convoy\Fakes;

use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Support\Testing\Fakes\MailFake as LaravelMailFake;

class MailFake extends LaravelMailFake
{

	public function queuedMailables(): array
	{
		return $this->queuedMailables;
	}

	public function queue($view, $queue = null): void
	{
		if (! $view instanceof Mailable) {
			return;
		}

		if($this->shouldJoinConvoy($view)){
			$this->queuedMailables[] = $view;
		}
	}

	protected function shouldJoinConvoy($view): bool
	{
		return ! is_null($view->getConvoyId());
	}
}