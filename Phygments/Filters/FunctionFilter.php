<?php
namespace Phygments\Filters;
class FunctionFilter extends AbstractFilter
{
	private $function;
	
	public function filter($lexer, $stream)
	{
		foreach($this->function($lexer, $stream, $this->options) as $result) {
			//yield ttype, value
		}
	}	
	
}