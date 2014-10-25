<?php
namespace Phygments;

use \Phygments\Python\Exception;

class Styles
{
	// Maps style names to classname.
	public static $STYLE_MAP = [
		'default'=>  'Standard',
		'standard'=> 'Standard',
// 		'emacs'=>    'Emacs',
// 		'friendly'=> 'Friendly',
// 		'colorful'=> 'Colorful',
// 		'autumn'=>   'Autumn',
// 		'murphy'=>   'Murphy',
// 		'manni'=>    'Manni',
 		'monokai'=>  'Monokai',
// 		'perldoc'=>  'Perldoc',
// 		'pastie'=>   'Pastie',
// 		'borland'=>  'Borland',
// 		'trac'=>     'Trac',
// 		'native'=>   'Native',
// 		'fruity'=>   'Fruity',
// 		'bw'=>       'BlackWhite',
// 		'vim'=>      'Vim',
// 		'vs'=>       'VisualStudio',
// 		'tango'=>    'Tango',
// 		'rrt'=>      'Rrt',
	];

	public static function get_style_by_name($name)
	{
		if(array_key_exists($name, self::$STYLE_MAP)) {
			$cls = '\\Phygments\\Styles\\' . self::$STYLE_MAP[$name];
		} else {
			// perhaps it got dropped into our styles package
			$cls = '\\Phygments\\Styles\\'. $name;
		}

		if(class_exists($cls)) {
			return new $cls;
		} else {
			Exception::raise('ClassNotFound', sprintf('Could not find style class %s.', $cls));
		}
	}

	/**
	 * Return an generator for all styles by name
	 */
	public static function get_all_styles()
	{
		foreach(array_keys(self::$STYLE_MAP) as $name) {
			yield $name;
		}
	}
}