<?php

namespace AdditionApps\Convoy\Tests\Stubs;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class FakeUser extends Model
{
	use Notifiable;

	protected $guarded = [];

}
