<?php

namespace AdditionApps\Convoy\Exceptions;

use Exception;

class ConvoyException extends Exception
{
    public static function incorrectRepositoryDriver($driver): self
    {
        return new static("Convoy repository driver '$driver' was not recognised.  Valid drivers are 'database' and 'redis'");
    }

    public static function missingNotificationTriggerClass($class): self
    {
        return new static("Convoy notification trigger class not found at: '$class'");
    }
}
