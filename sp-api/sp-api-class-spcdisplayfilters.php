<?php

/**
 * Core class used for display filters.
 *
 * @since 6.0
 *
 * Public methods available:
 * ------------------------
 *
 * $LastChangedDate: 2018-08-15 07:59:04 -0500 (Wed, 15 Aug 2018) $
 * $Rev: 15704 $
 */
class spcDisplayFilters {
	public function content($content) {
		$sffilters = SP()->options->get('sffilters');

		#save unedited content
		$original = $content;

		# apply any users custom filters for pre-content display processing
		$content = apply_filters('sph_display_post_content_pre_filter', $content);

		# 1: parse smileys and emojis
		$content = $this->smileys($content);

		# 2: parse it for the wp oEmbed class
		if (get_option('embed_autourls')) $content = $this->oEmbed($content);

		# 3: remove 'pre' tags (optional)
		if ($sffilters['sffilterpre']) $content = $this->pre($content);

		# 4: make links clickable
		$content = $this->links($content);

		# 5: format links (optional)
		if ($sffilters['sfurlchars']) $content = SP()->saveFilters->links($content, $sffilters['sfurlchars']);

		# 6: add nofollow to links (optional)
		if ($sffilters['sfnofollow']) $content = SP()->saveFilters->nofollow($content);

		# 7: add target blank (optional)
		if ($sffilters['sftarget']) $content = SP()->saveFilters->target($content);

		# 8: Convert Chars
		$content = $this->chars($content);

		# 9: Format the paragraphs
		$content = $this->paragraphs($content);

		# 10: Format the code select Divs.
		$content = $this->codeselect($content);

		# 11: Format image tags and check permissions
		$content = $this->images($content);

		# 12: Check permissiosn for viewing media
		$content = $this->media($content);

		# 13: strip shortcodes
		if (SP()->options->get('sffiltershortcodes')) $content = $this->shortcodes($content);

		# 14: hide links
		$forum_id = (!empty(SP()->rewrites->pageData['forumid'])) ? SP()->rewrites->pageData['forumid'] : '';
		if (!SP()->auths->get('view_links', $forum_id)) $content = $this->hidelinks($content);
		
		# 15: convert legacy http to https if SSL
		$content = $this->legacyUrl($content);

		# 16: balance html tags
		$content = SP()->saveFilters->balancetags($content);

		$content = apply_filters('sph_display_post_content_filter', $content, $original);

		return $content;
	}

	public function text($content) {
		#save unedited content
		$original = $content;

		$sffilters = SP()->options->get('sffilters');

		# 1: format links
		if ($sffilters['sfurlchars']) $content = SP()->saveFilters->links($content, $sffilters['sfurlchars']);

		# 2: add nofollow to links
		if ($sffilters['sfnofollow']) $content = SP()->saveFilters->nofollow($content);

		# 3: add target blank
		if ($sffilters['sftarget']) $content = SP()->saveFilters->target($content);

		# 4: Convert Chars
		$content = $this->chars($content);

		# 5: Format the paragraphs
		$content = $this->paragraphs($content);

		# 6: remove escape slashes
		$content = $this->stripslashes($content);

		$content = apply_filters('sph_display_text_filter', $content, $original);

		return $content;
	}

	public function title($content) {
		#save unedited content
		$original = $content;

		# 1: Convert Chars
		$content = $this->chars($content);

		# 2: Remove escape slashes
		$content = $this->stripslashes($content);
		
		# 3: Run it through the wp_kses_post function.
		$content = wp_kses_post($content);

		$content = apply_filters('sph_display_title_filter', $content, $original);

		return $content;
	}

	public function name($content) {
		#save unedited content
		$original = $content;

		# 1: Convert Chars
		$content = $this->chars($content);

		# 2: Remove escape slashes
		$content = $this->stripslashes($content);
		
		# 3: Sanitize text field.
		$content = sanitize_text_field($content);

		$content = apply_filters('sph_display_name_filter', $content, $original);

		return $content;
	}

	public function email($email) {
		#save unedited content
		$original = $email;

		# 1: Convert Chars
		$email = $this->chars($email);

		# 2: Remove escape slashes
		$email = $this->stripslashes($email);
		
		# 3: Run it through the native WP sanitize_email function.
		# Warning: This function uses a smaller allowable character set than the set defined by RFC 5322. Some legal email addresses may be changed.
		$email = sanitize_email($email);

		$email = apply_filters('sph_display_email_filter', $email, $original);

		return $email;
	}

	public function url($url) {
		#save unedited content
		$original = $url;

		$url = SP()->saveFilters->cleanurl($url);

		$url = apply_filters('sph_display_url_filter', $url, $original);

		return $url;
	}

	public function smileys($content) {
		global $wp_smiliessearch;

		#save unedited content
		$original = $content;

		# Custom
		$smileys_data = SP()->meta->get_value('smileys', 'smileys');
		if (!empty($smileys_data)) {
			foreach ($smileys_data as $sname => $sinfo) {
				if (strpos($content, $sinfo[1]) != 0) {
					$content = str_replace($sinfo[1], '<img src="'.SPSMILEYS.$sinfo[0].'" title="'.$sname.'" alt="'.$sname.'" />', $content);
				}
			}
			# and parse it by Wp smley codes as well.
			$output = '';
			if (get_option('use_smilies') && !empty($wp_smiliessearch)) {
				$textarr = preg_split('/(<.*>)/U', $content, -1, PREG_SPLIT_DELIM_CAPTURE); # capture the tags as well as in between
				$stop    = count($textarr); # loop stuff
				if ($stop) {
					for ($i = 0; $i < $stop; $i++) {
						$text = $textarr[$i];
						if ((strlen($text) > 0) && ('<' != $text[0])) { # If it's not a tag
							$text = preg_replace_callback($wp_smiliessearch, array($this, 'translate_wp_smiley'), $text);
						}
						$output .= $text;
					}
					$content = $output;
				}
			}
		}

		$content = apply_filters('sph_display_smileys_filter', $content, $original);

		return $content;
	}

	private function translate_wp_smiley($smiley) {
		global $wpsmiliestrans;
		if (count($smiley) == 0) {
			return '';
		}
		$smiley = trim(reset($smiley));
		$img    = $wpsmiliestrans[$smiley];

		if (strlen($img) <= 4) {
			return $img;
		} else {
			$smiley_masked = esc_attr($smiley);
			$srcurl        = apply_filters('smilies_src', includes_url("images/smilies/$img"), $img, site_url());

			return ' <img src="'.$srcurl.'" alt="'.$smiley_masked.'" class="spSmiley" /> ';
		}
	}

	public function links($content) {
		#save unedited content
		$original = $content;

		# Correct the TinyMCE/Chrome issue
		$content = str_replace(chr(194).chr(160).'http', ' http', $content);
		$content = str_replace(chr(194).chr(160).'www', ' http://www', $content);

		$content = make_clickable($content);

		$content = apply_filters('sph_display_links_filter', $content, $original);

		return $content;
	}

	public function pre($content) {
		$content = SP()->filters->pre($content);
		$content = apply_filters('sph_display_pre_filter', $content);

		return $content;
	}

	public function chars($content) {
		#save unedited content
		$original = $content;

		$content = convert_chars($content);

		# This simply replaces those odd 0003 chars we have seen
		$content = str_replace(chr(003), "'", $content);

		$content = apply_filters('sph_display_chars_filter', $content, $original);

		return $content;
	}

	public function paragraphs($content) {
		#save unedited content
		$original = $content;

		# check if syntax hoighlighted
		if (strpos($content, 'class="brush')) {
			$base = explode('<div class="sfcode">', $content);
			if ($base) {
				$comp = array();
				foreach ($base as $part) {
					if (substr(trim($part), 0, 18) == '<pre class="brush-') {
						$subparts = explode('</pre>', $part);
						if (!empty($subparts[1])) {
							$comp[]      = '<div class="sfcode">'.$subparts[0].'</pre></div>';
							$pos         = strpos($subparts[1], '</div>');
							$subparts[1] = substr($subparts[1], ($pos + 6));
							$comp[]      = wpautop($subparts[1]);
						}
						unset($subparts);
					} else {
						$comp[] = wpautop($part);
					}
				}
				$content = implode($comp);
			}
		} else {
			$content = wpautop($content);
		}

		$content = shortcode_unautop($content);

		$content = apply_filters('sph_display_paragraphs_filter', $content, $original);

		return $content;
	}

	public function codeselect($content) {
		#save unedited content
		$original = $content;

		# add the 'select code' button
		$pos = strpos($content, '<div class="sfcode">');
		if ($pos === false) return $content;

		# check if syntax highlighted
		if (strpos($content, 'class="brush')) return $content;

		while ($pos !== false) {
			$id      = rand(100, 10000);
			$replace = "<p><input type='button' class='sfcodeselect' name='sfselectit$id' value='".SP()->primitives->front_text('Select Code')."' data-codeid='sfcode$id' /></p><div class='sfcode' id='sfcode$id'>";
			$content = substr_replace($content, $replace, $pos, 20);
			$pos     = $pos + 140;
			$pos     = strpos($content, '<div class="sfcode">', $pos);
		}

		$content = apply_filters('sph_display_codeselect_filter', $content, $original);

		return $content;
	}

	public function images($content) {
		return $this->check_width($content);
	}

	private function check_width($content) {
		$content = preg_replace_callback('/<img[^>]*>/', array($this, 'format_image'), $content);

		return $content;
	}

	private function format_image($match) {
		preg_match('/width\s*=\s*"([^"]*)"|width\s*=\s*\'([^\']*)\'/i', $match[0], $width);

		$out     = '';
		$sfimage = SP()->options->get('sfimage');

		preg_match('/title\s*=\s*"([^"]*)"|title\s*=\s*\'([^\']*)\'/i', $match[0], $title);
		preg_match('/alt\s*=\s*"([^"]*)"|alt\s*=\s*\'([^\']*)\'/i', $match[0], $alt);
		preg_match('/width\s*=\s*"([^"]*)"|width\s*=\s*\'([^\']*)\'/i', $match[0], $width);
		preg_match('/height\s*=\s*"([^"]*)"|height\s*=\s*\'([^\']*)\'/i', $match[0], $height);
		preg_match('/src\s*=\s*"([^"]*)"|src\s*=\s*\'([^\']*)\'/i', $match[0], $src);
		preg_match('/style\s*=\s*"([^"]*)"|style\s*=\s*\'([^\']*)\'/i', $match[0], $style);
		preg_match('/class\s*=\s*"([^"]*)"|class\s*=\s*\'([^\']*)\'/i', $match[0], $class);

		# if no src attribute then bail now
		if (empty($src[1])) return '';

		if (isset($class[1]) && strpos($class[1], 'wp-image') === 0) return $match[0];

		# is this a smiley?
		if ((strpos($src[1], 'plugins/emotions')) || (strpos($src[1], SP()->plugin->storage['smileys']))) {
			return str_replace('img ', 'img class="spSmiley" style="margin:0" ', $match[0]);
		} elseif (strpos($src[1], 'images/smilies')) {
			return str_replace('class="spSmiley"', 'class="spWPSmiley" style="max-height:1em;margin:0" ', $match[0]);
		}

		# check the user is allowed to view image
		$forum_id = (!empty(SP()->rewrites->pageData['forumid'])) ? SP()->rewrites->pageData['forumid'] : '';
		if (!SP()->auths->get('can_view_images', $forum_id, SP()->user->thisUser->ID)) {
			return '['.SP()->primitives->front_text('Permission to view this image is denied').']<br />';
		}

		# is any of this needed?
		if ($sfimage['enlarge'] == false && $sfimage['process'] == false) return $match[0];

		$thumb = $sfimage['thumbsize'];
		if ((empty($thumb)) || ($thumb < 100)) $thumb = 100;

		if (empty($style[1])) {
			if ($sfimage['style'] == 'left' || $sfimage['style'] == 'right') {
				$style[1] = 'float: '.$sfimage['style'];
			} else {
				$style[1] = 'vertical-align: '.$sfimage['style'];
			}
		}

		# Might be inherited image with wp standard alignleft and alignright in use
		if (isset($class[1])) {
			if (strpos($class[1], 'alignleft') !== false) {
				$style[1] = 'float: left';
			} else if (strpos($class[1], 'alignright') !== false) {
				$style[1] = 'float: right';
			} else if (strpos($class[1], 'aligncenter') !== false) {
				$style[1] = 'margin: 0 auto';
			}
		}

		$iclass = '';
		$mclass = 'sfmouseother';
		$mstyle = '';

		switch ($style[1]) {
			case 'float: left':
				$iclass = 'sfimageleft';
				$mclass = 'sfmouseleft';
				break;
			case 'float: right':
				$iclass = 'sfimageright';
				$mclass = 'sfmouseright';
				break;
			case 'margin: 0 auto':
				$iclass = 'sfimagecenter';
				# mouse icon not possible with center unless we can work it out
				$mclass = 'na';
				break;
			case 'vertical-align: baseline':
				$iclass = 'sfimagebaseline';
				break;
			case 'vertical-align: top':
				$iclass = 'sfimagetop';
				break;
			case 'vertical-align: middle':
				$iclass = 'sfimagemiddle';
				break;
			case 'vertical-align: bottom':
				$iclass = 'sfimagebottom';
				break;
			case 'vertical-align: text-top':
				$iclass = 'sfimagetexttop';
				break;
			case 'vertical-align: text-bottom':
				$iclass = 'sfimagetextbottom';
				break;
		}

		if (empty($width[1])) {
			$size = SP()->primitives->get_image_size($src[1], true);
			if ($size) {
				if ($size[0]) {
					$width[1]  = $size[0];
					$height[1] = $size[1];
				} else {
					$width[1]  = 0;
					$height[1] = 0;
				}
			} elseif (ini_get('allow_url_fopen') == true && $size == false) {
				return '['.SP()->primitives->front_text('Image Can Not Be Found').']';
			}
		}

		if (isset($src[1])) $thissrc = 'src="'.$src[1].'" '; else $thissrc = '';
		if (isset($title[1])) $thistitle = 'title="'.$title[1].'" '; else $thistitle = '';
		if (isset($alt[1])) $thisalt = 'alt="'.$alt[1].'" '; else $thisalt = 'alt="'.basename($thissrc).'" ';

		$anchor    = false;
		$thiswidth = '';
		if (empty($width[1])) {
			# couldn't determine width, so don't output it
			$thiswidth = '';
			$mclass    = '';
			$anchor    = false;
		} elseif ((int)$width[1] > (int)$thumb) {
			# is width > thumb size
			$thiswidth = 'width="'.$thumb.'" ';
			$anchor    = true;
		} else if (!empty($width[1]) && (int)$width[1] > 0) {
			# width is smaller than thumb, so use the width
			$thiswidth = 'width="'.$width[1].'" ';
			$mclass    = '';
			$anchor    = false;
		}

		if (!empty($iclass)) {
			$thisformat = ' class="'.$iclass.' spUserImage" ';
		} else {
			$thisformat = ' style="'.$style[1].'" class="spUserImage" ';
		}

		if ($anchor) {
			$w = (!empty($width) && $width[1]) ? $width[1] : 'auto';
			$h = (!empty($height) && $height[1]) ? $height[1] : 'auto';
			# Use popup or not?
			if ($sfimage['enlarge'] == true) {
				$out = "<a class='spShowPopupImage' title='".SP()->primitives->front_text('Click image to enlarge')."' data-src='$src[1]' data-width='$w' data-height='$h' data-constrain='".$sfimage['constrain']."'>";
				$out = apply_filters('sph_display_image_popup', $out, $src[1]);
			} else {
				$out = '<a href="'.$src[1].'" '.$thistitle.'>';
			}
		}

		# let plugins play with the adjusted image elements
		$image_array = compact('thissrc', 'thiswidth', 'thisformat', 'thistitle', 'thisalt' );
		$image_array = apply_filters('sph_display_image_data', $image_array, $src, $width, $height, $title, $alt, $style, $class);
		extract($image_array);

		$out .= '<img '.$thissrc.$thiswidth.$thisformat.$thistitle.$thisalt.'/>';

		if ($mclass) {
			$mouse = '<img src="'.SP()->theme->paint_file_icon(SPTHEMEICONSURL, 'sp_Mouse.png').'" class="'.$iclass.' '.$mclass.'" alt="'.SP()->primitives->front_text('Image Enlarger').'" '.$mstyle.'/>';
			$out .= apply_filters('sph_display_image_mouse', $mouse);
		}

		if ($anchor) $out .= '</a>';

		if ($sfimage['forceclear']) $out .= '<div style="clear:both"></div>';

		return $out;
	}

	public function media($content) {
		$content = $this->video($content);
		$content = $this->audio($content);

		return $content;
	}

	public function video($content) {
		$content = preg_replace_callback('/<video[^>]*>/', array($this, 'check_media_permissions'), $content);

		return $content;
	}

	public function audio($content) {
		$content = preg_replace_callback('/<audio[^>]*>/', array($this, 'check_media_permissions'), $content);

		return $content;
	}

	private function check_media_permissions($match) {
		# check the user is allowed to view media
		$forum_id = (!empty(SP()->rewrites->pageData['forumid'])) ? SP()->rewrites->pageData['forumid'] : '';
		if (!SP()->auths->get('can_view_media', $forum_id, SP()->user->thisUser->ID)) {
			return '['.SP()->primitives->front_text('Permission to view this media is denied').']<br />';
		} else {
			return $match[0];
		}
	}

	public function stripslashes($content) {
		$content = stripslashes($content);

		return $content;
	}

	public function scleanurl($url) {
		$url = esc_url($url);

		return $url;
	}

	public function shortcodes($content) {
		global $shortcode_tags;

		#save unedited content
		$original = $content;

		# Backup current registered shortcodes
		$orig_shortcode_tags = $shortcode_tags;
		$allowed_shortcodes  = explode("\n", stripslashes(SP()->options->get('sfshortcodes')));
		if ($allowed_shortcodes) {
			foreach ($allowed_shortcodes as $tag) {
				if (array_key_exists($tag, $orig_shortcode_tags)) unset($shortcode_tags[$tag]);
			}
		}

		# allow our internal shortcodes (letting plugins add others)
		$internal_shortcodes = apply_filters('sph_internal_shortcodes', array('spoiler'));
		foreach ($internal_shortcodes as $shortcode) {
			unset($shortcode_tags[$shortcode]);
		}

		# strip all but allowed shortcodes
		$content = strip_shortcodes($content);

		# Restore registered shortcodes
		$shortcode_tags = $orig_shortcode_tags;

		$content = apply_filters('sph_display_shortcodes_filter', $content, $original);

		return $content;
	}

	public function oEmbed($content) {
		$content = preg_replace_callback('#(?<!=\')(?<!=")(http|ftp|https):\/\/[\w\-_]+(\.[\w\-_]+)+([\w\-\.,@?^=%&:/~\+\#]*[\w\-\@?^=%&amp;/~\+\#])?#i', array($this, 'check_oEmbed'), $content);

		return $content;
	}

	private function check_oEmbed($match) {
		# check the user is allowed to view media
		$forum_id = (!empty(SP()->rewrites->pageData['forumid'])) ? SP()->rewrites->pageData['forumid'] : '';
		if (!SP()->auths->get('can_view_media', $forum_id, SP()->user->thisUser->ID)) {
			return '['.SP()->primitives->front_text('Permission to view this media is denied').']<br />';
		}

		require_once ABSPATH.WPINC.'/class-oembed.php';

		$url    = $match[0];
		$oembed = _wp_oembed_get_object();
		foreach ($oembed->providers as $provider => $data) {
			list($providerurl, $regex) = $data;

			# Turn the asterisk-type provider URLs into regex
			if (!$regex) {
				$provider = '#'.str_replace('___wildcard___', '(.+)', preg_quote(str_replace('*', '___wildcard___', $provider), '#')).'#i';
			}
			if (preg_match($provider, $url)) {
				$embedUrl = wp_oembed_get($url, array('discover' => false));
				if (empty($embedUrl)) {
					return $url;
				} else {
					return $embedUrl;
				}
			}
		}

		return $url;
	}

	public function hidelinks($content) {
		#save unedited content
		$original = $content;

		$sffilters = SP()->options->get('sffilters');
		$string    = stripslashes($sffilters['sfnolinksmsg']);
		$content   = preg_replace("#(<a.*>).*(</a>)#", $string, $content);

		$content = apply_filters('sph_display_hidelinks_filter', $content, $original);

		return $content;
	}

	public function legacyUrl($content) {
		# some legacy links to media or internal links to topics may need scheme changing to avoid mixed content errors
		$content = str_replace(SPALTURL, SPHOMEURL, $content);
		
		return $content;
	}
	
	public function signature($content) {
		#save unedited content
		$original = $content;

		$content = $this->text($content);

		$sfsigimagesize = SP()->options->get('sfsigimagesize');
		if ($sfsigimagesize['sfsigwidth'] > 0 || $sfsigimagesize['sfsigheight'] > 0) $content = preg_replace_callback('/<img[^>]*>/', array($this, 'check_sig'), $content);

		$content = apply_filters('sph_display_signature_filter', $content, $original);

		return $content;
	}

	# Version: 5.0

	private function check_sig($match) {
		$sfsigimagesize = SP()->options->get('sfsigimagesize');

		# get the elements of the img tags
		preg_match('/title\s*=\s*"([^"]*)"|title\s*=\s*\'([^\']*)\'/i', $match[0], $title);
		preg_match('/width\s*=\s*"([^"]*)"|width\s*=\s*\'([^\']*)\'/i', $match[0], $width);
		preg_match('/height\s*=\s*"([^"]*)"|height\s*=\s*\'([^\']*)\'/i', $match[0], $height);
		preg_match('/src\s*=\s*"([^"]*)"|src\s*=\s*\'([^\']*)\'/i', $match[0], $src);
		preg_match('/style\s*=\s*"([^"]*)"|style\s*=\s*\'([^\']*)\'/i', $match[0], $style);
		preg_match('/alt\s*=\s*"([^"]*)"|alt\s*=\s*\'([^\']*)\'/i', $match[0], $alt);

		# check for possible single quote match or double quote
		if (empty($title[1]) && !empty($title[2])) $title[1] = $title[2];
		if (empty($width[1]) && !empty($width[2])) $width[1] = $width[2];
		if (empty($height[1]) && !empty($height[2])) $height[1] = $height[2];
		if (empty($src[1]) && !empty($src[2])) $src[1] = $src[2];
		if (empty($style[1]) && !empty($style[2])) $style[1] = $style[2];
		if (empty($alt[1]) && !empty($alt[2])) $alt[1] = $alt[2];

		# if user defined heights are valid, just return
		if ((isset($width[1]) && $width[1] <= $sfsigimagesize['sfsigwidth']) && (isset($height[1]) && $height[1] <= $sfsigimagesize['sfsigheight'])) {
			return $match[0];
		}

		# insepct the image itself
		$display_width  = '';
		$display_height = '';
		$size           = SP()->primitives->get_image_size($src[1], true);

		if (!empty($size)) {
			# Did image exist?
			if ($size[0] && $size[1]) {
				# check width
				if (isset($width[1]) && ($width[1] <= $sfsigimagesize['sfsigwidth'] || $sfsigimagesize['sfsigwidth'] == 0)) {# width specified and less than max allowed
					$display_width = ' width="'.$width[1].'"';
				} else if ($sfsigimagesize['sfsigwidth'] > 0 && $size[0] > $sfsigimagesize['sfsigwidth']) {
					$display_width = ' width="'.$sfsigimagesize['sfsigwidth'].'"';
				}

				# check the height
				if (isset($height[1]) && ($height[1] <= $sfsigimagesize['sfsigheight'] || $sfsigimagesize['sfsigheight'] == 0)) { # height specified and less than max allowed
					$display_height = ' height="'.$height[1].'"';
				} else if ($sfsigimagesize['sfsigheight'] > 0 && $size[1] > $sfsigimagesize['sfsigheight']) {
					$display_height = ' height="'.$sfsigimagesize['sfsigheight'].'"';
				}
			} else {
				# image not found, strip tags
				return '';
			}
		} else {
			# problem checking sizes, so just limit
			$display_width  = ' width="'.$sfsigimagesize['sfsigwidth'].'"';
			$display_height = ' height="'.$sfsigimagesize['sfsigheight'].'"';
		}

		# add attributes back in if passed
		$style = (!empty($style)) ? ' style="'.$style[1].'"' : '';
		$title = (!empty($title)) ? ' title="'.$title[1].'"' : '';
		$alt   = (!empty($alt)) ? ' alt="'.$alt[1].'"' : '';

		return '<img src="'.$src[1].'"'.$display_width.$display_height.$style.$title.$alt.' />';
	}

	public function tooltip($content, $status) {
		#save unedited content
		$original = $content;

		# can the current user view this post?
		if (!SP()->user->thisUser->moderator && $status == 1) {
			$content = SP()->primitives->front_text('Post Awaiting Approval by Forum Administrator');
		} else {
			$content = addslashes($content);
			$content = SP()->saveFilters->nohtml($content);

			# remove shortcodes to prevent messing up tooltip
			$content = strip_shortcodes($content);
			$length  = apply_filters('sph_tooltip_length_chars', 300);
			if (strlen($content) > $length) {
				$pos = strpos($content, ' ', $length);
				if ($pos === false) $pos = $length;
				$content = substr($content, 0, $pos).'...';
			}
			$content = htmlspecialchars($content, ENT_QUOTES, SPCHARSET);
			$content = str_replace('&amp;', '&', $content);
		}

		$content = apply_filters('sph_display_tooltip_filter', $content, $original, $status);

		return $content;
	}

	public function rss($content) {
		#save unedited content
		$original = $content;

		# 1: Backwards compatible make links clickable
		$content = $this->links($content);

		# 3: Convert Chars
		$content = $this->chars($content);

		# 4: Format the paragraphs
		$content = $this->paragraphs($content);

		# 5: strip shortcodes
		if (SP()->options->get('sffiltershortcodes')) $content = $this->shortcodes($content);

		# 6: hide links
		$forum_id = (!empty(SP()->rewrites->pageData['forumid'])) ? SP()->rewrites->pageData['forumid'] : '';
		if (!SP()->auths->get('view_links', $forum_id)) $content = $this->hidelinks($content);

		# 7: apply any users custom filters
		$content = apply_filters('sph_display_rss_content_filter', $content, $original);

		return $content;
	}

	public function spoilers($atts, $content) {
		global $spoilerID;

		#save unedited content
		$original = $content;

		if (!isset($spoilerID)) {
			$spoilerID = 1;
		} else {
			$spoilerID++;
		}

		$out = '';
		$out .= '<div class="spSpoiler">';
		$out .= '<div class="spReveal">';
		$reveal = SP()->primitives->front_text('Reveal Spoiler');
		$hide   = SP()->primitives->front_text('Hide Spoiler');
		$out .= "<a id='spRevealLink$spoilerID' class='spShowSpoiler' data-spoilerid='$spoilerID' data-reveal='".esc_attr($reveal)."' data-hide='".esc_attr($hide)."'>$reveal</a>";
		$out .= '<input type="hidden" id="spSpoilerState'.$spoilerID.'" name="spSpoilerState'.$spoilerID.'" value="0" />';
		$out .= '</div>';
		$out .= '<div class="spSpoilerContent" id="spSpoilerContent'.$spoilerID.'">';
		$out .= '<p>'.$content.'</p>';
		$out .= '</div></div>';

		$out = apply_filters('sph_display_spoiler_filter', $out, $original);

		return $out;
	}
}