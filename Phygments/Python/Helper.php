<?php
namespace Phygments\Python;

class Helper
{
	public static function string_format($string, array $args = array())
	{
		$search = array();
		$i = 1;
		foreach($args as $key => $value) {
			$search[] = '%('.$key.')';
			$replace[] = '%'.$i.'$';
			$i++;
		}

		$format = str_replace($search, $replace, $string);
		$string = vsprintf($format, array_values($args));
		
		return $string;
	}
	
	// http://stackoverflow.com/questions/28165/does-php-have-an-equivalent-to-this-type-of-python-string-substitution
	//http://stackoverflow.com/questions/7683133/does-php-have-a-feature-like-pythons-template-strings
	
}