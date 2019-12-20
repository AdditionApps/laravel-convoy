<?php

namespace AdditionApps\Convoy\Tests\Support\Generators;

use AdditionApps\Convoy\DataTransferObjects\ConvoyData;
use AdditionApps\Convoy\Traits\ConvertsAttributes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ConvoyGenerator
{
    use ConvertsAttributes;

    public $id;
    public $manifest = [];
    public $config = [];
    public $total;
    public $totalComplete;
    public $totalFailed;
    public $start;
    public $casts = [
        'manifest' => 'json',
        'config' => 'json',
        'started_at' => 'date',
    ];

    public function withId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function withManifest(array $manifest): self
    {
        $this->manifest = $manifest;

        return $this;
    }

    public function withConfig(array $config): self
    {
        $this->config = $config;

        return $this;
    }

    public function withTotal(int $total): self
    {
        $this->total = $total;

        return $this;
    }

    public function withTotalComplete(int $total): self
    {
        $this->totalComplete = $total;

        return $this;
    }

    public function withTotalFailed(int $total): self
    {
        $this->totalFailed = $total;

        return $this;
    }

    public function withStart(Carbon $date): self
    {
        $this->start = $date->toDateTimeString();

        return $this;
    }

    public function create(): ConvoyData
    {
        $attributes = [
            'id' => $this->id ?? Str::uuid()->toString(),
            'manifest' => json_encode($this->manifest),
            'config' => json_encode($this->config),
            'total' => $this->total ?? count($this->manifest),
            'total_completed' => $this->totalComplete ?? 0,
            'total_failed' => $this->totalFailed ?? 0,
            'started_at' => $this->start ?? Carbon::now()->toDateTimeString(),
        ];

        DB::connection(config('convoy.database_connection'))
            ->table(config('convoy.database_name'))
            ->insert($attributes);

        return ConvoyData::from($this->castAttributes($attributes));
    }
}
