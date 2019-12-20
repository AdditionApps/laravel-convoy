<?php

namespace AdditionApps\Convoy\Tests\Unit\Support;

use AdditionApps\Convoy\Events\ConvoyCompleted;
use AdditionApps\Convoy\Events\ConvoyUpdated;
use AdditionApps\Convoy\Support\Events;
use AdditionApps\Convoy\Tests\Stubs\FakeConvoyEvent;
use AdditionApps\Convoy\Tests\Support\Traits\BuildsConvoys;
use AdditionApps\Convoy\Tests\TestCase;
use Illuminate\Support\Facades\Event;

class EventsTest extends TestCase
{
    use BuildsConvoys;

    /** @test */
    public function convoy_completed_event_fired_if_all_jobs_processed()
    {
        Event::fake();

        $convoy = $this->convoyGenerator()
            ->withId('qux')
            ->withManifest(['foo', 'bar', 'baz'])
            ->withTotalComplete(3)
            ->create();

        (new Events)->fire($convoy);

        Event::assertDispatched(ConvoyCompleted::class, function ($event) {
            return $event->convoy->id === 'qux';
        });
    }

    /** @test */
    public function custom_convoy_completed_event_fired_if_all_jobs_processed()
    {
        Event::fake();

        $convoy = $this->convoyGenerator()
            ->withId('qux')
            ->withManifest(['foo', 'bar', 'baz'])
            ->withConfig([
                'events' => [
                    'completed' => FakeConvoyEvent::class,
                ],
            ])
            ->withTotalComplete(3)
            ->create();

        (new Events)->fire($convoy);

        Event::assertDispatched(FakeConvoyEvent::class, function ($event) {
            return $event->convoy->id === 'qux';
        });
    }

    /** @test */
    public function no_events_triggered_if_notify_config_not_set()
    {
        Event::fake();

        $convoy = $this->convoyGenerator()
            ->withId('qux')
            ->withManifest(['foo', 'bar', 'baz'])
            ->withTotalComplete(2)
            ->create();

        (new Events)->fire($convoy);

        Event::assertNotDispatched(ConvoyCompleted::class);
        Event::assertNotDispatched(ConvoyUpdated::class);
    }

    /** @test */
    public function convoy_update_events_triggered_once_if_both_trigger_types_are_triggered()
    {
        Event::fake();

        $convoy = $this->convoyGenerator()
            ->withId('qux')
            ->withManifest([
                'memberOne',
                'memberTwo',
                'memberThree',
                'memberFour',
                'memberFive',
            ])
            ->withConfig([
                'notify' => [
                    'jobs' => 2,
                    'percentage' => 20,
                ],
            ])
            ->withTotalComplete(2)
            ->create();

        (new Events)->fire($convoy);

        Event::assertDispatched(ConvoyUpdated::class, function ($event) {
            return $event->convoy->id === 'qux';
        });

        Event::assertDispatchedTimes(ConvoyUpdated::class, 1);
    }

    /** @test */
    public function convoy_update_events_triggered_once_if_first_trigger_type_is_triggered()
    {
        Event::fake();

        $convoy = $this->convoyGenerator()
            ->withId('qux')
            ->withManifest([
                'memberOne',
                'memberTwo',
                'memberThree',
                'memberFour',
                'memberFive',
            ])
            ->withConfig([
                'notify' => [
                    'jobs' => 2,
                    'percentage' => 30,
                ],
            ])
            ->withTotalComplete(2)
            ->create();

        (new Events)->fire($convoy);

        Event::assertDispatched(ConvoyUpdated::class, function ($event) {
            return $event->convoy->id === 'qux';
        });

        Event::assertDispatchedTimes(ConvoyUpdated::class, 1);
    }

    /** @test */
    public function convoy_update_events_triggered_once_if_last_trigger_type_is_triggered()
    {
        Event::fake();

        $convoy = $this->convoyGenerator()
            ->withId('qux')
            ->withManifest([
                'memberOne',
                'memberTwo',
                'memberThree',
                'memberFour',
                'memberFive',
            ])
            ->withConfig([
                'notify' => [
                    'jobs' => 3,
                    'percentage' => 20,
                ],
            ])
            ->withTotalComplete(2)
            ->create();

        (new Events)->fire($convoy);

        Event::assertDispatched(ConvoyUpdated::class, function ($event) {
            return $event->convoy->id === 'qux';
        });

        Event::assertDispatchedTimes(ConvoyUpdated::class, 1);
    }

    /** @test */
    public function custom_convoy_updated_event_fired_if_triggered()
    {
        Event::fake();

        $convoy = $this->convoyGenerator()
            ->withId('qux')
            ->withManifest([
                'memberOne',
                'memberTwo',
                'memberThree',
                'memberFour',
                'memberFive',
            ])
            ->withConfig([
                'notify' => [
                    'jobs' => 2,
                ],
                'events' => [
                    'updated' => FakeConvoyEvent::class,
                ],
            ])
            ->withTotalComplete(2)
            ->create();

        (new Events)->fire($convoy);

        Event::assertDispatched(FakeConvoyEvent::class, function ($event) {
            return $event->convoy->id === 'qux';
        });
    }
}
