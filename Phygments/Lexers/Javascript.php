<?php
namespace Phygments\Lexers;
use \Phygments\Python\Re as re;

class Javascript extends Regex
{
	/*
    For JavaScript source code.
	*/

    public $name = 'JavaScript';
    public $aliases = ['js', 'javascript'];
    public $filenames = ['*.js'];
    public $mimetypes = ['application/javascript', 'application/x-javascript',
				'text/x-javascript', 'text/javascript'];
	
	protected function __declare()
	{
		$this->flags = [re::DOTALL];
		$this->tokens = [
			'commentsandwhitespace'=> [
				['\s+', 'Text'],
				['<!--', 'Comment'],
				['//.*?\n', 'Comment.Single'],
				['/\*.*?\*/', 'Comment.Multiline']
			],
			'slashstartsregex'=> [
				$this->_include('commentsandwhitespace'),
				['/(\\.|[^[/\\\n]|\[(\\.|[^\]\\\n])*])+/'.
				 '([gim]+\b|\B)', 'String.Regex', '#pop'],
				['(?=/)', 'Text', ['#pop', 'badregex']],
				['', 'Text', '#pop']
			],
			'badregex'=> [
				['\n', 'Text', '#pop']
			],
			'root'=> [
				['^(?=\s|/|<!--)', 'Text', 'slashstartsregex'],
				$this->_include('commentsandwhitespace'),
				['\+\+|--|~|&&|\?|:|\|\||\\\\(?=\n)|'.   //fixed \\ => \\\\
				 '(<<|>>>?|==?|!=?|[-<>+*%&\|\^/])=?', 'Operator', 'slashstartsregex'],
				['[{(\[;,]', 'Punctuation', 'slashstartsregex'],
				['[})\].]', 'Punctuation'],
				['(for|in|while|do|break|return|continue|switch|case|default|if|else|'.
				 'throw|try|catch|finally|new|delete|typeof|instanceof|void|'.
				 'this)\b', 'Keyword', 'slashstartsregex'],
				['(var|let|with|function)\b', 'Keyword.Declaration', 'slashstartsregex'],
				['(abstract|boolean|byte|char|class|const|debugger|double|enum|export|'.
				 'extends|final|float|goto|implements|import|int|interface|long|native|'.
				 'package|private|protected|public|short|static|super|synchronized|throws|'.
				 'transient|volatile)\b', 'Keyword.Reserved'],
				['(true|false|null|NaN|Infinity|undefined)\b', 'Keyword.Constant'],
				['(Array|Boolean|Date|Error|Function|Math|netscape|'.
				 'Number|Object|Packages|RegExp|String|sun|decodeURI|'.
				 'decodeURIComponent|encodeURI|encodeURIComponent|'.
				 'Error|eval|isFinite|isNaN|parseFloat|parseInt|document|this|'.
				 'window)\b', 'Name.Builtin'],
				['[$a-zA-Z_][a-zA-Z0-9_]*', 'Name.Other'],
				['[0-9][0-9]*\.[0-9]+([eE][0-9]+)?[fd]?', 'Number.Float'],
				['0x[0-9a-fA-F]+', 'Number.Hex'],
				['[0-9]+', 'Number.Integer'],
				['"(\\\\|\\"|[^"])*"', 'String.Double'],
				["'(\\\\|\\'|[^'])*'", 'String.Single'],
			]
		];
	}
}
