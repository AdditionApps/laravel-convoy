<?php

namespace AdditionApps\Convoy\Tests\Unit\Support;

use AdditionApps\Convoy\Contracts\ConvoyContract;
use AdditionApps\Convoy\Contracts\ConvoyRepositoryContract;
use AdditionApps\Convoy\Support\Events;
use AdditionApps\Convoy\Tests\Stubs\FakeJob;
use AdditionApps\Convoy\Tests\Stubs\FakeJobWithException;
use AdditionApps\Convoy\Tests\Stubs\FakeNormalJob;
use AdditionApps\Convoy\Tests\Support\Traits\BuildsConvoys;
use AdditionApps\Convoy\Tests\TestCase;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\Events\JobProcessed;
use Mockery as m;

class MonitorTest extends TestCase
{
	use DispatchesJobs, BuildsConvoys;

	protected $repoMock;

	protected $eventsMock;

	public function setUp(): void
	{
		parent::setUp();

		$this->repoMock = m::mock(ConvoyRepositoryContract::class);
		app()->instance(ConvoyRepositoryContract::class, $this->repoMock);

		$this->eventsMock = m::mock(Events::class);
		app()->instance(Events::class, $this->eventsMock);
	}

	/** @test */
	public function job_complete_monitor_returns_early_for_non_convoy_job()
	{
		$this->repoMock->shouldNotReceive('updateAfterJobProcessed');
		$this->eventsMock->shouldNotReceive('fire')->withAnyArgs();

		$this->dispatch(new FakeNormalJob());
	}

	/** @test */
	public function job_complete_monitor_returns_early_for_convoy_job_without_convoy_info()
	{
		$this->repoMock->shouldNotReceive('updateAfterJobProcessed');
		$this->eventsMock->shouldNotReceive('fire')->withAnyArgs();

		$this->dispatch(new FakeJob());
	}

	/** @test */
	public function job_complete_monitor_updates_convoy_if_convoy_info_present_on_job()
	{
		$convoyData = $this->convoyGenerator()
			->withManifest($manifest = [ 'foo', 'bar' ])
			->create();

		$convoy = app(ConvoyContract::class);
		$convoy->id = $convoyData->id;
		$convoy->members = $manifest; // fake a built manifest

		$job = (new FakeJob())->onConvoy($convoy);
		$memberId = $job->getConvoyMemberId();

		$this->repoMock
			->shouldReceive('updateAfterJobCompleted')
			->with($convoyData->id, $memberId)
			->once()
			->andReturn($convoyData);

		$this->eventsMock
			->shouldReceive('fire')
			->with($convoyData)
			->once();

		$this->dispatch($job);
	}

	/** @test */
	public function job_complete_monitor_deletes_convoy_if_all_members_processed()
	{
		$convoyData = $this->convoyGenerator()
			->withManifest($manifest = [ 'foo' ])
			->create();

		$convoy = app(ConvoyContract::class);
		$convoy->id = $convoyData->id;
		$convoy->members = $manifest;

		$job = (new FakeJob())->onConvoy($convoy);
		$memberId = $job->getConvoyMemberId();

		$convoyData->totalCompleted++;
		$convoyData->totalProcessed++;

		new JobProcessed('connection', $job);

		$this->repoMock
			->shouldReceive('updateAfterJobCompleted')
			->with($convoyData->id, $memberId)
			->once()
			->andReturn($convoyData);

		$this->repoMock
			->shouldReceive('delete')
			->with($convoyData->id)
			->once();

		$this->eventsMock
			->shouldReceive('fire')
			->with($convoyData)
			->once();

		$this->dispatch($job);
	}

	/** @test */
	public function job_failed_monitor_updates_convoy_if_convoy_info_present_on_job()
	{
		$this->expectException(\Exception::class);

		$convoyData = $this->convoyGenerator()
			->withManifest($manifest = [ 'foo', 'bar' ])
			->create();

		$convoy = app(ConvoyContract::class);
		$convoy->id = $convoyData->id;
		$convoy->members = $manifest;

		$job = (new FakeJobWithException())->onConvoy($convoy);
		$memberId = $job->getConvoyMemberId();

		$this->repoMock
			->shouldReceive('updateAfterJobFailed')
			->with($convoyData->id, $memberId)
			->once()
			->andReturn($convoyData);

		$this->eventsMock
			->shouldReceive('fire')
			->with($convoyData)
			->once();

		$this->dispatch($job);
	}

	/** @test */
	public function job_failed_monitor_deletes_convoy_if_all_members_processed()
	{
		$this->expectException(\Exception::class);

		$convoyData = $this->convoyGenerator()
			->withManifest($manifest = [ 'foo' ])
			->create();

		$convoy = app(ConvoyContract::class);
		$convoy->id = $convoyData->id;
		$convoy->members = $manifest;

		$job = (new FakeJobWithException())->onConvoy($convoy);
		$memberId = $job->getConvoyMemberId();

		$convoyData->totalFailed++;
		$convoyData->totalProcessed++;

		$this->repoMock
			->shouldReceive('updateAfterJobFailed')
			->with($convoyData->id, $memberId)
			->once()
			->andReturn($convoyData);

		$this->repoMock
			->shouldReceive('delete')
			->with($convoyData->id)
			->once();

		$this->eventsMock
			->shouldReceive('fire')
			->with($convoyData)
			->once();

		$this->dispatch($job);
	}
}
