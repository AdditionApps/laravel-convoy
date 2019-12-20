<?php

namespace AdditionApps\Convoy\Contracts;

use AdditionApps\Convoy\DataTransferObjects\ConvoyData;

interface ConvoyRepositoryContract
{
    public function find(string $id): ?ConvoyData;

    public function create(string $id, array $manifest, array $config): ConvoyData;

    public function update(string $id, array $attributes): void;

    public function delete(string $id): void;

    public function updateAfterJobCompleted(
        string $convoyId,
        string $convoyMemberId
    ): ?ConvoyData;

    public function updateAfterJobFailed(
        string $convoyId,
        string $convoyMemberId
    ): ?ConvoyData;
}