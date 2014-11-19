<?php

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 1);

class _Inherit
{
	public function __toString()
	{
		return 'inherit';
	}
}

function _inherit()
{
	return new _Inherit();
}


$tokendefs = array(
	array(
		'group1' => array('g1','g1', _inherit()),
		'group2' => array('g2','g2'),
	),
	array(
		'group1' => array('g1b','g1c'),
		'group3' => array('g3','g3'),
	),
);

$tokens = [];
$inheritable = [];

foreach($tokendefs as $toks) {

	foreach($toks as $state => $items) {

		//$curitems = $tokens[$state];

		if(!isset($tokens[$state])) {
			$tokens[$state] = $items;
				
			$inherit_ndx = array_search(_inherit(), $items, false);
			if(!$inherit_ndx) {
				continue;
			}
				
			$inheritable[$state] = $inherit_ndx;
			continue;
		}

		if(isset($inheritable[$state])) {
			$inherit_ndx = $inheritable[$state];
			unset($inheritable[$state]);
		} else {
			continue;
		}

		//Replace the "inherit" value with the items
		array_splice($tokens[$state], $inherit_ndx, 1, $items);
		//curitems[inherit_ndx:inherit_ndx+1] = items

		$new_inh_ndx = array_search(_inherit(), $items, false);
		if($new_inh_ndx) {
			$inheritable[$state] = $inherit_ndx + $new_inh_ndx;
		}

	}
		
}

var_dump($tokens);


