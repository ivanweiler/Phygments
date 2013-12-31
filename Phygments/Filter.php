<?php
namespace Phygments;
class Filter
{
	
	public static function apply_filters($stream, $filters, $lexer=null)
	{
		/*
	    Use this method to apply an iterable of filters to
	    a stream. If lexer is given it's forwarded to the
	    filter, otherwise the filter receives `None`.
	    */	
		$_apply = function($filter_, $stream) use ($lexer) {
			foreach($filter_['filter']($lexer, $stream) as $token) {
				//yield $token;
			}
	
		};
		
		foreach($filters as $filter_) {
			$stream = $_apply($filter_, $stream);
		}
		
		return $stream;
	}
	
	public static function simplefilter($f)
	{
		/*
	    Decorator that converts a function into a filter
		*/
		return new FunctionFilter($f);
	}
	    		
	    
	
}