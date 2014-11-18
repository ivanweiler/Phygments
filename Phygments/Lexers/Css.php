<?php
namespace Phygments\Lexers;

use \Phygments\Python\Re as re;

/**
 * For CSS (Cascading Style Sheets).
 */
class Css extends Regex
{
	public $name = 'CSS';
	public $aliases = ['css'];
	public $filenames = ['*.css'];
	public $mimetypes = ['text/css'];

	protected function tokendefs()
	{
		return [
			'root'=> [
				$this->_include('basics'),
			],
			'basics'=> [
				['\\s+', 'Text'],
				['/\\*(?:.|\\n)*?\\*/', 'Comment'],
				['{', 'Punctuation', 'content'],
				['\\:[a-zA-Z0-9_-]+', 'Name.Decorator'],
				['\\.[a-zA-Z0-9_-]+', 'Name.Class'],
				['#[a-zA-Z0-9_-]+', 'Name.Function'],	//mod #?
				['@[a-zA-Z0-9_-]+', 'Keyword', 'atrule'],
				['[a-zA-Z0-9_-]+', 'Name.Tag'],
				['[~\\^\\*!%&\\[\\]\\(\\)<>\\|+=@:;,./?-]', 'Operator'],
				['"(\\\\\\\\|\\\\"|[^"])*"', 'String.Double'],
				["'(\\\\\\\\|\\\\'|[^'])*'", 'String.Single']
			],
			'atrule'=> [
				['{', 'Punctuation', 'atcontent'],
				[';', 'Punctuation', '#pop'],
				$this->_include('basics'),
			],
			'atcontent'=> [
				$this->_include('basics'),
				['}', 'Punctuation', '#pop:2'],
			],
			'content'=> [
				['\\s+', 'Text'],
				['}', 'Punctuation', '#pop'],
				['url\\(.*?\\)', 'String.Other'],
				['^@.*?$', 'Comment.Preproc'],
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
				 'outline-style|outline-width|overflow(?:-x|-y)?|padding-bottom|'.
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
				 'x-low|x-small|x-soft|xx-large|xx-small|yes)\\b', 'Keyword'],
				 
				['(indigo|gold|firebrick|indianred|yellow|darkolivegreen|'.
				 'darkseagreen|mediumvioletred|mediumorchid|chartreuse|'.
				 'mediumslateblue|black|springgreen|crimson|lightsalmon|brown|'.
				 'turquoise|olivedrab|cyan|silver|skyblue|gray|darkturquoise|'.
				 'goldenrod|darkgreen|darkviolet|darkgray|lightpink|teal|'.
				 'darkmagenta|lightgoldenrodyellow|lavender|yellowgreen|thistle|'.
				 'violet|navy|orchid|blue|ghostwhite|honeydew|cornflowerblue|'.
				 'darkblue|darkkhaki|mediumpurple|cornsilk|red|bisque|slategray|'.
				 'darkcyan|khaki|wheat|deepskyblue|darkred|steelblue|aliceblue|'.
				 'gainsboro|mediumturquoise|floralwhite|coral|purple|lightgrey|'.
				 'lightcyan|darksalmon|beige|azure|lightsteelblue|oldlace|'.
				 'greenyellow|royalblue|lightseagreen|mistyrose|sienna|'.
				 'lightcoral|orangered|navajowhite|lime|palegreen|burlywood|'.
				 'seashell|mediumspringgreen|fuchsia|papayawhip|blanchedalmond|'.
				 'peru|aquamarine|white|darkslategray|ivory|dodgerblue|'.
				 'lemonchiffon|chocolate|orange|forestgreen|slateblue|olive|'.
				 'mintcream|antiquewhite|darkorange|cadetblue|moccasin|'.
				 'limegreen|saddlebrown|darkslateblue|lightskyblue|deeppink|'.
				 'plum|aqua|darkgoldenrod|maroon|sandybrown|magenta|tan|'.
				 'rosybrown|pink|lightblue|palevioletred|mediumseagreen|'.
				 'dimgray|powderblue|seagreen|snow|mediumblue|midnightblue|'.
				 'paleturquoise|palegoldenrod|whitesmoke|darkorchid|salmon|'.
				 'lightslategray|lawngreen|lightgreen|tomato|hotpink|'.
				 'lightyellow|lavenderblush|linen|mediumaquamarine|green|'.
				 'blueviolet|peachpuff)\\b', 'Name.Builtin'],
				['\\!important', 'Comment.Preproc'],
				['/\\*(?:.|\\n)*?\\*/', 'Comment'],
				['#[a-zA-Z0-9]{1,6}', 'Number'],	//mod #?
				['[\\.-]?[0-9]*[\\.]?[0-9]+(em|px|\\%|pt|pc|in|mm|cm|ex|s)\\b', 'Number'],
				['-?[0-9]+', 'Number'],
				['[~\\^\\*!%&<>\\|+=@:,./?-]+', 'Operator'],
				['[\\[\\]();]+', 'Punctuation'],
				['"(\\\\\\\\|\\\\"|[^"])*"', 'String.Double'],
				["'(\\\\\\\\|\\\\'|[^'])*'", 'String.Single'],
				['[a-zA-Z_][a-zA-Z0-9_]*', 'Name']
			]
		];
	}
}
