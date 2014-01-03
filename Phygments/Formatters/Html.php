<?php
namespace Phygments\Formatters;
use \Phygments\Util;

class Html extends AbstractFormatter
{
    public $name = 'HTML';
    public $aliases = ['html'];
    public $filenames = ['*.html', '*.htm'];
	
	public function __construct($options)
	{
		parent::__construct($options);
		$this->title = $this->_decodeifneeded($this->title);
        $this->nowrap = Util::get_bool_opt($options, 'nowrap', false);
        $this->noclasses = Util::get_bool_opt($options, 'noclasses', false);
        $this->classprefix = Util::get_opt($options, 'classprefix', '');
        $this->cssclass = $this->_decodeifneeded(Util::get_opt($options, 'cssclass', 'highlight'));
        $this->cssstyles = $this->_decodeifneeded(Util::get_opt($options, 'cssstyles', ''));
        $this->prestyles = $this->_decodeifneeded(Util::get_opt($options, 'prestyles', ''));
        $this->cssfile = $this->_decodeifneeded(Util::get_opt($options, 'cssfile', ''));
        $this->noclobber_cssfile = Util::get_bool_opt($options, 'noclobber_cssfile', false);
        
        /*@todo: check if ctags are possible through shell exec
         * https://github.com/jeremykendall/phpctagger        
        //$this->tagsfile = $this->_decodeifneeded(options.get('tagsfile', ''));
        //$this->tagurlformat = $this->_decodeifneeded(options.get('tagurlformat', ''));

        if($this->tagsfile) {
            if(!ctags) {
                raise RuntimeError('The "ctags" package must to be installed '
                                   'to be able to use the "tagsfile" feature.')
			}
            $this->._ctags = ctags.CTags($this->tagsfile)
		}
		*/
		
        $linenos = Util::get_opt($options, 'linenos', false);
        if($linenos == 'inline') {
            $this->linenos = 2;
        } elseif(linenos) {
            # compatibility with <= 0.7
            $this->linenos = 1;
		} else {
            $this->linenos = 0;
		}
        $this->linenostart = abs(Util::get_int_opt($options, 'linenostart', 1));
        $this->linenostep = abs(Util::get_int_opt($options, 'linenostep', 1));
        $this->linenospecial = abs(Util::get_int_opt($options, 'linenospecial', 0));
        $this->nobackground = Util::get_bool_opt($options, 'nobackground', false);
        $this->lineseparator = options.get('lineseparator', '\n');
        $this->lineanchors = Util::get_opt($options, 'lineanchors', '');
        $this->linespans = Util::get_opt($options, 'linespans', '');
        $this->anchorlinenos = Util::get_opt($options, 'anchorlinenos', false);
        $this->hl_lines = [];
		
		foreach(Util::get_list_opt($options, 'hl_lines', []) as $lineno) {
			$this->hl_lines[] = (int)$lineno;
		}

        $this->_create_stylesheet();
	}
	
	
	private function _get_css_class($ttype)
	{
        /*Return the css class of this token type prefixed with
        the classprefix option.*/
        $ttypeclass = _get_ttype_class($ttype);
        if($ttypeclass) {
            return $this->classprefix . $ttypeclass;
		}
        return '';
	}
	
	private function _create_stylesheet()
	{
        //$t2c = &$this->ttype2class = {Token: ''};
        //$c2s = &$this->class2style = {};
		
		/*
        for ttype, ndef in $this->style:
            name = $this->_get_css_class(ttype)
            style = ''
            if ndef['color']:
                style += 'color: #%s; ' % ndef['color']
            if ndef['bold']:
                style += 'font-weight: bold; '
            if ndef['italic']:
                style += 'font-style: italic; '
            if ndef['underline']:
                style += 'text-decoration: underline; '
            if ndef['bgcolor']:
                style += 'background-color: #%s; ' % ndef['bgcolor']
            if ndef['border']:
                style += 'border: 1px solid #%s; ' % ndef['border']
            if style:
                t2c[ttype] = name
                # save len(ttype) to enable ordering the styles by
                # hierarchy (necessary for CSS cascading rules!)
                c2s[name] = (style[:-2], ttype, len(ttype))
		*/
	}
	
    public function get_style_defs()
	{
		
	}
	
    private function _decodeifneeded($value)
	{
		if($this->encoding) {
			return iconv($this->encoding, "UTF-8", $value);
		}
        return $value;
	}
	
	private function _wrap_full($inner, $outfile)
	{
		
	}
	
	private function _wrap_tablelinenos($inner)
	{
	
	}
	
	private function _wrap_inlinelinenos($inner)
	{
	
	}
	
    private function _wrap_lineanchors($inner)
	{
        $s = $this->lineanchors;
        $i = $this->linenostart - 1; # subtract 1 since we have to increment i
									# *before* yielding
        foreach($inner as $x) {
			
		}
		
		/*
		for t, line in inner:
            if t:
                i += 1
                yield 1, '<a name="%s-%d"></a>' % (s, i) + line
            else:
                yield 0, line	
		*/
	}
	
	
	
	private function _format_lines($tokensource)
	{
		/*
        Just format the tokens, without any wrapping tags.
        Yield individual lines.
		*/
        $nocls = $this->noclasses;
        $lsep = $this->lineseparator;
        # for <span style=""> lookup only
        $getcls = $this->ttype2class.get;
        $c2s = $this->class2style;
        $escape_table = _escape_html_table;
        $tagsfile = $this->tagsfile;

        $lspan = '';
        $line = '';
        
        foreach($tokensource as $ttype => $value) {
            if($nocls) {
                $cclass = getcls($ttype);
                while(!$cclass) {
                    $ttype = $ttype->parent;
                    $cclass = getcls($ttype);
                }
                $cspan = cclass and '<span style="%s">' % c2s[cclass][0] or ''
            } else {
                $cls = $this->_get_css_class($ttype);
                cspan = cls and '<span class="%s">' % cls or ''
			}
			$parts = htmlspecialchars($parts, ENT_QUOTES);
			$parts = explode("\n", $parts);
			
			/*
            if tagsfile and ttype in Token.Name:
                filename, linenumber = $this->_lookup_ctag(value)
                if linenumber:
                    base, filename = os.path.split(filename)
                    if base:
                        base += '/'
                    filename, extension = os.path.splitext(filename)
                    url = $this->tagurlformat % {'path': base, 'fname': filename,
                                               'fext': extension}
                    parts[0] = "<a href=\"%s#%s-%d\">%s" % \
                        (url, $this->lineanchors, linenumber, parts[0])
                    parts[-1] = parts[-1] + "</a>"
			*/

            # for all but the last line
            for part in parts[:-1]:
                if line:
                    if lspan != cspan:
                        line += (lspan and '</span>') + cspan + part + \
                                (cspan and '</span>') + lsep
                    else: # both are the same
                        line += part + (lspan and '</span>') + lsep
                    yield 1, line
                    line = ''
                elif part:
                    yield 1, cspan + part + (cspan and '</span>') + lsep
                else:
                    yield 1, lsep
            # for the last line
            if line and parts[-1]:
                if lspan != cspan:
                    line += (lspan and '</span>') + cspan + parts[-1]
                    lspan = cspan
                else:
                    line += parts[-1]
            elif parts[-1]:
                line = cspan + parts[-1]
                lspan = cspan
            # else we neither have to open a new span nor set lspan

        if line:
            yield 1, line + (lspan and '</span>') + lsep	
	}
	
	public function format_unencoded($tokensource, $outfile)
	{
		/*
        The formatting process uses several nested generators; which of
        them are used is determined by the user's options.

        Each generator should take at least one argument, ``inner``,
        and wrap the pieces of text generated by this.

        Always yield 2-tuples: (code, text). If "code" is 1, the text
        is part of the original tokensource being highlighted, if it's
        0, the text is some piece of wrapping. This makes it possible to
        use several different wrappers that process the original source
        linewise, e.g. line number generators.
		*/
        $source = $this->_format_lines($tokensource);
		/*
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
            $source = $this->wrap($source, $outfile);
            if($this->linenos == 1) {
                $source = $this->_wrap_tablelinenos($source);
			}
            if($this->full) {
                $source = $this->_wrap_full($source, $outfile);
			}
		}
		*/
        for t, piece in source:
            outfile.write(piece)	
	}
	
}