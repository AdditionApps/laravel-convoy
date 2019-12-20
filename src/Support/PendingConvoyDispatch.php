<?php

namespace AdditionApps\Convoy\Support;

use AdditionApps\Convoy\Contracts\ConvoyContract;
use Illuminate\Foundation\Bus\PendingDispatch;

class PendingConvoyDispatch extends PendingDispatch
{
	public function onConvoy(ConvoyContract $convoy): self
	{
		$this->job->onConvoy($convoy);

		return $this;
	}
}