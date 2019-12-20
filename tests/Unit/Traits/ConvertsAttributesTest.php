<?php

namespace AdditionApps\Convoy\Tests\Unit\Traits;

use AdditionApps\Convoy\Tests\TestCase;
use AdditionApps\Convoy\Traits\ConvertsAttributes;
use Illuminate\Support\Carbon;

class ConvertsAttributesTest extends TestCase
{
    /** @var \AdditionApps\Convoy\Tests\Unit\Traits\TestConvertsAttributesTest */
    protected $testClass;

    public function setUp(): void
    {
        parent::setUp();

        $this->testClass = new TestConvertsAttributesTest;
    }

    /** @test */
    public function uncast_attributes_are_returned_unchanged_when_preparing_for_save()
    {
        $result = $this->testClass->prepareAttributes([
            'id' => 123,
        ]);

        $this->assertEquals([
            'id' => 123,
        ], $result);
    }

    /** @test */
    public function uncast_attributes_are_returned_unchanged_when_casting_for_use()
    {
        $result = $this->testClass->castAttributes([
            'id' => 123,
        ]);

        $this->assertEquals([
            'id' => 123,
        ], $result);
    }

    /** @test */
    public function json_cast_attributes_are_returned_as_json_when_preparing_for_save()
    {
        $result = $this->testClass->prepareAttributes([
            'manifest' => ['foo', 'bar'],
        ]);

        $this->assertEquals([
            'manifest' => json_encode(['foo', 'bar']),
        ], $result);
    }

    /** @test */
    public function json_cast_attributes_are_returned_as_json_when_casting_for_use()
    {
        $result = $this->testClass->castAttributes([
            'manifest' => json_encode(['foo', 'bar']),
        ]);

        $this->assertEquals([
            'manifest' => ['foo', 'bar'],
        ], $result);
    }

    /** @test */
    public function date_cast_attributes_are_returned_as_datetime_string_when_preparing_for_save()
    {
        Carbon::setTestNow(Carbon::parse('12/12/2019'));

        $result = $this->testClass->prepareAttributes([
            'started_at' => Carbon::now(),
        ]);

        $this->assertEquals([
            'started_at' => Carbon::now()->toDateTimeString(),
        ], $result);
    }

    /** @test */
    public function date_cast_attributes_are_returned_as_datetime_string_when_casting_for_use()
    {
        Carbon::setTestNow(Carbon::parse('12/12/2019'));

        $result = $this->testClass->castAttributes([
            'started_at' => Carbon::now()->toDateTimeString(),
        ]);

        $this->assertEquals([
            'started_at' => Carbon::now(),
        ], $result);
    }
}

class TestConvertsAttributesTest
{
    public $casts = [
        'manifest' => 'json',
        'started_at' => 'date',
    ];

    use ConvertsAttributes;
}
