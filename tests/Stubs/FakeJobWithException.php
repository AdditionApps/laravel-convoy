<?php

namespace AdditionApps\Convoy\Tests\Stubs;

use AdditionApps\Convoy\Traits\DispatchesToConvoy;
use AdditionApps\Convoy\Traits\JoinsConvoy;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FakeJobWithException implements ShouldQueue
{
    use JoinsConvoy, DispatchesToConvoy, InteractsWithQueue, Queueable, SerializesModels;

    public $foo;

    public function __construct($foo = 'bar')
    {
        $this->foo = $foo;
    }

    public function handle()
    {
        throw new \Exception('foo');
    }
}
