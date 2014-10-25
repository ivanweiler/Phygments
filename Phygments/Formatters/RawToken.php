<?php
namespace Phygments\Formatters;

/**
 *  Format tokens as a raw representation for storing token streams.
 *
 */
class RawToken extends AbstractFormatter
{
    public $name = 'Raw tokens';
    public $aliases = ['raw', 'tokens'];
    public $filenames = ['*.raw'];

    public $unicodeoutput = false;

	public function __construct($options=array())
	{
        parent::__construct($options);
	}
	
    public function format($tokensource, $outfile)
    {
    	$data = '';
    	foreach($tokensource as $ttype => $value) {
    		$value = str_replace(array("\r","\n","\t","\v"), array('\r','\n','\t','\v'), $value);
    		$data .= sprintf("%s\t'%s'\n", $ttype, $value);
    	}

    	file_put_contents($outfile, $data); 
    }
}