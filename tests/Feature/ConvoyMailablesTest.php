<?php

namespace AdditionApps\Convoy\Tests\Feature;

use AdditionApps\Convoy\Facades\Convoy;
use AdditionApps\Convoy\Tests\Stubs\FakeConvoyEvent;
use AdditionApps\Convoy\Tests\Stubs\FakeMail;
use AdditionApps\Convoy\Tests\TestCase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use TiMacDonald\Log\LogFake;

class ConvoyMailablesTest extends TestCase
{

    /** @test */
    public function job_classes_are_tracked_through_the_queue_and_events_are_fired()
    {
        Log::swap(new LogFake);

        Convoy::notifyEvery(1)
            ->onUpdateFire(FakeConvoyEvent::class)
            ->onCompleteFire(FakeConvoyEvent::class)
            ->track(function ($convoy) {
                Mail::to('test1@example.com')
                    ->send(
                        (new FakeMail())
                            ->onConvoy($convoy)
                            ->onQueue('foo')
                    );

                Mail::to(['test2@example.com', 'test3@example.com'])
                    ->send(
                        (new FakeMail())
                            ->onConvoy($convoy)
                            ->onQueue('foo')
                    );

            });

        // Jobs are processed
        Log::assertLogged('info', function ($message, $context) {
            return $message === 'Fake mail';
        });

        // First mailable
        Log::assertLogged('info', function ($message, $context) {
            return $message === 1;
        });
        Log::assertLogged('warning', function ($message, $context) {
            return $message === 0;
        });
        Log::assertLogged('notice', function ($message, $context) {
            return $message === 1;
        });

        // Final (completed) mailable
        Log::assertLogged('info', function ($message, $context) {
            return $message === 2;
        });
        Log::assertLogged('warning', function ($message, $context) {
            return $message === 0;
        });
        Log::assertLogged('notice', function ($message, $context) {
            return $message === 2;
        });

    }

}