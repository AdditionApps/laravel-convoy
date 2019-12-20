<?php

namespace AdditionApps\Convoy\Tests\Stubs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FakeNormalJob implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	public $foo;

	public function __construct($foo = 'bar')
	{
		$this->foo = $foo;
	}

	public function handle()
	{
		$this->delete();
	}
}
