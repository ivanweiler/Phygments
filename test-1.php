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


$code = <<<'CODE'
<b><span class="lol">test 123</span></b>
CODE;

$code2 = <<<'CODE'
* { border: 0; margin: 0; }
#lol { color: #fff; }
CODE;

/*
$token = \Phygments\Token::getToken('Name.Tag');
var_dump((string)$token);
var_dump($token);
die();
*/

use \Phygments\Phygments;

$lexer = new \Phygments\Lexers\Css();
//$generator = $lexer->get_tokens($code);


$formater = new \Phygments\Formatters\RawToken();
//echo $formater->format($generator);

echo '<pre>';
echo htmlspecialchars(Phygments::highlight($code2, $lexer, $formater));

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


