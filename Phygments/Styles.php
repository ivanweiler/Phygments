<?php
namespace Phygments;
class Styles
{
	#: Maps style names to 'submodule::classname'.
	public static $STYLE_MAP = [
		'default'=>  'standard::StandardStyle',
		'standard'=> 'standard::StandardStyle',
		'emacs'=>    'emacs::EmacsStyle',
		'friendly'=> 'friendly::FriendlyStyle',
		'colorful'=> 'colorful::ColorfulStyle',
		'autumn'=>   'autumn::AutumnStyle',
		'murphy'=>   'murphy::MurphyStyle',
		'manni'=>    'manni::ManniStyle',
		'monokai'=>  'monokai::MonokaiStyle',
		'perldoc'=>  'perldoc::PerldocStyle',
		'pastie'=>   'pastie::PastieStyle',
		'borland'=>  'borland::BorlandStyle',
		'trac'=>     'trac::TracStyle',
		'native'=>   'native::NativeStyle',
		'fruity'=>   'fruity::FruityStyle',
		'bw'=>       'bw::BlackWhiteStyle',
		'vim'=>      'vim::VimStyle',
		'vs'=>       'vs::VisualStudioStyle',
		'tango'=>    'tango::TangoStyle',
		'rrt'=>      'rrt::RrtStyle',
	];

	public static function get_style_by_name($name)
	{
		return new \Phygments\Styles\Standard();
		
		if(array_key_exists($name, self::$STYLE_MAP)) {
			list($mod, $cls) = explode('::', self::$STYLE_MAP[$name]);
			$builtin = "yes";
		} else {
			/*for found_name, style in find_plugin_styles():
				if name == found_name:
					return style
			*/
			# perhaps it got dropped into our styles package
			$builtin = "";
			$mod = $name;
			$cls = ucwords(strtolower($name)) + "Style";
		}
		
		/*
		if(substr() == 'Style') {
			
		}
		*/
		
		//return $cls; 
		
		/*
		try:
			mod = __import__('pygments.styles.' + mod, None, None, [cls])
		except ImportError:
			raise ClassNotFound("Could not find style module %r" % mod +
							 (builtin and ", though it should be builtin") + ".")
		try:
			return getattr(mod, cls)
		except AttributeError:
			raise ClassNotFound("Could not find style class %r in style module." % cls)
		*/
	}

	public static function get_all_styles()
	{
		/*Return an generator for all styles by name,
		both builtin and plugin.*/
		foreach(array_keys(self::$STYLE_MAP) as $name) {
			yield $name;
		}
		/*for name, _ in Plugin::find_plugin_styles():
			yield name
		*/
	}
	
}