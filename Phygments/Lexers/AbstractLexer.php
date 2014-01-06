<?php
namespace Phygments\Lexers;
use \Phygments\Util;
abstract class AbstractLexer
{
	/*
    Lexer for a specific language.

    Basic options recognized:
    ``stripnl``
        Strip leading and trailing newlines from the input (default: True).
    ``stripall``
        Strip all leading and trailing whitespace from the input
        (default: False).
    ``ensurenl``
        Make sure that the input ends with a newline (default: True).  This
        is required for some lexers that consume input linewise.
        *New in Pygments 1.3.*
    ``tabsize``
        If given and greater than 0, expand tabs in the input (default: 0).
    ``encoding``
        If given, must be an encoding name. This encoding will be used to
        convert the input string to Unicode, if it is not already a Unicode
        string (default: ``'latin1'``).
        Can also be ``'guess'`` to use a simple UTF-8 / Latin1 detection, or
        ``'chardet'`` to use the chardet library, if it is installed.
	*/

    #: Name of the lexer
    public $name;

    #: Shortcuts for the lexer
    public $aliases = [];

    #: File name globs
    public $filenames = [];

    #: Secondary file name globs
    public $alias_filenames = [];

    #: MIME types
    public $mimetypes = [];

    #: Priority, should multiple lexers match and no content is provided
    public $priority = 0;

    //protected $__metaclass__ = 'Phygments\Lexers\LexerMeta';

	public function __construct($options=array())
	{
		$this->options = $options;
		
		# declare dynamic properties
		//$this->__declare();

        $this->stripnl = Util::get_bool_opt($options, 'stripnl', True);
        $this->stripall = Util::get_bool_opt($options, 'stripall', False);
        $this->ensurenl = Util::get_bool_opt($options, 'ensurenl', True);
        $this->tabsize = Util::get_int_opt($options, 'tabsize', 0);
        $this->encoding = Util::get_opt('encoding', 'latin1');
        # self.encoding = options.get('inencoding', None) or self.encoding
        $this->filters = [];
        /*
        foreach(Util::get_list_opt($options, 'filters', []) as $filter_) {
            $this->add_filter($filter_);
		}
		*/
	}
	
	#: php can't declare dynamic properties in class like python
	protected function __declare(){}
	
	/*
    def __repr__(self):
        if self.options:
            return '<pygments.lexers.%s with %r>' % (self.__class__.__name__,
                                                     self.options)
        else:
            return '<pygments.lexers.%s>' % self.__class__.__name__
	*/
	
	public function add_filter($filter_, $options)
	{
		/*
        Add a new stream filter to this lexer.
		*/
        if(!($filter instanceof Filter)){
            $filter_ = get_filter_by_name($filter_, $options);
		}
        $this->filters[] = $filter_;
	}
	
	public function analyse_text($text)
	{
		/*
        Has to return a float between ``0`` and ``1`` that indicates
        if a lexer wants to highlight this text. Used by ``guess_lexer``.
        If this method returns ``0`` it won't highlight it in any case, if
        it returns ``1`` highlighting with this lexer is guaranteed.

        The `LexerMeta` metaclass automatically wraps this function so
        that it works like a static method (no ``self`` or ``cls``
        parameter) and the return value is automatically converted to
        `float`. If the return value is an object that is boolean `False`
        it's the same as if the return values was ``0.0``.
		*/
	}

    public function get_tokens($text, $unfiltered=false)
	{
		/*
        Return an iterable of (tokentype, value) pairs generated from
        `text`. If `unfiltered` is set to `True`, the filtering mechanism
        is bypassed even if filters are defined.

        Also preprocess the text, i.e. expand tabs and strip it if
        wanted and applies registered filters.
		*/
		
		/* mb_ functions here
        if not isinstance(text, unicode):
            if $this->encoding == 'guess':
                try:
                    text = text.decode('utf-8')
                    if text.startswith(u'\ufeff'):
                        text = text[len(u'\ufeff'):]
                except UnicodeDecodeError:
                    text = text.decode('latin1')
            elif $this->encoding == 'chardet':
                try:
                    import chardet
                except ImportError:
                    raise ImportError('To enable chardet encoding guessing, '
                                      'please install the chardet library '
                                      'from http://chardet.feedparser.org/')
                # check for BOM first
                decoded = None
                for bom, encoding in _encoding_map:
                    if text.startswith(bom):
                        decoded = unicode(text[len(bom):], encoding,
                                          errors='replace')
                        break
                # no BOM found, so use chardet
                if decoded is None:
                    enc = chardet.detect(text[:1024]) # Guess using first 1KB
                    decoded = unicode(text, enc.get('encoding') or 'utf-8',
                                      errors='replace')
                text = decoded
            else:
                text = text.decode($this->encoding)
        else:
            if text.startswith(u'\ufeff'):
                text = text[len(u'\ufeff'):]
		*/
		
        # text now *is* a unicode string
		$text = str_replace(array("\r\n","\r"), "\n", $text);
		
        if($this->stripall) {
            $text = trim($text);
		} elseif($this->stripnl) {
			$text = trim($text,"\n");
		}
        if($this->tabsize > 0) {
			$text = str_replace("\t", str_repeat(' ', $this->tabsize), $text);
		}
        if($this->ensurenl && substr($text, -1)!="\n") {
            $text .= "\n";
		}
		
		/*
        def streamer():
            for i, t, v in $this->get_tokens_unprocessed(text):
                yield t, v
        stream = streamer()
        */
		
		$streamer = function() use ($text) {
			foreach($this->get_tokens_unprocessed($text) as $token) {
				//yield array($token[1], $token[2]); //go with key => value here?
				yield (string)$token[1] => $token[2];
			}
		};
		$stream = $streamer();
		
        if(!$unfiltered) {
            //$stream = Filters::apply_filters($stream, $this->filters, $this);
		}
        return $stream;
	}
	
	/*
	Return an iterable of (tokentype, value) pairs.
	In subclasses, implement this method as a generator to
	maximize effectiveness.
	*/	
	public abstract function get_tokens_unprocessed(&$text);
	
	
	
	/*
	Weiler: Below isn't really an abstract, so maybe it should be seperate helper class,
	but I wanted easier syntax for lexer rules, $this->_include(), etc.
	*/
	protected function _include($str)
	{
		return new Helper\_Include($str);
	}
	
	protected function _inherit()
	{
		return new  Helper\_Inherit($str);
	}
	
	protected function _combined($arr)
	{
		return new Helper\_Combined($str);
	}
	
	protected function _bygroups()
	{
		/*
		Callback that yields multiple actions for each group in the match.
		*/
		$args = func_get_args();
		$callback = function($lexer, $match, $pos, $ctx=null) {
			
			foreach($args as $i => $action) {
				if(!$action){
					continue;
				} elseif(is_string($action)) {
					//$action = Token::getToken($action);
					$data = isset($match[$i+1]) ? $match[$i+1] : '';
					if($data) {
						yield [$pos+1, Token::getToken($action), $data];
					}
				} else {
					$data = isset($match[$i+1]) ? $match[$i+1] : null;
					if(!is_null($data)) {
						if($ctx) {
							$ctx->pos = $pos+1;
						}
						foreach($action($lexer, _PseudoMatch($pos+1, $data), $ctx) as $item) {
							if($item) {
								yield $item;
							}
						}
					}
				}
			}
			
			if($ctx) {
				//$ctx->pos = $pos+strlen($match);
			}
		};
			
			/*
			for i, action in enumerate(args):
				if action is None:
					continue
				elif type(action) is _TokenType:
					data = match.group(i + 1)
					if data:
						yield match.start(i + 1), action, data
				else:
					data = match.group(i + 1)
					if data is not None:
						if ctx:
							ctx.pos = match.start(i + 1)
						for item in action(lexer, _PseudoMatch(match.start(i + 1),
										   data), ctx):
							if item:
								yield item
			if ctx:
				ctx.pos = match.end()
			*/

		return $callback;		
	}
	
	//@todo: _using needs serious revamp
	protected function _using($_other, $kwargs=[])
	{
		/*
	    Callback that processes the match with a different lexer.
			
	    The keyword arguments are forwarded to the lexer, except `state` which
	    is handled separately.
			
	    `state` specifies the state that the new lexer will start in, and can
	    be an enumerable such as ('root', 'inline', 'string') or a simple
	    string which is assumed to be on top of the root state.
			
	    Note: For that to work, `_other` must not be an `ExtendedRegexLexer`.
	    */
		$gt_kwargs = [];
		if(isset($kwargs['state'])) {
			$s = $kwargs['state'];
			unset($kwargs['state']);
			
			if(is_array($s)) {
				$gt_kwargs['stack'] = $s;
			} else {
				$gt_kwargs['stack'] = ['root', $s];
			}
		}
		
		$gt_kwargs = array('root'); //hack
		
		//Weiler: lexer should be class not current object? parent tokens?
		//why always new class inside callback? can we define it outside+use()
		
		$lexer = $this;
		
		if(is_object($_other) && get_class($_other) == get_class()) {
			$callback = function($match, &$ctx=null) use ($lexer, $kwargs, $gt_kwargs) 
			{
				# if keyword arguments are given the callback
				# function has to create a new lexer instance
				if($kwargs) {
					# XXX: cache that somehow
					//Weiler: options needed before __declare() !!
					$kwargs = array_merge($kwargs, $lexer->options);
					$lexer = '\\Phygments\\Lexers\\'.$lexer;
					$lx = new $lexer($kwargs);
					//kwargs.update(lexer.options)
					//lx = lexer.__class__(**kwargs)
				} else {
					$lx = $lexer;
				}
				
				/*
				s = match.start()
				for i, t, v in lx.get_tokens_unprocessed(match.group(), **gt_kwargs):
					yield i + s, t, v
				if ctx:
					ctx.pos = match.end()
				*/
			};
		} else {
			$callback = function($match, $pos, &$ctx=null) use ($_other, $kwargs, $gt_kwargs) 
			{
				//var_dump(get_class($this));
				//var_dump($gt_kwargs);
				
				# XXX: cache that somehow
				$kwargs = array_merge($kwargs, $this->options);
				$_other = '\\Phygments\\Lexers\\'.$_other;
				$lx = new $_other($kwargs);
				//kwargs.update(lexer.options)
				//lx = _other(**kwargs)
				
				$s = $pos; //is $pos reference?
				foreach($lx->get_tokens_unprocessed($match, $gt_kwargs) as $tokenu) {
					list($i, $t, $v) = $tokenu;
					yield [$i + $s, $t, $v];
				}
				if($ctx) {
					$ctx->pos = $pos+strlen($match);
				}				
				
				/*
				s = match.start()
				for i, t, v in lx.get_tokens_unprocessed(match.group(), **gt_kwargs):
					yield i + s, t, v
				if ctx:
					ctx.pos = match.end()
				*/
			};
		}

		return $callback;
	}
	
	public function do_insertions($insertions, $tokens)
	{
		
	}

}


namespace Phygments\Lexers\Helper;

class _Include
{
	private $_value;

	public function __construct($str)
	{
		$this->_value = $str;
	}

	public function __toString()
	{
		return $this->_value;
	}
}

class _Inherit
{
	public function __toString()
	{
		return 'inherit';
	}
}

class _Combined
{
	public function __construct($str)
	{
		$this->_value = $str;
	}
}

class _PseudoMatch
{
	/*
    A pseudo match object constructed from a string.
	*/
	
	public function __construct($start, $text)
	{
		$this->_text = $text;
		$this->_start = $start;		
	}
	
	public function start()
	{
		return $this->_start;
	}
	
	public function end()
	{
		return $this->_start + strlen($this->_text);
	}

	
	public function group($arg)
	{
		if($arg) {
			//raise IndexError('No such group')
		}
		return  $this->_text;
	}

	
	public function groups()
	{
		return [self._text];
	}

	public function groupdict()
	{
		return [];  //{}
	}
	    		
}

