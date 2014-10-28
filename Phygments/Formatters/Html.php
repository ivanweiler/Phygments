<?php
namespace Phygments\Formatters;

use \Phygments\Util;
use \Phygments\Token;
use \Phygments\Python\Helper;

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
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
  <title>%(title)s</title>
  <meta http-equiv="content-type" content="text/html; charset="%(encoding)s">
  <style type="text/css">
CSSFILE_TEMPLATE
  </style>
</head>
<body>
<h2>%(title)s</h2>

CONST;
    
    const DOC_HEADER_EXTERNALCSS = <<<'CONST'
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
  <title>%(title)s</title>
  <meta http-equiv="content-type" content="text/html; charset="%(encoding)s">
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
        
        /**
         * @todo: check if ctags are possible; shell exec maybe
         * https://github.com/jeremykendall/phpctagger
         */
		
        $linenos = Util::get_opt($options, 'linenos', false);
        if($linenos == 'inline') {
            $this->linenos = 2;
        } elseif($linenos) {
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
	
	/**
	 * Return the css class of this token type prefixed with the classprefix option.
	 */
	private function _get_css_class($ttype)
	{
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
		
		//@todo: test, remove $i
		$i = 1;
		$aname = '';
		$fname = '';
		while(!$fname) {
			$aname = '-' + end(explode('.', $ttype)) + $aname;
			$ttype = (string)Token::getToken($ttype)->parent;
			$fname = isset(Token::$STANDARD_TYPES[$ttype]) ? Token::$STANDARD_TYPES[$ttype] : '';
			
			$i++;
			if($i>=5) {
				trigger_error("Possible infinite loop.", E_USER_NOTICE);
				break;
			}
		}
		return $fname + $aname;
	}
	
	private function _create_stylesheet()
	{
        $this->ttype2class = ['Token'=>''];
        $t2c = &$this->ttype2class;
        
        $this->class2style = [];
        $c2s = &$this->class2style;
        
		foreach($this->style as $ttype => $ndef) {

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
                /**
                 * save len(ttype) to enable ordering the styles by 
                 * hierarchy (necessary for CSS cascading rules!)
                 */
                
                //$c2s[$name] = [style[:-2], ttype, len(ttype)];
                $c2s[$name] = [substr($style, 0, -2), $ttype]; //@todo: wtf is len here
            }
		}
	}
	
	/**
	 * Return CSS style definitions for the classes produced by the current
	 * highlighting style. ``arg`` can be a string or list of selectors to
	 * insert before the token type classes.
	 */
    public function get_style_defs($arg=null)
	{
		if(!$arg) {
			$arg = isset($this->options['cssclass']) ? '.'.$this->cssclass : '';
		}
		if(is_string($arg)) {
			$args = array($arg);
		} else {
			$args = (array)$arg;
		}
		
		$prefix = function($cls) use ($args) {
			if($cls) {
				$cls = '.' . $cls;
			}
			$tmp = [];
			foreach($args as $arg) {
				$tmp[] = ($arg ? $arg . ' ' : '') . $cls;
			}
			return implode(', ', $tmp);			
		};
		
		//var_dump($this->class2style); 
		//var_dump($this->ttype2class);
		//die();
		
		$styles = [];
		foreach($this->class2style as $cls => $sstyle) {
			$styles[] = [
				'style' => $sstyle[0],
				'ttype'	=> $sstyle[1],
				'level'	=> '0',
				'cls'	=> $cls
			];
		}
		
		//var_dump($styles);

		$lines = [];
		foreach($styles as $sstyle) {
			//list($level, $ttype, $cls, $style) = $sstyle;
			extract($sstyle);
			$lines[] = sprintf('%s { %s } /* %s */', $prefix($cls), $style, substr($ttype, 6));
		}
		
		//@todo:  styles.sort() .. by len/level?
		
		if($arg && !$this->nobackground && $this->style->background_color) {
			$text_style = '';
			if(array_key_exists('Text', $this->ttype2class)) {
				$text_style = ' ' . $this->class2style[$this->ttype2class['Text']][0];
			}
			array_splice($lines, 0, 0,
				sprintf('%s { background: %s;%s }', $prefix(''), $this->style->background_color, $text_style)
			);							
		}		

		if($this->style->highlight_color) {
			array_splice($lines, 0, 0, 
				sprintf('%s.hll { background-color: %s }', $prefix(''), $this->style->highlight_color)
			);
		}
		
		return implode("\n", $lines);
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
		//@todo: finish

        if(0 && $this->cssfile) {
        	/*
            if os.path.isabs(self.cssfile):
                # it's an absolute filename
                cssfilename = self.cssfile
            else:
                try:
                    filename = outfile.name
                    if not filename or filename[0] == '<':
                        # pseudo files, e.g. name == '<fdopen>'
                        raise AttributeError
                    cssfilename = os.path.join(os.path.dirname(filename),
                                               self.cssfile)
                except AttributeError:
                    print >>sys.stderr, 'Note: Cannot determine output file name, ' \
                          'using current directory as base for the CSS file name'
                    cssfilename = self.cssfile
            */
        	
        	$cssfilename = $this->cssfile;
        	
            # write CSS file only if noclobber_cssfile isn't given as an option.
            if(!file_exists($cssfilename) || !$this->noclobber_cssfile) {
            	file_put_contents(
            		$cssfilename, 
            		Helper::string_format(self::CSSFILE_TEMPLATE, 
            			array('styledefs' => $this->get_style_defs('body')))
            	);
            	//raise IOError, Error writing CSS file
            }

            yield [0, Helper::string_format(self::DOC_HEADER_EXTERNALCSS,
						array(	'title'		=> $this->title,
								'cssfile'   => $this->cssfile,
								'encoding'  => $this->encoding))];
        } else {
        	//@note: this won't be needed with constants in php 5.6
        	$doc_header = str_replace('CSSFILE_TEMPLATE', self::CSSFILE_TEMPLATE, self::DOC_HEADER);
            yield [0, Helper::string_format($doc_header,
						array(	'title'		=> $this->title,
								'styledefs'	=> $this->get_style_defs('body'),
								'encoding'	=> $this->encoding))];
        }
        
		foreach($inner as $_inner) {
        	//t, line in inner:
            //yield t, line
            yield $_inner;
		}
        yield [0, self::DOC_FOOTER];
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

            foreach(range($fl, $fl+$lncount-1) as $i) {
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
			foreach(range($fl, $fl+$lncount-1) as $i) {
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
		
        /** 
         * in case you wonder about the seemingly redundant <div> here: since the
         * content in the other cell also is wrapped in a div, some browsers in
         * some configurations seem to mess up the formatting...
         */ 
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
        // need a list of lines since we need the width of a single number :(
        $lines = $inner;
        $sp = $this->linenospecial;
        $st = $this->linenostep;
        $num = $this->linenostart;
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
        $i = $this->linenostart - 1; // subtract 1 since we have to increment i before yielding
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
            	$this->style->background_color) {
            $style[] = sprintf('background: %s', $this->style->background_color);
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
	
	/**
	 * Just format the tokens, without any wrapping tags.
	 * Yield individual lines.
	 */
	private function _format_lines($tokensource)
	{
        $nocls = $this->noclasses;
        $lsep = $this->lineseparator;
        // for <span style=""> lookup only
        $getcls = &$this->ttype2class;
        $c2s = &$this->class2style;
        
        $lspan = '';
        $line = '';
        foreach($tokensource as $ttype => $value) {
            if($nocls) {
                $cclass = $getcls[$ttype];
                $i = 0;
                while(is_null($cclass)) {
                    $ttype = Token::getToken($ttype)->parent;
                    $cclass = $getcls["$ttype"];

                    if($i>=10) break;
                    $i++;
                }
                $cspan = $cclass ? sprintf('<span style="%s">', $c2s[$cclass][0]) : '';
            } else {
                $cls = $this->_get_css_class($ttype);
                $cspan = $cls ? sprintf('<span class="%s">', $cls) : '';
                
			}
			$parts = htmlspecialchars($value, ENT_QUOTES);
			$parts = explode("\n", $parts);
			
			$part_last = array_pop($parts);
			
			// Weiler: ZERO bug, $part = '0'; we need to use !=''
			
			// for all but the last line
			foreach($parts as $part) {
                if($line!='') {
                    if($lspan != $cspan) {
                        $line .= ($lspan ? '</span>' : '') . $cspan . $part .
                                ($cspan ? '</span>' : '') . $lsep;
                    } else { // both are the same
                        $line .= $part . ($lspan ? '</span>' : '') . $lsep;
                    }
                    yield [1, $line];
                    $line = '';
                } elseif($part!='') {
                    yield [1, $cspan . $part . ($cspan ? '</span>' : '') . $lsep];
                } else {
                    yield [1, $lsep];
                }
			}
            // for the last line
			if($line!='' && $part_last!='') {
                if($lspan != $cspan) {
                    $line .= ($lspan ? '</span>' : '') . $cspan . $part_last;
                    $lspan = $cspan;
                } else {
                    $line .= $part_last;
                }
			} elseif($part_last!='') {
                $line = $cspan . $part_last;
                $lspan = $cspan;
			}
            // else we neither have to open a new span nor set lspan
        }
        
        if($line!='') {
        	yield [1, $line . ($lspan ? '</span>' : '') . $lsep];
        }

	}
	
	/**
	 * Highlighted the lines specified in the `hl_lines` option by
	 * post-processing the token stream coming from `_format_lines`.
	 */
	private function _highlight_lines($inner)
	{
		$hls = $this->hl_lines;

        foreach($inner as $i => $iinner) {
			list($t, $value) = $iinner;
        	if($t != 1) {
        		yield [$t, $value];
        	}
        	if(in_array($i+1, $hls)) { // i + 1 because indexes start at 0
        		if($this->noclasses) {
        			$style = '';
        			if($this->style->highlight_color) {
        				$style = sprintf(' style="background-color: %s"', 
        							$this->style->highlight_color);
        			}
        			yield [1, sprintf('<span%s>%s</span>', $style, $value)];
        		} else {
        			yield [1, sprintf('<span class="hll">%s</span>', $value)];
        		}
        	} else {
            	yield [1, $value];
        	}
        }
	}

	/**
	 * Wrap the ``source``, which is a generator yielding 
	 * individual lines, in custom generators. See docstring 
	 * for `format`. Can be overridden.
	 */
	public function wrap($source)
	{
		return $this->_wrap_div($this->_wrap_pre($source));
	}
	
	/**
	 * The formatting process uses several nested generators; which of
	 * them are used is determined by the user's options.
	 * 
	 * Each generator should take at least one argument, ``inner``,
	 * and wrap the pieces of text generated by this.
	 * 
	 * Always yield 2-tuples: (code, text). If "code" is 1, the text 
	 * is part of the original tokensource being highlighted, if it's
	 * 0, the text is some piece of wrapping. This makes it possible to
	 * use several different wrappers that process the original source
	 * linewise, e.g. line number generators.
	 */
	public function format_unencoded($tokensource, $outfile)
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