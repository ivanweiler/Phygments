<?php
namespace Phygments\Python;

class Re
{
	/*
	protected static $regex_modifiers = array(
		'PCRE_CASELESS' => 'i',
		'PCRE_MULTILINE' => 'm',
		'PCRE_DOTALL' => 's',
		'PCRE_EXTENDED' => 'x',
		'PREG_REPLACE_EVAL' => 'e',
		'PCRE_ANCHORED' => 'A',
		'PCRE_DOLLAR_ENDONLY' => 'D',
		'S' => 'S',
		'PCRE_UNGREEDY' => 'U',
		'PCRE_EXTRA' => 'X',
		'PCRE8' => 'u'
	);
	*/
	
	const IGNORECASE = 'i';
	const MULTILINE = 'm';
	const DOTALL = 's';
	
	//emulates pythons re.match()
	//@todo: $ctx->end not suported
	public static function match($pattern, $string, $pos=0, $end=null)
	{
		//inject \G at the beggining
		$delimiter = $pattern[0];
		$pattern = substr_replace($pattern, "$delimiter\G", 0, 1);
		
		//it's either this or matches offset + len can't be greater than $end
		if($end) {
			$string = substr($string, 0, $end);
		}

		$matches = array();
		$m = preg_match($pattern, $string, $matches, PREG_OFFSET_CAPTURE, $pos);
		
		if($m && $matches[0][1]==$pos) {
			return new Re\MatchObject($matches, $pos);
		} else {
			return false;
		}
	}
	
	//@todo
	//public static function search() {}
	
}
