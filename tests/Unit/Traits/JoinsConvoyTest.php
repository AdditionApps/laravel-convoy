<?php

namespace AdditionApps\Convoy\Tests\Unit\Traits;

use AdditionApps\Convoy\Contracts\ConvoyContract;
use AdditionApps\Convoy\Tests\TestCase;
use AdditionApps\Convoy\Traits\JoinsConvoy;

class JoinsConvoyTest extends TestCase
{
    /** @test */
    public function convoy_id_is_set_on_consuming_class()
    {
        $convoy = app(ConvoyContract::class);
        $convoy->id = 'foo';

        $testJob = new TestJoinsConvoyTest();
        $testJob->onConvoy($convoy);

        $this->assertEquals('foo', $testJob->getConvoyId());
    }

    /** @test */
    public function member_id_not_set_if_no_members_present_on_convoy()
    {
        $convoy = app(ConvoyContract::class);

        $testJob = new TestJoinsConvoyTest();
        $testJob->onConvoy($convoy);

        $this->assertEquals(null, $testJob->getConvoyMemberId());
    }

    /** @test */
    public function single_member_id_is_set_and_id_is_removed_from_convoy_members_array()
    {
        $convoy = app(ConvoyContract::class);
        $convoy->members = ['foo', 'bar', 'baz'];

        $testJob = new TestJoinsConvoyTest();
        $testJob->onConvoy($convoy);

        $this->assertEquals('foo', $testJob->getConvoyMemberId());
        $this->assertCount(2, $convoy->members);
        $this->assertEquals(['bar', 'baz'], $convoy->members);
    }
}

class TestJoinsConvoyTest
{
    use JoinsConvoy;
}
