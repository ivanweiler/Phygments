<?php

//bootstrap
define('PHYGMENTS_PATH', dirname(__FILE__));

//register include path & autoload
set_include_path(PHYGMENTS_PATH . PATH_SEPARATOR . get_include_path());
spl_autoload_register(function($class) {
	require str_replace(array('_','\\'), DIRECTORY_SEPARATOR, $class) . '.php';
	/*
	if (false !== $pos = strrpos($class, '\\')) {
		// namespaced class name
		$classPath = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, 0, $pos)).DIRECTORY_SEPARATOR;
		$className = substr($class, $pos + 1);
	} else {
		// PEAR-like class name
		$classPath = null;
		$className = $class;
	}

	$classPath .= str_replace('_', DIRECTORY_SEPARATOR, $className).'.php';	
	
	require $classPath;
	*/
});
//

/*
echo '<pre>';
$style = new \Phygments\Styles\Def();
return;
*/

$code = <<<'CODE'
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
foreach(array('lol') as $x) $x++;
CODE;


/*
$token = \Phygments\Token::getToken('Name.Tag');
var_dump((string)$token);
var_dump($token);
die();
*/

use \Phygments\Phygments;

//$lexer = new \Phygments\Lexers\Php(array('_startinline'=>true));
$lexer = new \Phygments\Lexers\Html();
//$generator = $lexer->get_tokens($code);

//$formater = new \Phygments\Formatters\RawToken();
$formater = new \Phygments\Formatters\Html(array(
	'noclasses' => true,
));
//echo $formater->format($generator);

//echo '<pre>';
//echo htmlspecialchars(Phygments::highlight($code, $lexer, $formater));
echo Phygments::highlight($code, $lexer, $formater);

/*
foreach($generator as $token => $value) {
	//echo (string)$token[1] . '  ' . htmlspecialchars($token[2]). '<br />';
	echo $token . '  ' . htmlspecialchars($value). '<br />';
}
*/

/*
$lexer = get_lexer_by_name("python", array('stripall'=>true));
$formatter = new HtmlFormatter(array('linenos'=>true, 'cssclass'=>"source"));
$result = highlight($code, $lexer, $formatter);

echo $result;
*/


