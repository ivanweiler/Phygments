<?php
namespace Phygments\Lexers;

use \Phygments\Lexers\Php as PhpLexer;
use \Phygments\Util;

/**
 * Subclass of `PhpLexer` that highlights unhandled data with the `HtmlLexer`.
 * Nested Javascript and CSS is highlighted too.
 */
class HtmlPhp extends Delegating
{
	public $name = 'HTML+PHP';
	public $aliases = ['html+php'];
	public $filenames = ['*.phtml'];
	public $alias_filenames = ['*.php', '*.html', '*.htm', '*.xhtml', '*.php[345]'];
	public $mimetypes = ['application/x-php',
				'application/x-httpd-php', 'application/x-httpd-php3',
				'application/x-httpd-php4', 'application/x-httpd-php5'];
	
	public function __construct($options = array())
	{
		parent::__construct('\Phygments\Lexers\Html', '\Phygments\Lexers\Php', 'Other', $options);
	}

	public static function analyse_text($text)
	{
		$rv = PhpLexer::analyse_text($text) - 0.01;
		if(Util::html_doctype_matches($text)) {
			$rv += 0.5;
			return $rv;
		}
	}
}