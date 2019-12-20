<?php

namespace AdditionApps\Convoy\Tests\Stubs;

use AdditionApps\Convoy\Traits\JoinsConvoy;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FakeMailTwo extends Mailable
{
    use JoinsConvoy, Queueable, SerializesModels;

    public function build()
    {
        return $this
            ->from('example@example.com')
            ->view('test-email');
    }
}