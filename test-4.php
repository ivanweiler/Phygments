<?php

//bootstrap
define('PHYGMENTS_PATH', dirname(__FILE__));

//register include path & autoload
set_include_path(PHYGMENTS_PATH . PATH_SEPARATOR . get_include_path());
spl_autoload_register(function($class) {
	require str_replace(array('_','\\'), DIRECTORY_SEPARATOR, $class) . '.php';
});
//

$code1 = <<<'CODE'
<b><?php echo "test 123"; ?></b>
<?php 
$i = 8;
#I am comment!
?>
<br />
CODE;

$code2 = <<<'CODE'
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


$lexer1 = new \Phygments\Lexers\HtmlPhp();
$lexer2 = new \Phygments\Lexers\Scss();

$formater1 = new \Phygments\Formatters\RawToken();
$formater2 = new \Phygments\Formatters\Html(array(
	//'noclasses' =>	true,
	'full'	=> true,
	'title'	=> 'My code'
	//'style'		=>	'Github'
));

echo \Phygments\Phygments::highlight($code1, $lexer1, $formater2);
return;

echo '<pre>';
echo htmlspecialchars(\Phygments\Phygments::highlight($code, $lexer, $formater1));


function token_dump($array) {
	array_walk_recursive($array, function(&$item){
		if($item instanceof \Phygments\_TokenType) $item = "$item";
	});
	var_dump($array);
}


