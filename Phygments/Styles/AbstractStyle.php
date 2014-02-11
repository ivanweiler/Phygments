<?php
namespace Phygments\Styles;
use \Phygments\Token;

class AbstractStyle implements \IteratorAggregate, \Countable
{
	#: overall background color (``None`` means transparent)
	public $background_color = '#ffffff';
	#: highlight background color
	public $highlight_color = '#ffffcc';
	
	public function __construct()
	{
		//aliases to real token names
		$dummy = array();
		foreach($this->styles as $token => $value) {
			$dummy[Token::alias_to_name($token)] = $value;
		}
		$this->styles = $dummy;
		
		foreach(array_keys(Token::$STANDARD_TYPES) as $token) {
			if(!isset($this->styles[$token])) {
				$this->styles[$token] = '';
			}
		}
		
		//var_dump($this->styles);
        
		$this->_styles = [];
		$_styles = &$this->_styles;
		
		/*
        for ttype in obj.styles:
            for token in ttype.split():
                if token in _styles:
                    continue
                ndef = _styles.get(token.parent, None)
                styledefs = obj.styles.get(token, '').split()
                if  not ndef or token is None:
                    ndef = ['', 0, 0, 0, '', '', 0, 0, 0]
                elif 'noinherit' in styledefs and token is not Token:
                    ndef = _styles[Token][:]
                else:
                    ndef = ndef[:]
                _styles[token] = ndef
		*/
		foreach(array_keys($this->styles) as $ttype) {
			$parent = '';
			foreach(explode('.', $ttype) as $token) {
				
				if(array_key_exists($token, $this->_styles)) {
					continue;
				}
				
				$ndef = $_styles[$parent] ?: null;
				$styledefs = explode(' ', $this->styles["$token"]);
				
				$parent .= ($parent ? ".$token" : $token);
				
				if(!$ndef || !$token) {
					$ndef = ['', 0, 0, 0, '', '', 0, 0, 0];
				} elseif(in_array('noinherit', $styledefs) && $token!='Token') {
					$ndef = $_styles['Token'];
				} else {
					//$ndef = ndef[:]
					$ndef = $ndef;
				}
				
				$_styles[$token] = $ndef;
				
				foreach($styledefs as $styledef) {
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
					} elseif( styledef == 'sans') {
						$ndef[7] = 1;
					} elseif($styledef == 'mono') {
						$ndef[8] = 1;
					} else {
						$ndef[0] = $this->colorformat($styledef);
					}			
				}
			}
		}
		
		//var_dump($this->_styles);
	}
        
	public function style_for_token($token)
	{
        $t = $this->_styles["$token"];
        return [
            'color'=>        isset($t[0]) ?: null,
            'bold'=>         (bool)$t[1],
            'italic'=>       (bool)$t[2],
            'underline'=>    (bool)$t[3],
            'bgcolor'=>      isset($t[4]) ?: null,
            'border'=>       isset($t[5]) ?: null,
            'roman'=>        isset($t[6]) ? (bool)$t[6] : null,
            'sans'=>         isset($t[7]) ? (bool)$t[7] : null,
            'mono'=>         isset($t[8]) ? (bool)$t[8] : null,
        ];
	}

	/*
    public function list_styles()
    {
        //return list(cls) //?? property list?
    }
    */
	
    public function styles_token($ttype)
    {
        //return ttype in cls._styles
        return $this->_styles["$ttype"];
    }
    
    private function colorformat($text)
    {
    	if($text[0] == '#') {
    		$col = substr($text, 1);
    		if(strlen($col) == 6) {
    			return $col;
    		} elseif(strlen($col) == 3) {
    			return str_repeat($col[0],2) . str_repeat($col[1],2) . str_repeat($col[2],2);
    		}
    	} elseif($text == '') {
    		return '';
    	}

    	//assert False, "wrong color format %r" % text
    }
    
    /*
    def __iter__(cls):
        for token in cls._styles:
            yield token, cls.style_for_token(token)
                  

    def __len__(cls):
        return len(cls._styles)
	*/		
    
    //will this work? Generator implements Iterator, it should
    public function getIterator()
    {
    	//return new \ArrayIterator($this->_styles);
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
       
}