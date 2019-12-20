<?php

namespace AdditionApps\Convoy\Contracts;

interface ManifestContract
{
    public function make(ConvoyContract $convoy, callable $callback): array;
}