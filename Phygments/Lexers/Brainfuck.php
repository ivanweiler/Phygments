<?php
namespace Phygments\Lexers;

use \Phygments\Python\Re as re;

/**
 * Lexer for the esoteric `BrainFuck <http://www.muppetlabs.com/~breadbox/bf/>`_
 * language.
 */
class BrainfuckLexer extends Regex
{
    public $name = 'Brainfuck';
    public $aliases = ['brainfuck', 'bf'];
    public $filenames = ['*.bf', '*.b'];
    public $mimetypes = ['application/x-brainfuck'];
    
	protected function __declare()
	{
		$this->tokens = [
			'common'=> [
				// use different colors for different instruction types
				['[.,]+', 'Name.Tag'],
				['[+-]+', 'Name.Builtin'],
				['[<>]+', 'Name.Variable'],
				['[^.,+\\-<>\\[\\]]+', 'Comment'],
			],
			'root'=> [
				['\\[', 'Keyword', 'loop'],
				['\\]', 'Error'],
				$this->_include('common'),
			],
			'loop'=> [
				['\\[', 'Keyword', '#push'],
				['\\]', 'Keyword', '#pop'],
				$this->_include('common'),
			]
		];
	}
}
