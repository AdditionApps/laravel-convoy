<?php

namespace AdditionApps\Convoy\Traits;

use AdditionApps\Convoy\Contracts\ConvoyContract;

trait JoinsConvoy
{
    protected $convoyId = null;
    protected $convoyMemberId = null;

    public function onConvoy(ConvoyContract $convoy): self
    {
        $this->convoyId = $convoy->id;

        if ($this->manifestSetFor($convoy)) {
            $this->convoyMemberId = array_shift($convoy->members);
        }

        return $this;
    }

    private function manifestSetFor(ConvoyContract $convoy): bool
    {
        return count($convoy->members) > 0;
    }

    public function getConvoyId(): ?string
    {
        return $this->convoyId;
    }

    public function getConvoyMemberId(): ?string
    {
        return $this->convoyMemberId;
    }
}
