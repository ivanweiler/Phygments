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

	
	public function do_insertions($insertions, $tokens)
	{
		/*
	    Helper for lexers which must combine the results of several
	    sublexers.
			
	    ``insertions`` is a list of ``(index, itokens)`` pairs.
	    Each ``itokens`` iterable should be inserted at position
	    ``index`` into the token stream given by the ``tokens``
	    argument.
			
	    The result is a combined token stream.
    	*/
		
		//@todo
	}

}
