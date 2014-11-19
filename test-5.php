<?php

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 1);

//bootstrap
define('PHYGMENTS_PATH', dirname(__FILE__));

//register include path & autoload
set_include_path(PHYGMENTS_PATH . PATH_SEPARATOR . get_include_path());
spl_autoload_register(function($class) {
	require str_replace(array('_','\\'), DIRECTORY_SEPARATOR, $class) . '.php';
});
//

$code = <<<'CODE'
@mixin font-size ($value) {
	font-size: $value + px;
	font-size: $value / $main-font-size + rem;
}

@mixin transform($property: none) {
	-webkit-transform: $property;
	-moz-transform: $property;
	-ms-transform: $property;
	-o-transform: $property;
	transform: $property;
}

@mixin transition($transition-property, $transition-time, $method) {
	-webkit-transition: $transition-property $transition-time $method;
	-moz-transition: $transition-property $transition-time $method;
	-ms-transition: $transition-property $transition-time $method;
	-o-transition: $transition-property $transition-time $method;
	transition: $transition-property $transition-time $method;
}
CODE;

$lexer = new \Phygments\Lexers\Scss();

//$formater1 = new \Phygments\Formatters\RawToken();
$formater = new \Phygments\Formatters\Html(array(
	//'noclasses' =>	true,
	'full'		=> true,
	'title'		=> 'My Test Code',
	//'cssfile'	=> 'http://localho.st/default.css',
	//'cssclass'	=> 'codehilite',
	//'style'		=>	'Github'
));

echo \Phygments\Phygments::highlight($code, $lexer, $formater);

