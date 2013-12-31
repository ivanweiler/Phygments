<?php
namespace Phygments;
class Lexers
{
	private $_lexer_cache = {};
	
	private function __construct()
	{
	}
	
	public function getInstance()
	{
		return new self();
	}
	
	function _load_lexers($module_name)
	{
		"""
		Load a lexer (and all others in the module too).
		"""
		/*
		mod = __import__(module_name, None, None, ['__all__'])
		for lexer_name in mod.__all__:
			cls = getattr(mod, lexer_name)
			_lexer_cache[cls.name] = cls
		*/
	}

	//Get a lexer by an alias.
	function get_lexer_by_name(_alias, **options)
	{
		# lookup builtin lexers
		for module_name, name, aliases, _, _ in LEXERS.itervalues():
			if _alias in aliases:
				if name not in _lexer_cache:
					_load_lexers(module_name)
				return _lexer_cache[name](**options)
		# continue with lexers from setuptools entrypoints
		for cls in find_plugin_lexers():
			if _alias in cls.aliases:
				return cls(**options)
		raise ClassNotFound('no lexer for alias %r found' % _alias)	
	}
	
	//Load a lexer (and all others in the module too).
	private function _load_lexers($module_name)
	{
		mod = __import__(module_name, None, None, ['__all__'])
		for lexer_name in mod.__all__:
			cls = getattr(mod, lexer_name)
			_lexer_cache[cls.name] = cls
		
	}
}