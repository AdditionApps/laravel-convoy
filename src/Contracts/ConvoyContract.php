<?php

namespace AdditionApps\Convoy\Contracts;

use AdditionApps\Convoy\DataTransferObjects\ConvoyData;

interface ConvoyContract
{
    public function setId(string $id): self;

    public function notifyEvery(int $jobs): self;

    public function notifyEveryPercent(int $percentage): self;

    public function onUpdateFire($class): self;

    public function onCompleteFire($class): self;

    public function track(callable $callback): ConvoyData;
}