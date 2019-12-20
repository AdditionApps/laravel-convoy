<?php

namespace AdditionApps\Convoy\Tests\Unit\Support\NotificationTriggers;

use AdditionApps\Convoy\DataTransferObjects\ConvoyData;
use AdditionApps\Convoy\Support\NotificationTriggers\JobsNotificationTrigger;
use AdditionApps\Convoy\Tests\TestCase;
use Illuminate\Support\Carbon;

class JobNotificationTriggerTest extends TestCase
{
    /** @test */
    public function returns_true_when_number_of_jobs_processed_matches_trigger_value()
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

        $notification = new JobsNotificationTrigger($convoy, $triggerValue = 3);

        $this->assertTrue($notification->isTriggered());
    }

    /** @test */
    public function returns_false_when_number_of_jobs_processed_matches_trigger_value()
    {
        $convoy = ConvoyData::from([
            'id' => 'foo',
            'manifest' => [],
            'config' => [],
            'total' => 10,
            'total_completed' => 3,
            'total_failed' => 1,
            'started_at' => Carbon::now(),
        ]);

        $notification = new JobsNotificationTrigger($convoy, $triggerValue = 3);

        $this->assertFalse($notification->isTriggered());
    }
}
