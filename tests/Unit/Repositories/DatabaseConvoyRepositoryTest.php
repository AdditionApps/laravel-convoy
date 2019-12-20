<?php

namespace AdditionApps\Convoy\Tests\Unit\Repositories;

use AdditionApps\Convoy\DataTransferObjects\ConvoyData;
use AdditionApps\Convoy\Repositories\DatabaseConvoyRepository;
use AdditionApps\Convoy\Tests\Support\Traits\BuildsConvoys;
use AdditionApps\Convoy\Tests\TestCase;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Mockery as m;

class DatabaseConvoyRepositoryTest extends TestCase
{
    use BuildsConvoys;

    /** @var \AdditionApps\Convoy\Repositories\DatabaseConvoyRepository */
    protected $repository;

    public function setUp(): void
    {
        parent::setUp();

        $this->repository = new DatabaseConvoyRepository();
    }

    /** @test */
    public function convoy_is_retrieved_via_id_and_data_returned()
    {
        $this->convoyGenerator()
            ->withId('foo')
            ->create();

        $convoyData = $this->repository->find('foo');
        $this->assertInstanceOf(ConvoyData::class, $convoyData);
    }

    /** @test */
    public function null_returned_if_convoy_not_found_using_id()
    {
        $convoyData = $this->repository->find('foo');
        $this->assertNull($convoyData);
    }

    /** @test */
    public function convoy_is_created_with_id_and_manifest()
    {
        Carbon::setTestNow('12/12/19 13:20:30');

        $id = Str::uuid()->toString();
        $manifest = ['member_1_uuid', 'member_2_uuid'];
        $config = [];

        $this->repository->create($id, $manifest, $config);

        $this->assertDatabaseHas('convoys', [
            'id' => $id,
            'manifest' => json_encode(['member_1_uuid', 'member_2_uuid']),
            'config' => json_encode([]),
            'total' => 2,
            'total_completed' => 0,
            'total_failed' => 0,
            'started_at' => Carbon::now()->toDateTimeString(),
        ]);
    }

    /** @test */
    public function convoy_is_updated_with_given_attributes()
    {
        Carbon::setTestNow('12/12/19 13:20:30');

        $this->convoyGenerator()
            ->withId('baz')
            ->withManifest(['foo', 'bar'])
            ->create();

        $this->repository->update('baz', [
            'manifest' => ['boo'],
            'total_completed' => 1,
            'started_at' => Carbon::now()
        ]);

        $this->assertDatabaseHas('convoys', [
            'id' => 'baz',
            'manifest' => json_encode(['boo']),
            'total_completed' => 1,
            'started_at' => Carbon::now()->toDateTimeString()
        ]);
    }

    /** @test */
    public function convoy_with_given_id_is_deleted()
    {
        $this->convoyGenerator()
            ->withId('foo')
            ->create();

        $this->assertDatabaseHas('convoys', ['id' => 'foo']);

        $this->repository->delete('foo');

        $this->assertDatabaseMissing('convoys', ['id' => 'foo']);
    }

    /** @test */
    public function update_after_processing_is_rolled_back_if_convoy_not_found()
    {
        $return = $this->repository->updateAfterJobCompleted('foo', 'bar');

        $this->assertNull($return);
    }

    /** @test */
    public function update_after_processing_is_rolled_back_if_convoy_member_not_found()
    {
        $this->convoyGenerator()
            ->withId('foo')
            ->withManifest(['baz'])
            ->create();

        $return = $this->repository->updateAfterJobCompleted('foo', 'bar');

        $this->assertNull($return);
    }

    /** @test */
    public function convoy_update_after_job_completed()
    {
        $this->convoyGenerator()
            ->withId('foo')
            ->withManifest(['bar', 'baz', 'qux'])
            ->withTotalComplete(1)
            ->withTotalFailed(1)
            ->create();

        $return = $this->repository->updateAfterJobCompleted('foo', 'baz');

        $this->assertInstanceOf(ConvoyData::class, $return);
        $this->assertDatabaseHas('convoys', [
            'id' => 'foo',
            'manifest' => json_encode(['bar', 'qux']),
            'total_completed' => 2,
            'total_failed' => 1
        ]);
    }

    /** @test */
    public function convoy_update_after_job_failed()
    {
        $this->convoyGenerator()
            ->withId('foo')
            ->withManifest(['bar', 'baz', 'qux'])
            ->withTotalComplete(1)
            ->withTotalFailed(1)
            ->create();

        $return = $this->repository->updateAfterJobFailed('foo', 'baz');

        $this->assertInstanceOf(ConvoyData::class, $return);
        $this->assertDatabaseHas('convoys', [
            'id' => 'foo',
            'manifest' => json_encode(['bar', 'qux']),
            'total_failed' => 2,
            'total_completed' => 1
        ]);
    }

    /** @test */
    public function exception_during_convoy_update_a_results_in_no_changes()
    {
        $this->expectException(Exception::class);

        $repoMock = m::mock(DatabaseConvoyRepository::class)->makePartial();
        app()->instance(DatabaseConvoyRepository::class, $repoMock);

        $repoMock
            ->shouldReceive('update')
            ->once()
            ->andThrow(Exception::class);

        $this->convoyGenerator()
            ->withId('foo')
            ->withManifest(['bar', 'baz', 'qux'])
            ->withTotalComplete(1)
            ->create();

        $return = app()
            ->make(DatabaseConvoyRepository::class)
            ->updateAfterJobCompleted('foo', 'baz');

        $this->assertInstanceOf(ConvoyData::class, $return);
        $this->assertDatabaseHas('convoys', [
            'id' => 'foo',
            'manifest' => json_encode(['bar', 'baz', 'qux']),
            'total_completed' => 1
        ]);
    }
}
