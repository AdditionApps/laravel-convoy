<?php

namespace AdditionApps\Convoy\Traits;

use AdditionApps\Convoy\Support\PendingConvoyDispatch;
use Illuminate\Foundation\Bus\Dispatchable;

trait DispatchesToConvoy
{

	use Dispatchable;

	public static function dispatch(): PendingConvoyDispatch
	{
		return new PendingConvoyDispatch(new static(...func_get_args()));
	}

}