<?php
namespace Phygments\Python;

class Exception
{
	//yes, I know there is assert() in php :)
	public static function assert($assertion, $description=null)
	{
		if($assertion===false) {
			throw new Exception\AssertionError($description);
			return false;
		}
	
		return true;
	}
	
	public static function raise($exception)
	{
		if(is_string($exception)) {
			$exception = "Exception\\$exception";
			$exception = new $exception(func_get_arg(1));
		}
	
		throw $exception;
	}	
}

/*
namespace Phygments\Python\Exception;
class AssertionError extends \Exception {}
class ValueError extends \Exception {}
*/
