<?php
namespace Phygments\Lexers;
class LexerMeta extends \Phygments\Lexers\Python\MetaClass
{
	/*
    This metaclass automagically converts ``analyse_text`` methods into
    static methods which always return float values.
	*/

	protected function __new__(cls, name, bases, d)
	{	
		
		
        if 'analyse_text' in d:
            d['analyse_text'] = make_analysator(d['analyse_text'])
		return parent::__new__(cls, name, bases, d);
	}

}
