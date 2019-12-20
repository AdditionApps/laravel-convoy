<?php

namespace AdditionApps\Convoy\Tests\Unit\Support;

use AdditionApps\Convoy\Exceptions\ConvoyException;
use AdditionApps\Convoy\Tests\TestCase;

class ConvoyExceptionTest extends TestCase
{
	/** @test */
	public function correct_exception_message_for_incorrect_repo_driver()
	{
		$this->expectException(ConvoyException::class);
		$this->expectExceptionMessage("Convoy repository driver 'foo' was not recognised.  Valid drivers are 'database' and 'redis'");

		throw ConvoyException::incorrectRepositoryDriver('foo');
	}

	/** @test */
	public function correct_exception_message_for_missing_trigger_class()
	{
		$this->expectException(ConvoyException::class);
		$this->expectExceptionMessage("Convoy notification trigger class not found at: 'foo'");

		throw ConvoyException::missingNotificationTriggerClass('foo');
	}
}
