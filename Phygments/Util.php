<?php
namespace Phygments;

class Util
{
	const split_path_re = '[/\\\\ ]';
	const doctype_lookup_re = '\'\'(?smx)
    (<\\?.*?\\?>)?\\s*
    <!DOCTYPE\\s+(
     [a-zA-Z_][a-zA-Z0-9]*\\s+
     [a-zA-Z_][a-zA-Z0-9]*\\s+
     "[^"]*")
     [^>]*>
\'\'';
	const tag_re = '<(.+?)(\\s.*?)?>.*?</.+?>(?uism)';
	
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
			//OptionError
			throw new \Exception(sprintf(
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
			throw new \Exception(sprintf(
				'Invalid type for option %s; use 1/0, yes/no, true/false, on/off',
				$optname
			));			
		} elseif(in_array(strtolower($string), array('1', 'yes', 'true', 'on'))) {
			return true;
		} elseif(in_array(strtolower($string), array('0', 'no', 'false', 'off'))) {
			return false;
		} else {
			//OptionError
			throw new \Exception(sprintf(
					'Invalid type for option %s; use 1/0, yes/no, true/false, on/off',
					$optname
			));			
		}
	}

	public static function get_int_opt($options, $optname, $default=null)
	{
		$string = self::get_opt($options, $optname, $default);
		//integer check => //OptionError
		return (int)$string;
	}
	
	public static function get_list_opt($options, $optname, $default=null)
	{
		$val = self::get_opt($options, $optname, $default);
		
		if(is_string($val)) {
			return explode(' ', $val); //why 1 2 3?
		} elseif(is_array($val)) {
			return $val;
		} else {
			throw new \Exception(sprintf(
				'Invalid type for option %s; you must give a list value',
				$optname
			));			
		}
	}
	
	public static function docstring_headline($obj)
	{
		//@todo: finish, test
		$r = new ReflectionClass($obj);
		$doc = $r->getDocComment();
		preg_match_all('#@(.*?)\n#s', $doc, $annotations);
		return $annotations[1];		
	}
	
	/**
	 * Check if the given regular expression matches the last part of the
	 * shebang if one exists.
	 * 
	 * >>> shebang_matches('#!/usr/bin/env python', 'python(2\.\d)?')
	 * True
	 * >>> shebang_matches('#!/usr/bin/python2.4', 'python(2\.\d)?')
	 * True
	 * >>> shebang_matches('#!/usr/bin/python-ruby', 'python(2\.\d)?')
	 * False
	 * >>> shebang_matches('#!/usr/bin/python/ruby', 'python(2\.\d)?')
	 * False
	 * >>> shebang_matches('#!/usr/bin/startsomethingwith python', 'python(2\.\d)?')
	 * True
	 * 
	 * It also checks for common windows executable file extensions::
	 * >>> shebang_matches('#!C:\\Python2.4\\Python.exe', r'python(2\.\d)?')
	 * True
	 * 
	 * Parameters (``'-f'`` or ``'--foo'`` are ignored so ``'perl'`` does
	 * the same as ``'perl -e'``)
	 * 
	 * Note that this method automatically searches the whole string (eg:
	 * the regular expression is wrapped in ``'^$'``)
	 */
	public static function shebang_matches($text, $regex)
	{
		$index = strpos($text, "\n");
		if($index===0 || $index>0) {
			$first_line = strtolower(substr($text, $index));
		} else {
			$first_line = strtolower($text);
		}
		if(substr($first_line, 0, 2)=='#!') {
			$matches = array();
			foreach(preg_split("#".split_path_re."#", trim(substr($first_line, 2))) as $x) {
				if($x && substr($x, 0, 1)!='-') {
					$matches[] = $x;
				}
			}
			
			if($matches) {
				$found = end($matches);
			} else {
				return false;
			}

			//escape # in $regex
			if(preg_match("#^$regex(\.(exe|cmd|bat|bin))?$#i")) {
				return true;
			}

		}
		return false;
	}
	
	/**
	 * Check if the doctype matches a regular expression (if present).
	 * Note that this method only checks the first part of a DOCTYPE.
	 * eg: 'html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"'
	 */
	public static function doctype_matches($text, $regex)
	{
	    $matches = array();
	    if(!preg_match("#\G".doctype_lookup_re."#", $text, $matches)) {
	    	return false;
	    }

	    $doctype = $matches[2];
	    //escape # in $regex
	    return (bool)preg_match("#\G$regex#", trim($doctype));
	}	
	
	/**
	 * Check if the file looks like it has a html doctype.
	 */
	public static function html_doctype_matches($text)
	{
    	return self::doctype_matches($text, 'html\\s+PUBLIC\\s+"-//W3C//DTD X?HTML.*');	
	}
	
	private static $_looks_like_xml_cache = [];
	
	/**
	 * Check if a doctype exists or if we have some tags.
	 */
	public static function looks_like_xml($text)
	{
	    $key = hash('md5', $text);
	    
	    if(isset(self::$_looks_like_xml_cache[$key])) {
	    	return self::$_looks_like_xml_cache[$key];
	    } else {
	    	if(preg_match("#\G".doctype_lookup_re."#", $text)) {
	    		return true;
	    	}
	    	$rv = (bool)preg_match("#".tag_re."#", substr($text, 0 , 1000));  	
	    	self::$_looks_like_xml_cache[$key] = $rv;
	    	return $rv;
	    }
	}

	
	// ... @todo rest of def-s
	
}