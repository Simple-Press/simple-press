<?php

/**
 * Core class used for generic filters.
 *
 * @since 6.0
 *
 * Public methods available:
 * ------------------------
 *
 * $LastChangedDate: 2018-08-15 07:59:04 -0500 (Wed, 15 Aug 2018) $
 * $Rev: 15704 $
 */
class spcFilters {
	public function pre($content) {
		# remove pre tags
		$content = str_replace('<pre>', '', $content);
		$content = str_replace('</pre>', '', $content);

		$content = str_replace('&lt;pre&gt;', '', $content);
		$content = str_replace('&lt;/pre&gt;', '', $content);

		return $content;
	}

	public function table_prefix($content) {
		$long  = array(SP_PREFIX.'commentmeta', SP_PREFIX.'comments', SP_PREFIX.'options', SP_PREFIX.'postmeta', SP_PREFIX.'posts', SP_PREFIX.'terms', SP_PREFIX.'term_taxonomy', SP_PREFIX.'term_relationships', SP_PREFIX.'users', SP_PREFIX.'usermeta', SP_PREFIX.'sfauths', SP_PREFIX.'sfgroups', SP_PREFIX.'sfforums', SP_PREFIX.'sftopics', SP_PREFIX.'sfposts', SP_PREFIX.'sfwaiting', SP_PREFIX.'sftrack', SP_PREFIX.'sfusergroups', SP_PREFIX.'sfpermissions', SP_PREFIX.'sfdefpermissions', SP_PREFIX.'sfroles', SP_PREFIX.'sfmembers', SP_PREFIX.'sfmemberships', SP_PREFIX.'sfmeta', SP_PREFIX.'sflog', SP_PREFIX.'sfoptions');
		$short = array('commentmeta', 'comments', 'options', 'postmeta', 'posts', 'terms', 'term_taxonomy', 'term_relationships', 'users', 'usermeta', 'sfauths', 'sfgroups', 'sfforums', 'sftopics', 'sfposts', 'sfwaiting', 'sftrack', 'sfusergroups', 'sfpermissions', 'sfdefpermissions', 'sfroles', 'sfmembers', 'sfmemberships', 'sfmeta', 'sflog', 'sfoptions');

		return str_ireplace($long, $short, $content);
	}

	public function regex($str) {
		$patterns = array('/\//', '/\^/', '/\./', '/\$/', '/\|/', '/\(/', '/\)/', '/\[/', '/\]/', '/\*/', '/\+/', '/\?/', '/\{/', '/\}/', '/\,/');
		$replace  = array('\/', '\^', '\.', '\$', '\|', '\(', '\)', '\[', '\]', '\*', '\+', '\?', '\{', '\}', '\,');

		return addslashes(preg_replace($patterns, $replace, $str));
	}

	public function integer($checkval) {
		$actual = '';
		if (isset($checkval)) {
			if (is_numeric($checkval)) $actual = $checkval;
			$checklen = strlen(strval($actual));
			if ($checklen != strlen($checkval)) die(SP()->primitives->front_text('A Suspect Request has been Rejected'));
		}

		return $actual;
	}

	public function str($string) {
		$string = $this->esc_sql($string);
		$string = wp_kses($string, array());

		return $string;
	}
	
	public function filename($string) {
		
		# 1. Remove slashes.
		$string = str_replace ( '/' , "", $string);
		
		#2. Remove backslashes (note use of double back-slash - str_replace needs it as an escape mechanism since back-slash has special meaning for it.)
		$string = str_replace ( '\\' , "", $string);
		
		#3. Run it through the wp file sanitization function to remove everything else
		$string = sanitize_file_name($string);
	
		return $string;
	}

	public function esc_sql($string) {
		if (is_array($string)) {
			for($x = 0; $x < count($string); $x++) {
				$string[$x] = $this->do_esc_sql($string[$x]);
			}
		} else {
			$string = $this->do_esc_sql($string);
		}
		
		return $string;
	}
	
	private function do_esc_sql($string) {
		global $wpdb;
		return mysqli_real_escape_string($wpdb->dbh, $string);
	}
	
	public function url($string) {
		return filter_var($string, FILTER_SANITIZE_URL);
	}

	public function parse_inline_bbcode($content) {
		$content = trim($content);

		# BBCode to find...
		$in = array('/\[b\](.*?)\[\/b\]/ms', '/\[i\](.*?)\[\/i\]/ms', '/\[u\](.*?)\[\/u\]/ms', '/\[left\](.*?)\[\/left\]/ms', '/\[right\](.*?)\[\/right\]/ms', '/\[center\](.*?)\[\/center\]/ms', '/\[img\](.*?)\[\/img\]/ms', '/\[url\="?(.*?)"?\](.*?)\[\/url\]/is', '/\[url\="?(.*?)"?\](.*?)\]/is', '/\[url\](.*?)\[\/url\]/is', '/\[quote\](.*?)\[\/quote\]/ms', '/\[quote\="?(.*?)"?\](.*?)\[\/quote\]/ms', '/\[list\=(.*?)\](.*?)\[\/list\]/ms', '/\[list\](.*?)\[\/list\]/ms', '/\[B\](.*?)\[\/B\]/ms', '/\[I\](.*?)\[\/I\]/ms', '/\[U\](.*?)\[\/U\]/ms', '/\[LEFT\](.*?)\[\/LEFT\]/ms', '/\[RIGHT\](.*?)\[\/RIGHT\]/ms', '/\[CENTER\](.*?)\[\/CENTER\]/ms', '/\[IMG\](.*?)\[\/IMG\]/ms', '/\[COLOR=(.*?)](.*?)\[\/COLOR]/is', '/\[URL\="?(.*?)"?\](.*?)\[\/URL\]/is', '/\[QUOTE\](.*?)\[\/QUOTE\]/ms', '/\[QUOTE\="?(.*?)"?\](.*?)\[\/QUOTE\]/ms', '/\[LIST\=(.*?)\](.*?)\[\/LIST\]/ms', '/\[LIST\](.*?)\[\/LIST\]/ms', '/\[\*\]\s?(.*?)\n/ms');

		# And replace them by...
		$out     = array('<strong>\1</strong>', '<em>\1</em>', '<u>\1</u>', '<div style="text-align:left">\1</div>', '<div style="text-align:right">\1</div>', '<div style="text-align:center">\1</div>', '<img src="\1" alt="\1" />', '<a href="\1">\2</a>', '<a href="\1">\2</a>', '<a href="\1">\2</a>', '<blockquote>\1</blockquote>', '<blockquote>\1 said:<br />\2</blockquote>', '<ol start="\1">\2</ol>', '<ul>\1</ul>', '<strong>\1</strong>', '<em>\1</em>', '<u>\1</u>', '<div style="text-align:left">\1</div>', '<div style="text-align:right">\1</div>', '<div style="text-align:center">\1</div>', '<img src="\1" alt="\1" />', '<span style="color: \1">\2</span>', '<a href="\1">\2</a>', '<blockquote>\1</blockquote>', '<blockquote>\1 said:<br />\2</blockquote>', '<ol start="\1">\2</ol>', '<ul>\1</ul>', '<li>\1</li>');
		$content = preg_replace($in, $out, $content);

		# special case for nested quotes
		$content = str_replace('[quote]', '<blockquote>', $content);
		$content = str_replace('[/quote]', '</blockquote>', $content);

		return $content;
	}

	public function email_content($content) {
		#save unedited content
		$original = $content;

		# apply any users custom filters for pre email content processing
		$content = apply_filters('sph_email_content_pre_filter', $content);

		# 1: Convert Chars
		$content = SP()->displayFilters->chars($content);

		# 2: Format the paragraphs
		$content = SP()->displayFilters->paragraphs($content);

		# 3: do shortcodes
		if (SP()->options->get('sffiltershortcodes')) $content = SP()->displayFilters->shortcodes($content);

		# 4: lets fix up spacing for br and p tags
		$content = SP()->saveFilters->linebreaks($content);

		# 5: Fix up quotes
		$content = html_entity_decode($content, ENT_QUOTES);

		# 6: change to spaces
		$content = str_replace('&nbsp;', ' ', $content);

		# 7: strip html tags
		$content = strip_tags($content);

		# 8: apply any users custom filters
		$content = apply_filters('sph_email_content_filter', $content, $original);

		return $content;
	}

	public function excerpt($text, $words, $size = 100) {
		# find the search terms
		$s = '\s\x00-/:-@\[-`{-~';
		preg_match_all('#(?<=['.$s.']).{1,'.$size.'}(('.$words.').{1,'.$size.'})+(?=['.$s.'])#uis', preg_quote($text), $matches, PREG_SET_ORDER);

		# add delimiter around the snippets
		$results = array();
		if (!empty($matches)) {
			foreach ($matches as $line) {
				$results[] = '<br />&hellip;'.htmlspecialchars(stripslashes($line[0]), 0, 'UTF-8');
			}
		}

		# combine snippets into excerpt
		$excerpt = implode('&hellip;<br />', $results);

		return $excerpt;
	}

	public function ampersand($url) {
		return str_replace('&', '&amp;', $url);
	}
}