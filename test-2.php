<?php
//phpinfo();

$matches = array();
$m = preg_match('/(\w+)((\d*)| )(\w+)/','The Cat in the Hat.', $matches, PREG_OFFSET_CAPTURE);

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
