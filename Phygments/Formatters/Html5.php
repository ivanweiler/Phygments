<?php
namespace Phygments\Formatters;

use \Phygments\Util;
use \Phygments\Token;
use \Phygments\Python\Helper;

/**
 * Html5 Formatter
 * 
 * Modern version of Html Formatter.
 * Fomat tokens in <span>-s, wrapped in <code><pre>.
 * Mixed code support. No line numbers in html. External css.
 */
class Html5 extends Html
{
	public $name = 'HTML5';
	public $aliases = ['html5'];
	public $filenames = ['*.html'];	
	
	const DOC_HEADER = <<<'CONST'
<!DOCTYPE html>
<html>
<head>
	<title>%(title)s</title>
	<meta charset="%(encoding)s">
	<link rel="stylesheet" type="text/css" href="%(cssfile)s" media="all" />
</head>
<body>
<h1>%(title)s</h1>

CONST;
	
	const DOC_FOOTER = <<<'CONST'
</body>
</html>
CONST;
	
	public function __construct($options=array())
	{
		$options = array_merge($options, array(
			'noclasses' => false,
			//'linenos'	=> 'inline',
			'encoding'	=> 'utf-8',
			'nowrap'	=> false
		));
		
		parent::__construct($options);
		
		$this->cssfile = $this->cssfile ?: 'default.css';
	}
	
	private function _wrap_code_pre($inner)
	{
		yield [0, '<pre' . ($this->cssclass ? sprintf(' class="%s"', $this->cssclass) : '') . '><code>'];
		foreach($inner as $tup) {
			yield $tup;
		}
		yield [0, "</code></pre>\n"];       		
	}
	
	protected function _wrap_full($inner, $outfile)
	{
		yield [0, Helper::string_format(static::DOC_HEADER,
				array(	'title'		=> $this->title,
						'encoding'	=> $this->encoding,
						'cssfile'	=> $this->cssfile
				))];
	
		foreach($inner as $_inner) {
			yield $_inner;
		}
		yield [0, self::DOC_FOOTER];
	}	
	
	public function wrap($source)
	{
		return $this->_wrap_code_pre($source);
	}	
	
	public function __format_unencoded($tokensource, $outfile)
	{
		$source = $this->_format_lines($tokensource);
		
		if($this->hl_lines) {
			$source = $this->_highlight_lines($source);
		}
		
		if(!$this->nowrap) {
			
			
			
			if($this->linenos == 2) { echo 123;
				$source = $this->_wrap_inlinelinenos($source);
			}
			if($this->lineanchors) {
				$source = $this->_wrap_lineanchors($source);
			}
			if($this->linespans) {
				$source = $this->_wrap_linespans($source);
			}
			$source = $this->wrap($source);
			if($this->linenos == 1) {	//default one
				$source = $this->_wrap_tablelinenos($source);
			}
			if($this->full) {
				$source = $this->_wrap_full($source, $outfile);
			}
		}
		
		$handle = fopen($outfile, 'wb');
		foreach($source as $ssource) {
			list($t, $piece) = $ssource;
			fwrite($handle, $piece);
		}
		fclose($handle);
	}	
	
}