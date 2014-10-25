<?php
namespace Phygments\Lexers;

use \Phygments\Token;
use \Phygments\Python\Re as re;
use \Phygments\Python\Exception;

class ExtendedRegex extends Regex
{	
	/**
	 * A RegexLexer that uses a context object to store its state.
	 */
	
	private static $_indentation;

	public function get_tokens_unprocessed(&$text, $stack=array('root'), $context=null)
	{
        /**
         * Split ``text`` into (tokentype, text) pairs.
         * If ``context`` is given, use this lexer context instead.
         */
        $tokendefs = &$this->_tokens;
        if(!$context) {
			$ctx = new LexerContext($text, 0);
			$statetokens = $tokendefs['root'];
        } else {
			$ctx = $context;
			$statetokens = $tokendefs[$ctx->stack[count($ctx->stack)-1]];
			$text = $ctx->text;
        }
        
		while(1) {
            $doelse = true;
            foreach($statetokens as $statetoken) {
            	list($rexmatch, $action, $new_state) = $statetoken;
            	//@todo: $ctx->end not suported
                $m = re::match($rexmatch, $text, $ctx->pos, $ctx->end);
                if($m) {
                	if($action instanceof \Phygments\_TokenType) {
                		yield [$ctx->pos, $action, $m->group()];
                		$ctx->pos = $m->end();
                	} else {
                		foreach($action($this, $m, $ctx) as $item) {
                			yield $item;
                		}
                		if(!$new_state) {
                			# altered the state stack?
                			$statetokens = $tokendefs[$ctx->stack[count($ctx->stack)-1]];
                		}
                	}
                    # CAUTION: callback must set ctx.pos!
                    if($new_state) {
                        # state transition
                        if(is_array($new_state)) {
                        	foreach($new_state as $state) {
                        		if($state == '#pop') {
                        			array_pop($ctx->stack);
                        		}elseif($state == '#push') {
                        			$ctx->stack[] = $ctx->stack[count($ctx->stack)-1];
                        		} else {
                        			$ctx->stack[] = $state;
                        		}
                        	}
						} elseif(is_int($new_state)) {
                            # pop
                            array_splice($ctx->stack, $new_state);
						} elseif($new_state == '#push') {
                            $ctx->stack[] = $ctx->stack[count($ctx->stack)-1];
						} else {
							Exception::assert(false, sprintf(
								'wrong state def: %s', print_r($new_state)
							));
						}
                        
						$statetokens = $tokendefs[$ctx->stack[count($ctx->stack)-1]];
                    }
                    
                    $doelse = false;
                    break;                    
				}
            }
            
            if($doelse) {
  				if($ctx->pos >= $ctx->end) {
  					break;
  				}
            	if(!isset($text[$ctx->pos])) {
            		break;
            	}
            	if($text[$ctx->pos] == "\n") {
            		# at EOL, reset state to "root"
            		$ctx->stack = ['root'];
            		$statetokens = $tokendefs['root'];
            		yield [$ctx->pos, Token::getToken('Text'), "\n"];
            		$ctx->pos += 1;
            		continue;
            	}
            	yield [$ctx->pos, Token::getToken('Error'), $text[$ctx->pos]];
            	$ctx->pos += 1;
            }
		}
		
	}
	
	private function _indentation()
	{
		if(is_null(self::$_indentation)) {
			
			self::$_indentation = function($match, $ctx) {
			
				$indentation = $match->group(0);
				yield [$match->start(), Token::getToken('Text'), $indentation];
				$ctx->last_indentation = $indentation;
				$ctx->pos = $match->end();
			
				if(property_exists($ctx, 'block_state') && $ctx->block_state &&
						substr($indentation, 0, strlen($ctx->block_indentation)) == $ctx->block_indentation &&
						$indentation != $ctx->block_indentation) {
							$ctx->stack[] = $ctx->block_state;
						} else {
							$ctx->block_state = null;
							$ctx->block_indentation = null;
							$ctx->stack[] = 'content';
						}
			
			};
				
		}
		
		return self::_indentation;
	}
	
	private function _starts_block($lexer, $token, $state)
	{
		$callback = function($lexer, $match, $ctx) use ($token, $state) {
			//Weiler: check if Token::getToken needed?
			yield [$match->start(), Token::getToken($token), $match->group(0)];

			if(property_exists($ctx, 'last_indentation')) {
				$ctx->block_indentation = $ctx->last_indentation;
			} else {
				$ctx->block_indentation = '';
			}
			$ctx->block_state = $state;
			$ctx->pos = $match->end();
		};
		
		return $callback;	
	}	
	
}
