<?php
namespace Phygments\Filters;
use \Phygments\Python\Exception;

abstract class AbstractFilter
{
	public function __construct($options)
	{
		$this->options = $options;
	}
	
	public function filter($lexer, $stream)
	{
		Exception::raise('NotImplementedError');
	}
	
	protected function _replace_special()
	{
		//@todo
	}
		
}