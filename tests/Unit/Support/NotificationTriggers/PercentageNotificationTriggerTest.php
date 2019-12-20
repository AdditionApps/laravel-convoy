<?php

namespace AdditionApps\Convoy\Tests\Unit\Support\NotificationTriggers;

use AdditionApps\Convoy\DataTransferObjects\ConvoyData;
use AdditionApps\Convoy\Support\NotificationTriggers\PercentageNotificationTrigger;
use AdditionApps\Convoy\Tests\TestCase;
use Illuminate\Support\Carbon;

class PercentageNotificationTriggerTest extends TestCase
{
    /** @test */
    public function returns_false_when_of_percent_of_processed_jobs_under_trigger_value()
    {
        $convoy = ConvoyData::from([
            'id' => 'foo',
            'manifest' => [],
            'config' => [],
            'total' => 10,
            'total_completed' => 2,
            'total_failed' => 0,
            'started_at' => Carbon::now(),
        ]);

        $notification = new PercentageNotificationTrigger($convoy, $triggerValue = 25);

        $this->assertFalse($notification->isTriggered());
    }

    /** @test */
    public function returns_false_when_of_percent_of_processed_jobs_approaching_next_trigger_point()
    {
        $convoy = ConvoyData::from([
            'id' => 'foo',
            'manifest' => [],
            'config' => [],
            'total' => 10,
            'total_completed' => 4,
            'total_failed' => 0,
            'started_at' => Carbon::now(),
        ]);

        $notification = new PercentageNotificationTrigger($convoy, $triggerValue = 25);

        $this->assertFalse($notification->isTriggered());
    }

    /** @test */
    public function returns_true_when_of_percent_of_processed_jobs_exactly_at_trigger_point()
    {
        $convoy = ConvoyData::from([
            'id' => 'foo',
            'manifest' => [],
            'config' => [],
            'total' => 100,
            'total_completed' => 25,
            'total_failed' => 0,
            'started_at' => Carbon::now(),
        ]);

        $notification = new PercentageNotificationTrigger($convoy, $triggerValue = 25);

        $this->assertTrue($notification->isTriggered());
    }

    /** @test */
    public function returns_true_when_of_percent_of_processed_jobs_has_just_passed_trigger_point()
    {
        $convoy = ConvoyData::from([
            'id' => 'foo',
            'manifest' => [],
            'config' => [],
            'total' => 10,
            'total_completed' => 3,
            'total_failed' => 0,
            'started_at' => Carbon::now(),
        ]);

        $notification = new PercentageNotificationTrigger($convoy, $triggerValue = 25);

        $this->assertTrue($notification->isTriggered());
    }

    /** @test */
    public function correct_return_value_returned_for_non_idealised_values()
    {
        $convoy = ConvoyData::from([
            'id' => 'foo',
            'manifest' => [],
            'config' => [],
            'total' => 213,
            'total_completed' => 41,
            'total_failed' => 0,
            'started_at' => Carbon::now(),
        ]);

        // Each job in convoy is 0.46948356807%
        // Multiplied by number processed (41) = = 19.2488262911%
        // Modulus 19.2488262911 % 17 = 2
        // Expected value = false
        $notification = new PercentageNotificationTrigger($convoy, $triggerValue = 17);

        $this->assertFalse($notification->isTriggered());

        $convoy->totalCompleted = 37;

        // Each job in convoy is 0.46948356807%
        // Multiplied by number processed (37) = 17.3708920186%
        // Modulus 17.3708920186 % 17 = 0
        // Expected value = true

        $notification = new PercentageNotificationTrigger($convoy, $triggerValue = 17);

        $this->assertFalse($notification->isTriggered());
    }
}
