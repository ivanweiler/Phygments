<?php
namespace Phygments\Lexers;
use \Phygments\Python\Re as re;
use \Phygments\Util;

class Php extends Regex
{
	/*
    For `PHP <http://www.php.net/>`_ source code.
    For PHP embedded in HTML, use the `HtmlPhpLexer`.

    Additional options accepted:

    `startinline`
        If given and ``True`` the lexer starts highlighting with
        php code (i.e.: no starting ``<?php`` required).  The default
        is ``False``.
    `funcnamehighlighting`
        If given and ``True``, highlight builtin function names
        (default: ``True``).
    `disabledmodules`
        If given, must be a list of module names whose function names
        should not be highlighted. By default all modules are highlighted
        except the special ``'unknown'`` module that includes functions
        that are known to php but are undocumented.

        To get a list of allowed modules have a look into the
        `_phpbuiltins` module:

        .. sourcecode:: pycon

            >>> from pygments.lexers._phpbuiltins import MODULES
            >>> MODULES.keys()
            ['PHP Options/Info', 'Zip', 'dba', ...]

        In fact the names of those modules match the module names from
        the php documentation.
	*/

    public $name = 'PHP';
    public $aliases = ['php', 'php3', 'php4', 'php5'];
    public $filenames = ['*.php', '*.php[345]', '*.inc'];
    public $mimetypes = ['text/x-php'];
    
    protected function __declare()
    {
	    $this->flags = array(re::IGNORECASE, re::DOTALL, re::MULTILINE);
	    $this->tokens = [
	        'root' => [
	            ['<\?(php)?', 'Comment.Preproc', 'php'],
	            ['[^<]+', 'Other'],
	            ['<', 'Other']
	        ],
	        'php' => [
	            ['\?>', 'Comment.Preproc', '#pop'],
	            ['<<<(\'?)([a-zA-Z_][a-zA-Z0-9_]*)\1\n.*?\n\2\;?\n', 'String'],
	            ['\s+', 'Text'],
	            ['\#.*?\n', 'Comment.Single'], 		//modified
	            ['//.*?\n', 'Comment.Single'],
	            # put the empty comment here, it is otherwise seen as
	            # the start of a docstring
	            ['/\*\*/', 'Comment.Multiline'],
	            ['/\*\*.*?\*/', 'String.Doc'],
	            ['/\*.*?\*/', 'Comment.Multiline'],
	            ['(->|::)(\s*)([a-zA-Z_][a-zA-Z0-9_]*)',
	              $this->_bygroups('Operator', 'Text', 'Name.Attribute')],
	            ['[~!%^&*+=|:.<>/?@-]+', 'Operator'],
	            ['[\[\]{}();,]+', 'Punctuation'],
	            ['(class)(\s+)',  $this->_bygroups('Keyword', 'Text'), 'classname'],
	            ['(function)(\s*)(?=\()',  $this->_bygroups('Keyword', 'Text')],
	            ['(function)(\s+)(&?)(\s*)',
	               $this->_bygroups('Keyword', 'Text', 'Operator', 'Text'), 'functionname'],
	            ['(const)(\s+)([a-zA-Z_][a-zA-Z0-9_]*)',
	               $this->_bygroups('Keyword', 'Text', 'Name.Constant')],
	            ['(and|E_PARSE|old_function|E_ERROR|or|as|E_WARNING|parent|'.
	             'eval|PHP_OS|break|exit|case|extends|PHP_VERSION|cfunction|'.
	             'FALSE|print|for|require|continue|foreach|require_once|'.
	             'declare|return|default|static|do|switch|die|stdClass|'.
	             'echo|else|TRUE|elseif|var|empty|if|xor|enddeclare|include|'.
	             'virtual|endfor|include_once|while|endforeach|global|__FILE__|'.
	             'endif|list|__LINE__|endswitch|new|__sleep|endwhile|not|'.
	             'array|__wakeup|E_ALL|NULL|final|php_user_filter|interface|'.
	             'implements|public|private|protected|abstract|clone|try|'.
	             'catch|throw|this|use|namespace|trait)\b', 'Keyword'],
	            ['(true|false|null)\b', 'Keyword.Constant'],
	            ['\$\{\$+[a-zA-Z_][a-zA-Z0-9_]*\}', 'Name.Variable'],
	            ['\$+[a-zA-Z_][a-zA-Z0-9_]*', 'Name.Variable'],
	            ['[\\a-zA-Z_][\\a-zA-Z0-9_]*', 'Name.Other'],
	            ['(\d+\.\d*|\d*\.\d+)([eE][+-]?[0-9]+)?', 'Number.Float'],
	            ['\d+[eE][+-]?[0-9]+', 'Number.Float'],
	            ['0[0-7]+', 'Number.Oct'],
	            ['0[xX][a-fA-F0-9]+', 'Number.Hex'],
	            ['\d+', 'Number.Integer'],
	            ["'([^'\\\\]*(?:\\\\.[^'\\\\]*)*)'", 'String.Single'], //modified
	            ['`([^`\\\\]*(?:\\.[^`\\\\]*)*)`', 'String.Backtick'], //modified
	            ['"', 'String.Double', 'string'],
	        ],
	        'classname' => [
	            ['[a-zA-Z_][\\a-zA-Z0-9_]*', 'Name.Class', '#pop']
	        ],
	        'functionname' => [
	            ['[a-zA-Z_][a-zA-Z0-9_]*', 'Name.Function', '#pop']
	        ],
	        'string' => [
	            ['"', 'String.Double', '#pop'],
	            ['[^{$"\\]+', 'String.Double'],
	            ['\\([nrt\"$\\]|[0-7]{1,3}|x[0-9A-Fa-f]{1,2})', 'String.Escape'],
	            ['\$[a-zA-Z_][a-zA-Z0-9_]*(\[\S+\]|->[a-zA-Z_][a-zA-Z0-9_]*)?',
	             'String.Interpol'],
	            ['(\{\$\{)(.*?)(\}\})',
	              $this->_bygroups('String.Interpol', $this->_using($this, array('_startinline'=>true)),
	                      'String.Interpol')],
	            ['(\{)(\$.*?)(\})',
	              $this->_bygroups('String.Interpol', $this->_using($this, array('_startinline'=>true)),
	                      'String.Interpol')],
	            ['(\$\{)(\S+)(\})',
	              $this->_bygroups('String.Interpol', 'Name.Variable', 'String.Interpol')],
	            ['[${\\]+', 'String.Double']
	        ],
	    ];
    
    }

	public function __construct($options = array())
	{
        $this->funcnamehighlighting = Util::get_bool_opt(
            $options, 'funcnamehighlighting', true);
        $this->disabledmodules = Util::get_list_opt(
            $options, 'disabledmodules', ['unknown']);
        $this->startinline = Util::get_bool_opt($options, 'startinline', false);

        # private option argument for the lexer itself
        if(isset($options['_startinline'])) {
			$this->startinline = $options['_startinline'];
			unset($options['_startinline']);
        }

        # collect activated functions in a set
        $this->_functions = [];
        if($this->funcnamehighlighting) {
            include dirname(__FILE__).'/_phpbuiltins.php';
        	foreach($MODULES as $key => $value) {
        		if(!in_array($key, $this->disabledmodules)) {
        			$this->_functions = array_merge($this->_functions, $value);
        		}
        	}
        }

		parent::__construct($options);
	}
        
    public function get_tokens_unprocessed(&$text)
    {
    	$stack = ['root'];
    	if($this->startinline) {
    		$stack[] = 'php';
    	}
    	foreach(parent::get_tokens_unprocessed($text, $stack) as $tokendata) {
    		list($index, $token, $value) = $tokendata;
    		if("$token"=='Token.Name.Other') {
    			if(in_array($value, $this->_functions)) {
    				yield [$index, Token::getToken('Name.Builtin'), $value];
    				continue;
    			}
    		}
    		yield $tokendata; //[$index, $token, $value]
    	}
    }
    
	public function analyse_text($text)
	{
        $rv = 0.0;
        if(preg_match('#<\?(?!xml)#', $text)) {
            $rv += 0.3;
        }
        if(strpos($text, '?>')!==false) {
            $rv += 0.1;
        }
        return $rv;
	}

        
}
