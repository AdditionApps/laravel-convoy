<?php

namespace AdditionApps\Convoy\Tests\Stubs;

use AdditionApps\Convoy\DataTransferObjects\ConvoyData;
use Illuminate\Support\Facades\Log;

class FakeConvoyEvent
{
	/** @var \AdditionApps\Convoy\DataTransferObjects\ConvoyData */
	public $convoy;

	public function __construct(ConvoyData $convoy)
	{
		$this->convoy = $convoy;

		Log::info($this->convoy->totalProcessed);
		Log::warning($this->convoy->totalFailed);
		Log::notice($this->convoy->totalCompleted);
	}
}