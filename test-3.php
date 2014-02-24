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
<b><div class="lol">test 123</div></b>
<script>
	var a = 1337;
</script>
<style>
* { border: 0; margin: 0; }
#lol { color: #fff; }
</style>
CODE;

$code2 = <<<'CODE'
var a = 1337;
b = function(){ console.log(123); };
CODE;

$code3 = <<<'CODE'
$i = 8;
if($i) {
	echo 123;
}
foreach(array('lol') as $x) { $x++; }
CODE;


$lexer1 = new \Phygments\Lexers\Html();
$lexer3 = new \Phygments\Lexers\Php(array('_startinline'=>true));

//$formater1 = new \Phygments\Formatters\RawToken();
$formater2 = new \Phygments\Formatters\Html(array(
	'noclasses' => true,
));

//echo '<pre>';
//echo htmlspecialchars(Phygments::highlight($code, $lexer, $formater));
echo \Phygments\Phygments::highlight($code3, $lexer3, $formater2);






