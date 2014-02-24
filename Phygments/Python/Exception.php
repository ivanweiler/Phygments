<?php
namespace Phygments\Python;
use \Phygments\Python\Exception;

class Exception
{
	public static function assert($assertion, $description=null)
	{
		if($assertion===false) {
			throw new Exception\AssertionError($description);
			return false;
		}
	
		return true;
	}
	
	public static function raise($exception, $message=null)
	{
		if(is_string($exception)) {
			$exception = "\\Phygments\\Python\\Exception\\$exception";
			$exception = new $exception($message);
		}
	
		throw $exception;
	}	
}

/*
namespace Phygments\Python\Exception;
class AssertionError extends \Exception {}
class ValueError extends \Exception {}
*/
