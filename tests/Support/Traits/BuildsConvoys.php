<?php

namespace AdditionApps\Convoy\Tests\Support\Traits;

use AdditionApps\Convoy\Tests\Support\Generators\ConvoyGenerator;

trait BuildsConvoys
{
    public function convoyGenerator()
    {
        return new ConvoyGenerator();
    }
}
