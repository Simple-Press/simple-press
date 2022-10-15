<?php

/**
 * Core class used for save filters.
 *
 * @since 6.0
 *
 * Public methods available:
 * ------------------------
 *
 * $LastChangedDate: 2018-11-20 20:18:00 -0600 (Tue, 20 Nov 2018) $
 * $Rev: 15831 $
 */
class spcSaveFilters {
	public function content($content, $action, $doEsc = true, $table = '', $column = '') {
		#save unedited content
		$original = $content;

		$sffilters = SP()->options->get('sffilters');

		# 1: strip mb4 chars if unsuppofrted
		$content = $this->utf8mb4($content, $table, $column);

		# 2: prepare edits - editor specific filter
		if ($action == 'edit') {
			if (function_exists('sp_editor_prepare_edit_content')) $content = sp_editor_prepare_edit_content($content, SP()->core->forumData['editor']);
		}

		# 3: convert code tags to our own code display tags and parse for inine bbCode
		$content = $this->codetags1($content, SP()->core->forumData['editor'], $action);

		# 4: run it through kses
		$content = $this->kses($content);

		# 5: remove nbsp and p/br tags
		$content = $this->linebreaks($content);

		# 6: revist code tags in case post edit save
		$content = $this->codetags2($content, SP()->core->forumData['editor'], $action);

		# 7: remove 'pre' tags (optional)
		if ($sffilters['sffilterpre']) $content = $this->pre($content);

		# 8: deal with single quotes (tinymce encodes them)
		$content = $this->quotes($content);

		# 9: balance html tags
		$content = $this->balancetags($content);

		# 10: escape it All
		if ($doEsc) $content = $this->escape($content);

		# 11: strip spoiler shortcode if not allowed
		$fid = (isset(SP()->rewrites->pageData['forumid'])) ? SP()->rewrites->pageData['forumid'] : '';
		if (!SP()->auths->get('use_spoilers', $fid)) $content = $this->spoiler($content);

		# 12: Try and determine images widths if not set
		$content = $this->images($content);

		# 13: apply any users custom filters
		$content = apply_filters('sph_save_post_content_filter', $content, $original, $action);

		return $content;
	}

	public function text($content) {
		#save unedited content
		$original = $content;

		# Decode the entities first that were applied for display
		$content = html_entity_decode($content, ENT_COMPAT, SPCHARSET);

		# 1: run it through kses
		$content = $this->kses($content);

		# 2: remove nbsp and p/br tags
		$content = $this->linebreaks($content);

		# 3: deal with single quotes (tinymce encodes them)
		$content = $this->quotes($content);

		# 4: balance html tags
		$content = $this->balancetags($content);

		# 5: escape it All
		$content = $this->escape($content);

		# 6: apply any users custom filters
		$content = apply_filters('sph_save_text_filter', $content, $original);

		return $content;
	}

	public function title($content, $table = '', $column = '') {
		#save unedited content
		$original = $content;

		# 1: strip mb4 chars if unsupported
		$content = $this->utf8mb4($content, $table, $column);

		# 2: remove all html
		$content = $this->nohtml($content);

		# 3: encode brackets
		$content = $this->brackets($content);

		# 4: escape it All
		$content = $this->escape($content);

		# 5: apply any users custom filters
		$content = apply_filters('sph_save_title_filter', $content, $original);

		return $content;
	}

	public function name($content) {
		#save unedited content
		$original = $content;

		#1: Remove control chars
		$content = $this->nocontrolchars($content);

		# 2: Remove any html
		$content = $this->nohtml($content);

		# 3: Encode
		$content = $this->encode($content);

		# 4: escape it
		$content = $this->escape($content);

		# 5: apply any users custom filters
		$content = apply_filters('sph_save_name_filter', $content, $original);

		return $content;
	}

	public function email($email) {
		#save unedited content
		$original = $email;

		# 1: Remove any html
		$email = $this->nohtml($email);

		# 2: Validate and Sanitize Email
		$email = $this->cleanemail($email);

		# 3: escape it
		$email = $this->escape($email);

		# 4: apply any users custom filters
		$email = apply_filters('sph_save_email_filter', $email, $original);

		return $email;
	}

	public function url($url) {
		#save unedited content
		$original = $url;

		# 1: clean up url for database
		$url = $this->cleanurl($url);

		# 2: apply any users custom filters
		$url = apply_filters('sph_save_url_filter', $url, $original);

		return $url;
	}

	public function filename($filename) {
		#save unedited content
		$original = $filename;

		# 1: clean up filename
		$filename = $this->sanitize_filename($filename);

		# 2: apply any users custom filters
		$filename = apply_filters('sph_save_filename_filter', $filename, $original);

		return $filename;
	}

	private function sanitize_filename($filename) {
		$special_chars = array('?', '[', ']', '/', "\\", '=', '<', '>', ':', ';', ',', "'", "\"", '&', '$', '#', '*', '(', ')', '|', '~', '`', '!', '{', '}', chr(0));
		$filename      = str_replace($special_chars, '', $filename);
		$filename      = preg_replace('/[\s-]+/', '-', $filename);
		$filename      = trim($filename, '.-_');

		# Split the filename into a base and extension[s]
		$parts = explode('.', $filename);

		# Return if only one extension
		if (count($parts) <= 2) return $filename;

		# Process multiple extensions
		$filename  = array_shift($parts);
		$extension = array_pop($parts);
		$mimes     = get_allowed_mime_types();

		# Loop over any intermediate extensions.  Munge them with a trailing underscore if they are a 2 - 5 character
		# long alpha string not in the extension whitelist.
		foreach ((array)$parts as $part) {
			$filename .= '.'.$part;
			if (preg_match("/^[a-zA-Z]{2,5}\d?$/", $part)) {
				$allowed = false;
				foreach ($mimes as $ext_preg => $mime_match) {
					$ext_preg = '!(^'.$ext_preg.')$!i';
					if (preg_match($ext_preg, $part)) {
						$allowed = true;
						break;
					}
				}
				if (!$allowed) $filename .= '_';
			}
		}
		$filename = str_replace(' ', '_', $filename);
		$filename .= '.'.$extension;

		return $filename;
	}

	public function utf8mb4($content, $table, $column) {
		if (empty($table) || empty($column)) return $content;

		global $wpdb;

		return $wpdb->strip_invalid_text_for_column($table, $column, $content);
	}

	public function codetags1($content, $editor, $action) {
		#save unedited content
		$original = $content;

		if (function_exists('sp_editor_parse_codetags')) $content = sp_editor_parse_codetags($content, $editor, $action);

		# Parse for inline entered bbCode (popular with spammers)
		$content = SP()->filters->parse_inline_bbcode($content);

		# Shouldn't need any of these but there just in case...
		$content = str_replace('<code>', '<div class="sfcode">', $content);
		$content = str_replace('</code>', '</div>', $content);
		$content = str_replace('&lt;code&gt;', '<div class="sfcode">', $content);
		$content = str_replace('&lt;/code&gt;', '</div>', $content);

		$content = apply_filters('sph_save_codetags1_filter', $content, $original, $editor, $action);

		return $content;
	}

	public function codetags2($content, $editor, $action) {
		#save unedited content
		$original = $content;

		# check if syntax highlighted - if so not needed
		if (strpos($content, 'class="brush')) return $content;

		$content = apply_filters('sph_save_codetags2_filter', $content, $original, $editor, $action);

		return $content;
	}

	public function kses($content) {
		global $allowedforumtags, $allowedforumprotocols;

		#save unedited content
		$original = $content;

		if (!isset($allowedforumtags)) {
			$this->kses_array();
			$allowedforumtags = apply_filters('sph_custom_kses', $allowedforumtags);
		}

		$content = wp_kses(stripslashes($content), $allowedforumtags, $allowedforumprotocols);

		$content = apply_filters('sph_save_kses_filter', $content, $original);

		return $content;
	}

	public function linebreaks($content) {
		#save unedited content
		$original = $content;

		$gap = '<p>'.chr(194).chr(160).'</p>'.chr(13).chr(10);
		$end = '<p>'.chr(194).chr(160).'</p>';

		# trim unwanted empty space
		$content = trim($content);

		while (substr($content, 0, 11) == $gap) {
			$content = substr_replace($content, '', 0, 11);
		}

		while (substr($content, (strlen($content) - 9), 9) == $end) {
			$content = substr_replace($content, '', (strlen($content) - 9), 9);
		}

		while (substr($content, (strlen($content) - 11), 11) == $gap) {
			$content = substr_replace($content, '', (strlen($content) - 11), 11);
		}

		# On savibng edit a 'br' may have a trailng line break which
		# will display like a paragraph break
		$content = str_replace('<br />'.chr(13).chr(10), "\n", $content);

		# change br's to linebreaks
		$content = str_replace('<br />', "\n", $content);

		# change tiny blank line to a newline
		$content = str_replace($gap.$gap, $gap, $content);

		# same for blank line with p tags
		$content = str_replace('<p></p>', "\n\n", $content);
		$content = str_replace('<p> </p>', "\n\n", $content);
		$content = str_replace('<p>', '', $content);
		$content = str_replace('</p>', chr(13).chr(10), $content);

		$content = apply_filters('sph_save_linebreaks_filter', $content, $original);

		return $content;
	}

	public function pre($content) {
		$content = SP()->filters->pre($content);
		$content = apply_filters('sph_save_pre_filter', $content);

		return $content;
	}

	public function quotes($content) {
		#save unedited content
		$original = $content;

		# Replace tinymce encoded single quotes with standard quotes
		$content = str_replace('&#39;', "'", $content);
		$content = str_replace('&#039;', "'", $content);

		# Replace those odd 0003 chars we have seen here and there
		$content = str_replace(chr(003), "'", $content);

		# ensure all img tags use double quotes
		$content = preg_replace_callback('/<img([^<>]+)>/', array($this, 'strip_img'), $content);

		$content = apply_filters('sph_save_quotes_filter', $content, $original);

		return $content;
	}

	private function strip_img($matches) {
		return '<img '.str_replace("'", '"', $matches[1]).'>';
	}

	public function balancetags($content) {
		#save unedited content
		$original = $content;

		$content = balanceTags($content, true);
		$content = apply_filters('sph_save_balancetags_filter', $content, $original);

		return $content;
	}

	public function nofollow($content) {
		#save unedited content
		$original = $content;

		$content = preg_replace_callback('|<a (.+?)>|i', array($this, 'nofollow_callback'), $content);
		$content = apply_filters('sph_save_nofollow_filter', $content, $original);

		return $content;
	}

	private function nofollow_callback($matches) {
		$text = $matches[1];
		$text = str_replace(array(' rel="nofollow"', " rel='nofollow'", 'rel="nofollow"', "rel='nofollow'"), '', $text);

		return '<a '.$text.' rel="nofollow">';
	}

	public function target($content) {
		$content = preg_replace_callback('|<a (.+?)>|i', array($this, 'target_callback'), $content);

		return $content;
	}

	private function target_callback($matches) {
		$text = $matches[1];
		if (strpos($text, 'javascript:void(0)')) return "<a $text>";
		$text = str_replace(array(' target="_blank"', " target='_blank'", 'target="_blank"', "target='_blank'"), '', $text);

		return '<a '.$text.' target="_blank">';
	}

	public function links($content, $charcount) {
		#save unedited content
		$original = $content;

		$content = make_clickable($content);

		# pad it with a space
		$content = ' '.$content;

		# chunk those long urls as long as not in pre (or syntax highligthed code) segments
		$segments = preg_split('/(<\/?pre|\[)/', $content, -1, PREG_SPLIT_DELIM_CAPTURE);

		# $depth = how many nested pres we're inside of
		$depth = 0;
		foreach ($segments as &$segment) {
			if ($depth == 0 && ($segment != '<pre' && $segment != '[')) {
				$this->format_links($segment, $charcount);
			} else if ($segment == '<pre' || $segment == '[') {
				$depth++;
			} else if ($depth > 0 && ($segment == '</pre' || $segment == ']')) {
				$depth--;
			}
		}
		$content = implode($segments);

		# clean up email links
		$content = preg_replace("#(\s)([a-z0-9\-_.]+)@([^,< \n\r]+)#i", "$1<a href=\"mailto:$2@$3\">$2@$3</a>", $content);

		# Remove our padding..
		$content = substr($content, 1);

		$content = apply_filters('sph_save_links_filter', $content, $original, $charcount);

		return ($content);
	}

	private function format_links(&$content, $charcount) {
		$links      = explode('<a', $content);
		$countlinks = count($links);
		for ($i = 0; $i < $countlinks; $i++) {
			$link    = $links[$i];
			$link    = (preg_match('#(.*)(href=")#is', $link)) ? '<a'.$link : $link;
			$begin   = strpos($link, '>') + 1;
			$end     = strpos($link, '<', $begin);
			$length  = $end - $begin;
			$urlname = substr($link, $begin, $length);

			# We chunk urls that are longer than 50 characters. Just change
			# '50' to a value that suits your taste. We are not chunking the link
			# text unless if begins with 'http://', 'https://', 'ftp://', or 'www.'
			$chunked = (strlen($urlname) > $charcount && preg_match('#^(http://|https://|ftp://|www\.)#is', $urlname)) ? substr_replace($urlname, '.....', ($charcount - 10), -10) : $urlname;
			$content = str_replace('>'.$urlname.'<', '>'.$chunked.'<', $content);
		}
	}

	public function nocontrolchars($content) {
		# first decode any html encodings in name
		$content  = html_entity_decode($content, ENT_QUOTES, SPCHARSET);
		$fContent = '';
		# now remove control chars
		for ($x = 0; $x < strlen($content); $x++) {

			if (function_exists('mb_substr')) {
				$char = mb_substr($content, $x, 1, SPCHARSET);
			} else {
				$char = substr($content, $x, 1);
			}

			if (ctype_cntrl($char) == false) $fContent .= $char;
		}

		return $fContent;
	}

	public function nohtml($content) {
		#save unedited content
		$original = $content;

		$content = wp_kses(stripslashes($content), array());
		$content = apply_filters('sph_save_nohtml_filter', $content, $original);

		return $content;
	}

	public function brackets($content) {
		#save unedited content
		$original = $content;

		$content = str_replace('[', '&#091;', $content);
		$content = str_replace(']', '&#093;', $content);
		$content = apply_filters('sph_save_brackets_filter', $content, $original);

		return $content;
	}

	public function escape($content) {
		#save unedited content
		$original = $content;

		$content = SP()->filters->esc_sql($content);

		# handle wp SP()->filters->esc_sql() double slashing our return chars
		$search  = array("\\n", "\\r");
		$replace = array("\n", "\r");
		$content = str_replace($search, $replace, $content);

		$content = apply_filters('sph_save_escape_filter', $content, $original);

		return $content;
	}

	public function encode($content) {
		#save unedited content
		$original = $content;

		$content = esc_attr($content);
		$content = apply_filters('sph_save_encode_filter', $content, $original);

		return $content;
	}

	public function cleanemail($email) {
		$email = sanitize_email($email);

		return $email;
	}

	public function cleanurl($url) {
		$url = esc_url_raw($url);

		return $url;
	}

	public function spoiler($content) {
		#save unedited content
		$original = $content;

		$content = preg_replace('/\[spoiler\][^>]*\[\/spoiler\]/', '', $content);
		$content = apply_filters('sph_save_spoiler_filter', $content, $original);

		return $content;
	}

	public function images($content) {
		#save unedited content
		$original = $content;

		$content = apply_filters('sph_save_images_filter', $content, $original);

		return $this->check_image_width($content);
	}

	private function check_image_width($content) {
		$content = preg_replace_callback('/<img[^>]*>/', array($this, 'check_save_width'), $content);

		return $content;
	}

	private function check_save_width($match) {
		$out      = '';
		$match[0] = stripslashes($match[0]);

		preg_match('/title\s*=\s*"([^"]*)"|title\s*=\s*\'([^\']*)\'/i', $match[0], $title);
		preg_match('/alt\s*=\s*"([^"]*)"|alt\s*=\s*\'([^\']*)\'/i', $match[0], $alt);
		preg_match('/width\s*=\s*"([^"]*)"|width\s*=\s*\'([^\']*)\'/i', $match[0], $width);
		preg_match('/src\s*=\s*"([^"]*)"|src\s*=\s*\'([^\']*)\'/i', $match[0], $src);
		preg_match('/style\s*=\s*"([^"]*)"|style\s*=\s*\'([^\']*)\'/i', $match[0], $style);
		preg_match('/class\s*=\s*"([^"]*)"|class\s*=\s*\'([^\']*)\'/i', $match[0], $class);

		if (isset($width[1])) return $match[0];
		if (isset($class[1])) return $match[0];

		if ((strpos($src[1], 'plugins/emotions')) || (strpos($src[1], 'images/smilies')) || (strpos($src[1], SP()->plugin->storage['smileys']))) {
			$out = str_replace('img src', 'img class="spSmiley" src', $match[0]);

			return $out;
		}

		if (empty($width[1])) {
			$size = SP()->primitives->get_image_size($src[1], true);
			if ($size) {
				if ($size[0]) {
					$width[1] = $size[0];
				}
			} else if (ini_get('allow_url_fopen') == true && $size == false) {
				return '['.SP()->primitives->front_text('Image Can Not Be Found').']';
			}
		}

		$thissrc   = (isset($src[1])) ? 'src="'.$src[1].'" ' : '';
		$thistitle = (isset($title[1])) ? 'title="'.$title[1].'" ' : '';
		$thisalt   = (isset($alt[1])) ? 'alt="'.$alt[1].'" ' : 'alt="'.basename($thissrc).'" ';
		$thiswidth = (isset($width[1])) ? 'width="'.$width[1].'" ' : '';
		$thisstyle = (isset($style[1])) ? 'style="'.$style[1].'" ' : '';
		$thisclass = (isset($class[1])) ? 'class="'.$class[1].'" ' : '';

		$out .= SP()->filters->esc_sql('<img '.$thissrc.$thiswidth.$thisstyle.$thisclass.$thistitle.$thisalt.'/>');

		return $out;
	}

	private function kses_array() {
		global $allowedforumtags, $allowedforumprotocols;

		$allowedforumprotocols = apply_filters('sph_allowed_protocols', array('http', 'https', 'ftp', 'ftps', 'mailto', 'news', 'irc', 'gopher', 'nntp', 'feed', 'telnet', 'clsid', 'data'));
		$allowedforumtags      = array('address' => array('class' => true), 
										'a' => array('class' => true, 'href' => true, 'id' => true, 'title' => true, 'rel' => true, 'rev' => true, 'name' => true, 'target' => true, 'style' => true), 
										'abbr' => array('class' => true, 'title' => true), 
										'acronym' => array('title' => true, 'class' => true), 
										'article' => array('align' => true, 'class' => true, 'dir' => true, 'lang' => true, 'style' => true, 'xml:lang' => true), 
										'aside' => array('align' => true, 'class' => true, 'dir' => true, 'lang' => true, 'style' => true, 'xml:lang' => true), 
										'audio' => array('autoplay' => true, 'class' => true, 'controls' => true, 'id' => true, 'loop' => true, 'muted' => true, 'poster' => true, 'preload' => true, 'src' => true, 'style' => true), 
										'b' => array('class' => true), 
										'big' => array('class' => true), 
										'blockquote' => array('id' => true, 'cite' => true, 'class' => true, 'lang' => true, 'xml:lang' => true, 'style' => true), 
										'br' => array('class' => true), 
										'caption' => array('align' => true, 'class' => true), 
										'cite' => array('class' => true, 'dir' => true, 'lang' => true, 'title' => true), 
										'code' => array('class' => true, 'style' => true), 'dd' => array('class' => true), 
										'del' => array('datetime' => true), 
										'details' => array('align' => true, 'class' => true, 'dir' => true, 'lang' => true, 'open' => true, 'style' => true, 'xml:lang' => true), 
										'div' => array('align' => true, 'class' => true, 'dir' => true, 'lang' => true, 'style' => true, 'xml:lang' => true), 
										'dl' => array('class' => true), 
										'dt' => array('class' => true), 
										'em' => array('class' => true), 										
										'figure' => array('align' => true, 'class' => true, 'dir' => true, 'lang' => true, 'style' => true, 'xml:lang' => true), 
										'figcaption' => array('align' => true, 'class' => true, 'dir' => true, 'lang' => true, 'style' => true, 'xml:lang' => true), 
										'font' => array('color' => true, 'face' => true, 'size' => true), 
										'footer' => array('align' => true, 'class' => true, 'dir' => true, 'lang' => true, 'style' => true, 'xml:lang' => true), 
										'header' => array('align' => true, 'class' => true, 'dir' => true, 'lang' => true, 'style' => true, 'xml:lang' => true), 
										'hgroup' => array('align' => true, 'class' => true, 'dir' => true, 'lang' => true, 'style' => true, 'xml:lang' => true), 
										'h1' => array('align' => true, 'class' => true, 'id' => true, 'style' => true), 
										'h2' => array('align' => true, 'class' => true, 'id' => true, 'style' => true), 
										'h3' => array('align' => true, 'class' => true, 'id' => true, 'style' => true), 
										'h4' => array('align' => true, 'class' => true, 'id' => true, 'style' => true), 
										'h5' => array('align' => true, 'class' => true, 'id' => true, 'style' => true), 
										'h6' => array('align' => true, 'class' => true, 'id' => true, 'style' => true), 
										'hr' => array('align' => true, 'class' => true, 'noshade' => true, 'size' => true, 'width' => true), 
										'i' => array('class' => true), 
										'img' => array('alt' => true, 'title' => true, 'align' => true, 'border' => true, 'class' => true, 'height' => true, 'hspace' => true, 'longdesc' => true, 'vspace' => true, 'src' => true, 'style' => true, 'width' => true, 'data-upload' => true, 'data-width' => true, 'data-height' => true), 
										'ins' => array('datetime' => true, 'cite' => true), 
										'kbd' => array('class' => true), 
										'label' => array('for' => true), 
										'legend' => array('align' => true), 
										'li' => array('align' => true, 'class' => true, 'id' => true, 'style' => true), 
										'menu' => array('class' => true, 'style' => true, 'type' => true), 
										'nav' => array('align' => true, 'class' => true, 'dir' => true, 'lang' => true, 'style' => true, 'xml:lang' => true), 
										'param' => array('id' => true, 'name' => true, 'type' => true, 'value' => true, 'valuetype' => true), 
										'p' => array('class' => true, 'align' => true, 'dir' => true, 'lang' => true, 'style' => true, 'xml:lang' => true), 
										'pre' => array('class' => true, 'style' => true, 'width' => true), 'q' => array('cite' => true), 
										's' => array('class' => true), 
										'section' => array('align' => true, 'class' => true, 'dir' => true, 'lang' => true, 'style' => true, 'xml:lang' => true), 
										'small' => array('class' => true), 
										'source' => array('class' => true, 'id' => true, 'media' => true, 'src' => true, 'style' => true, 'type' => true), 
										'span' => array('class' => true, 'dir' => true, 'align' => true, 'lang' => true, 'style' => true, 'title' => true, 'xml:lang' => true, 'id' => true), 
										'strike' => array('class' => true), 
										'strong' => array('class' => true), 
										'sub' => array('class' => true), 
										'summary' => array('align' => true, 'class' => true, 'dir' => true, 'lang' => true, 'style' => true, 'xml:lang' => true), 
										'sup' => array('class' => true), 
										'table' => array('align' => true, 'bgcolor' => true, 'border' => true, 'cellpadding' => true, 'cellspacing' => true, 'class' => true, 'dir' => true, 'id' => true, 'rules' => true, 'style' => true, 'summary' => true, 'width' => true), 
										'tbody' => array('align' => true, 'char' => true, 'charoff' => true, 'valign' => true), 
										'td' => array('abbr' => true, 'align' => true, 'axis' => true, 'bgcolor' => true, 'char' => true, 'charoff' => true, 'class' => true, 'colspan' => true, 'dir' => true, 'headers' => true, 'height' => true, 'nowrap' => true, 'rowspan' => true, 'scope' => true, 'style' => true, 'valign' => true, 'width' => true), 
										'tfoot' => array('align' => true, 'char' => true, 'class' => true, 'charoff' => true, 'valign' => true), 
										'th' => array('abbr' => true, 'align' => true, 'axis' => true, 'bgcolor' => true, 'char' => true, 'charoff' => true, 'class' => true, 'colspan' => true, 'headers' => true, 'height' => true, 'nowrap' => true, 'rowspan' => true, 'scope' => true, 'valign' => true, 'width' => true), 
										'thead' => array('align' => true, 'char' => true, 'charoff' => true, 'class' => true, 'valign' => true), 
										'title' => array('class' => true), 
										'tr' => array('align' => true, 'bgcolor' => true, 'char' => true, 'charoff' => true, 'class' => true, 'style' => true, 'valign' => true), 
										'tt' => array('class' => true), 
										'u' => array('class' => true), 
										'ul' => array('class' => true, 'style' => true, 'type' => true), 
										'ol' => array('class' => true, 'start' => true, 'style' => true, 'type' => true), 
										'var' => array('class' => true), 
										'video' => array('autoplay' => true, 'class' => true, 'controls' => true, 'height' => true, 'id' => true, 'loop' => true, 'muted' => true, 'poster' => true, 'preload' => true, 'src' => true, 'style' => true, 'width' => true)
									);

		$target = (isset(SP()->rewrites->pageData['forumid'])) ? SP()->rewrites->pageData['forumid'] : 'global';

		if (isset(SP()->user->thisUser) && SP()->auths->get('can_use_iframes', $target, SP()->user->thisUser->ID)) {
			$allowedforumtags['iframe'] = array('width' => true, 'height' => true, 'frameborder' => true, 'src' => true, 'marginwidth' => true, 'marginheight' => true);
		}
		if (isset(SP()->user->thisUser) && SP()->auths->get('can_use_object_tag', $target, SP()->user->thisUser->ID)) {
			$allowedforumtags['object'] = array('classid' => true, 'codebase' => true, 'codetype' => true, 'data' => true, 'declare' => true, 'height' => true, 'name' => true, 'param' => true, 'standby' => true, 'type' => true, 'usemap' => true, 'width' => true);
			$allowedforumtags['embed']  = array('height' => true, 'name' => true, 'pallette' => true, 'src' => true, 'type' => true, 'width' => true);
		}

		$allowedforumtags = apply_filters('sph_kses_allowed_tags', $allowedforumtags);
	}
}