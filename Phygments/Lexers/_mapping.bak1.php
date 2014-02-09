<?php
/*
    phygments.lexers._mapping
    ~~~~~~~~~~~~~~~~~~~~~~~~

    Lexer mapping defintions. This file is generated by itself. Everytime
    you change something on a builtin lexer defintion, run this script from
    the lexers folder to update it.

    Do not alter the LEXERS dictionary by hand.

    :copyright: Copyright 2006-2013 by the Pygments team, see AUTHORS.
	:copyright: Copyright 2013-2014 by the Phygments team, see AUTHORS.
    :license: BSD, see LICENSE for details.
*/

$LEXERS = [
	'HtmlLexer'	=> ['HTML', ['html',], ['*.html', '*.htm', '*.xhtml', '*.xslt'], ['text/html', 'application/xhtml+xml']],
    'HtmlPhpLexer' => ['HTML+PHP', ['html+php',], ['*.phtml',], ['application/x-php', 'application/x-httpd-php', 'application/x-httpd-php3', 'application/x-httpd-php4', 'application/x-httpd-php5']],
];
