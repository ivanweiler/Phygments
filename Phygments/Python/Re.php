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
	
	//emulates python re.match()
	// match(string[, pos[, endpos]])
	public static function match($pattern, $string, $flags='', $pos=0)
	{
		$flags = is_array($flags) ? implode('', $flags) : $flags;
		//$regex = addcslashes($regex, '#');
		$pattern = "#$pattern#$flags";
				
		$matches = array();
		$m = preg_match($pattern, $string, $matches, PREG_OFFSET_CAPTURE, $pos);
		
		return new Re\MatchObject($matches, $pos);
	}
	
	//@todo
	//public static function search() {}
	
}
