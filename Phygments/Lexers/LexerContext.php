<?php
namespace Phygments\Lexers;

/**
 * A helper object that holds lexer position data.
 */
class LexerContext
{
	public $text, $pos, $end, $stack;
	
	public function __construct($text, $pos, $stack=null, $end=null)
	{
		$this->text = $text;
		$this->pos = $pos;
		$this->end = $end ?: strlen($text); # end=0 not supported ;-)
		$this->stack = $stack ?: ['root'];
	}
	
	/*
	public function __toString(){
		return sprintf('LexerContext(%s, %s, %s)',
				$this->text, $this->pos, print_r($this->stack, true));	
	}
	*/
}