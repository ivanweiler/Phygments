<?php
namespace Phygments\Lexers;
use \Phygments\Python\Re as re;

class Html extends Regex
{
	/*
    For HTML 4 and XHTML 1 markup. Nested JavaScript and CSS is highlighted
    by the appropriate lexer.
	*/

    public $name = 'HTML';
    public $aliases = ['html'];
    public $filenames = ['*.html', '*.htm', '*.xhtml', '*.xslt'];
    public $mimetypes = ['text/html', 'application/xhtml+xml'];
	
	protected function __declare()
	{
		$this->flags = [re::IGNORECASE, re::DOTALL];
		$this->tokens = [
			'root'=> [
				['[^<&]+', 'Text'],
				['&\S*?;', 'Name.Entity'],
				['\<\!\[CDATA\[.*?\]\]\>', 'Comment.Preproc'],
				['<!--', 'Comment', 'comment'],
				['<\?.*?\?>', 'Comment.Preproc'],
				['<![^>]*>', 'Comment.Preproc'],
// 				['<\s*script\s*', 'Name.Tag', ['script-content', 'tag']],
				['<\s*style\s*', 'Name.Tag', ['style-content', 'tag']],
				['<\s*[a-zA-Z0-9:]+', 'Name.Tag', 'tag'],
				['<\s*/\s*[a-zA-Z0-9:]+\s*>', 'Name.Tag'],
			],
			'comment'=> [
				['[^-]+', 'Comment'],
				['-->', 'Comment', '#pop'],
				['-', 'Comment'],
			],
			'tag'=> [
				['\s+', Text],
				['[a-zA-Z0-9_:-]+\s*=', 'Name.Attribute', 'attr'],
				['[a-zA-Z0-9_:-]+', 'Name.Attribute'],
				['/?\s*>', 'Name.Tag', '#pop'],
			],
// 			'script-content'=> [
// 				['<\s*/\s*script\s*>', 'Name.Tag', '#pop'],
// 				['.+?(?=<\s*/\s*script\s*>)', $this->_using('Javascript')],
// 			],
 			'style-content'=> [
 				['<\s*/\s*style\s*>', 'Name.Tag', '#pop'],
 				['.+?(?=<\s*/\s*style\s*>)',  $this->_using('Css')],
 			],
			'attr'=> [
				['".*?"', 'String', '#pop'],
				["'.*?'", 'String', '#pop'],
				['[^\s>]+', 'String', '#pop'],
			],
		];
	}

	public function analyse_text($text)
	{
        if(html_doctype_matches($text)) {
            return 0.5;
		}
	}
	
}
