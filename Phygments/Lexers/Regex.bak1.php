<?php
namespace Phygments\Lexers;
use \Phygments\Util;
use \Phygments\Token;
use \Phygments\Python\Re as re;
use \Phygments\Python\Exception;

class Regex extends AbstractLexer
{
	/*
    Base for simple stateful regular expression-based lexers.
    Simplifies the lexing process so that you need only
    provide a list of states and regular expressions.
	*/
    //__metaclass__ = RegexLexerMeta

    #: Flags for compiling the regular expressions.
    #: Defaults to MULTILINE.
    public $flags = re::MULTILINE;

    #: Dict of ``{'state': [(regex, tokentype, new_state), ...], ...}``
    #:
    #: The initial state is 'root'.
    #: ``new_state`` can be omitted to signify no state transition.
    #: If it is a string, the state is pushed on the stack and changed.
    #: If it is a tuple of strings, all states are pushed on the stack and
    #: the current state will be the topmost.
    #: It can also be ``combined('state1', 'state2', ...)``
    #: to signify a new, anonymous state combined from the rules of two
    #: or more existing ones.
    #: Furthermore, it can be '#pop' to signify going back one step in
    #: the state stack, or '#push' to push the current state on the stack
    #: again.
    #:
    #: The tuple can also be replaced with ``include('state')``, in which
    #: case the rules from the state named by the string are included in the
    #: current one.
    public $tokens = [];
	
	public function __construct($options=array())
	{
		$this->__declare();
		
		/* Metaclass __call() logic */
		if(is_null($this->_tokens)) {
			$this->_all_tokens = [];
            $this->_tmpname = 0;
			
			if($this->token_variants) {
				//pass
			} else {
				$this->_tokens = $this->process_tokendef('', $this->get_tokendefs());
			}
		}

		parent::__construct($options);
	}

	public function get_tokens_unprocessed(&$text, $stack=array('root'))
	{
		/*
        Split ``text`` into (tokentype, text) pairs.

        ``stack`` is the inital stack (default: ``['root']``)
		*/

		//echo 123; die();
		
        $pos = 0;
        $tokendefs = &$this->_tokens;
        $statestack = (array)$stack;
        $statetokens = $tokendefs[$statestack[count($statestack)-1]];
        
        //var_dump(array_keys($tokendefs));	//all tokens
        //var_dump($statestack[count($statestack)-1]); //'root'
        //var_dump($statetokens); //root tokens
        
        //$lol = 1; 
        //$length = strlen($text); //while $pos < $length
        while(1) {
        	//$lol++;
        	
        	//$doelse = !count($statetokens);
			$doelse = true;
				
			foreach($statetokens as $statetoken) {
				$rexmatch = $statetoken[0]; 
				$action = $statetoken[1];
				$new_state = $statetoken[2];
				
				$matches = array();
				//$texttomatch = substr($text, $pos);
				
				//echo $pos . "\n";
				//echo htmlspecialchars($texttomatch) . "\n";
				//echo htmlspecialchars($rexmatch) . "\n";
				
				$m = preg_match($rexmatch, $text, $matches, PREG_OFFSET_CAPTURE, $pos);
				//var_dump($matches);
				
				//$m = re::match($rexmatch, $text, $pos);
				
				//@todo: not needed?
 				if($m && $matches[0][1]!=$pos) {
 					$m = false;
 				}
				
				if($m) {
					//echo $pos . "\n";
					//echo $rexmatch . "\n";
					//var_dump($matches);
					//echo "match\n";
					if($action instanceof \Phygments\_TokenType) {
						yield array($pos, $action, $matches[0][0]);
					} else {
						foreach($action(/*$this,*/ $matches[0][0], $pos) as $item) {
							yield $item;
						}						
					}
					
					//m.end();
					//$pos += ($matches[0][1]==-1) ? 0 : $matches[0][1]+strlen($matches[0][0]);
					$pos += strlen($matches[0][0]);
					//var_dump($new_state);
					
					//@todo: push isn't tested
					if($new_state) {
						# state transition
						if(is_array($new_state)) {
							foreach($new_state as $state) {
								if($state == '#pop') {
									array_pop($statestack);
								}elseif($state == '#push') {
									$statestack[] = $statestack[count($statestack)-1];
								} else {
									$statestack[] = $state;
								}
							}						
						}elseif(is_int($new_state)) {
							# pop
							array_splice($statestack, $new_state);
							//del statestack[new_state:]
						} elseif($new_state == '#push') {
							$statestack[] = $statestack[count($statestack)-1];
						} else {
							//assert False, "wrong state def: %r" % new_state
							throw new \Exception(sprintf(
								'wrong state def: %s', print_r($new_state)
							));
						}
						
						//var_dump($statestack);
						$statetokens = $tokendefs[$statestack[count($statestack)-1]];						
					}
					
					$doelse = false;
					break;
				}
				
			}

			if($doelse) {
				if(!isset($text[$pos])) {
					break;
				}
				
				if($text[$pos] == "\n") {
					# at EOL, reset state to "root"
					$statestack = ['root'];
					$statetokens = $tokendefs['root'];
					yield array($pos, Token::getToken('Text'), "\n");
					$pos += 1;
					continue;
				}
				yield array($pos, Token::getToken('Error'), $text[$pos]);
				$pos += 1;			
			}
		}

	}
	
	/*
    Metaclass for RegexLexer, creates the self._tokens attribute from
    self.tokens on the first instantiation.
	*/
	
	private function _process_regex($regex, $rflags)
	{
		/*Preprocess the regular expression component of a token definition.*/

		$flags = implode((array)$rflags);
		//$regex = addcslashes($regex, '#');
		return "#\G$regex#$flags";
	}
				
	private function _process_token($token)
	{
		/*Preprocess the token component of a token definition.*/
		
		//check string format? Xyz.Xyz?
		if(is_string($token) && preg_match('#^[A-Z][a-z]*(?:\.[A-Z][a-z]*)*$#', $token)) {
			$token = Token::getToken($token);
		}
		
		if(!(($token instanceof \Phygments\_TokenType) || is_callable($token))) {
			throw new \Exception(sprintf('token type must be simple type or callable, not %s', gettype($token)));
		}

		/*
        assert type(token) is _TokenType or callable(token), \
               'token type must be simple type or callable, not %r' % (token,)
		*/
        return $token;
	}

	private function _process_new_state($new_state, &$unprocessed, &$processed)
	{
        /*Preprocess the state transition action of a token definition.*/
		
		if(is_string($new_state)) {
			# an existing state
			if($new_state == '#pop') {
				return -1;
			} elseif(isset($unprocessed[$new_state])) {
				return [$new_state]; //(new_state,) //??
			} elseif($new_state == '#push') {
				return $new_state;
			} elseif(substr($new_state, 0, 5) == '#pop:') {
				return -(int)substr($new_state, 5);
			} else {
				//assert False, 'unknown new state %r' % new_state
				throw new \Exception(sprintf('unknown new state %s', (string)$new_state));
			}			
		} elseif(0) {
			//@todo: combined missing 
		} elseif(is_array($new_state)) {
			foreach($new_state as $istate) {
				if(!(isset($unprocessed[$istate]) || in_array($istate, array('#pop', '#push')))) {
					throw new \Exception(sprintf('unknown new state %s', (string)$istate));
				}
			}
			return $new_state;
		} else {
			throw new \Exception(sprintf('unknown new state def %s', (string)$new_state));
		}
		
		/*
        if isinstance(new_state, str):
            # an existing state
            if($new_state == '#pop') {
                return -1;
			} elseif($new_state in unprocessed) {
                return (new_state,)
			} elseif($new_state == '#push') {
                return new_state
			} elseif($new_state[:5] == '#pop:') {
                return -int(new_state[5:])
			} else {
                assert False, 'unknown new state %r' % new_state
			}
        elif isinstance(new_state, combined):
            # combine a new state from existing ones
            tmp_state = '_tmp_%d' % cls._tmpname
            cls._tmpname += 1
            itokens = []
            for istate in new_state:
                assert istate != new_state, 'circular state ref %r' % istate
                itokens.extend(cls._process_state(unprocessed,
                                                  processed, istate))
            processed[tmp_state] = itokens
            return (tmp_state,)
        elif isinstance(new_state, tuple):
            # push more than one state
            for istate in new_state:
                assert (istate in unprocessed or
                        istate in ('#pop', '#push')), \
                       'unknown new state ' + istate
            return new_state
        else:
            assert False, 'unknown new state def %r' % new_state
            */
	}
	
	private function _process_state(&$unprocessed, &$processed, $state)
	{
        /*Preprocess a single state definition.*/
		
		if(isset($processed[$state])) {
			return $processed[$state];
		}
		
		$processed[$state] = [];
		$tokens = &$processed[$state];
		$rflags = $this->flags;
		foreach($unprocessed[$state] as $tdef) {
			//var_dump($tdef);
			//@todo: include, _inherit support
			
			if($tdef instanceof \Phygments\Lexers\Regex\Helper\_Include) {
				# it's a state reference
				if($tdef == $state) {
					throw new \Exception(sprintf('circular state reference %s', (string)$state));
				}

				$tokens = array_merge($tokens, $this->_process_state($unprocessed, $processed, (string)$tdef));
				//tokens.extend(cls._process_state(unprocessed, processed,str(tdef)))
				continue;			
			}
			
			if($tdef instanceof \Phygments\Lexers\Regex\Helper\_Inherit) {
				# processed already
				continue;
			}
			
			$rex = $this->_process_regex($tdef[0], $rflags);
			//$rex = $tdef[0];
			$token = $this->_process_token($tdef[1]);
			
			if(count($tdef) == 2) {
				$new_state = null;
			} else {
				$new_state = $this->_process_new_state($tdef[2], $unprocessed, $processed);
			}
			
			//tokens.append((rex, token, new_state))
			$tokens[] = array($rex, $token, $new_state);		
		}
		
		return $tokens;
		
		/*
        assert type(state) is str, "wrong state name %r" % state
        assert state[0] != '#', "invalid state name %r" % state
        if state in processed:
            return processed[state]
        tokens = processed[state] = []
        rflags = cls.flags
        for tdef in unprocessed[state]:
            if isinstance(tdef, include):
                # it's a state reference
                assert tdef != state, "circular state reference %r" % state
                tokens.extend(cls._process_state(unprocessed, processed,
                                                 str(tdef)))
                continue
            if isinstance(tdef, _inherit):
                # processed already
                continue

            assert type(tdef) is tuple, "wrong rule def %r" % tdef

            try:
                rex = cls._process_regex(tdef[0], rflags)  //compile, no need in php
            except Exception, err:
                raise ValueError("uncompilable regex %r in state %r of %r: %s" %
                                 (tdef[0], state, cls, err))

            token = cls._process_token(tdef[1])

            if len(tdef) == 2:
                new_state = None
            else:
                new_state = cls._process_new_state(tdef[2],
                                                   unprocessed, processed)

            tokens.append((rex, token, new_state))
        return tokens
        */
	}
	
	public function process_tokendef($name, $tokendefs=null)
	{
        /*Preprocess a dictionary of token definitions.*/

        $processed = $this->_all_tokens[$name] = [];
        if(!$tokendefs) {
        	$tokendefs = $this->tokens[$name];
        }
        
        foreach(array_keys($tokendefs) as $state) {
        	$this->_process_state($tokendefs, $processed, $state);
        }
        
        return $processed;
	}
	
	public function get_tokendefs()
	{
        /*
        Merge tokens from superclasses in MRO order, returning a single tokendef
        dictionary.

        Any state that is not defined by a subclass will be inherited
        automatically.  States that *are* defined by subclasses will, by
        default, override that state in the superclass.  If a subclass wishes to
        inherit definitions from a superclass, it can use the special value
        "inherit", which will cause the superclass' state definition to be
        included at that point in the state.
        */
		
		/*
		Weiler: I believe we can access defined parent properties through Reflection if nothing else
		http://stackoverflow.com/questions/2439181/php-and-classes-access-to-parents-public-property-within-the-parent-class
		but I think tokens must by dynamicly declared in php (__declare()), so I'm not sure how to do this ??
		Can every parent do this merge for himself? parent::get_tokendefs() ?
		How many Lexers are using parent tokens ?
		*/
		
		/*
        $tokens = {}
        $inheritable = {}
        for c in itertools.chain((cls,), cls.__mro__):
            toks = c.__dict__.get('tokens', {})

            for state, items in toks.iteritems(): //normal k/v iterator
                curitems = tokens.get(state)
                if curitems is None:
                    tokens[state] = items
                    try:
                        inherit_ndx = items.index(inherit)
                    except ValueError:
                        continue
                    inheritable[state] = inherit_ndx
                    continue

                inherit_ndx = inheritable.pop(state, None)
                if inherit_ndx is None:
                    continue

                # Replace the "inherit" value with the items
                curitems[inherit_ndx:inherit_ndx+1] = items
                try:
                    new_inh_ndx = items.index(inherit)
                except ValueError:
                    pass
                else:
                    inheritable[state] = inherit_ndx + new_inh_ndx

        return tokens
        */
		
		return $this->tokens;
		
	}
	
	
	
	/* Helpers */

	protected function _include($str)
	{
		return new Regex\Helper\_Include($str);
	}
	
	protected function _inherit()
	{
		return new  Regex\Helper\_Inherit($str);
	}
	
	protected function _combined($arr)
	{
		return new Regex\Helper\_Combined($str);
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
						foreach($action($lexer, new _PseudoMatch($pos+1, $data), $ctx) as $item) {
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
	
}


namespace Phygments\Lexers\Regex\Helper;

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
			throw new Exception\IndexError('No such group');
		}
		return  $this->_text;
	}

	public function groups()
	{
		return [self._text];
	}

	public function groupdict()
	{
		return array();  //{}
	}
	 
}