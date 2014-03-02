<?php
namespace Phygments\Formatters;
class Null extends AbstractFormatter
{
	public $name = 'Text only';
	public $aliases = ['text', 'null'];
	public $filenames = ['*.txt'];	
	
	public function format($tokensource, $outfile)
	{
    	$data = '';
    	foreach($tokensource as $ttype => $value) {
			$data .= $value;
    	}

    	file_put_contents($outfile, $data);		
	}
		
}