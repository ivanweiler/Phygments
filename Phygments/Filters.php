<?php
namespace Phygments;
class Filters
{
	public static $FILTERS = [
		'codetagify'	=> 'CodeTagFilter',
		'keywordcase'	=> 'KeywordCaseFilter',
		'highlight'		=> 'NameHighlightFilter',
		'raiseonerror'	=> 'RaiseOnErrorTokenFilter',
		'whitespace'	=> 'VisibleWhitespaceFilter',
		'gobble'		=> 'GobbleFilter',
		'tokenmerge'	=> 'TokenMergeFilter',
	];

	public static function find_filter_class($filtername)
	{
		/*
		Lookup a filter by name. Return None if not found.
		*/
		if(array_key_exists($filtername, self::$FILTERS)) {
			return self::$FILTERS[$filtername]; //instance??
		}
		
		/* @todo: plugins
		for name, cls in find_plugin_filters():
			if name == filtername:
				return cls
		*/
		
		return null;
	}

	public static function get_filter_by_name($filtername, $options=array())
	{
		/*
		Return an instantiated filter. Options are passed to the filter
		initializer if wanted. Raise a ClassNotFound if not found.
		*/
		$cls = self::find_filter_class($filtername);
		if($cls) {
			return new $cls($options);
		} else {
			//raise ClassNotFound('filter %r not found' % filtername)
			
		}
	}

	public static function get_all_filters()
	{
		/*
		Return a generator of all filter names.
		*/
		/*
		for name in FILTERS:
			yield name
		for name, _ in find_plugin_filters():
			yield name
		*/
	}
	
	/* this here or in abstract?
	public static function _replace_special($ttype, $value, $regex, $specialttype,
						 replacefunc=lambda x: x)
	{
		last = 0
		for match in regex.finditer(value):
			start, end = match.start(), match.end()
			if start != last:
				yield ttype, value[last:start]
			yield specialttype, replacefunc(value[start:end])
			last = end
		if last != len(value):
			yield ttype, value[last:]
	}
	*/
	    			
}