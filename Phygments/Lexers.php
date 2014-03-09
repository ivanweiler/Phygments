<?php
namespace Phygments;

use Phygments\Python\Exception;
class Lexers
{
	private static $_lexer_cache = [];
	
	public static function __declare()
	{
		if(!self::$LEXERS) {
			require dirname(__FILE__).'/Lexers/_mapping.php';
			self::$LEXERS = $LEXERS;
		}		
	}
	
	private static function _load_lexers()
	{
		/*
		Load a lexer (and all others in the module too).
		*/
		
		//self::__declare();
		
		foreach(self::$LEXERS as $cls => $lexer) {
			list($name, $aliases) = $lexer;
			$cls = "\\Phygments\\Lexers\\$cls";
			self::$_lexer_cache[$name] = $cls;
			//why no aliases here?
		}
	}

	public static function get_all_lexers()
	{
		/*
		Return a generator of tuples in the form ``(name, aliases,
		filenames, mimetypes)`` of all know lexers.
		*/
		foreach($LEXERS as $item) {
			yield $item;
		}

// 		foreach(Plugin::find_plugin_lexers() as $lexer) {
// 			yield lexer.name, lexer.aliases, lexer.filenames, lexer.mimetypes
// 		}
	}

	public static function find_lexer_class($name)
	{
		/*
		Lookup a lexer class by name. Return None if not found.
		*/
		
		self::_load_lexers();
		
		# lookup builtin lexers
		if(isset(self::$_lexer_cache[$name])) {
			return self::$_lexer_cache[$name];
		}

// 		foreach(Plugin::find_plugin_lexers() as $cls) {
// 			if(cls.name == name) {
//				return $cls;
//			}
// 		}		
	}

	public static function get_lexer_by_name($_alias, $options=[])
	{
		/*
		Get a lexer by an alias.
		*/
		
		self::_load_lexers();
		
		# lookup builtin lexers
		foreach(self::$LEXERS as $lexer) {
			list($name, $aliases) = $lexer;
			if(array_search($_alias, $aliases)!==false) {
				return self::$_lexer_cache[$name]($options);
			}
		}

		//foreach(Plugin::find_plugin_lexers() as $cls) {}
		
		Exception::raise('ClassNotFound', sprintf('no lexer for alias %s found', $_alias));
	}

	public static function get_lexer_for_filename($_fn, $code=null, $options=[])
	{
		/*
		Get a lexer for a filename.  If multiple lexers match the filename
		pattern, use ``analyze_text()`` to figure out which one is more
		appropriate.
		*/
		
		self::_load_lexers();
		
		$matches = [];
		$fn = basename($_fn);
		foreach(self::$LEXERS as $lexer) {
			list($name, , $filenames) = $lexer;
			foreach($filenames as $filename) {
				if(fnmatch($filename, $fn)) {
					$matches[] = array(self::$_lexer_cache[$name], $filename);
				}
			}
		}
		
		//foreach(Plugin::find_plugin_lexers() as $cls) {}
		
		$get_rating = function($info) use ($code) {
			list($cls, $filename) = $info;
			# explicit patterns get a bonus
			$bonus = (strpos($filename, '*')===false) ? 0.5 : 0;
			# The class _always_ defines analyse_text because it's included in
			# the Lexer class.  The default implementation returns None which
			# gets turned into 0.0.  Run scripts/detect_missing_analyse_text.py
			# to find lexers which need it overridden.
			if($code) {
				return $cls::analyse_text($code) + $bonus;
			}
			return $cls::$priority + $bonus;			
		};
		
		if($matches) {
			usort($matches, function($a, $b) use ($get_rating) { return $get_rating($a)-$get_rating($b);  });
			return $matches[count($matches)-1][0]($options);
		}
		
		Exception::raise('ClassNotFound', sprintf('no lexer for filename %s found', $_fn));
	}

	public static function get_lexer_for_mimetype($_mime, $options=[])
	{
		/*
		Get a lexer for a mimetype.
		*/
		
		self::_load_lexers();
		
		foreach(self::$LEXERS as $lexer) {
			list($name, , , $mimetypes) = $lexer;
			if(array_search($_mime, $mimetypes)!==false) {
				return self::$_lexer_cache[$name]($options);
			}
		}
		
		//foreach(Plugin::find_plugin_lexers() as $cls) {}
		
		Exception::raise('ClassNotFound', sprintf('no lexer for mimetype %s found', $_mime));
	}


	public static function _iter_lexerclasses()
	{
		/*
		Return an iterator over all lexer classes.
		*/
		
		self::_load_lexers();
		
		foreach(sort(array_keys(self::$LEXERS)) as $key) {
			$name = self::$LEXERS[$key][0];
			yield self::$_lexer_cache[$name];
		}
		
		//foreach(Plugin::find_plugin_lexers() as $lexer) {}
	}

	public static function guess_lexer_for_filename($_fn, $_text, $options=[])
	{
		/*
		Lookup all lexers that handle those filenames primary (``filenames``)
		or secondary (``alias_filenames``). Then run a text analysis for those
		lexers and choose the best result.

		usage::

			>>> guess_lexer_for_filename('hello.html', '<%= @foo %>')
			<pygments.lexers.templates.RhtmlLexer object at 0xb7d2f32c>
			>>> guess_lexer_for_filename('hello.html', '<h1>{{ title|e }}</h1>')
			<pygments.lexers.templates.HtmlDjangoLexer object at 0xb7d2f2ac>
			>>> guess_lexer_for_filename('style.css', 'a { color: <?= $link ?> }')
			<pygments.lexers.templates.CssPhpLexer object at 0xb7ba518c>
		*/

		/*
		$fn = basename($_fn);
		$primary = null;
		$matching_lexers = [];
		foreach(self::_iter_lexerclasses() as $lexer) {
			foreach(lexer.filenames as $filename) {
				if(fnmatch($filename, $fn)) {
					$matching_lexers[] = $lexer;
					$primary = $lexer;
				}
			}
			foreach(lexer.alias_filenames as $filename) {
				if(fnmatch($filename, $fn)) {
					$matching_lexers[] = $lexer;
				}
			}
		}
		
		if(!$matching_lexers) {
			Exception::raise('ClassNotFound', sprintf('no lexer for filename %s found', $fn));
		}
		if(count($matching_lexers) == 1) {
			return array_pop($matching_lexers)($options);
		}
		$result = [];
		foreach($matching_lexers as $lexer) {
			$rv = $lexer::analyse_text($_text);
			if($rv == 1.0) {
				return new $lexer($options);
			}
			$result[] = [$rv, $lexer];
		}
		
		$result.sort() // sort by [0] then [1]
		if(!$result[count($result)-1][0] && !is_null($primary)) {
			return new $primary($options);
		}
		return $result[count($result)-1][1]($options);
		*/
	}

	public static function guess_lexer($_text, $options=[])
	{
		/*
		Guess a lexer by strong distinctions in the text (eg, shebang).
		*/
		$best_lexer = [0.0, null];
		foreach(self::_iter_lexerclasses() as $lexer) {
			$rv = $lexer::analyse_text($_text);
			if($rv == 1.0) {	//@todo: float eq?
				return new $lexer($options);
			}
			if($rv > $best_lexer[0]) {
				$best_lexer = [$rv, $lexer];
			}
		}
		if(!$best_lexer[0] || is_null($best_lexer[1])) {
			Exception::raise('ClassNotFound', 'no lexer matching the text found');
		}
		return new $best_lexer[1]($options);
	}
	
}

Lexers::__declare();
