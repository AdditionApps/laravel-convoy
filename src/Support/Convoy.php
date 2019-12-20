<?php

namespace AdditionApps\Convoy\Support;

use AdditionApps\Convoy\Contracts\ConvoyContract;
use AdditionApps\Convoy\Contracts\ConvoyRepositoryContract;
use AdditionApps\Convoy\Contracts\ManifestContract;
use AdditionApps\Convoy\DataTransferObjects\ConvoyData;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class Convoy implements ConvoyContract
{

	/** @var \AdditionApps\Convoy\Contracts\ManifestContract */
	protected $manifest;

	/** @var \AdditionApps\Convoy\Contracts\ConvoyRepositoryContract */
	protected $convoyRepository;

	/** @var string */
	public $id;

	/** @var array */
	public $members = [];

	/** @var array */
	protected $config = [];

	/** @var int */
	protected $notifyJobsProcessed;

	/** @var int */
	protected $notifyPercentageProcessed;

	/** @var string */
	protected $updatedEventClass;

	/** @var string */
	protected $completedEventClass;

	public function __construct(
		ManifestContract $manifest,
		ConvoyRepositoryContract $convoyRepository
	)
	{
		$this->manifest = $manifest;
		$this->convoyRepository = $convoyRepository;
	}

	public function setId(string $id): ConvoyContract
	{
		$this->id = $id;

		return $this;
	}

	public function notifyEvery(int $jobs): ConvoyContract
	{
		$this->notifyJobsProcessed = $jobs;

		return $this;
	}

	public function notifyEveryPercent(int $percentage): ConvoyContract
	{
		$this->notifyPercentageProcessed = $percentage;

		return $this;
	}

	public function onUpdateFire($class): ConvoyContract
	{
		$this->updatedEventClass = $class;

		return $this;
	}

	public function onCompleteFire($class): ConvoyContract
	{
		$this->completedEventClass = $class;

		return $this;
	}

	public function track(callable $callback): ConvoyData
	{
		if(is_null($this->id)){
			$this->id = Str::uuid()->toString();
		}

		$this->makeConfig();
		$this->members = $this->manifest->make($this, $callback);
		$convoyData = $this->convoyRepository->create(
			$this->id, $this->members, $this->config
		);

		$callback($this);

		return $convoyData;
	}

	protected function makeConfig(): void
	{
		$this->pushToConfig('notify.jobs', $this->notifyJobsProcessed);
		$this->pushToConfig('notify.percentage', $this->notifyPercentageProcessed);
		$this->pushToConfig('events.updated', $this->updatedEventClass);
		$this->pushToConfig('events.completed', $this->completedEventClass);
	}

	protected function pushToConfig($key, $value): void
	{
		if($value){
			Arr::set($this->config, $key, $value);
		}
	}

}