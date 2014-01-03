<?php
namespace Phygments\Lexer;

class _Include 
{
	private $_value;
	
	public function __construct($str)
	{
		$this->_value = $str;
	}
	
	public function __toString()
	{
		return $this->_value;
	}
}

class _Inherit 
{
	public function __toString()
	{
		return 'inherit';
	}
}

class _Combined
{
	public function __construct($str)
	{
		$this->_value = $str;
	}
}

class _PseudoMatch
{
	
}


namespace Phygments;

class Lexer
{
	public static function include($str)
	{
		return new _Include($str);
	}
	
	public static function inherit()
	{
		return new _Include($str);
	}
	
	public static function combined($arr)
	{
		return new _Combined($str);
	}
	
	public static function bygroups()
	{
		
	}
	
	public static function using($_other, $kwargs)
	{
		
	}
	
	public static function do_insertions($insertions, $tokens)
	{
		
	}		
	
}