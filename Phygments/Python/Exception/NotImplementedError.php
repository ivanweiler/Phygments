<?php
namespace Phygments\Python\Exception;

class NotImplementedError extends \Exception 
{
	public function __construct()
	{
		parent::__construct('Not Implemented Error.');
	}	
	
}