<?php
/*
Simple:Press
Plain Text Editor plugin content filters
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# ----------------------------------------------
# Prtepare content for an edit action
# ----------------------------------------------
function sp_editor_prepare_edit_content($content, $editor) {
	return $content;
}

# ----------------------------------------------
# Save Filter - Parse for codetags
# ----------------------------------------------
function sp_editor_parse_codetags($content, $editor) {
	if ($editor == PLAINTEXT) $content = sp_Raw2Html(' '.$content);
	return $content;
}

# ----------------------------------------------
# Save Filter - Save codetags and callback
# ----------------------------------------------
function sp_editor_save_codetags($content, $editor) {
	return $content;
}

# ----------------------------------------------
# Save Filter - Save linebreaks filter
# ----------------------------------------------
function sp_editor_save_linebreaks($content, $editor) {
	if ($editor == PLAINTEXT) $content = sp_filter_save_linebreaks($content);
	return $content;
}

# ----------------------------------------------
# Edit Filter - Prepare p and br tags for edit
# ----------------------------------------------
function sp_editor_format_paragraphs_edit($content, $editor) {
	return $content;
}

# ----------------------------------------------
# Edit Filter - Parse html - to raw text
# ----------------------------------------------
function sp_editor_parse_for_edit($content, $editor) {
	if ($editor == PLAINTEXT) $content = sp_Html2Raw($content);
	return $content;
}

# ----------------------------------------------
# Parsers: PLAINTEXT/Raw
# ----------------------------------------------
function sp_Raw2Html($text) {
	$text = trim($text);
	if (!function_exists('rawtohtml_escape')) {
		function rawtohtml_escape($s) {
			global $text;
			return '<code>'.htmlspecialchars($s[1], ENT_QUOTES, SFCHARSET).'</code>';
		}
	}
	$text = preg_replace_callback('/\<code\>(.*?)\<\/code\>/ms', "rawtohtml_escape", $text);
	return $text;
}

function sp_Html2Raw($text) {
	$text = trim($text);
	$text = str_replace ("\n\n", "\n", $text);
	$text = str_replace ('<div class="sfcode">', "<code>", $text);
	$text = str_replace ('</div>', "</code>", $text);

	# BBCode [code]
	if (!function_exists('rawescape')) {
		function rawescape($s) {
			global $text;
			return '<code>'.htmlspecialchars_decode($s[1]).'</code>';
		}
	}
	$text = preg_replace_callback('/\<code\>(.*?)\<\/code\>/ms', "rawescape", $text);
	return $text;
}

?>