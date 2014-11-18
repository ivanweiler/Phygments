<?php
namespace Phygments\Lexers;

use \Phygments\Python\Re as re;

/**
 * For SCSS stylesheets.
 */
class Scss extends Sass
{
	public $name = 'SCSS';
	public $aliases = ['scss'];
	public $filenames = ['*.scss'];
	public $mimetypes = ['text/x-scss'];
	
	protected $flags = [re::IGNORECASE, re::DOTALL];

	protected function tokendefs()
	{
    	$tokendefs = [
			'root'=> [
				['\\s+', 'Text'],
				['//.*?\\n', 'Comment.Single'],
				['/\\*.*?\\*/', 'Comment.Multiline'],
				['@import', 'Keyword', 'value'],
				['@for', 'Keyword', 'for'],
				['@(debug|warn|if|while)', 'Keyword', 'value'],
				['(@mixin)( [\\w-]+)', $this->_bygroups('Keyword', 'Name.Function'), 'value'],
				['(@include)( [\\w-]+)', $this->_bygroups('Keyword', 'Name.Decorator'), 'value'],
				['@extend', 'Keyword', 'selector'],
				['@[a-z0-9_-]+', 'Keyword', 'selector'],
				['(\$[\\w-]*\\w)([ \\t]*:)', $this->_bygroups('Name.Variable', 'Operator'), 'value'],
				['(?=[^;{}][;}])', 'Name.Attribute', 'attr'],
				['(?=[^;{}:]+:[^a-z])', 'Name.Attribute', 'attr'],
				['', 'Text', 'selector'],
			],

			'attr'=> [
				['[^\\s:="\\[]+', 'Name.Attribute'],
				['#{', 'String.Interpol', 'interpolation'],
				['[ \\t]*:', 'Operator', 'value'],
			],

			'inline-comment'=> [
				["(\\\\#|#(?=[^{])|\\*(?=[^/])|[^#*])+", 'Comment.Multiline'],
				['#\\{', 'String.Interpol', 'interpolation'],
				["\\*/", 'Comment', '#pop'],
			],
		
		];
		
		//@todo: not needed here, will be inherited?
		$tokendefs = array_merge($tokendefs, $this->common_sass_tokens());

		array_push($tokendefs['value'], ['\\n', 'Text'], ['[;{}]', 'Punctuation', 'root']);
		array_push($tokendefs['selector'], ['\\n', 'Text'], ['[;{}]', 'Punctuation', 'root']);
		
		return $this->inherit_tokendefs($tokendefs, parent::tokendefs());
	}
}