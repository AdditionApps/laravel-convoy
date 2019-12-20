<?php

namespace AdditionApps\Convoy\Tests\Feature;

use AdditionApps\Convoy\Facades\Convoy;
use AdditionApps\Convoy\Tests\Stubs\FakeConvoyEvent;
use AdditionApps\Convoy\Tests\Stubs\FakeJob;
use AdditionApps\Convoy\Tests\TestCase;
use Illuminate\Support\Facades\Log;
use TiMacDonald\Log\LogFake;

class ConvoyJobsTest extends TestCase
{

    /** @test */
    public function job_classes_are_tracked_through_the_queue_and_events_are_fired()
    {
        Log::swap(new LogFake);

        Convoy::notifyEvery(1)
            ->onUpdateFire(FakeConvoyEvent::class)
            ->onCompleteFire(FakeConvoyEvent::class)
            ->track(function ($convoy) {
                dispatch((new FakeJob('fizz'))->onConvoy($convoy));
                FakeJob::dispatch('buzz')->onConvoy($convoy);
            });

        // Jobs are processed
        Log::assertLogged('info', function ($message, $context) {
            return $message === 'Fake job: fizz';
        });

        Log::assertLogged('info', function ($message, $context) {
            return $message === 'Fake job: buzz';
        });

        // First notification
        Log::assertLogged('info', function ($message, $context) {
            return $message === 1;
        });
        Log::assertLogged('warning', function ($message, $context) {
            return $message === 0;
        });
        Log::assertLogged('notice', function ($message, $context) {
            return $message === 1;
        });

        // Final (completed) notification
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