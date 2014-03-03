<?php

//bootstrap
define('PHYGMENTS_PATH', dirname(__FILE__));

//register include path & autoload
set_include_path(PHYGMENTS_PATH . PATH_SEPARATOR . get_include_path());
spl_autoload_register(function($class) {
	require str_replace(array('_','\\'), DIRECTORY_SEPARATOR, $class) . '.php';
});
//

$code = <<<'CODE'
<b><?php echo "test 123"; ?></b>
<?php 
$i = 8;
#I am comment!
?>
<br />
CODE;

$lexer = new \Phygments\Lexers\HtmlPhp();

$formater1 = new \Phygments\Formatters\RawToken();
$formater2 = new \Phygments\Formatters\Html(array(
	'noclasses' =>	true,
	'style'		=>	'monokai'
));

echo \Phygments\Phygments::highlight($code, $lexer, $formater2); 
return;

echo '<pre>';
echo htmlspecialchars(\Phygments\Phygments::highlight($code, $lexer, $formater1));


function token_dump($array) {
	array_walk_recursive($array, function(&$item){
		if($item instanceof \Phygments\_TokenType) $item = "$item";
	});
	var_dump($array);
}


