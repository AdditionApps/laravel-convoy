<?php

namespace AdditionApps\Convoy\Tests\Stubs;

use AdditionApps\Convoy\Traits\JoinsConvoy;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FakeMail extends Mailable implements ShouldQueue
{
	use JoinsConvoy, Queueable, SerializesModels;

	public function build()
	{
		Log::info('Fake mail');

		return $this
			->from('example@example.com')
			->view('test-email');
	}
}