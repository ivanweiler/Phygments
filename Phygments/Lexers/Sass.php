<?php
namespace Phygments\Lexers;

use \Phygments\Python\Re as re;

class Sass extends ExtendedRegex
{
	/**
	 * For Sass stylesheets.
	 */

    public $name = 'Sass';
    public $aliases = ['sass', 'SASS'];
    public $filenames = ['*.sass'];
    public $mimetypes = ['text/x-sass'];
    
    protected function __declare()
    {
    	$this->flags = [re::IGNORECASE];
    	$this->tokens = [
			'root'=> [
				['[ \\t]*\\n', 'Text'],
				['[ \\t]*', $this->_indentation()],
			],

			'content'=> [
				['//[^\\n]*', $this->_starts_block('Comment.Single', 'single-comment'),
				 'root'],
				['/\\*[^\\n]*', $this->_starts_block('Comment.Multiline', 'multi-comment'),
				 'root'],
				['@import', 'Keyword', 'import'],
				['@for', 'Keyword', 'for'],
				['@(debug|warn|if|while)', 'Keyword', 'value'],
				['(@mixin)( [\\w-]+)', $this->_bygroups('Keyword', 'Name.Function'), 'value'],
				['(@include)( [\\w-]+)', $this->_bygroups('Keyword', 'Name.Decorator'), 'value'],
				['@extend', 'Keyword', 'selector'],
				['@[a-z0-9_-]+', 'Keyword', 'selector'],
				['=[\\w-]+', 'Name.Function', 'value'],
				['\\+[\\w-]+', 'Name.Decorator', 'value'],
				['([!$][\\w-]\\w*)([ \\t]*(?:(?:\\|\\|)?=|:)]',
				 $this->_bygroups('Name.Variable', 'Operator'), 'value'],
				[':', 'Name.Attribute', 'old-style-attr'],
				['(?=.+?[=:]([^a-z]|$))', 'Name.Attribute', 'new-style-attr'],
				['', 'Text', 'selector'],
			],

			'single-comment'=> [
				['.+', 'Comment.Single'],
				['\\n', 'Text', 'root'],
			],

			'multi-comment'=> [
				['.+', 'Comment.Multiline'],
				['\\n', 'Text', 'root'],
			],

			'import'=> [
				['[ \\t]+', 'Text'],
				['\\S+', 'String'],
				['\\n', 'Text', 'root'],
			],

			'old-style-attr'=> [
				['[^\\s:="\\[]+', 'Name.Attribute'],
				['#{', 'String.Interpol', 'interpolation'],
				['[ \\t]*=', 'Operator', 'value'],
				['', 'Text', 'value'],
			],

			'new-style-attr'=> [
				['[^\\s:="\\[]+', 'Name.Attribute'],
				['#{', 'String.Interpol', 'interpolation'],
				['[ \\t]*[=:]', 'Operator', 'value'],
			],

			'inline-comment'=> [
				["(\\\\#|#(?=[^\\n{]]|\\*(?=[^\\n/])|[^\\n#*])+", 'Comment.Multiline'],
				['#\\{', 'String.Interpol', 'interpolation'],
				["\\*/", 'Comment', '#pop'],
			],    		
		];
    	
    	foreach($this->common_sass_tokens() as $group => $common) {
    		$this->tokens[$group] = $common; //copy.copy(common) //array_merge??
    	}
    	$this->tokens['value'][] = ['\\n', 'Text', 'root'];
    	$this->tokens['selector'][] = ['\\n', 'Text', 'root'];    	
    	
    }
    
    
    public function common_sass_tokens()
    {
		return [
			'value'=> [
				['[ \\t]+', 'Text'],
				['[!$][\\w-]+', 'Name.Variable'],
				['url\\(', 'String.Other', 'string-url'],
				['[a-z_-][\\w-]*(?=\\()', 'Name.Function'],
				['(azimuth|background-attachment|background-color|'.
				 'background-image|background-position|background-repeat|'.
				 'background|border-bottom-color|border-bottom-style|'.
				 'border-bottom-width|border-left-color|border-left-style|'.
				 'border-left-width|border-right|border-right-color|'.
				 'border-right-style|border-right-width|border-top-color|'.
				 'border-top-style|border-top-width|border-bottom|'.
				 'border-collapse|border-left|border-width|border-color|'.
				 'border-spacing|border-style|border-top|border|caption-side|'.
				 'clear|clip|color|content|counter-increment|counter-reset|'.
				 'cue-after|cue-before|cue|cursor|direction|display|'.
				 'elevation|empty-cells|float|font-family|font-size|'.
				 'font-size-adjust|font-stretch|font-style|font-variant|'.
				 'font-weight|font|height|letter-spacing|line-height|'.
				 'list-style-type|list-style-image|list-style-position|'.
				 'list-style|margin-bottom|margin-left|margin-right|'.
				 'margin-top|margin|marker-offset|marks|max-height|max-width|'.
				 'min-height|min-width|opacity|orphans|outline|outline-color|'.
				 'outline-style|outline-width|overflow|padding-bottom|'.
				 'padding-left|padding-right|padding-top|padding|page|'.
				 'page-break-after|page-break-before|page-break-inside|'.
				 'pause-after|pause-before|pause|pitch|pitch-range|'.
				 'play-during|position|quotes|richness|right|size|'.
				 'speak-header|speak-numeral|speak-punctuation|speak|'.
				 'speech-rate|stress|table-layout|text-align|text-decoration|'.
				 'text-indent|text-shadow|text-transform|top|unicode-bidi|'.
				 'vertical-align|visibility|voice-family|volume|white-space|'.
				 'widows|width|word-spacing|z-index|bottom|left|'.
				 'above|absolute|always|armenian|aural|auto|avoid|baseline|'.
				 'behind|below|bidi-override|blink|block|bold|bolder|both|'.
				 'capitalize|center-left|center-right|center|circle|'.
				 'cjk-ideographic|close-quote|collapse|condensed|continuous|'.
				 'crop|crosshair|cross|cursive|dashed|decimal-leading-zero|'.
				 'decimal|default|digits|disc|dotted|double|e-resize|embed|'.
				 'extra-condensed|extra-expanded|expanded|fantasy|far-left|'.
				 'far-right|faster|fast|fixed|georgian|groove|hebrew|help|'.
				 'hidden|hide|higher|high|hiragana-iroha|hiragana|icon|'.
				 'inherit|inline-table|inline|inset|inside|invert|italic|'.
				 'justify|katakana-iroha|katakana|landscape|larger|large|'.
				 'left-side|leftwards|level|lighter|line-through|list-item|'.
				 'loud|lower-alpha|lower-greek|lower-roman|lowercase|ltr|'.
				 'lower|low|medium|message-box|middle|mix|monospace|'.
				 'n-resize|narrower|ne-resize|no-close-quote|no-open-quote|'.
				 'no-repeat|none|normal|nowrap|nw-resize|oblique|once|'.
				 'open-quote|outset|outside|overline|pointer|portrait|px|'.
				 'relative|repeat-x|repeat-y|repeat|rgb|ridge|right-side|'.
				 'rightwards|s-resize|sans-serif|scroll|se-resize|'.
				 'semi-condensed|semi-expanded|separate|serif|show|silent|'.
				 'slow|slower|small-caps|small-caption|smaller|soft|solid|'.
				 'spell-out|square|static|status-bar|super|sw-resize|'.
				 'table-caption|table-cell|table-column|table-column-group|'.
				 'table-footer-group|table-header-group|table-row|'.
				 'table-row-group|text|text-bottom|text-top|thick|thin|'.
				 'transparent|ultra-condensed|ultra-expanded|underline|'.
				 'upper-alpha|upper-latin|upper-roman|uppercase|url|'.
				 'visible|w-resize|wait|wider|x-fast|x-high|x-large|x-loud|'.
				 'x-low|x-small|x-soft|xx-large|xx-small|yes)\b', 'Name.Constant'],
				['(indigo|gold|firebrick|indianred|darkolivegreen|'.
				 'darkseagreen|mediumvioletred|mediumorchid|chartreuse|'.
				 'mediumslateblue|springgreen|crimson|lightsalmon|brown|'.
				 'turquoise|olivedrab|cyan|skyblue|darkturquoise|'.
				 'goldenrod|darkgreen|darkviolet|darkgray|lightpink|'.
				 'darkmagenta|lightgoldenrodyellow|lavender|yellowgreen|thistle|'.
				 'violet|orchid|ghostwhite|honeydew|cornflowerblue|'.
				 'darkblue|darkkhaki|mediumpurple|cornsilk|bisque|slategray|'.
				 'darkcyan|khaki|wheat|deepskyblue|darkred|steelblue|aliceblue|'.
				 'gainsboro|mediumturquoise|floralwhite|coral|lightgrey|'.
				 'lightcyan|darksalmon|beige|azure|lightsteelblue|oldlace|'.
				 'greenyellow|royalblue|lightseagreen|mistyrose|sienna|'.
				 'lightcoral|orangered|navajowhite|palegreen|burlywood|'.
				 'seashell|mediumspringgreen|papayawhip|blanchedalmond|'.
				 'peru|aquamarine|darkslategray|ivory|dodgerblue|'.
				 'lemonchiffon|chocolate|orange|forestgreen|slateblue|'.
				 'mintcream|antiquewhite|darkorange|cadetblue|moccasin|'.
				 'limegreen|saddlebrown|darkslateblue|lightskyblue|deeppink|'.
				 'plum|darkgoldenrod|sandybrown|magenta|tan|'.
				 'rosybrown|pink|lightblue|palevioletred|mediumseagreen|'.
				 'dimgray|powderblue|seagreen|snow|mediumblue|midnightblue|'.
				 'paleturquoise|palegoldenrod|whitesmoke|darkorchid|salmon|'.
				 'lightslategray|lawngreen|lightgreen|tomato|hotpink|'.
				 'lightyellow|lavenderblush|linen|mediumaquamarine|'.
				 'blueviolet|peachpuff)\b', 'Name.Entity'],
				['(black|silver|gray|white|maroon|red|purple|fuchsia|green|'.
				 'lime|olive|yellow|navy|blue|teal|aqua)\b', 'Name.Builtin'],
				['\\!(important|default)', 'Name.Exception'],
				['(true|false)', 'Name.Pseudo'],
				['(and|or|not)', 'Operator.Word'],
				['/\\*', 'Comment.Multiline', 'inline-comment'],
				['//[^\\n]*', 'Comment.Single'],
				['\\#[a-z0-9]{1,6}', 'Number.Hex'],
				['(-?\\d+)(\\%|[a-z]+)?', $this->_bygroups('Number.Integer', 'Keyword.Type')],
				['(-?\\d*\\.\\d+)(\%|[a-z]+)?', $this->_bygroups('Number.Float', 'Keyword.Type')],
				['#{', 'String.Interpol', 'interpolation'],
				['[~\\^\\*!&%<>\\|+=@:,./?-]+', 'Operator'],
				['[\\[\\]()]+', 'Punctuation'],
				['"', 'String.Double', 'string-double'],
				["'", 'String.Single', 'string-single'],
				['[a-z_-][\\w-]*', 'Name'],
			],

			'interpolation'=> [
				['\\}', 'String.Interpol', '#pop'],
				$this->_include('value'),
			],

			'selector'=> [
				['[ \\t]+', 'Text'],
				['\\:', 'Name.Decorator', 'pseudo-class'],
				['\\.', 'Name.Class', 'class'],
				['\\#', 'Name.Namespace', 'id'],
				['[a-zA-Z0-9_-]+', 'Name.Tag'],
				['#\\{', 'String.Interpol', 'interpolation'],
				['&', 'Keyword'],
				['[~\\^\\*!&\\[\\]\\(\\)<>\\|+=@:;,./?-]', 'Operator'],
				['"', 'String.Double', 'string-double'],
				["'", 'String.Single', 'string-single'],
			],

			'string-double'=> [
				['(\\\\.|#(?=[^\\n{])|[^\\n"#])+', 'String.Double'],
				['#\\{', 'String.Interpol', 'interpolation'],
				['"', 'String.Double', '#pop'],
			],

			'string-single'=> [
				["(\\\\.|#(?=[^\\n{])|[^\\n'#])+", 'String.Double'],
				['#\\{', 'String.Interpol', 'interpolation'],
				["'", 'String.Double', '#pop'],
			],

			'string-url'=> [
				['(\\\\#|#(?=[^\\n{])|[^\\n#)])+', 'String.Other'],
				['#\\{', 'String.Interpol', 'interpolation'],
				['\\)', 'String.Other', '#pop'],
			],

			'pseudo-class'=> [
				['[\\w-]+', 'Name.Decorator'],
				['#\\{', 'String.Interpol', 'interpolation'],
				['', 'Text', '#pop'],
			],

			'class'=> [
				['[\\w-]+', 'Name.Class'],
				['#\\{', 'String.Interpol', 'interpolation'],
				['', 'Text', '#pop'],
			],

			'id'=> [
				['[\\w-]+', 'Name.Namespace'],
				['#\\{', 'String.Interpol', 'interpolation'],
				['', 'Text', '#pop'],
			],

			'for'=> [
				['(from|to|through)', 'Operator.Word'],
				$this->_include('value'),
			], 
		];
    }
    
    
}
