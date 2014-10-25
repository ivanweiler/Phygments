<?php
namespace Phygments;

use \Phygments\Python\Exception;

class Formatters
{
	public static $FORMATTERS = [];
	private static $_formatter_alias_cache = [];
	private static $_formatter_filename_cache = [];
	
	public static function __declare()
	{
		if(!self::$FORMATTERS) {
			require dirname(__FILE__).'/Formatters/_mapping.php';
			self::$FORMATTERS = $FORMATTERS;
		}
	}	
	
	public static function _init_formatter_cache()
	{
		if(self::$_formatter_alias_cache) {
			return;
		}
		foreach(self::get_all_formatters() as $cls => $data) {
			$cls = "\\Phygments\\Formatters\\$cls";
			$aliases = $data[1];
			foreach($aliases as $alias) {
				self::$_formatter_alias_cache[$alias] = $cls;
			}
			self::$_formatter_alias_cache["$clsFormatter"] = $cls; //compatibility
			$filenames = $data[2];
			foreach($filenames as $fn) {
				self::$_formatter_filename_cache[] = [$fn, $cls];
			}	
		}
	}

	public static function find_formatter_class($name)
	{
		self::_init_formatter_cache();
		$cls = isset(self::$_formatter_alias_cache[$name]) ? self::$_formatter_alias_cache[$name] : null;
		return $cls;
	}

	public static function get_formatter_by_name($name, $options)
	{
		self::_init_formatter_cache();
		$cls = isset(self::$_formatter_alias_cache[$name]) ? self::$_formatter_alias_cache[$name] : null;
		if(!$cls) {
			Exception::raise('ClassNotFound', sprintf("No formatter found for name %s", $name));
		}
		return new $cls($options);
	}

	public static function get_formatter_for_filename($fn, $options)
	{
		self::_init_formatter_cache();
		$fn = basename($fn);
		foreach(self::$_formatter_filename_cache as $data) {
			list($pattern, $cls) = $data;
			if(fnmatch($pattern, $fn)) {
				return $cls($options);
			}
		}
		Exception::raise('ClassNotFound', sprintf("No formatter found for file name %s", $fn));
	}

	public static function get_all_formatters()
	{
		/*Return a generator for all formatters.*/
		foreach(self::$FORMATTERS as $formatter) {
			yield $formatter;
		}
		
// 		//in the future
// 		foreach(Plugin::find_plugin_formatters() as $formatter) {
// 			yield $formatter;
// 		}

	}
	
}

Formatters::__declare();
