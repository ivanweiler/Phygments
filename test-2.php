<?php
//phpinfo();


class A
{
	public $lol = 'a';
}

class B extends A
{
	public $lol = 'b';
}


class C extends B
{
	public $lol = 'c';
	
	public function __construct()
	{
		$this->test();
	}
	
	public function test()
	{
		
	}
	
}


$test = new C();






return;

$matches = array();

//pos>0, with [-1]
$m = preg_match('#\A(?:.|\n){1}Lorem.*#m', "XLorem ipsum sit amet", $matches);

//pos=0, no last char
//$m = preg_match('#\A^Lorem.*#m', "Lorem ipsum sit amet", $matches, PREG_OFFSET_CAPTURE, 0);

echo '<pre>';
var_dump($m);
var_dump($matches);

return;

$matches = array();
$m = preg_match('/^^(\w+)((\d*)| )(\w+)/','The Cat in the Hat.', $matches, PREG_OFFSET_CAPTURE);

echo '<pre>';
var_dump($m);
var_dump($matches);

return;

function test($start, $limit, $step = 1) {
    if ($start < $limit) {
        for ($i = $start; $i <= $limit; $i += $step) { echo 123;
            yield $i;
        }
    }
}

$lol = test(1, 9);
$lol2 = $lol;

var_dump(get_class($lol));

return;

foreach (test(1, 9) as $key=>$number) {
    //echo "$number ";
	var_dump($number);
}
