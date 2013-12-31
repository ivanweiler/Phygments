<?php
namespace Phygments;
class Util
{
	public $split_path_re = '[/\\ ]';
	public $doctype_lookup_re = '\'\'(?smx)
    (<\?.*?\?>)?\s*
    <!DOCTYPE\s+(
     [a-zA-Z_][a-zA-Z0-9]*\s+
     [a-zA-Z_][a-zA-Z0-9]*\s+
     "[^"]*")
     [^>]*>
\'\'';
	public $tag_re = '<(.+?)(\s.*?)?>.*?</.+?>(?uism)';
	
	public static function get_opt($options, $optname, $default=null)
	{
		return (isset($options[$optname])) ? $options[$optname] : $default;		
	}
	
	public static function get_choice_opt($options, $optname, $allowed, 
			$default=null, $normcase=false)
	{
		$string = self::get_opt($options, $optname, $default);
		if($normcase) {
			$string = strtolower($string);
		}
		if($allowed && !in_array($string, $allowed)) {
			/*
			raise OptionError('Value for option %s must be one of %s' %
				(optname, ', '.join(map(str, allowed))))
			*/
			throw new Exception(sprintf(
				'Value for option %s must be one of %s',
				$optname,
				implode(',', $allowed)
			));
		}

		return $string;
	}

	public static function get_bool_opt($options, $optname, $default=null)
	{
		$string = self::get_opt($options, $optname, $default);
		
		if(is_bool($string)) {
			return $string;
		} elseif(is_int($string)) {
			return (bool)$string;
		} elseif(!is_string($string)) {
			//OptionError
			throw new Exception(sprintf(
				'Invalid type for option %s; use 1/0, yes/no, true/false, on/off',
				$optname
			));			
		} elseif(in_array(strtolower($string), array('1', 'yes', 'true', 'on'))) {
			return true;
		} elseif(in_array(strtolower($string), array('0', 'no', 'false', 'off'))) {
			return false;
		} else {
			//OptionError
			throw new Exception(sprintf(
					'Invalid type for option %s; use 1/0, yes/no, true/false, on/off',
					$optname
			));			
		}
	}

	public static function get_int_opt($options, $optname, $default=null)
	{
		$string = self::get_opt($options, $optname, $default);
		return (int)$string;
		/*
		try:
			return int(string)
		except TypeError:
			raise OptionError('Invalid type %r for option %s; you '
							  'must give an integer value' % (
							  string, optname))
		except ValueError:
			raise OptionError('Invalid value %r for option %s; you '
							  'must give an integer value' % (
							  string, optname))
		*/

	}
	
	public static function get_list_opt($options, $optname, $default=null)
	{
		$val = self::get_opt($options, $optname, $default);
		
		if(is_string($val)) {
			return explode(' ', $val); //??
		} elseif(is_array($val)) {
			return $val;
		} else {
			throw new Exception(sprintf(
				'Invalid type for option %s; you must give a list value',
				$optname
			));			
		}

		/*		
		if isinstance(val, basestring):
			return val.split()
		elif isinstance(val, (list, tuple)):
			return list(val)
		else:
			raise OptionError('Invalid type %r for option %s; you '
							  'must give a list value' % (
							  val, optname))
		*/
	}
	
	
	// ... @todo rest of def-s
	
}