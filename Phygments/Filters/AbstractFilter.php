<?php
namespace Phygments\Filters;
abstract class AbstractFilter
{
	
	public function __construct($options)
	{
		$this->options = $options;
	}
	
	public function filter($lexer, $stream)
	{
		//raise NotImplementedError()
		throw new Exception('Not Implemented Error.');
	}
		
}