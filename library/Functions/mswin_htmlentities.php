<?php

/**
* Yau Tools
*
* @author   John Yau
* @category Yau
* @package  Yau_Functions
*/

namespace Yau\Functions;

/**
* Convert applicable characters to to HTML entities, including those from Windows-1252
*
* @param  string  $str
* @param  integer $flags
* @param  string  $encoding
* @return string
* @uses   htmlentities()
* @link   http://utopia.knoware.nl/users/eprebel/Communication/CharacterSets/Windows.html
* @link   http://en.wikipedia.org/wiki/Windows-1252
* @link   http://www.unicode.org/Public/MAPPINGS/VENDORS/MICSFT/WindowsBestFit/bestfit1252.txt
*/
function mswin_htmlentities($str, $flags = NULL, $encode = NULL)
{
	return strtr($str, get_html_translation_table(HTML_ENTITIES, $flags, $encoding) + array(
		"\x85" => '&hellip;', // 133
		"\x88" => '&circ;',   // 136
		"\x89" => '&permil;', // 137
		"\x8a" => '&Scaron;', // 138
		"\x8b" => '&lsaquo;', // 139
		"\x8c" => '&OElig;',  // 140
		"\x91" => '&lsquo;',  // 145
		"\x92" => '&rsquo;',  // 146
		"\x93" => '&ldquo;',  // 147
		"\x94" => '&rdquo;',  // 148
		"\x95" => '&bull;',   // 149
		"\x96" => '&ndash;',  // 150
		"\x97" => '&mdash;',  // 151
		"\x98" => '&tilde;',  // 152
		"\x99" => '&trade;',  // 153
		"\x9a" => '&scaron;', // 154
		"\x9b" => '&rsaquo;', // 155
		"\x9c" => '&oelig;',  // 156
		"\x9f" => '&Yuml;',   // 159
		"\xa0" => '&nbsp;',   // 160
		"\xa1" => '&iexcl;',  // 161
		"\xa2" => '&cent;',   // 162
		"\xa3" => '&pound;',  // 163
		"\xa4" => '&curren;', // 164
		"\xa5" => '&yen;',    // 165
		"\xa6" => '&brvbar;', // 166
		"\xa7" => '&sect;',   // 167
		"\xa8" => '&uml;',    // 168
		"\xa9" => '&copy;',   // 169
		"\xaa" => '&ordf;',   // 170
		"\xab" => '&laquo;',  // 171
		"\xac" => '&not;',    // 172
		"\xad" => '&shy;',    // 173
		"\xae" => '&reg;',    // 174
		"\xaf" => '&macr;',   // 175
		"\xb0" => '&deg;',    // 176
		"\xb1" => '&plusmn;', // 177
		"\xb2" => '&sup2;',   // 178
		"\xb3" => '&sup3;',   // 179
		"\xb4" => '&acute;',  // 180
		"\xb5" => '&micro;',  // 181
		"\xb6" => '&para;',   // 182
		"\xb7" => '&middot;', // 183
		"\xb8" => '&cedil;',  // 184
		"\xb9" => '&sup1;',   // 185
		"\xba" => '&ordm;',   // 186
		"\xbb" => '&raquo;',  // 187
		"\xbc" => '&frac14;', // 188
		"\xbd" => '&frac12;', // 189
		"\xbe" => '&frac34;', // 190
		"\xbf" => '&iquest;', // 191
		"\xc0" => '&Agrave;', // 192
		"\xc1" => '&Aacute;', // 193
		"\xc2" => '&Acirc;',  // 194
		"\xc3" => '&Atilde;', // 195
		"\xc4" => '&Auml;',   // 196
		"\xc5" => '&Aring;',  // 197
		"\xc6" => '&AElig;',  // 198
		"\xc7" => '&Ccedil;', // 199
		"\xc8" => '&Egrave;', // 200
		"\xc9" => '&Eacute;', // 201
		"\xca" => '&Ecirc;',  // 202
		"\xcb" => '&Euml;',   // 203
		"\xcc" => '&Igrave;', // 204
		"\xcd" => '&Iacute;', // 205
		"\xce" => '&Icirc;',  // 206
		"\xcf" => '&Iuml;',   // 207
		"\xd0" => '&ETH;',    // 208
		"\xd1" => '&Ntilde;', // 209
		"\xd2" => '&Ograve;', // 210
		"\xd3" => '&Oacute;', // 211
		"\xd4" => '&Ocirc;',  // 212
		"\xd5" => '&Otilde;', // 213
		"\xd6" => '&Ouml;',   // 214
		"\xd7" => '&times;',  // 215
		"\xd8" => '&Oslash;', // 216
		"\xd9" => '&Ugrave;', // 217
		"\xda" => '&Uacute;', // 218
		"\xdb" => '&Ucirc;',  // 219
		"\xdc" => '&Uuml;',   // 220
		"\xdd" => '&Yacute;', // 221
		"\xde" => '&THORN;',  // 222
		"\xdf" => '&szlig;',  // 223
		"\xe0" => '&agrave;', // 224
		"\xe1" => '&aacute;', // 225
		"\xe2" => '&acirc;',  // 226
		"\xe3" => '&atilde;', // 227
		"\xe4" => '&auml;',   // 228
		"\xe5" => '&aring;',  // 229
		"\xe6" => '&aelig;',  // 230
		"\xe7" => '&ccedil;', // 231
		"\xe8" => '&egrave;', // 232
		"\xe9" => '&eacute;', // 233
		"\xea" => '&ecirc;',  // 234
		"\xeb" => '&euml;',   // 235
		"\xec" => '&igrave;', // 236
		"\xed" => '&iacute;', // 237
		"\xee" => '&icirc;',  // 238
		"\xef" => '&iuml;',   // 239
		"\xf0" => '&eth;',    // 240
		"\xf1" => '&ntilde;', // 241
		"\xf2" => '&ograve;', // 242
		"\xf3" => '&oacute;', // 243
		"\xf4" => '&ocirc;',  // 244
		"\xf5" => '&otilde;', // 245
		"\xf6" => '&ouml;',   // 246
		"\xf7" => '&divide;', // 247
		"\xf8" => '&oslash;', // 248
		"\xf9" => '&ugrave;', // 249
		"\xfa" => '&uacute;', // 250
		"\xfb" => '&ucirc;',  // 251
		"\xfc" => '&uuml;',   // 252
		"\xfd" => '&yacute;', // 253
		"\xfe" => '&thorn;',  // 254
		"\xff" => '&yuml;',   // 255
	));
}