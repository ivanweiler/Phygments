<?php
namespace Phygments\Lexers;
use \Phygments\Util;
use \Phygments\Token;
use \Phygments\Python\Re as re;

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
				
				//var_dump($new_state);
				
				$matches = array();
				
				//echo $pos . '<br />';
				//echo htmlspecialchars($rexmatch) . '<br />';
				
				//$m = preg_match($rexmatch, $text, $matches, PREG_OFFSET_CAPTURE, $pos);
				
				$texttomatch = substr($text, $pos);
				
				$m = preg_match($rexmatch, $texttomatch, $matches, PREG_OFFSET_CAPTURE);
				
				if($m) {
					//echo 'match <br />';
					if($action instanceof \Phygments\_TokenType) {
						yield array($pos, $action, $matches[0][0]);
					} else {
						foreach($action($this, $m) as $item) {
							yield $item;
						}						
					}
					
					//m.end();
					$pos += ($matches[0][1]==-1) ? -1 : $matches[0][1]+strlen($matches[0][0]);
					
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
							throw new Exception(sprintf(
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
		//return re.compile(regex, rflags).match
		
		$flags = implode((array)$rflags);
		$regex = addcslashes($regex, '#');
		return "#^$regex#$flags";
	}
				
	private function _process_token($token)
	{
		//check string format? Xyz.Xyz?
		$token = Token::getToken($token);
		//var_dump($token);
		
        /*Preprocess the token component of a token definition.*/
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
				throw new Exception(sprinf('unknown new state %s', (string)$new_state));
			}			
		} elseif(0) {
			//@todo: combined missing 
		} elseif(is_array($new_state)) {
			foreach($new_state as $istate) {
				if(!(isset($unprocessed[$istate]) || in_array($istate, array('#pop', '#push')))) {
					throw new Exception(sprinf('unknown new state %s', (string)$istate));
				}
			}
			return $new_state;
		} else {
			throw new Exception(sprinf('unknown new state def %s', (string)$new_state));
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
			
			$rex = $this->_process_regex($tdef[0], $rflags);
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
	
	
}