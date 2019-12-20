<?php

namespace AdditionApps\Convoy\Tests\Unit\Support;

use AdditionApps\Convoy\Contracts\ConvoyContract;
use AdditionApps\Convoy\Support\Manifest;
use AdditionApps\Convoy\Tests\Stubs\FakeJob;
use AdditionApps\Convoy\Tests\Stubs\FakeMail;
use AdditionApps\Convoy\Tests\TestCase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use TiMacDonald\Log\LogFake;

class ManifestTest extends TestCase
{
    /** @test */
    public function manifest_with_member_ids_is_returned_for_regular_jobs()
    {
        Log::swap(new LogFake);

        $convoy = app(ConvoyContract::class);
        $convoy->setId('foo'); // Fakes look for presence of a convoy ID

        $manifest = (new Manifest())
            ->make($convoy, function ($convoy) {
                dispatch((new FakeJob('alpha'))->onConvoy($convoy));
                dispatch((new FakeJob('bravo'))->onConvoy($convoy));
            });

        $this->assertIsArray($manifest);
        $this->assertCount(2, $manifest);
        $this->assertTrue(collect($manifest)->every(function ($id) {
            return strlen($id) === 36; // UUID length
        }));

        // This is a proxy for checking that the jobs did not run for real
        // If they had we'd see something logged by the job
        Log::assertNothingLogged();
    }

    /** @test */
    public function manifest_with_member_ids_is_returned_for_mailables()
    {
        Log::swap(new LogFake);

        $convoy = app(ConvoyContract::class);
        $convoy->setId('foo'); // Fakes look for presence of a convoy ID

        $manifest = (new Manifest())
            ->make($convoy, function ($convoy) {
                Mail::to('test1@example.com')
                    ->queue((new FakeMail())->onConvoy($convoy));
            });

        $this->assertIsArray($manifest);
        $this->assertCount(1, $manifest);
        $this->assertTrue(collect($manifest)->every(function ($id) {
            return strlen($id) === 36; // UUID length
        }));

        // This is a proxy for checking that the jobs did not run for real
        // If they had we'd see something logged by the job
        Log::assertNothingLogged();
    }
}
