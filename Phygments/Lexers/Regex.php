<?php
namespace Phygments\Lexers;

use \Phygments\Util;
use \Phygments\Token;
use \Phygments\Python\Re as re;
use \Phygments\Python\Exception;

/**
 * Base for simple stateful regular expression-based lexers.
 * Simplifies the lexing process so that you need only
 * provide a list of states and regular expressions.
 */
class Regex extends AbstractLexer
{
    /** 
     * Flags for compiling the regular expressions.
     * Defaults to MULTILINE.
     */
    protected $flags = re::MULTILINE;

    /**
     * Dict of ``{'state': [(regex, tokentype, new_state), ...], ...}``
     *
     * The initial state is 'root'.
     * ``new_state`` can be omitted to signify no state transition.
     * If it is a string, the state is pushed on the stack and changed.
     * If it is a tuple of strings, all states are pushed on the stack and
     * the current state will be the topmost.
     * It can also be ``combined('state1', 'state2', ...)``
     * to signify a new, anonymous state combined from the rules of two
     * or more existing ones.
     * Furthermore, it can be '#pop' to signify going back one step in
     * the state stack, or '#push' to push the current state on the stack
     * again.
     *
     * The tuple can also be replaced with ``include('state')``, in which
     * case the rules from the state named by the string are included in the
     * current one.
     */
    
    private $_tokens;
    private $_tokens_inherited = false;
    protected $token_variants = false;
	
	public function __construct($options=array())
	{
		//$this->__declare();

		parent::__construct($options);
		
		/**
		 * Metaclass for RegexLexer, creates the self._tokens attribute from
		 * self.tokens on the first instantiation.
		 * 
		 * Metaclass __call() logic
		 */
		if(is_null($this->_tokens)) {
			$this->_all_tokens = [];
            $this->_tmpname = 0;
			
			if($this->token_variants) {
				//pass
			} else {
				$this->_tokens = $this->process_tokendef('', $this->get_tokendefs());
			}
		}
	}
	
	/**
	 * Metaclass for RegexLexer, creates the self._tokens attribute from
	 * self.tokens on the first instantiation.
	 *
	 * Metaclass __call() logic
	 */	
	private function _init_tokens()
	{
		if(is_null($this->_tokens)) {
			$this->_all_tokens = [];
			$this->_tmpname = 0;
				
			if($this->token_variants) {
				//pass
			} else {
				$this->_tokens = $this->process_tokendef('', $this->get_tokendefs());
			}
		}		
	}
	
	/**
	 * Split ``text`` into (tokentype, text) pairs.
	 * ``stack`` is the inital stack (default: ``['root']``)
	 */	
	public function get_tokens_unprocessed(&$text, $stack=array('root'))
	{
        $pos = 0;
        $tokendefs = &$this->_tokens;
        $statestack = (array)$stack;
        $statetokens = $tokendefs[$statestack[count($statestack)-1]];
        
        while(1) {
			$doelse = true;
			foreach($statetokens as $statetoken) {
				list($rexmatch, $action, $new_state) = $statetoken;
				$m = re::match($rexmatch, $text, $pos);
				if($m) {
					//var_dump($rexmatch);
					//var_dump($m->group());
					if($action instanceof \Phygments\_TokenType) {
						yield [$pos, $action, $m->group()];
					} else {
						foreach($action($this, $m) as $item) {
							yield $item;
						}						
					}
					$pos = $m->end();
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
						} elseif(is_int($new_state)) {
							# pop
							array_splice($statestack, $new_state);
						} elseif($new_state == '#push') {
							$statestack[] = $statestack[count($statestack)-1];
						} else {
							Exception::assert(sprintf(
								'wrong state def: %s', print_r($new_state)
							));
						}

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
					// at EOL, reset state to "root"
					$statestack = ['root'];
					$statetokens = $tokendefs['root'];
					yield array($pos, Token::getToken('Text'), "\n");
					$pos += 1;
					continue;
				}
				yield [$pos, Token::getToken('Error'), $text[$pos]];
				$pos += 1;
			}
		}

	}
	
	/**
	 * Preprocess the regular expression component of a token definition.
	 */
	private function _process_regex($regex, $rflags)
	{
		$flags = implode((array)$rflags);
		//$regex = addcslashes($regex, '#');
		$regex = str_replace(array('#','\\\\#'), array('\\#','\\\\\\#'), $regex);
		return "#$regex#$flags";
	}

	/** 
	 * Preprocess the token component of a token definition. 
	 */
	private function _process_token($token)
	{
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

	/** 
	 * Preprocess the state transition action of a token definition. 
	 */
	private function _process_new_state($new_state, &$unprocessed, &$processed)
	{
		if(is_string($new_state)) {
			// an existing state
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
			
		//@todo combined not tested
		} elseif($new_state instanceof \Phygments\Lexers\Regex\Helper\_Combined) {
			// combine a new state from existing ones
			$tmp_state = sprintf('_tmp_%d', $this->_tmpname);
			$this->_tmpname += 1;
			$itokens = [];
			foreach($new_state as $istate) {
				if($istate == $new_state) {
					throw new \Exception(sprintf('circular state ref %s', (string)$istate));
				}
				//assert istate != new_state, 'circular state ref %r' % istate
				$itokens = array_merge($itokens, $this->_process_state($unprocessed, $processed, $istate));
			}
			$processed[$tmp_state] = $itokens;
			return [$tmp_state]; //(tmp_state,)			
			
		} elseif(is_array($new_state)) {
			foreach($new_state as $istate) {
				//assert
				if(!(isset($unprocessed[$istate]) || in_array($istate, array('#pop', '#push')))) {
					throw new \Exception(sprintf('unknown new state %s', (string)$istate));
				}
			}
			return $new_state;
			
		} else {
			throw new \Exception(sprintf('unknown new state def %s', (string)$new_state));
			//assert False, 'unknown new state %r' % new_state
		}
	}
	
	/** 
	 * Preprocess a single state definition. 
	 */
	private function _process_state(&$unprocessed, &$processed, $state)
	{
		if(isset($processed[$state])) {
			return $processed[$state];
		}
		
		$processed[$state] = [];
		$tokens = &$processed[$state];
		$rflags = $this->flags;
		foreach($unprocessed[$state] as $tdef) {
			
			if($tdef instanceof \Phygments\Lexers\Regex\Helper\_Include) {
				// it's a state reference
				if($tdef == $state) {
					throw new \Exception(sprintf('circular state reference %s', (string)$state));
				}

				$tokens = array_merge($tokens, $this->_process_state($unprocessed, $processed, (string)$tdef));
				continue;			
			}
			
			if($tdef instanceof \Phygments\Lexers\Regex\Helper\_Inherit) {
				// processed already
				continue;
			}
			
			$rex = $this->_process_regex($tdef[0], $rflags);
			$token = $this->_process_token($tdef[1]);
			
			if(count($tdef) == 2) {
				$new_state = null;
			} else {
				$new_state = $this->_process_new_state($tdef[2], $unprocessed, $processed);
			}
			
			$tokens[] = array($rex, $token, $new_state);		
		}
		
		return $tokens;
	}
	
	/** 
	 * Preprocess a dictionary of token definitions. 
	 */
	public function process_tokendef($name, $tokendefs=null)
	{
        $processed = $this->_all_tokens[$name] = [];
        if(!$tokendefs) {
        	$tokendefs = $this->tokens[$name];
        }
        
        foreach(array_keys($tokendefs) as $state) {
        	$this->_process_state($tokendefs, $processed, $state);
        }
        
        return $processed;
	}
	
	
	protected function inherit_tokendefs($tokendefs, $stack)
	{
		if(!$this->_tokens_inherited) {
			$stack = array($tokendefs, $stack);
			$this->_tokens_inherited = true;
		} else {
			array_unshift($stack, $tokendefs);
		}
		
		return $stack;
		
		//$tokendefs_array = func_get_args();
		//get_called_class() == $this
	}
	
	/**
	 * Merge tokens from superclasses in MRO order, returning a single tokendef
	 * dictionary.
	 * 
	 * Any state that is not defined by a subclass will be inherited
	 * automatically.  States that *are* defined by subclasses will, by
	 * default, override that state in the superclass.  If a subclass wishes to
	 * inherit definitions from a superclass, it can use the special value
	 * "inherit", which will cause the superclass' state definition to be
	 * included at that point in the state.
	 */	
	public function get_tokendefs()
	{
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
		
		//check for 

		if(!$this->_tokens_inherited) {
			return $this->tokendefs();
		}
		
		$tokens = [];
		$inheritable = [];

		foreach($this->tokendefs() as $toks) {
				
			foreach($toks as $state => $items) {
				
				//$curitems = $tokens[$state];
				
				if(!isset($tokens[$state])) {
					$tokens[$state] = $items;
					
					$inherit_ndx = array_search($this->_inherit(), $items, false);
					if(!$inherit_ndx) {
						continue;
					}
					
					$inheritable[$state] = $inherit_ndx;
					continue;
				}
				
				if(isset($inheritable[$state])) {
					$inherit_ndx = $inheritable[$state];
					unset($inheritable[$state]);
				} else {
					continue;
				}
				
				// Replace the "inherit" value with the items
				
				array_splice($a, 1, 0, $items);
				//curitems[inherit_ndx:inherit_ndx+1] = items
				$tokens[$state][$inherit_ndx] = $items;
				
				$new_inh_ndx = array_search($this->_inherit(), $items, false);
				if($new_inh_ndx) {
					$inheritable[$state] = $inherit_ndx + $new_inh_ndx;
				}
				
			}
			
		}		

		return $tokens;
	}
	
	
	
	/* Helpers */

	protected function _include($str)
	{
		return new Regex\Helper\_Include($str);
	}
	
	protected function _inherit()
	{
		return new Regex\Helper\_Inherit();
	}
	
	protected function _combined($arr)
	{
		return new Regex\Helper\_Combined($arr);
	}
	
	/**
	 * Callback that yields multiple actions for each group in the match.
	 */
	protected function _bygroups()
	{
		$args = func_get_args();
		$callback = function($lexer, $match, $ctx=null) use ($args) {
			foreach($args as $i => $action) {
				if(!$action){
					continue;
				} elseif(is_string($action)) {
					//$action = Token::getToken($action);
					$data = $match->group($i+1);
					if(!is_null($data)) {
						yield [$match->start($i+1), Token::getToken($action), $data];
					}
				} else {
					$data = $match->group($i+1);
					if(!is_null($data)) {
						if($ctx) {
							$ctx->pos = $match->start($i+1);
						}
						foreach($action($lexer, new _PseudoMatch($match->start($i+1), $data), $ctx) as $item) {
							if($item) {
								yield $item;
							}
						}
					}
				}
			}
			
			if($ctx) {
				$ctx->pos = $match->end();
			}
		};

		return $callback;		
	}
	
	/**
	 * Callback that processes the match with a different lexer.
	 * The keyword arguments are forwarded to the lexer, except `state` which
	 * is handled separately.
	 * 
	 * `state` specifies the state that the new lexer will start in, and can
	 * be an enumerable such as ('root', 'inline', 'string') or a simple
	 * string which is assumed to be on top of the root state.
	 * 
	 * Note: For that to work, `_other` must not be an `ExtendedRegexLexer`.
	 * 
	 * 
	 * @todo: _using() needs serious revamp/cleanup
	 */
	protected function _using($_other, $kwargs=[])
	{
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
		
		//$lexer = $this;
		
		if(is_object($_other) && get_class($_other) == get_class()) {
			$callback = function($lexer, $match, $ctx=null) use ($kwargs, $gt_kwargs) 
			{
				# if keyword arguments are given the callback
				# function has to create a new lexer instance
				if($kwargs) {
					# XXX: cache that somehow
					//Weiler: options needed before __declare() !!
					$kwargs = array_merge($kwargs, $lexer->options);
					$lexer = '\\Phygments\\Lexers\\'.$lexer;
					$lx = new $lexer($kwargs);
				} else {
					$lx = $lexer;
				}
				
				$s = $match->start();
				foreach($lx->get_tokens_unprocessed($match->group(), $gt_kwargs) as $tokenu) {
					list($i, $t, $v) = $tokenu;
					yield [$i + $s, $t, $v];
				}
				if($ctx) {
					$ctx->pos = $match->end();
				}	
			};
		} else {
			$callback = function($lexer, $match, $ctx=null) use ($_other, $kwargs, $gt_kwargs) 
			{
				# XXX: cache that somehow
				$kwargs = array_merge($kwargs, $this->options);
				$_other = '\\Phygments\\Lexers\\'.$_other;
				$lx = new $_other($kwargs);
				
				$s = $match->start();
				foreach($lx->get_tokens_unprocessed($match->group(), $gt_kwargs) as $tokenu) {
					list($i, $t, $v) = $tokenu;
					yield [$i + $s, $t, $v];
				}
				if($ctx) {
					$ctx->pos = $match->end();
				}
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

/**
 * A pseudo match object constructed from a string.
 */
class _PseudoMatch
{
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
			Exception::raise('IndexError', 'No such group');
		}
		return  $this->_text;
	}

	public function groups()
	{
		return array($this->_text);
	}

	public function groupdict()
	{
		return array();
	}
	 
}