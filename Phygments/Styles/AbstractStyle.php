<?php
namespace Phygments\Styles;

use \Phygments\Token;
use \Phygments\Python\Exception;

class AbstractStyle implements \IteratorAggregate, \Countable
{
	// overall background color (``None`` means transparent)
	public $background_color = '#ffffff';
	// highlight background color
	public $highlight_color = '#ffffcc';
	
	public function __construct()
	{
		// aliases to real token names
		Token::alias_to_name_keys($this->styles);
		
		foreach(array_keys(Token::$STANDARD_TYPES) as $token) {
			if(!isset($this->styles[$token])) {
				$this->styles[$token] = '';
			}
		}
		
		$this->_styles = [];
		$_styles = &$this->_styles;

		foreach(array_keys($this->styles) as $ttype) {
			foreach(Token::getToken($ttype)->split() as $token) {

				if(array_key_exists("$token", $this->_styles)) {
					continue;
				}
				
				$ndef = $token->parent ? $_styles["{$token->parent}"] : null;
				// Weiler: fix for explode of empty string not returning empty array
				$styledefs = $this->styles["$token"] ? explode(' ', $this->styles["$token"]) : [];
				
				if(!$ndef || !$token) {
					$ndef = ['', 0, 0, 0, '', '', 0, 0, 0];
				} elseif(in_array('noinherit', $styledefs) && "$token"!='Token') {
					$ndef = $_styles['Token'];
				} else {
					//$ndef = ndef[:]
					$ndef = $ndef;
				}		
				
				foreach($styledefs as $styledef) {
					
					if(!$styledef) {
						continue;
					}
					
					if($styledef == 'noinherit') {
						continue;
					} elseif($styledef == 'bold') {
						$ndef[1] = 1;
					}elseif($styledef == 'nobold') {
						$ndef[1] = 0;
					} elseif($styledef == 'italic') {
						$ndef[2] = 1;
					} elseif($styledef == 'noitalic') {
						$ndef[2] = 0;
					} elseif($styledef == 'underline') {
						$ndef[3] = 1;
					} elseif($styledef == 'nounderline') {
						$ndef[3] = 0;
					} elseif(substr($styledef, 0, 3) == 'bg:') {
						$ndef[4] = $this->colorformat(substr($styledef, 3));
					} elseif(substr($styledef, 0, 7) == 'border:') {
						$ndef[5] = $this->colorformat(substr($styledef, 7));
					} elseif($styledef == 'roman') {
						$ndef[6] = 1;
					} elseif($styledef == 'sans') {
						$ndef[7] = 1;
					} elseif($styledef == 'mono') {
						$ndef[8] = 1;
					} else {
						$ndef[0] = $this->colorformat($styledef);
					}			
				}
				
				$_styles["$token"] = $ndef;
			}
		}

	}
        
	public function style_for_token($token)
	{
        $t = $this->_styles["$token"];
        return [
            'color'=>        $t[0] ?: null,
            'bold'=>         (bool)$t[1],
            'italic'=>       (bool)$t[2],
            'underline'=>    (bool)$t[3],
            'bgcolor'=>      $t[4] ?: null,
            'border'=>       $t[5] ?: null,
            'roman'=>        $t[6] ? (bool)$t[6] : null,
            'sans'=>         $t[7] ? (bool)$t[7] : null,
            'mono'=>         $t[8] ? (bool)$t[8] : null,
        ];
	}
	
    public function styles_token($ttype)
    {
        return $this->_styles["$ttype"];
    }
    
    private function colorformat($text)
    {
    	if(isset($text[0]) && $text[0] == '#') {
    		$col = substr($text, 1);
    		if(strlen($col) == 6) {
    			return $col;
    		} elseif(strlen($col) == 3) {
    			return str_repeat($col[0],2) . str_repeat($col[1],2) . str_repeat($col[2],2);
    		}
    	} elseif($text == '') {
    		return '';
    	}

    	Exception::assert(false, sprintf("wrong color format %s", $text));
    }
    
    // Generator implements Iterator, this works
    public function getIterator()
    {
    	$generator = function() {
        	foreach(array_keys($this->_styles) as $token) {
            	yield $token => $this->style_for_token($token);
        	}
    	};
    	
    	return $generator();
    }
    
    public function count()
    {
    	return count($this->_styles);
    }
    
    public function len()
    {
    	return $this->count();
    }

}