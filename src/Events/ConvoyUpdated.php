<?php

namespace AdditionApps\Convoy\Events;

use AdditionApps\Convoy\DataTransferObjects\ConvoyData;

class ConvoyUpdated
{
    /** @var \AdditionApps\Convoy\DataTransferObjects\ConvoyData */
    public $convoy;

    public function __construct(ConvoyData $convoy)
    {
        $this->convoy = $convoy;
    }
}
