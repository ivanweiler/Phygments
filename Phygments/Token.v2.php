<?php
namespace Phygments;
/* 
Singleton Token object, parent of token types, tree object

Token::getToken();
Token::getToken('Text.Whitespace');
Token::getToken()->Text->Whitespace
*/

class _TokenType
{
	public $name;
	public $parent;
	public $subtypes = array();
	
	public function __construct($name, $parent=null)
	{
		$this->name = $name;
		$this->parent = $parent;
	}
	
    public function __get($name)
    {
		$this->{$name} = new self($name, $this);
		$this->subtypes[] = $name;
		return $this->{$name};
    }
	
	public function __toString()
	{
		$name = $this->name;
		$parent = $this->parent;
		while($parent) {
			$name = $parent->name . '.' . $name;
			$parent = $parent->parent;
		}
		return $name;
	}
	
	public function split()
	{
		$buf = [];
		$node = $this;
		while($node) {
			$buf[] = $node;
			$node = $node->parent;
		}
		$buf = array_reverse($buf);
		return $buf;		
	}
}

class Token
{
	public static $STANDARD_TYPES = array(
	    'Token'=>								'',
	
	    'Token.Text'=>                          '',
	    'Token.Text.Whitespace'=>				'w',
	    'Token.Error'=>                         'err',
	    'Token.Other'=>                         'x',
		
		'Token.Keyword'=>                       'k',
		'Token.Keyword.Constant'=>              'kc',
		'Token.Keyword.Declaration'=>           'kd',
		'Token.Keyword.Namespace'=>             'kn',
		'Token.Keyword.Pseudo'=>                'kp',
		'Token.Keyword.Reserved'=>              'kr',
		'Token.Keyword.Type'=>                  'kt',
		
		'Token.Name'=>                          'n',
		'Token.Name.Attribute'=>                'na',
		'Token.Name.Builtin'=>                  'nb',
		'Token.Name.Builtin.Pseudo'=>           'bp',
		'Token.Name.Class'=>                    'nc',
		'Token.Name.Constant'=>                 'no',
		'Token.Name.Decorator'=>                'nd',
		'Token.Name.Entity'=>                   'ni',
		'Token.Name.Exception'=>                'ne',
		'Token.Name.Function'=>                 'nf',
		'Token.Name.Property'=>                 'py',
		'Token.Name.Label'=>                    'nl',
		'Token.Name.Namespace'=>				'nn',
		'Token.Name.Other'=>					'nx',
		'Token.Name.Tag'=>						'nt',
		'Token.Name.Variable'=>					'nv',
		'Token.Name.Variable.Class'=>			'vc',
		'Token.Name.Variable.Global'=>			'vg',
		'Token.Name.Variable.Instance'=>		'vi',
		
		'Token.Literal'=>						'l',
		'Token.Literal.Date'=>					'ld',
		
		'Token.Literal.String'=>'s',
		'Token.Literal.String.Backtick'=>		'sb',
		'Token.Literal.String.Char'=>			'sc',
		'Token.Literal.String.Doc'=>			'sd',
		'Token.Literal.String.Double'=>			's2',
		'Token.Literal.String.Escape'=>			'se',
		'Token.Literal.String.Heredoc'=>		'sh',
		'Token.Literal.String.Interpol'=>		'si',
		'Token.Literal.String.Other'=>			'sx',
		'Token.Literal.String.Regex'=>			'sr',
		'Token.Literal.String.Single'=>			's1',
		'Token.Literal.String.Symbol'=>			'ss',
		
		'Token.Literal.Number'=>				'm',
		'Token.Literal.Number.Float'=>			'mf',
		'Token.Literal.Number.Hex'=>			'mh',
		'Token.Literal.Number.Integer'=>		'mi',
		'Token.Literal.Number.Integer.Long'=>	'il',
		'Token.Literal.Number.Oct'=>			'mo',
		
		'Token.Operator'=>                      'o',
		'Token.Operator.Word'=>                 'ow',
		
		'Token.Punctuation'=>                   'p',
		
		'Token.Comment'=>                       'c',
		'Token.Comment.Multiline'=>             'cm',
		'Token.Comment.Preproc'=>               'cp',
		'Token.Comment.Single'=>                'c1',
		'Token.Comment.Special'=>               'cs',
		
		'Token.Generic'=>                       'g',
		'Token.Generic.Deleted'=>               'gd',
		'Token.Generic.Emph'=>                  'ge',
		'Token.Generic.Error'=>                 'gr',
		'Token.Generic.Heading'=>               'gh',
		'Token.Generic.Inserted'=>              'gi',
		'Token.Generic.Output'=>                'go',
		'Token.Generic.Prompt'=>                'gp',
		'Token.Generic.Strong'=>                'gs',
		'Token.Generic.Subheading'=>            'gu',
		'Token.Generic.Traceback'=>             'gt',
	);
	
	private static $_token = null;
	
	# Special token types
	private static $_aliases = array(
		'Whitespace'	=> 'Text.Whitespace',
		'String'		=> 'Literal.String',
		'Number'		=> 'Literal.Number'		
	);
	
    private function __construct()
    {
    }
	
    private static function __declare()
    {
		# declare main types and aliases
		
    	# Root Token
    	//self::$_token = new _TokenType('Token');
    	
    	# Special token types
    }
	
    public static function getToken($type=null)
    {
        if (null === self::$_token) {
			//self::__declare();
        	# Root Token
			self::$_token = new _TokenType('Token');
        }
		
        if (substr($type, 0, 6) == 'Token.') {
        	$type = substr($type, 6);
        }        
        
		if(!$type || $type=='Token') {
			return self::$_token;
		}
		
		//var_dump($type);
		$type = self::alias_to_name($type);
		//var_dump($type);
		
		$names = explode('.', $type);
		$class = self::$_token;
		foreach($names as $name) {
			$class = $class->{$name};
		}
		return $class;
    }
	
    //alias to real name (ex. String.Escape => Token.Literal.String.Escape)
    public static function alias_to_name($type, $short=false)
    {
		if (substr($type, 0, 6) == 'Token.') {
			$type = substr($type, 6);
        }
           
    	$names = explode('.', $type);
    	$type = '';
    	foreach($names as $key => $part) {
    		$type .= ($key ? ".$part" : $part);
    		if(isset(self::$_aliases[$type])) {
    			$type = self::$_aliases[$type];
    		}
    	}
    	    	
    	return "Token.$type";
    }
    
	public static function is_token_subtype($ttype, $other)
	{
		//return ttype in other
		//return property_exists($other, $ttype);
		return isset($other->subtypes[$ttype]);
	}
	
	public static function string_to_tokentype($s)
	{
		if($s instanceof \Phygments\_TokenType) {
			return s;
		}
		
		return Token::getToken($s);
	}
}
