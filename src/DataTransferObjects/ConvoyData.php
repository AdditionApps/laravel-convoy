<?php

namespace AdditionApps\Convoy\DataTransferObjects;

use Spatie\DataTransferObject\DataTransferObject;

class ConvoyData extends DataTransferObject
{
    /** @var string */
    public $id;

    /** @var \Illuminate\Support\Collection */
    public $manifest;

    /** @var array */
    public $config;

    /** @var int */
    public $total;

    /** @var int */
    public $totalProcessed;

    /** @var float */
    public $percentProcessed;

    /** @var int */
    public $totalCompleted;

    /** @var int */
    public $totalFailed;

    /** @var \Illuminate\Support\Carbon */
    public $startedAt;

    public static function from(array $record): self
    {
        $totalProcessed = $record['total_completed'] + $record['total_failed'];
        $percentProgress = ($record['total']) ? $totalProcessed / $record['total'] : 0;

        return new self([
            'id' => (string) $record['id'],
            'manifest' => collect($record['manifest']),
            'config' => (array) $record['config'],
            'total' => (int) $record['total'],
            'totalProcessed' => (int) $totalProcessed,
            'percentProcessed' => (float) round($percentProgress, 2),
            'totalCompleted' => (int) $record['total_completed'],
            'totalFailed' => (int) $record['total_failed'],
            'startedAt' => $record['started_at'],
        ]);
    }
}
