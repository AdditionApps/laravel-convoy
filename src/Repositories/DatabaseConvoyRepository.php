<?php

namespace AdditionApps\Convoy\Repositories;

use AdditionApps\Convoy\Contracts\ConvoyRepositoryContract;
use AdditionApps\Convoy\DataTransferObjects\ConvoyData;
use AdditionApps\Convoy\Traits\ConvertsAttributes;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DatabaseConvoyRepository implements ConvoyRepositoryContract
{
	const JOB_COMPLETED = 1;
	const JOB_FAILED = 2;

	use ConvertsAttributes;

	public $casts = [
		'manifest' => 'json',
		'config' => 'json',
		'started_at' => 'date'
	];

	public function find(string $id): ?ConvoyData
	{
		$convoy = DB::connection(config('convoy.database_connection'))
			->table(config('convoy.database_name'))
			->lockForUpdate()
			->where('id', $id)
			->first();

		return ($convoy)
			? ConvoyData::from($this->castAttributes((array)$convoy))
			: null;
	}

	public function create(string $id, array $manifest, array $config): ConvoyData
	{
		$attributes = $this->prepareAttributes([
			'id' => $id,
			'manifest' => $manifest,
			'config' => $config,
			'total' => count($manifest),
			'total_completed' => 0,
			'total_failed' => 0,
			'started_at' => Carbon::now()
		]);

		DB::connection(config('convoy.database_connection'))
			->table(config('convoy.database_name'))
			->insert($attributes);

		return ConvoyData::from($this->castAttributes($attributes));
	}

	public function update(string $id, array $attributes): void
	{
		DB::connection(config('convoy.database_connection'))
			->table(config('convoy.database_name'))
			->where('id', $id)
			->update($this->prepareAttributes($attributes));
	}

	public function delete(string $id): void
	{
		DB::connection(config('convoy.database_connection'))
			->table(config('convoy.database_name'))
			->where('id', $id)
			->delete();
	}

	public function updateAfterJobCompleted(
		string $convoyId, string $convoyMemberId
	): ?ConvoyData
	{
		return $this->updateAfterJobProcessed(
			$convoyId, $convoyMemberId, self::JOB_COMPLETED
		);
	}

	public function updateAfterJobFailed(
		string $convoyId, string $convoyMemberId
	): ?ConvoyData
	{
		return $this->updateAfterJobProcessed(
			$convoyId, $convoyMemberId, self::JOB_FAILED
		);
	}

	protected function updateAfterJobProcessed(
		string $convoyId, $convoyMemberId, int $jobStatus
	): ?ConvoyData
	{
		DB::beginTransaction();

		$convoy = $this->find($convoyId);

		if (is_null($convoy)) {
			DB::rollBack();
			return null;
		}

		$memberId = $this->locatedConvoyMember($convoy, $convoyMemberId);

		if (!$memberId) {
			DB::rollBack();
			return null;
		}

		$attributes = $this->getUpdateAttributes($convoy, $memberId, $jobStatus);

		return $this->attemptUpdate($convoy, $attributes);
	}

	protected function locatedConvoyMember($convoy, $convoyMemberId): ?string
	{
		return Collection::wrap($convoyMemberId)
			->first(function ($memberId) use ($convoy) {
				return $convoy->manifest->contains($memberId);
			});
	}

	protected function getUpdateAttributes(
		ConvoyData $convoy, string $memberId, int $jobStatus
	): array
	{
		$attributes = [
			'manifest' => $this->removeJobFromManifest($convoy->manifest, $memberId),
		];

		switch ($jobStatus) {
			case self::JOB_COMPLETED:
				Arr::set($attributes, 'total_completed', $convoy->totalCompleted + 1);
				break;
			case self::JOB_FAILED:
				Arr::set($attributes, 'total_failed', $convoy->totalFailed + 1);
				break;
		}

		return $attributes;
	}

	protected function removeJobFromManifest(Collection $manifest, $convoyMemberId): array
	{
		$index = $manifest->search($convoyMemberId);

		return $manifest->forget($index)->values()->all();
	}

	/**
	 * @throws Exception
	 */
	protected function attemptUpdate(ConvoyData $convoy, array $payload): ?ConvoyData
	{
		try {
			$this->update($convoy->id, $payload);
			$updatedConvoy = $this->find($convoy->id);
			DB::commit();

			return $updatedConvoy;
		} catch (Exception $e) {
			DB::rollBack();

			throw $e;
		}
	}

}