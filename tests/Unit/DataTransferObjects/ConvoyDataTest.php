<?php

namespace AdditionApps\Convoy\Tests\Unit\Support;

use AdditionApps\Convoy\DataTransferObjects\ConvoyData;
use AdditionApps\Convoy\Tests\TestCase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ConvoyDataTest extends TestCase
{
    /** @test */
    public function properties_Set_and_calculated_based_on_cast_record_from_db()
    {
        Carbon::setTestNow(Carbon::parse('12/12/19 15:30:30'));

        $record = [
            'id' => 'foo',
            'manifest' => ['bar', 'baz', 'qux'],
            'config' => [],
            'total' => 3,
            'total_completed' => 1,
            'total_failed' => 0,
            'started_at' => Carbon::now(),
        ];

        $convoyData = ConvoyData::from($record);

        $this->assertEquals('foo', $convoyData->id);
        $this->assertInstanceOf(Collection::class, $convoyData->manifest);
        $this->assertIsArray($convoyData->config);
        $this->assertEmpty($convoyData->config);
        $this->assertEquals(3, $convoyData->total);
        $this->assertEquals(1, $convoyData->totalProcessed);
        $this->assertEquals(0.33, $convoyData->percentProcessed);
        $this->assertEquals(1, $convoyData->totalCompleted);
        $this->assertEquals(0, $convoyData->totalFailed);
        $this->assertInstanceOf(Carbon::class, $convoyData->startedAt);
    }
}
