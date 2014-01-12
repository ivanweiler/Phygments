<?php
namespace Phygments\Lexers;
//use \Phygments\Util;
use \Phygments\Token;
//use \Phygments\Python\Re as re;

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
        
        $_needle = Token::getToken($_needle);
        $this->needle = get_class($_needle);
        
        parent::__construct($options);
    }
    
    public function get_tokens_unprocessed(&$text)
    {
        $buffered = '';
        $insertions = [];
        $lng_buffer = [];
        
        foreach($this->language_lexer->get_tokens_unprocessed($text) as $tokenu)
        {
        	list($i, $t, $v) = $tokenu;
        	if(get_class($t) == $this->needle) {
        		if($lng_buffer) {
        			$insertions[] = [strlen($buffered), $lng_buffer];
        		}
        		$buffered += $v;
        	} else {
        		$lng_buffer[] = $tokenu;
        	}
        }
        
        if($lng_buffer) {
        	$insertions[] = [strlen($buffered), $lng_buffer];
        }
        
        return $this->do_insertions($insertions,
        			$this->root_lexer->get_tokens_unprocessed($buffered));
        
        /*
        for i, t, v in self.language_lexer.get_tokens_unprocessed(text):
            if t is self.needle:
                if lng_buffer:
                    insertions.append((len(buffered), lng_buffer))
                    lng_buffer = []
                buffered += v
            else:
                lng_buffer.append((i, t, v))
        if lng_buffer:
            insertions.append((len(buffered), lng_buffer))
        return do_insertions(insertions,self.root_lexer.get_tokens_unprocessed(buffered))
        */
    }
}