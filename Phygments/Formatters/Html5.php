<?php
namespace Phygments\Formatters;
use \Phygments\Util;
use \Phygments\Token;

class Html5 extends Html
{
	/**
	 * Modern version of Html Formatter. 
	 * Fomat tokens in <span>-s, wrapped in <code><pre>.
	 * Mixed code support. No line numbers in html. External css.
	 */
	
	public $name = 'HTML5';
	public $aliases = ['html5'];
	public $filenames = ['*.html'];	
	
	const EXTERNALCSS_TEMPLATE = <<<'CONST'
<link rel="stylesheet" type="text/css" href="%(cssfile)s" media="all" />
CONST;
	
	const DOC_HEADER = <<<'CONST'
<!doctype html>
<html>
<head>
	<title>%(title)s</title>
	<meta http-equiv="content-type" content="text/html; charset=%(encoding)s">
	%(EXTERNALCSS_TEMPLATE)
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
		parent::__construct($options);
		
		//$this->cssfile =  Util::get_opt($options, 'cssfile', '');
		//$this->cssclass = Util::get_opt($options, 'cssclass', 'highlight');
		
		$this->noclasses = false;
		if($this->linenos == 1) {
			$this->linenos = 2;
		}
	}
	
     private function _wrap_code($inner)
	 {
		yield [0, '<code' . ($this->cssclass ? sprintf(' class="%s"', $this->cssclass) : '') . '>'];
		foreach($inner as $tup) {
			yield $tup;
		}
		yield [0, "</code>\n"];       		
	}
	
	public function wrap($source)
	{
		return $this->_wrap_pre($this->_wrap_code($source));
	}	
	
	public function __format_unencoded($tokensource, $outfile)
	{
		$source = $this->_format_lines($tokensource);
		
		if($this->hl_lines) {
			$source = $this->_highlight_lines($source);
		}
		
		if(!$this->nowrap) {
			if($this->linenos == 2) {
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
				//@todo
				//$source = $this->_wrap_full($source, $outfile);
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