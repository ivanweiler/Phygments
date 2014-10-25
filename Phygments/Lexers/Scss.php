<?php
namespace Phygments\Lexers;

use \Phygments\Python\Re as re;

class Scss extends Sass
{
	/**
	 * For SCSS stylesheets.
	 */

    public $name = 'SCSS';
    public $aliases = ['scss'];
    public $filenames = ['*.scss'];
    public $mimetypes = ['text/x-scss'];
    
    protected function __declare()
    {
    	$this->flags = [re::IGNORECASE, re::DOTALL];
    	$this->tokens = [	
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
    	
		foreach($this->common_sass_tokens() as $group => $common) {
			$this->tokens[$group] = $common; //copy.copy(common) //array_merge??
		}
		$this->tokens['value'][] = ['\\n', 'Text'];
		$this->tokens['value'][] = ['[;{}]', 'Punctuation', 'root'];
		$this->tokens['selector'][] = ['\\n', 'Text'];
		$this->tokens['selector'][] = ['[;{}]', 'Punctuation', 'root'];
    	
    }
    
    
}