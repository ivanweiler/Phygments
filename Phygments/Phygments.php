<?php
namespace Phygments;

class Phygments
{
	const VERSION = '0.2';
	const PYGMENTS_VERSION = '1.6';
	
	public function lex($code, $lexer)
	{
		try {
			return $lexer->get_tokens($code);
		} catch(Exception $e) {
			echo 'error';
		}
		/*
		try:
			return lexer.get_tokens(code)
		except TypeError, err:
			if isinstance(err.args[0], str) and \
			   'unbound method get_tokens' in err.args[0]:
				raise TypeError('lex() argument must be a lexer instance, '
								'not a class')
			raise
		*/
	}

	public function format($tokens, $formatter, $outfile=null)
	{
		try {
			if(!$outfile) {
				$realoutfile = 'php://output';
				ob_start();
				$formatter->format($tokens, $realoutfile);
				$out = ob_get_contents();
				ob_end_clean();
				return $out;
			} else {
				$formatter->format($tokens, $outfile);
			}
		} catch(Exception $e) {
			echo 'error';
		}
		/*
		except TypeError, err:
        if isinstance(err.args[0], str) and \
           'unbound method format' in err.args[0]:
            raise TypeError('format() argument must be a formatter instance, '
                            'not a class')
        raise
		*/
	}

	public function highlight($code, $lexer, $formatter, $outfile=null)
	{
		return self::format(self::lex($code, $lexer), $formatter, $outfile);
	}
	
// 	public function check()
// 	{
// 		return version_compare(phpversion(), '5.5.0', '>=');
// 	}
	
}
