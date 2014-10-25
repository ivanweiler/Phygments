<?php
namespace Phygments\Formatters;

use \Phygments\Util;
use \Phygments\Styles;

/**
 * Formatter Abstract
 * 
 * Options accepted:
 * 
 * ``style``
 * The style to use, can be a string or a Style subclass
 * (default: "default"). Not used by e.g. the
 * TerminalFormatter.
 * 
 * ``full``
 * Tells the formatter to output a "full" document, i.e.
 * a complete self-contained document. This doesn't have
 * any effect for some formatters (default: false).
 * 
 * ``title``
 * If ``full`` is true, the title that should be used to
 * caption the document (default: '').
 * 
 * ``encoding``
 * If given, must be an encoding name. This will be used to
 * convert the Unicode token strings to byte strings in the
 * output. If it is "" or None, Unicode strings will be written
 * to the output file, which most file-like objects do not
 * support (default: None).
 * 
 * ``outencoding``
 * Overrides ``encoding`` if given.
 */
abstract class AbstractFormatter
{
    // Name of the formatter
    public $name = null;

    // Shortcuts for the formatter
    public $aliases = [];

    // fn match rules
    public $filenames = [];

    // If True, this formatter outputs Unicode strings when no encoding option is given.
    public $unicodeoutput = true;

    public function __construct($options=array())
	{
        $this->style = $this->_lookup_style(Util::get_opt($options, 'style', 'default'));
        $this->full  = Util::get_bool_opt($options, 'full', false);
        $this->title =  Util::get_opt($options, 'title', '');
        $this->encoding =  Util::get_opt($options, 'outencoding', Util::get_opt($options, 'encoding'));
        $this->options = $options;
	}

	/**
	 * Return the style definitions for the current style as a string.
	 * 
	 * ``arg`` is an additional argument whose meaning depends on the
	 * formatter used. Note that ``arg`` can also be a list or tuple
	 * for some formatters like the html formatter.
	 */
    public function get_style_defs($arg='')
	{
        return '';
	}
	
	/**
	 * Format ``tokensource``, an iterable of ``(tokentype, tokenstring)``
	 * tuples and write it into ``outfile``.
	 */
    public function format($tokensource, $outfile)
	{
//         if self.encoding:
//             # wrap the outfile in a StreamWriter
//             outfile = codecs.lookup(self.encoding)[3](outfile)
//         return self.format_unencoded(tokensource, outfile)
		
		return $this->format_unencoded($tokensource, $outfile);
	}
	
	public function format_unencoded($tokensource, $outfile) 
	{
		return;
	}
	
	private function _lookup_style($style)
	{
		if(is_string($style)) {
			return Styles::get_style_by_name($style);
		}
		return $style;
	}
	
}