<?php
namespace Phygments\Lexers;

use \Phygments\Token;

class Delegating extends AbstractLexer
{
    /*
    This lexer takes two lexer as arguments. A root lexer and
    a language lexer. First everything is scanned using the language
    lexer, afterwards all ``Other`` tokens are lexed using the root
    lexer.

    The lexers from the ``template`` lexer package use this base lexer.
    */

    public function __construct($_root_lexer, $_language_lexer, $_needle='Other', $options=array())
    {
        $this->root_lexer = new $_root_lexer($options);
        $this->language_lexer = new $_language_lexer($options);
        $this->needle = Token::alias_to_name($_needle); 
        parent::__construct($options);
    }
    
    public function get_tokens_unprocessed(&$text)
    {
        $buffered = '';
        $insertions = [];
        $lng_buffer = [];
        
        foreach($this->language_lexer->get_tokens_unprocessed($text) as $tokenu) {
        	list($i, $t, $v) = $tokenu;
        	if("$t" == $this->needle) {
        		if($lng_buffer) {
        			$insertions[] = [strlen($buffered), $lng_buffer];
        			$lng_buffer = [];
        		}
        		$buffered .= $v;
        	} else {
        		$lng_buffer[] = $tokenu;
        	}
        }
        
        if($lng_buffer) {
        	$insertions[] = [strlen($buffered), $lng_buffer];
        }

        return $this->do_insertions($insertions,
        			$this->root_lexer->get_tokens_unprocessed($buffered));
    }
}