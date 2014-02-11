<?php
namespace Phygments\Formatters;
use \Phygments\Util;
use \Phygments\Token;

class Html extends AbstractFormatter
{
    public $name = 'HTML';
    public $aliases = ['html'];
    public $filenames = ['*.html', '*.htm'];
    
    const CSSFILE_TEMPLATE = <<<'CONST'
td.linenos { background-color: #f0f0f0; padding-right: 10px; }
span.lineno { background-color: #f0f0f0; padding: 0 5px 0 5px; }
pre { line-height: 125%%; }
%(styledefs)s
CONST;
    
    const DOC_HEADER = <<<'CONST'
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
    
<html>
<head>
  <title>%(title)s</title>
  <meta http-equiv="content-type" content="text/html; charset=%(encoding)s">
  <style type="text/css">
%(CSSFILE_TEMPLATE)
  </style>
</head>
<body>
<h2>%(title)s</h2>

CONST;
    
    const DOC_HEADER_EXTERNALCSS = <<<'CONST'
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
    
<html>
<head>
  <title>%(title)s</title>
  <meta http-equiv="content-type" content="text/html; charset=%(encoding)s">
  <link rel="stylesheet" href="%(cssfile)s" type="text/css">
</head>
<body>
<h2>%(title)s</h2>
    
CONST;
    
    const DOC_FOOTER = <<<'CONST'
</body>
</html>
CONST;
	
	public function __construct($options=array())
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
        $this->lineseparator = Util::get_opt($options, 'lineseparator', "\n");
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
        /*Return the css class of this token type prefixed with the classprefix option.*/
        $ttypeclass = $this->_get_ttype_class($ttype);
        if($ttypeclass) {
            return $this->classprefix . $ttypeclass;
		}
        return '';
	}
	
	private function _get_ttype_class($ttype)
	{
		if(isset(Token::$STANDARD_TYPES[$ttype])) {
			return Token::$STANDARD_TYPES[$ttype];
		}
		
		return 'XX';
		
		$i = 1;
		
		$aname = '';
		$fname = '';
		while(!$fname && $ttype && $i<20) {
			//$aname = '-' + ttype[-1] + $aname;
			$aname = '-' + 'XX' + $aname;
			$ttype = (string)Token::getToken($ttype)->parent;
			
			//var_dump($ttype);
			
			$fname = Token::$STANDARD_TYPES[$ttype];
			
			$i++;
		}
		return $fname + $aname;		
		
		/*
		fname = STANDARD_TYPES.get(ttype)
		if fname:
			return fname
		aname = ''
		while fname is None:
			aname = '-' + ttype[-1] + aname
			ttype = ttype.parent
			fname = STANDARD_TYPES.get(ttype)
		return fname + aname
		*/
	}
	
	private function _create_stylesheet()
	{
        $this->ttype2class = ['Token'=>''];
        $t2c = &$this->ttype2class;
        
        $this->class2style = [];
        $c2s = &$this->class2style;
        
		foreach($this->style as $ttype => $ndef) {
			
			//var_dump($ttype);
			
			$name = $this->_get_css_class($ttype);
            $style = '';
            if($ndef['color']) {
                $style .= sprintf('color: #%s; ', $ndef['color']);
            }
            if($ndef['bold']) {
                $style .= 'font-weight: bold; ';
            }
            if($ndef['italic']) {
                $style .= 'font-style: italic; ';
            }
            if($ndef['underline']) {
                $style .= 'text-decoration: underline; ';
            }
            if($ndef['bgcolor']) {
                $style .= sprintf('background-color: #%s; ', $ndef['bgcolor']);
            }
            if($ndef['border']) {
                $style .= sprintf('border: 1px solid #%s; ', $ndef['border']);
            }
            if($style) {
                $t2c[$ttype] = $name;
                # save len(ttype) to enable ordering the styles by
                # hierarchy (necessary for CSS cascading rules!)
                
                //$c2s[$name] = [style[:-2], ttype, len(ttype)]; //len??
            }
		}

	}
	
    public function get_style_defs($arg=null)
	{
        /*
        Return CSS style definitions for the classes produced by the current
        highlighting style. ``arg`` can be a string or list of selectors to
        insert before the token type classes.
        */
		/*
        if arg is None:
            arg = ('cssclass' in self.options and '.'+self.cssclass or '')
        if isinstance(arg, basestring):
            args = [arg]
        else:
            args = list(arg)

        def prefix(cls):
            if cls:
                cls = '.' + cls
            tmp = []
            for arg in args:
                tmp.append((arg and arg + ' ' or '') + cls)
            return ', '.join(tmp)

        styles = [(level, ttype, cls, style)
                  for cls, (style, ttype, level) in self.class2style.iteritems()
                  if cls and style]
        styles.sort()
        lines = ['%s { %s } /zvjezda %s zvjezda/' % (prefix(cls), style, repr(ttype)[6:])
                 for (level, ttype, cls, style) in styles]
        if arg and not self.nobackground and \
           self.style.background_color is not None:
            text_style = ''
            if Text in self.ttype2class:
                text_style = ' ' + self.class2style[self.ttype2class[Text]][0]
            lines.insert(0, '%s { background: %s;%s }' %
                         (prefix(''), self.style.background_color, text_style))
        if self.style.highlight_color is not None:
            lines.insert(0, '%s.hll { background-color: %s }' %
                         (prefix(''), self.style.highlight_color))
        return '\n'.join(lines)	
		*/	
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
		//@todo
	}
	
	private function _wrap_tablelinenos($inner)
	{
        $dummyoutfile = '';
        $lncount = 0;
        foreach($inner as $iinner) {
			list($t, $line) = $iinner;
            if($t) {
                $lncount += 1;
			}
            $dummyoutfile.=$line;
		}	

        $fl = $this->linenostart;
        $mw = strlen((string)($lncount + $fl - 1));
        $mw = str_repeat(' ', $mw);
        $sp = $this->linenospecial;
        $st = $this->linenostep;
        $la = $this->lineanchors;
        $aln = $this->anchorlinenos;
        $nocls = $this->noclasses;
        if($sp) {
            $lines = [];

            foreach(range($fl, $fl+$lncount) as $i) {
                if($i % $st == 0) {
                    if(i % $sp == 0) {
                        if($aln) {
                            $lines[] = sprintf('<a href="#%s-%d" class="special">%s%d</a>',
                                         $la, $i, $mw, $i);
                        } else {
                            $lines[] = sprintf('<span class="special">%s%d</span>', $mw, $i);
						}
                    } else {
                        if($aln) {
                            $lines[] = sprintf('<a href="#%s-%d">%s%d</a>', $la, $i, $mw, $i);
                        } else {
                            $lines[] = sprintf('%s%d' , $mw, $i);
						}
					}
                } else {
                    $lines[] = '';
				}
			}
            $ls = implode("\n", $lines);
        } else {
            $lines = [];
			foreach(range($fl, $fl+$lncount) as $i) {
                if($i % $st == 0) {
                    if($aln) {
                        $lines[] = sprintf('<a href="#%s-%d">%s%d</a>', $la, $i, $mw, $i);
                    } else {
                        $lines[] = sprintf('%s%d', $mw, $i);
					}
                } else {
                    $lines[] = '';
				}
			}
            $ls = implode("\n", $lines);
		}
		
        # in case you wonder about the seemingly redundant <div> here: since the
        # content in the other cell also is wrapped in a div, some browsers in
        # some configurations seem to mess up the formatting...
        if($nocls) {
            yield [0, sprintf('<table class="%stable">', $this->cssclass) .
                      '<tr><td><div class="linenodiv" ' .
                      'style="background-color: #f0f0f0; padding-right: 10px">' .
                      '<pre style="line-height: 125%">' .
                      $ls . '</pre></div></td><td class="code">'];
        } else {
            yield [0, sprintf('<table class="%stable">', $this->cssclass) .
                      '<tr><td class="linenos"><div class="linenodiv"><pre>' .
                      $ls . '</pre></div></td><td class="code">'];
		}			 
        yield [0, $dummyoutfile];
        yield [0, '</td></tr></table>'];		
	}
	
	private function _wrap_inlinelinenos($inner)
	{
        # need a list of lines since we need the width of a single number :(
        $lines = $inner;
        $sp = $this->linenospecial;
        $st = $this->linenostep;
        $num = $this->linenostart;
        //mw = len(str(len(lines) + num - 1))
        $mw = strlen((string)(count($lines) + $num - 1));
        $mw = str_repeat(' ', $mw);	

        if($this->noclasses) {
            if($sp) {
				foreach($lines as $llines) {
					list($t, $line) = $llines;
                    if($num%$sp == 0) {
                        $style = 'background-color: #ffffc0; padding: 0 5px 0 5px';
                    } else {
                        $style = 'background-color: #f0f0f0; padding: 0 5px 0 5px';
					}
                    yield [1, sprintf('<span style="%s">%s%s</span> ', 
                        $style, $mw, ($num%$st ? ' ' : $num)) . $line];
                    $num += 1;
				}
            } else {
				foreach($lines as $llines) {
					list($t, $line) = $llines;
                    yield [1, sprintf('<span style="background-color: #f0f0f0; ' .
                              'padding: 0 5px 0 5px">%s%s</span> ', 
                              $mw, ($num%$st ? ' ' : $num)) . $line];
                    $num += 1;
				}
			}
        } elseif($sp) {
			foreach($lines as $llines) {
				list($t, $line) = $llines;
                yield [1, sprintf('<span class="lineno%s">%s%s</span> ',
                    ($num%$sp == 0 ? ' special' : ''), $mw,
                    ($num%$st ? ' ' : $num)) . $line];
                $num += 1;
			}
        } else {
			foreach($lines as $llines) {
				list($t, $line) = $llines;
                yield [1, sprintf('<span class="lineno">%s%s</span> ',
                    $mw, ($num%$st ? ' ' : $num)) . $line];
                $num += 1;	
			}
		}
	}
	
    private function _wrap_lineanchors($inner)
	{
        $s = $this->lineanchors;
        $i = $this->linenostart - 1; # subtract 1 since we have to increment i
									# *before* yielding
        foreach($inner as $iinner) {
			list($t, $line) = $iinner;
            if($t) {
                $i += 1;
                yield [1, sprintf('<a name="%s-%d"></a>', $s, $i) . $line];
            } else {
                yield [0, $line];	
			}
		}
	}
	
    private function _wrap_linespans($inner)
	{
        $s = $this->linespans;
        $i = $this->linenostart - 1;
        foreach($inner as $iinner) {
        	list($t, $line) = $iinner;
            if($t) {
                $i += 1;
                yield [1, sprintf('<span id="%s-%d">%s</span>', $s, $i, $line)];
            } else {
                yield [0, $line];
           	}
        }
	}
	
     private function _wrap_div($inner)
	 {
        $style = [];
        if ($this->noclasses && !$this->nobackground && 
            isset($this->style['background_color'])) {
            $style[] = sprintf('background: %s', $this->style['background_color']);
        }
        if($this->cssstyles) {
            $style[] = $this->cssstyles;
        }
        $style = implode('; ',$style);

        yield [0, '<div' . ($this->cssclass ? sprintf(' class="%s"', $this->cssclass) : '')
                  . ($style ? sprintf(' style="%s"', $style) : '') . '>'];
        foreach($inner as $tup) {
            yield $tup;
        }
        yield [0, "</div>\n"];       		
	}
	
     private function _wrap_pre($inner)
	 {
        $style = [];
        if($this->prestyles) {
            $style[] = $this->prestyles;
        }
        if($this->noclasses) {
            $style[] = 'line-height: 125%';
        }
        $style = implode('; ',$style);

        yield [0, '<pre' . ($style ? sprintf(' style="%s"', $style) : '') . '>'];
        foreach($inner as $tup) {
            yield $tup;
        }
        yield [0, '</pre>'];
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
        $getcls = $this->ttype2class;
        $c2s = $this->class2style;
        $tagsfile = $this->tagsfile;

        $lspan = '';
        $line = '';
        foreach($tokensource as $ttype => $value) {
        	//var_dump($ttype); //var_dump($value);
        	
            if($nocls && 0) { //killed for now
                $cclass = $getcls[$ttype];
                while(!$cclass) {
                    $ttype = $ttype->parent;
                    $cclass = $getcls[$ttype];
                }
                //$cspan = cclass and '<span style="%s">' % c2s[cclass][0] or ''
            } else {
                $cls = $this->_get_css_class($ttype);
                $cspan = $cls ? sprintf('<span class="%s">', $cls) : '';
                
			}
			$parts = htmlspecialchars($value, ENT_QUOTES);
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
			
			$part_last = array_pop($parts);
			
			# for all but the last line
			foreach($parts as $part) {
                if($line) {
                    if($lspan != $cspan) {
                        $line .= ($lspan ? '</span>' : '') . $cspan . $part .
                                ($cspan ? '</span>' : '') . $lsep;
                    } else { # both are the same
                        $line .= $part . ($lspan ? '</span>' : '') . $lsep;
                    }
                    yield [1, $line];
                    $line = '';
                } elseif($part) {
                    yield [1, $cspan . $part . ($cspan ? '</span>' : '') . $lsep];
                } else {
                    yield [1, $lsep];
                }
			}
            # for the last line
			if($line && $part_last) {
                if($lspan != $cspan) {
                    $line .= ($lspan ? '</span>' : '') . $cspan . $part_last;
                    $lspan = $cspan;
                } else {
                    $line .= $part_last;
                }
			} elseif($part_last) {
                $line = $cspan . $part_last;
                $lspan = $cspan;
			}
            # else we neither have to open a new span nor set lspan

        }
        
        if($line) {
        	yield [1, $line . ($lspan ? '</span>' : '') . $lsep];
        }

	}
	
	public function wrap($source)
	{
		/*
        Wrap the ``source``, which is a generator yielding
        individual lines, in custom generators. See docstring
        for `format`. Can be overridden.
        */
		return $this->_wrap_div($this->_wrap_pre($source));
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

        if($this->hl_lines) {
            $source = $this->_highlight_lines($source);
		}
		
		$this->nowrap = 1;
		
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