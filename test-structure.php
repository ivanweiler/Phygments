<?php

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 1);

abstract class A
{

	public function test() {
		$this->tokens();
	}
	
	public function tokens() {
		echo 'A';
	}
		
}

class B extends A
{
	public function tokens() {
		echo 'B';
	}
}


class C extends B
{
	public function test() {
		parent::test();
	}
	
	public function tokens() {
		echo 'C';
	}
		
}

echo '<pre>';
$c = new C();
$c->test();


exit;


class Re
{
	const IGNORECASE = 'i';
	const MULTILINE = 'm';
	const DOTALL = 's';
}

use Re as re;


class AbstractLexer
{
	public function get_tokens($text, $unfiltered=false)
	{

	}
	
	public function get_tokens_unprocessed(&$text, $stack=array('root'))
	{
		
	}
}

class Regex extends AbstractLexer
{
	protected $flags = re::MULTILINE;
	protected $tokens = [];
	protected $token_variants = false;
	
	public function __construct($options=array())
	{
		$this->__declare();
	}
	
	public function get_tokendefs()
	{
		
	}
}

class Sass extends Regex
{
	public $name = 'Sass';
	public $aliases = ['sass', 'SASS'];
	public $filenames = ['*.sass'];
	public $mimetypes = ['text/x-sass'];
	
	protected $flags = [re::IGNORECASE];

	protected function __declare()
	{
		$this->tokens = [];
		
		self::$tokens = [];
	}
	
	public function get_tokendefs()
	{
		$this->tokens = ['a','b'];
	}	

}

class Scss extends Sass
{
	public $name = 'SCSS';
	public $aliases = ['scss'];
	public $filenames = ['*.scss'];
	public $mimetypes = ['text/x-scss'];
	
	protected $flags = [re::IGNORECASE, re::DOTALL];

	protected function __declare()
	{
		$this->tokens = [];
		//self::$tokens = [];
	}
	
	protected function _get_tokens() {
		return ['c','d'];
	}
	
	public function get_tokendefs()
	{
		//$tokens = ['c','d'];
		
		$this->tokens = parent::get_tokendefs();
		//$this->tokens is set now
		
		//merge this tokens with parent tokens
		$this->merge_tokendefs(parent::get_tokens(), $this->tokens);
		
		//current tokens
		//parent->get_tokendefs(current tokens)
	}
	
	function get_parent_tokens() {
		return parent::$tokens;
		//return parent::_get_tokens();
	}
	
	//pipe, every class needs to have it
	protected function get_tokens_chain() {
		//return array_merge([self::$tokens], parent::get_tokens_chain());
		
		$parent = new parent();
		$parent->tokens;
	}
	
}

echo '<pre>';
var_dump( (new Scss) );
exit;


$initial = memory_get_usage();
$a = str_repeat('w', 1024*1024);
var_dump( memory_get_usage()/1024 );
test1($a);
//var_dump( memory_get_usage() - $initial );

function test1( $string ) {
	var_dump( memory_get_usage()/1024 );
	$b = $string;
	var_dump( memory_get_usage()/1024 );
}




