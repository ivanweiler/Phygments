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
}

class Token
{
	public static $STANDARD_TYPES = array(
		'Token'					=> '',

		'Text'					=> '',
		'Whitespace'			=> 'w',
		'Error'					=> 'err',
		'Other'					=> 'x',
		//..
	);
	
	private static $_token = null;
	private static $_aliases = array();
	
    private function __construct()
    {
    }
	
    private static function __declare()
    {
		//declare main types and aliases
		
		$alias = array();
		
		self::$_token = new _TokenType('Token');

		# Special token types
		$alias['Text']			= self::$_token->Text;
		$alias['Whitespace']	= self::$_token->Text->Whitespace;
		$alias['Error']			= self::$_token->Token->Error;
		# Text that doesn't belong to this lexer (e.g. HTML in PHP)
		$alias['Other']			= self::$_token->Other;

		# Common token types for source code
		$alias['Keyword']		= self::$_token->Keyword;
		$alias['Name']			= self::$_token->Token->Name;
		$alias['Literal']		= self::$_token->Literal;
		$alias['String']		= $alias['Literal']->String;
		$alias['Number']		= $alias['Literal']->Number;
		$alias['Punctuation']	= self::$_token->Punctuation;
		$alias['Operator']		= self::$_token->Operator;
		$alias['Comment']		= self::$_token->Comment;

		# Generic types for non-source code
		$alias['Generic']		= self::$_token->Generic;

		# String and some others are not direct childs of Token.
		# alias them:
		$alias['Token.Token']	= self::$_token;
		$alias['Token.String']	= $alias['String'];
		$alias['Token.Number']	= $alias['Number'];
		
		self::$_aliases = $alias;
    }
	
    public static function getToken($type=null)
    {
        if (null === self::$_token) {
			self::__declare();
        }
		
		if(!$type) {
			return self::$_token;
		}
		
		if(isset(self::$_aliases[$type])) {
			return self::$_aliases[$type];
		}
	
		$names = explode('.', $type);
		$class = self::$_token;
		foreach($names as $name) {
			$class = $class->{$name};
		}
		return $class;
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
		
		/*
		if(!$s) {
			return Token::getToken(null);
		}
		
		$node = Token::getType(null);
		foreach(explode('.', $s) as $item) {
			$node = $node->{$item};
		}

		return $node;
		*/
	}
}
