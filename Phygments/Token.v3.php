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
	    'Token'=>                         '',
	
	    'Text'=>                          '',
	    'Whitespace'=>                    'w',
	    'Error'=>                         'err',
	    'Other'=>                         'x',
		
		'Keyword'=>                       'k',
		'Keyword.Constant'=>              'kc',
		'Keyword.Declaration'=>           'kd',
		'Keyword.Namespace'=>             'kn',
		'Keyword.Pseudo'=>                'kp',
		'Keyword.Reserved'=>              'kr',
		'Keyword.Type'=>                  'kt',
		
		'Name'=>                          'n',
		'Name.Attribute'=>                'na',
		'Name.Builtin'=>                  'nb',
		'Name.Builtin.Pseudo'=>           'bp',
		'Name.Class'=>                    'nc',
		'Name.Constant'=>                 'no',
		'Name.Decorator'=>                'nd',
		'Name.Entity'=>                   'ni',
		'Name.Exception'=>                'ne',
		'Name.Function'=>                 'nf',
		'Name.Property'=>                 'py',
		'Name.Label'=>                    'nl',
		'Name.Namespace'=>                'nn',
		'Name.Other'=>                    'nx',
		'Name.Tag'=>                      'nt',
		'Name.Variable'=>                 'nv',
		'Name.Variable.Class'=>           'vc',
		'Name.Variable.Global'=>          'vg',
		'Name.Variable.Instance'=>        'vi',
		
		'Literal'=>                       'l',
		'Literal.Date'=>                  'ld',
		
		'String'=>                        's',
		'String.Backtick'=>               'sb',
		'String.Char'=>                   'sc',
		'String.Doc'=>                    'sd',
		'String.Double'=>                 's2',
		'String.Escape'=>                 'se',
		'String.Heredoc'=>                'sh',
		'String.Interpol'=>               'si',
		'String.Other'=>                  'sx',
		'String.Regex'=>                  'sr',
		'String.Single'=>                 's1',
		'String.Symbol'=>                 'ss',
		
		'Number'=>                        'm',
		'Number.Float'=>                  'mf',
		'Number.Hex'=>                    'mh',
		'Number.Integer'=>                'mi',
		'Number.Integer.Long'=>           'il',
		'Number.Oct'=>                    'mo',
		
		'Operator'=>                      'o',
		'Operator.Word'=>                 'ow',
		
		'Punctuation'=>                   'p',
		
		'Comment'=>                       'c',
		'Comment.Multiline'=>             'cm',
		'Comment.Preproc'=>               'cp',
		'Comment.Single'=>                'c1',
		'Comment.Special'=>               'cs',
		
		'Generic'=>                       'g',
		'Generic.Deleted'=>               'gd',
		'Generic.Emph'=>                  'ge',
		'Generic.Error'=>                 'gr',
		'Generic.Heading'=>               'gh',
		'Generic.Inserted'=>              'gi',
		'Generic.Output'=>                'go',
		'Generic.Prompt'=>                'gp',
		'Generic.Strong'=>                'gs',
		'Generic.Subheading'=>            'gu',
		'Generic.Traceback'=>             'gt',
	);
	
	//public static $Token, $Text;
	
    private function __construct()
    {
    }
	
    public static function __declare()
    {
    	if (!is_null(self::$Token)) {
    		return;
    	}
    	
		//declare main types and aliases
		$Token = new _TokenType('Token');
		self::$Token = $Token;
		
		# Special token types
		self::$Text				= $Token->Text;
		self::$Whitespace		= $Token->Text->Whitespace;
		self::$Error			= $Token->Error;
		# Text that doesn't belong to this lexer (e.g. HTML in PHP)
		self::$Other			= $Token->Other;

		# Common token types for source code
		self::$Keyword			= $Token->Keyword;
		self::$Name				= $Token->Name;
		self::$Literal			= $Token->Literal;
		self::$String			= $Token->Literal->String;
		self::$Number			= $Token->Literal->Number;
		self::$Punctuation		= $Token->Punctuation;
		self::$Operator			= $Token->Operator;
		self::$Comment			= $Token->Comment;

		# Generic types for non-source code
		self::$Generic			= $Token->Generic;

		# String and some others are not direct childs of Token.
		# alias them:
		self::$Token->Token		= self::$Token;
		self::$Token->String	= self::$String;
		self::$Token->Number	= self::$Number;

		
		//aliases to real token names in $STANDARD_TYPES
		alias_to_name_keys(self::$STANDARD_TYPES);
		
    }
	
    public static function getToken($type=null)
    {
		self::__declare();
		
		if(!$type) {
			return self::$Token;
		}
	
		//'Token->Literal->String' => self::$Token->Literal->String
		//'String.Lol' =>self::$String->Lol
		
		$names = explode('.', $type);
		
		$object = self::$$array_shift($names);
		foreach($names as $name) {
			$object = $object->{$name};
		}
		return $object;
    }
    
    //alias to real name (ex. String.Escape => Token.Literal.String.Escape)
    public static function alias_to_name($type)
    {
		return self::getToken($type)->__toString();
    }
    
    public static function alias_to_name_keys(&$array, $return=false)
    {
    	$dummy = array();
    	foreach($array as $alias => $value) {
    		$dummy[self::alias_to_name($alias)] = $value;
    	}
    	
    	if($return) {
    		return $dummy;
    	} else {
    		$array = $dummy;
    	}   	
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

//initiate static variables
Token::__declare();

/*
	Token::getToken();
	Token::$Literal->String
*/

