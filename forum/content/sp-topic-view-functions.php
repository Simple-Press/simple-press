<?php
/*
Simple:Press
Template Function Handler
$LastChangedDate: 2016-10-01 12:35:19 -0500 (Sat, 01 Oct 2016) $
$Rev: 14605 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# ======================================================================================
#
# TOPIC VIEW
# Topic Head Functions
#
# ======================================================================================

# --------------------------------------------------------------------------------------
#
#	sp_TopicHeaderIcon()
#	Display Topic Icon
#	Scope:	Topic View
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_TopicHeaderIcon($args='') {
	global $spThisTopic;
	$defs = array('tagId' 		=> 'spTopicHeaderIcon',
				  'tagClass' 	=> 'spHeaderIcon',
				  'icon' 		=> 'sp_TopicIcon.png',
				  'echo'		=> 1,
				  'get'			=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_TopicHeaderIcon_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$echo		= (int) $echo;
	$get		= (int) $get;

	# Check if a custom icon
	if (!empty($spThisTopic->topic_icon)) {
		$icon = sp_paint_custom_icon($tagClass, SFCUSTOMURL.$spThisTopic->topic_icon);
	} else {
		$icon = sp_paint_icon($tagClass, SPTHEMEICONSURL, sanitize_file_name($icon));
	}

	if ($get) return $icon;

	$out = sp_paint_icon_id($icon, $tagId);
	$out = apply_filters('sph_TopicHeaderIcon', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_TopicHeaderName()
#	Display Topic Name/Title in Header
#	Scope:	Topic View
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_TopicHeaderName($args='') {
	global $spThisTopic;
	$defs = array('tagId' 		=> 'spTopicHeaderName',
				  'tagClass' 	=> 'spHeaderName',
				  'truncate'	=> 0,
				  'echo'		=> 1,
				  'get'			=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_TopicHeaderName_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$truncate	= (int) $truncate;
	$echo		= (int) $echo;
	$get		= (int) $get;

	if ($get) return sp_truncate($spThisTopic->topic_name, $truncate);

	$out = (empty($spThisTopic->topic_name)) ? '' : "<div id='$tagId' class='$tagClass'>".sp_truncate($spThisTopic->topic_name, $truncate)."</div>\n";
	$out = apply_filters('sph_TopicHeaderName', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_TopicHeaderRSSButton()
#	Display Topic Level RSS Button
#	Scope:	Topic View
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_TopicHeaderRSSButton($args='', $label='', $toolTip='') {
	global $spThisForum, $spThisTopic, $spThisUser;

    if (!sp_get_auth('view_forum', $spThisTopic->forum_id) || $spThisTopic->forum_rss_private) return;

	$defs = array('tagId' 		=> 'spTopicHeaderRSSButton',
				  'tagClass' 	=> 'spButton',
				  'icon' 		=> 'sp_Feed.png',
				  'iconClass'	=> 'spIcon',
				  'echo'		=> 1,
				  'get'			=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_TopicHeaderRSSButton_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$icon		= sanitize_file_name($icon);
	$iconClass 	= esc_attr($iconClass);
	$toolTip	= esc_attr($toolTip);
	$echo		= (int) $echo;
	$get		= (int) $get;

	# Get or construct rss url
	$rssOpt = sp_get_option('sfrss');
	if ($rssOpt['sfrssfeedkey'] && isset($spThisUser->feedkey)) {
		$rssUrl = trailingslashit(sp_build_url($spThisTopic->forum_slug, $spThisTopic->topic_slug, 0, 0, 0, 1)).user_trailingslashit($spThisUser->feedkey);
	} else {
		$rssUrl = sp_build_url($spThisTopic->forum_slug, $spThisTopic->topic_slug, 0, 0, 0, 1);
	}

	if ($get) return $rssUrl;

	$out = "<a class='$tagClass' id='$tagId' title='$toolTip' rel='nofollow' href='$rssUrl'>";
	if (!empty($icon)) $out.= sp_paint_icon($iconClass, SPTHEMEICONSURL, $icon);
	if (!empty($label)) $out.= sp_filter_title_display($label);
	$out.= "</a>\n";
	$out = apply_filters('sph_TopicHeaderRSSButton', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_NoTopicMessage()
#	Display Message when no Topic can be displayed
#	Scope:	Topic View
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_NoTopicMessage($args='', $deniedMessage='', $definedMessage='') {
	global $spTopicView;
	$defs = array('tagId'		=> 'spNoTopicMessage',
				  'tagClass'	=> 'spMessage',
				  'echo'		=> 1,
				  'get'			=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_NoTopicMessage_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$echo		= (int) $echo;
	$get		= (int) $get;

	# is Access denied
	if ($spTopicView->topicViewStatus == 'no access') {
		$m = sp_filter_title_display($deniedMessage);
	} elseif ($spTopicView->topicViewStatus == 'no data') {
		$m = sp_filter_title_display($definedMessage);
	} elseif ($spTopicView->topicViewStatus == 'sneak peek') {
		$sflogin = sp_get_option('sflogin');
		if (!empty($sflogin['sfsneakredirect'])) {
            sp_redirect(apply_filters('sph_sneak_redirect', $sflogin['sfsneakredirect']));
		} else {
			$sneakpeek = sp_get_sfmeta('sneakpeek', 'message');
			$m = ($sneakpeek) ? sp_filter_text_display($sneakpeek[0]['meta_value']) : '';
		}
	} else {
		return;
	}

	if ($get) return $m;

	$out = "<div id='$tagId' class='$tagClass'>$m</div>\n";
	$out = apply_filters('sph_NoTopicMessage', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_PostNewButton()
#	Display The New Post Button
#	Scope:	Topic View
#	Version: 5.0
#
#	Changelog
#	5.4.2	Added 'iconDenied' argument
#			Added 'toolTipDenied' parameter
#	5.5.0	Added 'iconStatusClass' argument
#
# --------------------------------------------------------------------------------------
function sp_PostNewButton($args='', $label='', $toolTip='', $toolTipLock='', $toolTipDenied='') {
	global $spThisTopic, $spGlobals, $spThisUser;

	# can be empty if request is for a bogus topic slug
	if(empty($spThisTopic)) return;

	if ($spThisTopic->editmode) return;

	$allowed = ($spThisTopic->reply_own_topics && $spThisTopic->topic_starter == $spThisUser->ID);
	if ($spThisTopic->reply_topics) $allowed = true;

	$defs = array('tagId' 			=> 'spPostNewButton',
				  'tagClass' 		=> 'spButton',
				  'icon' 			=> 'sp_NewPost.png',
				  'iconLock'		=> 'sp_TopicStatusLock.png',
				  'iconDenied'		=> 'sp_WriteDenied.png',
				  'iconClass'		=> 'spIcon',
				  'iconStatusClass'	=> 'spIcon',
				  'echo'			=> 1,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_PostNewButton_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId			= esc_attr($tagId);
	$tagClass		= esc_attr($tagClass);
	$icon			= sanitize_file_name($icon);
	$iconClass 		= esc_attr($iconClass);
	$iconStatusClass= esc_attr($iconStatusClass);
	$toolTip		= esc_attr($toolTip);
	$toolTipLock	= esc_attr($toolTipLock);
	$echo			= (int) $echo;

	# is the forum locked?
	$out = '';
    $lock = false;
	if ($spGlobals['lockdown'] || $spThisTopic->forum_status || $spThisTopic->topic_status) {
		if (!empty($iconLock)) {
			$iconLock = sp_paint_icon($tagClass.' '.$iconStatusClass, SPTHEMEICONSURL, sanitize_file_name($iconLock), $toolTipLock);
			$out.= sp_paint_icon_id($iconLock, $tagId);
		}
        if (!$spThisUser->admin) $lock = true;
	}

    if (!$lock && $allowed) {
		$out.= "<a class='$tagClass spNewPostButton' id='$tagId' title='$toolTip' data-form='spPostForm' data-type='post'>\n";
		if (!empty($icon)) $out.= sp_paint_icon($iconClass, SPTHEMEICONSURL, $icon);
		if (!empty($label)) $out.= sp_filter_title_display($label);
		$out.= "</a>\n";
	}
	# Display if user not allowed to start topics
	if (!$allowed && !empty($toolTipDenied)) {
		if (!empty($iconDenied)) {
			$out.= "<a href='#' class='$tagClass' id='$tagId' title='$toolTipDenied'>\n";
			$out.= sp_paint_icon($iconStatusClass, SPTHEMEICONSURL, sanitize_file_name($iconDenied), $toolTipDenied);
			$out.= "</a>\n";
		}
	}

	$out = apply_filters('sph_PostNewButton', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_PostNewTopicButton()
#	Display The New Topic Button
#	Scope:	Topic View
#	Version: 5.0
#
#	Changelog
#	5.5.0	Added 'iconStatusClass'
#
# --------------------------------------------------------------------------------------
function sp_PostNewTopicButton($args='', $label='', $toolTip='', $toolTipLock='') {
	global $spThisTopic, $spGlobals, $spThisUser;

	# can be empty if request is for a bogus topic slug
	if(empty($spThisTopic)) return;

	if (!sp_get_auth('start_topics', $spThisTopic->forum_id)) return;

	$defs = array('tagId' 			=> 'spPostNewTopicButton',
				  'tagClass' 		=> 'spButton',
				  'icon' 			=> 'sp_NewTopic.png',
				  'iconLock'		=> 'sp_ForumStatusLock.png',
				  'iconClass'		=> 'spIcon',
				  'iconStatusClass'	=> 'spIcon',
				  'echo'			=> 1,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_PostNewTopicButton_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId			= esc_attr($tagId);
	$tagClass		= esc_attr($tagClass);
	$icon			= sanitize_file_name($icon);
	$iconClass 		= esc_attr($iconClass);
	$iconStatusClass= esc_attr($iconStatusClass);
	$toolTip		= esc_attr($toolTip);
	$toolTipLock	= esc_attr($toolTipLock);
	$echo			= (int) $echo;

	# is the forum locked?
	$out = '';
    $lock = false;
	if ($spGlobals['lockdown'] || $spThisTopic->forum_status) {
		if (!empty($iconLock)) {
			$out.= sp_paint_icon($tagClass.' '.$iconStatusClass, SPTHEMEICONSURL, sanitize_file_name($iconLock), $toolTipLock);
		}
        if (!$spThisUser->admin) $lock = true;
	}
    if (!$lock && sp_get_auth('start_topics', $spThisTopic->forum_id)) {
		$url = sp_build_url($spThisTopic->forum_slug, '', 1, 0).sp_add_get().'new=topic';
		$out.= "<a href='$url' class='$tagClass' id='$tagId' title='$toolTip'>\n";
		if (!empty($icon)) $out.= sp_paint_icon($iconClass, SPTHEMEICONSURL, $icon);
		if (!empty($label)) $out.= sp_filter_title_display($label);
		$out.= "</a>\n";
	}

	$out = apply_filters('sph_PostNewTopicButton', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_PostIndexPageLinks()
#	Display page links for post list
#	Scope:	Post List Loop
#	Version: 5.0
#		5.2:	showEmpty added to display div even when empty
#
# --------------------------------------------------------------------------------------
function sp_PostIndexPageLinks($args='', $label='', $toolTip='', $jumpToolTip='') {
	global $spThisTopic, $spGlobals, $jumpID;

	# can be empty if request is for a bogus topic slug
	if(empty($spThisTopic)) return;

	$defs = array('tagClass' 		=> 'spPageLinks',
				  'prevIcon'		=> 'sp_ArrowLeft.png',
				  'nextIcon'		=> 'sp_ArrowRight.png',
				  'jumpIcon'		=> 'sp_Jump.png',
				  'iconClass'		=> 'spIcon',
				  'pageLinkClass'	=> 'spPageLinks',
				  'curPageClass'	=> 'spCurrent',
				  'linkClass'	    => 'spLink',
				  'showLinks'		=> 4,
				  'showJump'		=> 1,
				  'showEmpty'		=> 0,
				  'echo'			=> 1
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_PostIndexPageLinks_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagClass		= esc_attr($tagClass);
	$iconClass		= esc_attr($iconClass);
	$pageLinkClass	= esc_attr($pageLinkClass);
	$curPageClass	= esc_attr($curPageClass);
	$linkClass		= esc_attr($linkClass);
	$showLinks		= (int) $showLinks;
	$showJump		= (int) $showJump;
	$showEmpty		= (int) $showEmpty;
	$label			= sp_filter_title_display($label);
	$toolTip		= esc_attr($toolTip);
	$jumpToolTip	= esc_attr($jumpToolTip);
	$echo			= (int) $echo;

	if ($spThisTopic->posts_per_page >= $spThisTopic->post_count) {
		if ($showEmpty) echo "<div class='$tagClass'></div>";
		return;
	}

	if (!empty($prevIcon)) $prevIcon	= sp_paint_icon($iconClass, SPTHEMEICONSURL, sanitize_file_name($prevIcon), $toolTip);
	if (!empty($nextIcon)) $nextIcon	= sp_paint_icon($iconClass, SPTHEMEICONSURL, sanitize_file_name($nextIcon), $toolTip);
	if (!empty($jumpIcon)) $jumpIcon	= sp_paint_icon($iconClass, SPTHEMEICONSURL, sanitize_file_name($jumpIcon), $jumpToolTip);

	global $spVars;
	$curToolTip = str_ireplace('%PAGE%', $spVars['page'], $toolTip);

	if (isset($jumpID) ? $jumpID++ : $jumpID=1);

	$out = "<div class='$tagClass'>";
	$out.= "<span class='$pageLinkClass'>$label</span>";
	$out.= sp_page_prev($spVars['page'], $showLinks, $spThisTopic->topic_permalink, $pageLinkClass, $iconClass, $prevIcon, $nextIcon, $toolTip, '');

	$url = $spThisTopic->topic_permalink;
	if ($spVars['page'] > 1) $url = user_trailingslashit(trailingslashit($url).'page-'.$spVars['page']);
	$url = apply_filters('sph_page_link', $url, $spVars['page']);

	$out.= "<a href='$url' class='$pageLinkClass $curPageClass' title='$curToolTip'>".$spVars['page'].'</a>';

	$out.= sp_page_next($spVars['page'], $spThisTopic->total_pages, $showLinks, $spThisTopic->topic_permalink, $pageLinkClass, $iconClass, $prevIcon, $nextIcon, $toolTip, '');

    if ($showJump) {
		$out.= '<span class="spPageJump">';
		$site = wp_nonce_url(SPAJAXURL.'spTopicPageJump&amp;targetaction=page-popup&amp;url='.$spThisTopic->topic_permalink.'&amp;max='.$spThisTopic->total_pages, 'spPageJump');
		$out.= "<a id='jump-$jumpID' rel='nofollow' class='$linkClass spTopicPageJump' title='$jumpToolTip' data-site='$site' data-label='$jumpToolTip' data-width='250' data-height='0' data-align='0'>";
		$out.= $jumpIcon;
        $out.= '</a>';
        $out.= '</span>';
    }

	$out.= "</div>\n";
	$out = apply_filters('sph_PostIndexPageLinks', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# ======================================================================================
#
# Topic VIEW
# Post Loop Functions
#
# ======================================================================================

# --------------------------------------------------------------------------------------
#
#	sp_PostIndexAnchor()
#	Embed the anchor for locating this post in urls
#	Scope:	Post Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_PostIndexAnchor() {
	global $spThisPost;

	# Define the post anchor here
	echo "<a id='p$spThisPost->post_id'></a>\n";
}

# --------------------------------------------------------------------------------------
#
#	sp_PostIndexUserDate()
#	Display Post date
#	Scope:	Post Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_PostIndexUserDate($args='') {
	global $spThisPost, $spThisPostUser;

	$defs = array('tagId'    		=> 'spPostIndexUserDate%ID%',
				  'tagClass' 		=> 'spPostUserDate',
				  'nicedate'		=> 0,
				  'date'  			=> 1,
				  'time'  			=> 1,
				  'stackdate'		=> 1,
				  'echo'			=> 1,
				  'get'				=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_PostIndexUserDate_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$nicedate	= (int) $nicedate;
	$date		= (int) $date;
	$time		= (int) $time;
	$stackdate	= (int) $stackdate;
	$echo		= (int) $echo;
	$get		= (int) $get;

	($stackdate ? $dlb='<br />' : $dlb=' - ');

	$tagId = str_ireplace('%ID%', $spThisPost->post_id, $tagId);

	if ($get) return $spThisPost->post_date;

	$out = "<div id='$tagId' class='$tagClass'>";

	# date/time
	if ($nicedate) {
		$out.= sp_nicedate($spThisPost->post_date);
	} else {
		if ($date) {
			$out.= sp_date('d', $spThisPost->post_date);
			if ($time) {
				$out.= $dlb.sp_date('t', $spThisPost->post_date);
			}
		}
	}
	$out.= "</div>\n";
	$out = apply_filters('sph_PostIndexUserDate', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_PostIndexUserName()
#	Display Post display if user name (poster)
#	Scope:	Post Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_PostIndexUserName($args='') {
	global $spThisPost, $spThisPostUser;

	$defs = array('tagId'    		=> 'spPostIndexUserName%ID%',
				  'tagClass' 		=> 'spPostUserName',
				  'truncateUser'	=> 0,
				  'echo'			=> 1,
				  'get'				=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_PostIndexUserName_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId			= esc_attr($tagId);
	$tagClass		= esc_attr($tagClass);
	$truncateUser	= (int) $truncateUser;
	$echo			= (int) $echo;
	$get			= (int) $get;

	$tagId = str_ireplace('%ID%', $spThisPost->post_id, $tagId);

	$out = "<div id='$tagId' class='$tagClass'>";
	if ($spThisPostUser->member) {
		$name = sp_build_name_display($spThisPostUser->ID, sp_truncate($spThisPostUser->display_name, $truncateUser));
	} else {
		$name = sp_truncate($spThisPost->guest_name, $truncateUser);
	}
	$out.= $name;

	if ($get) return $name;

	$out.= "</div>\n";
	$out = apply_filters('sph_PostIndexUserName', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_PostIndexUserPosts()
#	Display Post count
#	Scope:	Post Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_PostIndexUserPosts($args='', $label='') {
	global $spThisPost, $spThisPostUser;
	if ($spThisPostUser->guest) return;

	$defs = array('tagId'    		=> 'spPostIndexUserPosts%ID%',
				  'tagClass' 		=> 'spPostUserPosts',
				  'echo'			=> 1,
				  'get'				=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_PostIndexUserPosts_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$text		= sp_filter_title_display(str_replace('%COUNT%', $spThisPostUser->posts, $label));
	$echo		= (int) $echo;
	$get		= (int) $get;

	$tagId = str_ireplace('%ID%', $spThisPost->post_id, $tagId);

	if ($get) return $spThisPostUser->posts;

	$out = "<div id='$tagId' class='$tagClass'>";
	$out.= $text;
	$out.= "</div>\n";
	$out = apply_filters('sph_PostIndexUserPosts', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_PostIndexUserRegistered()
#	Display user registration date
#	Scope:	Post Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_PostIndexUserRegistered($args='', $label='') {
	global $spThisPost, $spThisPostUser;
	if ($spThisPostUser->guest) return;

	$defs = array('tagId'    		=> 'spPostIndexUserRegistered%ID%',
				  'tagClass' 		=> 'spPostUserRegistered',
				  'dateFormat'		=> 'd',
				  'echo'			=> 1,
				  'get'				=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_PostIndexUserRegistered_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$dateFormat	= esc_attr($dateFormat);
	$text		= sp_filter_title_display(str_replace('%DATE%', sp_date($dateFormat, $spThisPostUser->user_registered), $label));
	$echo		= (int) $echo;
	$get		= (int) $get;

	$tagId = str_ireplace('%ID%', $spThisPost->post_id, $tagId);

	if ($get) return $spThisPostUser->posts;

	$out = "<div id='$tagId' class='$tagClass'>";
	$out.= $text;
	$out.= "</div>\n";
	$out = apply_filters('sph_PostIndexUserRegistered', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_PostIndexUserBadges()
#	Display user badges/ranks and.or usergroup badges
#	Scope:	Post Loop
#	Version: 5.6.0
#
# --------------------------------------------------------------------------------------
function sp_PostIndexUserBadges($args='', $label='') {
	global $spThisPost, $spThisPostUser, $spPaths;

	$defs = array('tagId'		=> 'spPostIndexUserBadges%ID%',
				  'tagClass' 	=> 'spPostUserRank',
				  'labelClass'	=> 'spInRowLabel',
				  'rank'		=> 0,
				  'rankClass'	=> 'spInRowRank',
				  'badge'		=> 1,
				  'badgeClass'	=> 'spUserBadge',
				  'stack'		=> 1,
				  'order'		=> 'SNU',
				  'showAll'		=> 1,
				  'echo'		=> 1,
				  'get'			=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_PostIndexUserBadges_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId			= esc_attr($tagId);
	$tagClass		= esc_attr($tagClass);
	$labelClass		= esc_attr($labelClass);
	$rankClass		= esc_attr($rankClass);
	$badgeClass		= esc_attr($badgeClass);
	$rank			= (int) $rank;
	$badge			= (int) $badge;
	$stack			= (int) $stack;
	$order			= esc_attr($order);
	$showAll		= (int) $showAll;
	$echo			= (int) $echo;
	$get			= (int) $get;

	$tagId = str_ireplace('%ID%', $spThisPost->post_id, $tagId);
	$att = ($stack) ? '<br />' : '';
	$ranks = array();
	$idx=0;

	for($x=0; $x<(strlen($order)); $x++) {
		$xRank = substr($order, $x, 1);
		switch($xRank) {
			case 'S': # Special Rank
				if(!empty($spThisPostUser->special_rank)) {
					foreach ($spThisPostUser->special_rank as $thisRank) {
						$ranks[$idx]['name'] = $thisRank['name'];
						if($thisRank['badge']) $ranks[$idx]['badge'] = $thisRank['badge'];
						$idx++;
					}
				}
				break;
			case 'N': # Normal Rank
				if(!empty($spThisPostUser->rank)) {
					$ranks[$idx]['name'] = $spThisPostUser->rank[0]['name'];
					if($spThisPostUser->rank[0]['badge']) $ranks[$idx]['badge'] = $spThisPostUser->rank[0]['badge'];
					$idx++;
				}
				break;
			case 'U': # UserGroup badge
				if(!empty($spThisPostUser->memberships)) {
					foreach($spThisPostUser->memberships as $r) {
						if($r['usergroup_badge']) $ranks[$idx]['badge'] = SF_STORE_URL.'/'.$spPaths['ranks'].'/'.$r['usergroup_badge'];
						$ranks[$idx]['name'] = $r['usergroup_name'];
						$idx++;
					}
				}
				break;
		}
		if(!$showAll) {
			if(!empty($ranks)) break;
		}
	}

	if ($get) return $ranks;

	# now render it
	$out = "<div id='$tagId' class='$tagClass'>";
	if (!empty($label)) $out.= "<span class='$labelClass'>".sp_filter_title_display($label)."$att</span>";
	foreach ($ranks as $thisRank) {
		if ($badge && !empty($thisRank['badge'])) $out.= "<img class='$badgeClass' src='".$thisRank['badge']."' alt='' />$att";
		if ($rank) $out.= "<span class='$rankClass'>".$thisRank['name']."</span>$att";
	}
	$out.= "</div>\n";
	$out = apply_filters('sph_PostIndexUserBadges', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_PostIndexUserRank()
#	Display user forum rank
#	Scope:	Post Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_PostIndexUserRank($args='') {
	global $spThisPost, $spThisPostUser;

	$defs = array('tagId'    	      => 'spPostIndexUserRank%ID%',
				  'tagClass' 	      => 'spPostUserRank',
				  'imgClass'	      => 'spUserBadge',
				  'showBadge'	      => 1,
				  'showTitle'	      => 1,
				  'hideIfSpecialRank' => 1,
				  'echo'		      => 1,
				  'get'			      => 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_PostIndexUserRank_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		        = esc_attr($tagId);
	$tagClass	        = esc_attr($tagClass);
	$imgClass	        = esc_attr($imgClass);
	$showBadge	        = (int) $showBadge;
	$showTitle	        = (int) $showTitle;
	$hideIfSpecialRank	= (int) $hideIfSpecialRank;
	$echo		        = (int) $echo;
	$get		        = (int) $get;

    if ($hideIfSpecialRank && !empty($spThisPostUser->special_rank)) return;

	$tagId = str_ireplace('%ID%', $spThisPost->post_id, $tagId);

	if ($get) return $spThisPostUser->rank[0];

    $show = false;
	$tout = "<div id='$tagId' class='$tagClass'>";
	if ($showBadge && !empty($spThisPostUser->rank[0]['badge'])) {
	    $show = true;
		$tout.= "<img class='$imgClass' src='".$spThisPostUser->rank[0]['badge']."' alt='' />\n";
		$tout.= "<br />";
	}
	if ($showTitle && !empty($spThisPostUser->rank[0]['name'])) {
	    $show = true;
		$tout.= '<span class="spRank-'.sp_create_slug($spThisPost->postUser->rank[0]['name'], false).'">'.$spThisPostUser->rank[0]['name'].'</span>';
	}
	$tout.= "</div>\n";

    $out = ($show) ? $tout : '';
	$out = apply_filters('sph_PostIndexUserRank', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_PostIndexUserSpecialRank()
#	Display user special ranks
#	Scope:	Post Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_PostIndexUserSpecialRank($args='') {
	global $spThisPost, $spThisPostUser;
	if ($spThisPostUser->guest) return;

	$defs = array('tagId'    	=> 'spPostIndexUserSpecialRank%ID%',
				  'tagClass' 	=> 'spPostUserSpecialRank',
				  'imgClass'	=> 'spUserBadge',
				  'showBadge'	=> 1,
				  'showTitle'	=> 1,
                  'stacked'     => 1,
				  'echo'		=> 1,
				  'get'			=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_PostIndexUserSpecialRank_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$imgClass	= esc_attr($imgClass);
	$showBadge	= (int) $showBadge;
	$showTitle	= (int) $showTitle;
	$stacked	= (int) $stacked;
	$echo		= (int) $echo;
	$get		= (int) $get;

    if (!$showTitle && !$showBadge) return;

	$tagId = str_ireplace('%ID%', $spThisPost->post_id, $tagId);

	if ($get) return $spThisPostUser->special_rank;

    $show = false;
	$tout = "<div id='$tagId' class='$tagClass'>";
	if (($showBadge || $showTitle) && !empty($spThisPostUser->special_rank)) {
		foreach ($spThisPostUser->special_rank as $rank) {
			if ($showBadge && !empty($rank['badge'])) {
        	    $show = true;
				$tout.= "<img class='$imgClass' src='".$rank['badge']."' alt='' />\n";
                $tout.= ($stacked) ? '<br />' : ' ';
			}
			if ($showTitle && !empty($rank['name'])) {
        	    $show = true;
				$tout.= '<span class="spSpecialRank-'.sp_create_slug($rank['name'], false).'">'.$rank['name'].'</span>';
                $tout.= ($stacked) ? '<br />' : ' ';
			}
		}
	}
	$tout.= "</div>\n";

    $out = ($show) ? $tout : '';
	$out = apply_filters('sph_PostIndexUserSpecialRank', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_PostIndexUserMemberships()
#	Display user group memberships for user
#	Scope:	Post Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_PostIndexUserMemberships($args='', $noMembershipLabel='', $adminLabel='') {
	global $spThisPost, $spThisPostUser, $spPaths;

	$defs = array('tagId'    	=> 'spPostIndexUserMemberships%ID%',
				  'tagClass' 	=> 'spPostUserMemberships',
                  'stacked'     => 1,
                  'showTitle'   => 1,
                  'showBadge'   => 1,
				  'echo'		=> 1,
				  'get'			=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_PostIndexUserMemberships_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$stacked	= (int) $stacked;
	$showTitle	= (int) $showTitle;
	$showBadge	= (int) $showBadge;
	$echo		= (int) $echo;
	$get		= (int) $get;

	$tagId = str_ireplace('%ID%', $spThisPost->post_id, $tagId);

	if ($get) return $spThisPostUser->memberships;

    $show = false;
	$tout = "<div id='$tagId' class='$tagClass'>";
    if (!empty($spThisPostUser->memberships)) {
        $first = true;
        $split = ($stacked) ? '<br />' : ', ';
    	foreach ($spThisPostUser->memberships as $membership) {
    	    if (!$first) $tout.= $split;
			if ($showBadge && !empty($membership['usergroup_badge'])) {
        	    $show = true;
                $tout.= "<img src='".SF_STORE_URL.'/'.$spPaths['ranks'].'/'.$membership['usergroup_badge']."' alt='' />";
                $tout.= '<br />';
            }
            if ($showTitle) {
        	    $show = true;
                $tout.= '<span class="spUserGroup-'.sp_create_slug($membership['usergroup_name'], false).'">'.$membership['usergroup_name'].'</span><br />';
            }
            $first = false;
    	}
    } else if ($spThisPostUser->admin) {
        if ($showTitle && !empty($adminLabel)) {
    	    $show = true;
            $tout.= sp_filter_title_display($adminLabel);
        }
    } else {
        if (!empty($noMembershipLabel)) {
    	    $show = true;
            $tout.= sp_filter_title_display($noMembershipLabel);
        }
    }
	$tout.= "</div>\n";

    $out = ($show) ? $tout : '';
	$out = apply_filters('sph_PostIndexUserMemberships', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_PostIndexNumber()
#	Display Post Index Number
#	Scope:	Post Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_PostIndexNumber($args='') {
	global $spThisPost;

	$defs = array('tagId'    	=> 'spPostIndexNumber%ID%',
				  'tagClass' 	=> 'spLabelBordered',
				  'echo'		=> 1,
				  'get'			=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_PostIndexNumber_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$echo		= (int) $echo;
	$get		= (int) $get;

	$tagId = str_ireplace('%ID%', $spThisPost->post_id, $tagId);

	if ($get) return $spThisPost->post_index;

	$out = "<span id='$tagId' class='$tagClass'>$spThisPost->post_index</span>";
	$out = apply_filters('sph_PostIndexNumber', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_PostIndexPinned()
#	Display Post Pin Stats Icon
#	Scope:	Post Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_PostIndexPinned($args='', $toolTip='') {
	global $spThisPost;

	if (!$spThisPost->post_pinned) return;

	$defs = array('tagId'    	=> 'spPostIndexPinned%ID%',
				  'tagClass' 	=> 'spStatusIcon',
				  'iconPin'		=> 'sp_TopicStatusPin.png',
				  'echo'		=> 1,
				  'get'			=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_PostIndexPinned_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$echo		= (int) $echo;
	$get		= (int) $get;
	$icon 		= sanitize_file_name($iconPin);
	$toolTip	= sp_filter_title_display($toolTip);

	$tagId = str_ireplace('%ID%', $spThisPost->post_id, $tagId);

	if ($get) return $spThisPost->post_pinned;

	$out = "<span id='$tagId' class='$tagClass'>";
	if (!empty($icon)) $out.= sp_paint_icon('', SPTHEMEICONSURL, $icon, $toolTip);
	$out.= "</span>";
	$out = apply_filters('sph_PostIndexPinned', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_PostIndexNewPost()
#	Display Post Index Number
#	Scope:	Post Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_PostIndexNewPost($args='', $label='') {
	global $spThisTopic, $spThisPost, $spThisUser;

	if (!$spThisUser->member || empty($label)) return;
	if (!$spThisPost->new_post) return;
	if ($spThisPost->user_id == $spThisUser->ID) return;

	$defs = array('tagId'    	=> 'spPostIndexNewPost%ID%',
				  'tagClass' 	=> 'spLabelBordered',
				  'echo'		=> 1,
				  'get'			=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_PostIndexNewPost_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$echo		= (int) $echo;
	$get		= (int) $get;

	$tagId = str_ireplace('%ID%', $spThisPost->post_id, $tagId);

	if ($get) return true;

	$out = "<span id='$tagId' class='$tagClass'>".sp_filter_title_display($label)."</span>";
	$out = apply_filters('sph_PostIndexNewPost', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_PostIndexEditHistory()
#	Display Edit History of Post
#	Scope:	Post Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_PostIndexEditHistory($args='', $label='', $legend='', $toolTip='') {
	global $spThisPost;

	if (empty($spThisPost->edits) || empty($legend)) return;

	$defs = array('tagId' 		=> 'spPostIndexEditHistory%ID%',
				  'tagClass' 	=> 'spButton',
				  'icon' 		=> 'sp_EditHistory.png',
				  'iconClass'	=> 'spIcon',
				  'popup'		=> 1,
				  'count'		=> 0,
				  'echo'		=> 1,
				  'get'			=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_PostIndexEditHistory_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$icon		= sanitize_file_name($icon);
	$iconClass 	= esc_attr($iconClass);
	$toolTip	= sp_filter_title_display($toolTip);
	$popup		= (int) $popup;
	$count		= (int) $count;
	$echo		= (int) $echo;
	$get		= (int) $get;

	$tagId = str_ireplace('%ID%', $spThisPost->post_id, $tagId);

	if ($get) return $spThisPost->edits;

    # build history to show
    $edits = (empty($count)) ? $spThisPost->edits : array_slice($spThisPost->edits, max(count($spThisPost->edits) - $count, 0), $count);

	# Construct text
    if ($edits) {
    	$history = '<p>';
    	foreach ($edits as $edit) {
    		$thisLegend = str_replace('%USER%', $edit->by, $legend);
    		$thisLegend = str_replace('%DATE%', sp_apply_timezone($edit->at), $thisLegend);
    		$history.= $thisLegend.'<br />';
    	}
    	$history.= '</p>';
    }

	if ($popup) {
		$out = "<a class='$tagClass spEditPostHistory' id='$tagId' title='$toolTip' rel='nofollow' data-html='".esc_attr($history)."' data-label='$toolTip' data-width='400' data-height='0' data-align='0'>";
		if (!empty($icon)) $out.= sp_paint_icon($iconClass, SPTHEMEICONSURL, $icon);
		if (!empty($label)) $out.= sp_filter_title_display($label);
		$out.= "</a>\n";
	} else {
		$out.= "<div id='$tagId' class='$tagClass'>$history</div>\n";
	}
	$out = apply_filters('sph_PostIndexEditHistory', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_PostIndexPermalink()
#	Display Post Permalink
#	Scope:	Post Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_PostIndexPermalink($args='', $label='', $toolTip='') {
	global $spThisPost;

	$defs = array('tagId' 		=> 'spPostIndexPermalink%ID%',
				  'tagClass' 	=> 'spButton',
				  'icon' 		=> 'sp_Permalink.png',
				  'iconClass'	=> 'spIcon',
				  'echo'		=> 1,
				  'get'			=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_PostIndexPermalink_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$icon		= sanitize_file_name($icon);
	$iconClass 	= esc_attr($iconClass);
	$toolTip	= esc_attr($toolTip);
	$echo		= (int) $echo;
	$get		= (int) $get;

	$tagId = str_ireplace('%ID%', $spThisPost->post_id, $tagId);

	if ($get) return $spThisPost->post_permalink;

	$out = "<a class='$tagClass' id='$tagId' title='$toolTip' rel='nofollow' href='$spThisPost->post_permalink'>";
	if (!empty($icon)) $out.= sp_paint_icon($iconClass, SPTHEMEICONSURL, $icon);
	if (!empty($label)) $out.= sp_filter_title_display($label);
	$out.= "</a>\n";
	$out = apply_filters('sph_PostIndexPermalink', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_PostIndexPrint()
#	Display Post Print button/link
#	Scope:	Post Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_PostIndexPrint($args='', $label='', $toolTip='') {
	global $spThisPost;

	if ($spThisPost->post_status != 0) return;

	$defs = array('tagId' 		=> 'spPostIndexPrint%ID%',
				  'tagClass' 	=> 'spButton',
				  'icon' 		=> 'sp_Print.png',
				  'iconClass'	=> 'spIcon',
				  'echo'		=> 1,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_PostIndexPrint_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$icon		= sanitize_file_name($icon);
	$iconClass 	= esc_attr($iconClass);
	$toolTip	= esc_attr($toolTip);
	$echo		= (int) $echo;

	$tagId = str_ireplace('%ID%', $spThisPost->post_id, $tagId);
	$out = "<a class='$tagClass spPrintThisPost' id='$tagId' title='$toolTip' rel='nofollow' data-postid='spPostIndexContent$spThisPost->post_id'>";
	if (!empty($icon)) $out.= sp_paint_icon($iconClass, SPTHEMEICONSURL, $icon);
	if (!empty($label)) $out.= sp_filter_title_display($label);
	$out.= "</a>\n";
	$out = apply_filters('sph_PostIndexPrint', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_PostIndexQuote()
#	Display Post reply with quote button/link
#	Scope:	Post Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_PostIndexQuote($args='', $label='', $toolTip='') {
	global $spThisUser, $spThisPost, $spThisPostUser, $spThisTopic, $spGlobals;

    # checks for displaying button
	if ($spThisTopic->editmode) return;
	if ($spThisPost->post_status != 0 && !$spThisUser->admin) return;
	if (!sp_get_auth('reply_topics', $spThisTopic->forum_id)) return;
	if (($spGlobals['lockdown'] || $spThisTopic->forum_status || $spThisTopic->topic_status) && !$spThisUser->admin) return;
    if (!sp_get_auth('view_admin_posts', $spThisTopic->forum_id) && sp_is_forum_admin($spThisPost->user_id)) return;
    if (sp_get_auth('view_own_admin_posts', $spThisTopic->forum_id) && !sp_is_forum_admin($spThisPost->user_id) && !sp_is_forum_mod($spThisPost->user_id) && $spThisUser->ID != $spThisPost->user_id) return;

	$defs = array('tagId' 		=> 'spPostIndexQuote%ID%',
				  'tagClass' 	=> 'spButton',
				  'icon' 		=> 'sp_QuotePost.png',
				  'iconClass'	=> 'spIcon',
				  'echo'		=> 1,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_PostIndexQuote_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$icon		= sanitize_file_name($icon);
	$iconClass 	= esc_attr($iconClass);
	$toolTip	= esc_attr($toolTip);
	$echo		= (int) $echo;

	$tagId = str_ireplace('%ID%', $spThisPost->post_id, $tagId);

	$quoteUrl = wp_nonce_url(SPAJAXURL.'spQuotePost', 'spQuotePost');
	if ($spThisPostUser->member) {
		$name = $spThisPostUser->display_name;
	} else {
		$name = $spThisPost->guest_name;
	}
	$intro = esc_attr($name.' '.sp_text('said').' ');
	$out = "<a class='$tagClass spQuotePost' id='$tagId' title='$toolTip' rel='nofollow' data-postid='$spThisPost->post_id' data-intro='$intro' data-forumid='$spThisTopic->forum_id' data-url='$quoteUrl'>";
	if (!empty($icon)) $out.= sp_paint_icon($iconClass, SPTHEMEICONSURL, $icon);
	if (!empty($label)) $out.= sp_filter_title_display($label);
	$out.= "</a>\n";
	$out = apply_filters('sph_PostIndexQuote', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_PostIndexEdit()
#	Edit a post
#	Scope:	Post Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_PostIndexEdit($args='', $label='', $toolTip='') {
	global $spThisPost, $spThisPostUser, $spThisTopic, $spThisUser, $spGlobals;

	if ($spThisTopic->editmode) return;
	if ($spGlobals['lockdown']) return;

	$canEdit = false;
	if (sp_get_auth('edit_any_post', $spThisTopic->forum_id)) {
		$canEdit = true;
	} else {
		if ($spThisPostUser->ID == $spThisUser->ID) {
        	$edit_days = sp_get_option('editpostdays');
            $post_date = strtotime(sp_date('d', $spThisPost->post_date));
            $date_diff = floor((time() - $post_date) / (60 * 60 * 24));
            if (sp_get_auth('edit_own_posts_forever', $spThisTopic->forum_id) ||
               (sp_get_auth('edit_own_posts_reply', $spThisTopic->forum_id) && $spThisPost->last_post) ||
               (sp_get_auth('edit_own_posts_for_time', $spThisTopic->forum_id) && $date_diff <= $edit_days)) {
                $canEdit = true;
            }
		}
	}
	if (!$canEdit) return;

	$defs = array('tagId' 		=> 'spPostIndexEdit%ID%',
				  'tagClass' 	=> 'spButton',
				  'icon' 		=> 'sp_EditPost.png',
				  'iconClass'	=> 'spIcon',
				  'echo'		=> 1,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_PostIndexEdit_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$icon		= sanitize_file_name($icon);
	$iconClass 	= esc_attr($iconClass);
	$toolTip	= esc_attr($toolTip);
	$echo		= (int) $echo;

	$tagId = str_ireplace('%ID%', $spThisPost->post_id, $tagId);

	$action = $spThisTopic->topic_permalink;
	if ($spThisTopic->display_page > 1) $action = user_trailingslashit(trailingslashit($action).'page-'.$spThisTopic->display_page);
	$out = "<form class='spButtonForm' action='$action' method='post' name='usereditpost$spThisPost->post_id'>\n";
	$out.= "<input type='hidden' name='postedit' value='".$spThisPost->post_id."' />\n";
	$out.= "<a class='$tagClass' id='$tagId' title='$toolTip' rel='nofollow' href='javascript:document.usereditpost$spThisPost->post_id.submit();'>";
	if (!empty($icon)) $out.= sp_paint_icon($iconClass, SPTHEMEICONSURL, $icon);
	if (!empty($label)) $out.= sp_filter_title_display($label);
	$out.= "</a>\n";
	$out.= '</form>'."\n";

	$out = apply_filters('sph_PostIndexEdit', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_PostIndexDelete()
#	Delete a post
#	Scope:	Post Loop
#	Version: 5.1
#
# --------------------------------------------------------------------------------------
function sp_PostIndexDelete($args='', $label='', $toolTip='') {
	global $spThisPost, $spThisTopic, $spThisUser, $spGlobals, $spVars;

	if ($spThisTopic->editmode) return;
	if ($spGlobals['lockdown']) return;

	if (!sp_get_auth('delete_any_post', $spThisTopic->forum_id) && !(sp_get_auth('delete_own_posts', $spThisTopic->forum_id) && $spThisUser->ID == $spThisPost->user_id)) return;

	$defs = array('tagId' 		=> 'spPostIndexDelete%ID%',
				  'tagClass' 	=> 'spButton',
				  'icon' 		=> 'sp_DeletePost.png',
				  'iconClass'	=> 'spIcon',
				  'echo'		=> 1,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_PostIndexDelete_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$icon		= sanitize_file_name($icon);
	$iconClass 	= esc_attr($iconClass);
	$toolTip	= esc_attr($toolTip);
	$echo		= (int) $echo;

	$tagId = str_ireplace('%ID%', $spThisPost->post_id, $tagId);

    $out = '';

	$ajaxUrl = wp_nonce_url(SPAJAXURL."spForumTools&targetaction=delete-post&killpost=$spThisPost->post_id&killposttopic=$spThisTopic->topic_id&killpostforum=$spThisTopic->forum_id&killpostposter=$spThisPost->user_id&page=".$spVars['page'], 'spForumTools');
	$out.= "<a class='$tagClass spDeletePost' id='$tagId' title='$toolTip' rel='nofollow' data-url='$ajaxUrl' data-postid='$spThisPost->post_id' data-topicid='$spThisTopic->topic_id'>";
	if (!empty($icon)) $out.= sp_paint_icon($iconClass, SPTHEMEICONSURL, $icon);
	if (!empty($label)) $out.= sp_filter_title_display($label);
	$out.= "</a>\n";

	$out = apply_filters('sph_PostIndexDelete', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_PostIndexContent()
#	Display Post Content
#	Scope:	Post Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_PostIndexContent($args='', $label='') {
	global $spThisTopic, $spThisPost, $spThisPostUser, $spThisUser, $spGuestCookie;
	$defs = array('tagId'    	=> 'spPostIndexContent%ID%',
				  'tagClass' 	=> 'spPostContent',
				  'modClass' 	=> 'spMessage',
				  'echo'		=> 1,
				  'get'			=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_PostIndexContent_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$modClass	= esc_attr($modClass);
	$echo		= (int) $echo;
	$get		= (int) $get;

	($label ? sp_filter_title_display($label) : sp_filter_title_display(sp_text('Awaiting Moderation')));
	$tagId = str_ireplace('%ID%', $spThisPost->post_id, $tagId);

	if ($get) return $spThisPost->post_content;

	$out = "<div id='$tagId' class='$tagClass'>\n";

	# Check moderation status
	if ($spThisPost->post_status == false) {
		$post_content = $spThisPost->post_content;
	} else {
		$modLabel = "<div class='$modClass'>$label</div>\n";
		if (sp_get_auth('moderate_posts', $spThisTopic->forum_id)
			|| ($spThisUser->member && $spThisUser->ID == $spThisPostUser->ID)
			|| ($spThisUser->guest && !empty($spGuestCookie->guest_email) && $spGuestCookie->guest_email == $spThisPost->guest_email)) {
			$post_content = $modLabel.'<hr />'.$spThisPost->post_content;
		} else {
			$post_content = $modLabel.'<hr />';
		}
	}

	$ob = sp_get_option('sfuseob');
	if (!$ob) {
        remove_filter('the_content', 'sp_render_forum', 1);
        $out.= apply_filters('the_content', $post_content);
        add_filter('the_content', 'sp_render_forum', 1);
    } else {
        $out.= $post_content;
    }

	$out.= "</div>\n";
	$out = apply_filters('sph_PostIndexContent', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_PostIndexUserSignature()
#	Display User's Signature
#	Scope:	Post Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_PostIndexUserSignature($args='') {
	global $spThisPost, $spThisPostUser;
	if ($spThisPostUser->guest) return;

	if (empty($spThisPostUser->signature)) return;
	$defs = array('tagId'    	    => 'spPostIndexUserSignature%ID%',
				  'tagClass' 	    => 'spPostUserSignature',
				  'containerClass'  => 'spPostSignatureSection',
				  'maxHeightBottom' => 55,
				  'echo'		    => 1,
				  'get'			    => 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_PostIndexUserSignature_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		     = esc_attr($tagId);
	$tagClass	     = esc_attr($tagClass);
	$containerClass	 = esc_attr($containerClass);
	$maxHeightBottom = (int) $maxHeightBottom;
	$echo		     = (int) $echo;
	$get		     = (int) $get;

	$tagId = str_ireplace('%ID%', $spThisPost->post_id, $tagId);

	if ($get) return $spThisPostUser->signature;

	# force sig to have no follow in links and follow size limits
	$sig = sp_filter_save_nofollow($spThisPostUser->signature);

    $containerStyle = (empty($maxHeightBottom)) ? '' : ' style="width:inherit; margin-top:'.($maxHeightBottom + 25).'px"';
    $tagStyle = (empty($maxHeightBottom)) ? '' : ' style="max-height:'.$maxHeightBottom.'px; position:absolute; bottom: 0; width:inherit;"';
	$out = "<div class='$containerClass'$containerStyle>\n";
	$out.= "<div id='$tagId' class='$tagClass'$tagStyle>\n";
	$out.= $sig."\n";
	$out.= "</div>\n";
    $out.= "</div>\n";
	$out = apply_filters('sph_PostIndexUserSignature', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_PostIndexUserFlexSignature()
#	Display User's Signature
#	Scope:	Post Loop
#	Version: 5.1.1
#
# --------------------------------------------------------------------------------------
function sp_PostIndexUserFlexSignature($args='') {
	global $spThisPost, $spThisPostUser;
	if ($spThisPostUser->guest) return;

	if (empty($spThisPostUser->signature)) return;
	$defs = array('tagId'    	    => 'spPostIndexUserFlexSignature%ID%',
				  'tagClass' 	    => 'spPostUserSignature',
				  'echo'		    => 1,
				  'get'			    => 0,

				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_PostIndexUserSignature_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		     = esc_attr($tagId);
	$tagClass	     = esc_attr($tagClass);
	$echo		     = (int) $echo;
	$get		     = (int) $get;

	$tagId = str_ireplace('%ID%', $spThisPost->post_id, $tagId);

	if ($get) return $spThisPostUser->signature;

	# force sig to have no follow in links and follow size limits
	$sig = sp_filter_save_nofollow($spThisPostUser->signature);

	$out = '';
	$out.= "<div id='$tagId' class='$tagClass'>\n";
	$out.= $sig."\n";
	$out.= "</div>\n";

	$out = apply_filters('sph_PostIndexUserFlexSignature', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_PostIndexUserTwitter()
#	Display User's Twitter icon & link
#	Scope:	Post Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_PostIndexUserTwitter($args='', $toolTip='') {
	global $spThisPost, $spThisPostUser;
	if ($spThisPostUser->guest) return;
	if (empty($spThisPostUser->twitter)) return;

	$defs = array('tagId'    	=> 'spPostIndexUserTwitter%ID%',
				  'tagClass' 	=> 'spPostUserTwitter',
				  'icon' 		=> 'sp_Twitter.png',
				  'iconClass'	=> 'spImg',
                  'targetNew'   => 1,
                  'noFollow'    => 0,
				  'echo'		=> 1,
				  'get'			=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_PostIndexUserTwitter_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$icon		= sanitize_file_name($icon);
	$iconClass 	= esc_attr($iconClass);
	$toolTip	= esc_attr($toolTip);
	$targetNew  = (int) $targetNew;
	$noFollow   = (int) $noFollow;
	$echo		= (int) $echo;
	$get		= (int) $get;

	$tagId = str_ireplace('%ID%', $spThisPost->post_id, $tagId);

	if (filter_var($spThisPostUser->twitter, FILTER_VALIDATE_URL)) {
		$url = sp_filter_url_display($spThisPostUser->twitter);
	} else {
		$url = 'http://twitter.com/'.$spThisPostUser->twitter;
	}

	if ($get) return $url;

    $target = ($targetNew) ? ' target="_blank"' : '';
    $follow = ($noFollow) ? ' rel="nofollow"' : '';
	if (!empty($icon)) {
		$out = "<a id='$tagId' class='$tagClass' href='$url' title='$toolTip'$target$follow>";
		$out.= sp_paint_icon($iconClass, SPTHEMEICONSURL, $icon);
		$out.= "</a>\n";
	}
	$out = apply_filters('sph_PostIndexUserTwitter', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_PostIndexUserFacebook()
#	Display User's facebook icon and link
#	Scope:	Post Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_PostIndexUserFacebook($args='', $toolTip='') {
	global $spThisPost, $spThisPostUser;
	if ($spThisPostUser->guest) return;
	if (empty($spThisPostUser->facebook)) return;

	$defs = array('tagId'    	=> 'spPostIndexUserFacebook%ID%',
				  'tagClass' 	=> 'spPostUserFacebook',
				  'icon' 		=> 'sp_Facebook.png',
				  'iconClass'	=> 'spImg',
                  'targetNew'   => 1,
                  'noFollow'    => 0,
				  'echo'		=> 1,
				  'get'			=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_PostIndexUserFacebook_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$icon		= sanitize_file_name($icon);
	$iconClass 	= esc_attr($iconClass);
	$toolTip	= esc_attr($toolTip);
	$targetNew  = (int) $targetNew;
	$noFollow   = (int) $noFollow;
	$echo		= (int) $echo;
	$get		= (int) $get;

	$tagId = str_ireplace('%ID%', $spThisPost->post_id, $tagId);

	if (filter_var($spThisPostUser->facebook, FILTER_VALIDATE_URL)) {
		$url = sp_filter_url_display($spThisPostUser->facebook);
	} else {
		$url = 'http://facebook.com/'.$spThisPostUser->facebook;
	}

	if ($get) return $url;

    $target = ($targetNew) ? ' target="_blank"' : '';
    $follow = ($noFollow) ? ' rel="nofollow"' : '';
	if (!empty($icon)) {
		$out = "<a id='$tagId' class='$tagClass' href='$url' title='$toolTip'$target$follow>";
		$out.= sp_paint_icon($iconClass, SPTHEMEICONSURL, $icon);
		$out.= "</a>\n";
	}
	$out = apply_filters('sph_PostIndexUserFacebook', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_PostIndexUserMySpace()
#	Display User's MySpace icon and link
#	Scope:	Post Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_PostIndexUserMySpace($args='', $toolTip='') {
	global $spThisPost, $spThisPostUser;
	if ($spThisPostUser->guest) return;
	if (empty($spThisPostUser->myspace)) return;

	$defs = array('tagId'    	=> 'spPostIndexUserMySpace%ID%',
				  'tagClass' 	=> 'spPostUserMySpace',
				  'icon' 		=> 'sp_MySpace.png',
				  'iconClass'	=> 'spImg',
                  'targetNew'   => 1,
                  'noFollow'    => 0,
				  'echo'		=> 1,
				  'get'			=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_PostIndexUserMySpace_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$icon		= sanitize_file_name($icon);
	$iconClass 	= esc_attr($iconClass);
	$toolTip	= esc_attr($toolTip);
	$targetNew  = (int) $targetNew;
	$noFollow   = (int) $noFollow;
	$echo		= (int) $echo;
	$get		= (int) $get;

	$tagId = str_ireplace('%ID%', $spThisPost->post_id, $tagId);

	if (filter_var($spThisPostUser->myspace, FILTER_VALIDATE_URL)) {
		$url = sp_filter_url_display($spThisPostUser->myspace);
	} else {
		$url = 'http://myspace.com/'.$spThisPostUser->myspace;
	}

	if ($get) return $url;

    $target = ($targetNew) ? ' target="_blank"' : '';
    $follow = ($noFollow) ? ' rel="nofollow"' : '';
	if (!empty($icon)) {
		$out = "<a id='$tagId' class='$tagClass' href='$url' title='$toolTip'$target$follow>";
		$out.= sp_paint_icon($iconClass, SPTHEMEICONSURL, $icon);
		$out.= "</a>\n";
	}
	$out = apply_filters('sph_PostIndexUserMySpace', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_PostIndexUserLinkedIn()
#	Display User's LinkedIn icon and link
#	Scope:	Post Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_PostIndexUserLinkedIn($args='', $toolTip='') {
	global $spThisPost, $spThisPostUser;
	if ($spThisPostUser->guest) return;
	if (empty($spThisPostUser->linkedin)) return;

	$defs = array('tagId'    	=> 'spPostIndexUserLinkedIn%ID%',
				  'tagClass' 	=> 'spPostUserLinkedIn',
				  'icon' 		=> 'sp_LinkedIn.png',
				  'iconClass'	=> 'spImg',
                  'targetNew'   => 1,
                  'noFollow'    => 0,
				  'echo'		=> 1,
				  'get'			=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_PostIndexUserLinkedIn_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$icon		= sanitize_file_name($icon);
	$iconClass 	= esc_attr($iconClass);
	$toolTip	= esc_attr($toolTip);
	$targetNew  = (int) $targetNew;
	$noFollow   = (int) $noFollow;
	$echo		= (int) $echo;
	$get		= (int) $get;

	$tagId = str_ireplace('%ID%', $spThisPost->post_id, $tagId);

	if (filter_var($spThisPostUser->linkedin, FILTER_VALIDATE_URL)) {
		$url = sp_filter_url_display($spThisPostUser->linkedin);
	} else {
		$url = 'http://linkedin.com/'.$spThisPostUser->linkedin;
	}

	if ($get) return $url;

    $target = ($targetNew) ? ' target="_blank"' : '';
    $follow = ($noFollow) ? ' rel="nofollow"' : '';
	if (!empty($icon)) {
		$out = "<a id='$tagId' class='$tagClass' href='$url' title='$toolTip'$target$follow>";
		$out.= sp_paint_icon($iconClass, SPTHEMEICONSURL, $icon);
		$out.= "</a>\n";
	}
	$out = apply_filters('sph_PostIndexUserLinkedIn', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_PostIndexUserYouTube()
#	Display User's YouTube icon and link
#	Scope:	Post Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_PostIndexUserYouTube($args='', $toolTip='') {
	global $spThisPost, $spThisPostUser;
	if ($spThisPostUser->guest) return;
	if (empty($spThisPostUser->youtube)) return;

	$defs = array('tagId'    	=> 'spPostIndexUserYouTube%ID%',
				  'tagClass' 	=> 'spPostUserYouTube',
				  'icon' 		=> 'sp_YouTube.png',
				  'iconClass'	=> 'spImg',
                  'targetNew'   => 1,
                  'noFollow'    => 0,
				  'echo'		=> 1,
				  'get'			=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_PostIndexUserYouTube_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$icon		= sanitize_file_name($icon);
	$iconClass 	= esc_attr($iconClass);
	$toolTip	= esc_attr($toolTip);
	$targetNew  = (int) $targetNew;
	$noFollow   = (int) $noFollow;
	$echo		= (int) $echo;
	$get		= (int) $get;

	$tagId = str_ireplace('%ID%', $spThisPost->post_id, $tagId);

	if (filter_var($spThisPostUser->youtube, FILTER_VALIDATE_URL)) {
		$url = sp_filter_url_display($spThisPostUser->youtube);
	} else {
		$url = 'http://youtube.com/user/'.$spThisPostUser->youtube;
	}

	if ($get) return $url;

    $target = ($targetNew) ? ' target="_blank"' : '';
    $follow = ($noFollow) ? ' rel="nofollow"' : '';
	if (!empty($icon)) {
		$out = "<a id='$tagId' class='$tagClass' href='$url' title='$toolTip'$target$follow>";
		$out.= sp_paint_icon($iconClass, SPTHEMEICONSURL, $icon);
		$out.= "</a>\n";
	}
	$out = apply_filters('sph_PostIndexUserYouTube', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_PostIndexUserGooglePlus()
#	Display User's GooglePlus icon and link
#	Scope:	Post Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_PostIndexUserGooglePlus($args='', $toolTip='') {
	global $spThisPost, $spThisPostUser;
	if ($spThisPostUser->guest) return;
	if (empty($spThisPostUser->googleplus)) return;

	$defs = array('tagId'    	=> 'spPostIndexUserGooglePlus%ID%',
				  'tagClass' 	=> 'spPostUserGooglePlus',
				  'icon' 		=> 'sp_GooglePlus.png',
				  'iconClass'	=> 'spImg',
                  'targetNew'   => 1,
                  'noFollow'    => 0,
				  'echo'		=> 1,
				  'get'			=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_PostIndexUserGooglePlus_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$icon		= sanitize_file_name($icon);
	$iconClass 	= esc_attr($iconClass);
	$toolTip	= esc_attr($toolTip);
	$targetNew  = (int) $targetNew;
	$noFollow   = (int) $noFollow;
	$echo		= (int) $echo;
	$get		= (int) $get;

	$tagId = str_ireplace('%ID%', $spThisPost->post_id, $tagId);

	if (filter_var($spThisPostUser->googleplus, FILTER_VALIDATE_URL)) {
		$url = sp_filter_url_display($spThisPostUser->googleplus);
	} else {
		$url = 'https://plus.google.com/u/'.$spThisPostUser->googleplus.'/'.$spThisPostUser->googleplus;
	}

	if ($get) return $url;

    $target = ($targetNew) ? ' target="_blank"' : '';
    $follow = ($noFollow) ? ' rel="nofollow"' : '';
	if (!empty($icon)) {
		$out = "<a id='$tagId' class='$tagClass' href='$url' title='$toolTip'$target$follow>";
		$out.= sp_paint_icon($iconClass, SPTHEMEICONSURL, $icon);
		$out.= "</a>\n";
	}
	$out = apply_filters('sph_PostIndexUserGooglePlus', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_PostIndexUserWebsite()
#	Display User's website icon and link
#	Scope:	Post Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_PostIndexUserWebsite($args='', $toolTip='') {
	global $spThisPost, $spThisPostUser;
	if ($spThisPostUser->guest) return;
	if (empty($spThisPostUser->user_url)) return;

	$defs = array('tagId'    	=> 'spPostIndexUserWebsite%ID%',
				  'tagClass' 	=> 'spPostUserWebsite',
				  'icon' 		=> 'sp_UserWebsite.png',
				  'iconClass'	=> 'spImg',
                  'targetNew'   => 1,
                  'noFollow'    => 0,
				  'echo'		=> 1,
				  'get'			=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_PostIndexUserWebsite_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$icon		= sanitize_file_name($icon);
	$iconClass 	= esc_attr($iconClass);
	$toolTip	= esc_attr($toolTip);
	$targetNew  = (int) $targetNew;
	$noFollow   = (int) $noFollow;
	$echo		= (int) $echo;
	$get		= (int) $get;

	$tagId = str_ireplace('%ID%', $spThisPost->post_id, $tagId);

	if ($get) return $spThisPostUser->user_url;

    $target = ($targetNew) ? ' target="_blank"' : '';
    $follow = ($noFollow) ? ' rel="nofollow"' : '';
	if (!empty($icon)) {
		$out = "<a id='$tagId' class='$tagClass' href='$spThisPostUser->user_url' title='$toolTip'$target$follow>";
		$out.= sp_paint_icon($iconClass, SPTHEMEICONSURL, $icon);
		$out.= "</a>\n";
	}
	$out = apply_filters('sph_PostIndexUserWebsite', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_PostIndexUserStatus()
#	Display users online status
#	Scope:	post loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_PostIndexUserStatus($args='', $onlineLabel='', $offlineLabel='') {
	global $spThisPost, $spThisPostUser, $spThisUser;
	if ($spThisPostUser->guest) return;

	$defs = array('tagId'				=> 'spPostIndexUserStatus%ID%',
				  'tagClass'			=> 'spPostUserStatus',
				  'iconClass'			=> 'spIcon',
				  'onlineLabelClass'	=> 'spPostUserStatusOnline',
				  'offlineLabelClass'	=> 'spPostUserStatusOffline',
				  'onlineIcon' 			=> 'sp_UserOnlineSmall.png',
				  'offlineIcon'			=> 'sp_UserOfflineSmall.png',
				  'echo'				=> 1,
				  'get'					=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_PostIndexUserStatus_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId				= esc_attr($tagId);
	$tagClass			= esc_attr($tagClass);
	$iconClass			= esc_attr($iconClass);
	$onlineLabelClass	= esc_attr($onlineLabelClass);
	$offlineLabelClass	= esc_attr($offlineLabelClass);
	$onlineIcon			= sanitize_file_name($onlineIcon);
	$offlineIcon		= sanitize_file_name($offlineIcon);
	$onlineLabel		= sp_filter_title_display($onlineLabel);
	$offlineLabel		= sp_filter_title_display($offlineLabel);
	$echo				= (int) $echo;
	$get				= (int) $get;

	$tagId = str_ireplace('%ID%', $spThisPost->post_id, $tagId);

	$spMemberOpts = sp_get_option('sfmemberopts');
	$icon = '';
	if (($spThisUser->admin ||
        (!$spMemberOpts['sfhidestatus'] || (!isset($spThisPostUser->hidestatus) || !$spThisPostUser->hidestatus))) &&
        sp_is_online($spThisPostUser->ID)) {
        if (!empty($onlineIcon)) $icon = sp_paint_icon($iconClass, SPTHEMEICONSURL, sanitize_file_name($onlineIcon));
		$label = $onlineLabel;
		$labelClass = $onlineLabelClass;
		$status = true;
	} else {
		if (!empty($offlineIcon)) $icon = sp_paint_icon($iconClass, SPTHEMEICONSURL, sanitize_file_name($offlineIcon));
		$label = $offlineLabel;
		$labelClass = $offlineLabelClass;
		$status = false;
	}

	if ($get) return $status;

	$out = "<div id='$tagId' class='$tagClass'><span class='$labelClass'>\n";
	if (!empty($icon)) $out.= $icon;
	$out.= $label;
	$out.= "</span></div>\n";


	$out = apply_filters('sph_PostIndexUserStatus', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_PostIndexUserLocation()
#	Display user location
#	Scope:	Post Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_PostIndexUserLocation($args='', $label='') {
	global $spThisPost, $spThisPostUser;
	if ($spThisPostUser->guest) return;
	if (empty($spThisPostUser->location)) return;

	$defs = array('tagId'    		=> 'spPostIndexUserLocatin%ID%',
				  'tagClass' 		=> 'spPostUserLocation',
				  'echo'			=> 1,
				  'get'				=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_PostIndexUserLocation_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$label		= (!empty($label)) ? sp_filter_title_display($label) : '';
	$echo		= (int) $echo;
	$get		= (int) $get;

	$tagId = str_ireplace('%ID%', $spThisPost->post_id, $tagId);

	if ($get) return $spThisPostUser->posts;

	$out = "<div id='$tagId' class='$tagClass'>";
	$out.= $label.$spThisPostUser->location;
	$out.= "</div>\n";
	$out = apply_filters('sph_PostIndexUserLocation', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_NoPostsInTopicMessage()
#	Display Message when no posts are found in a Topic
#	THIS FUNCTION SHOLD NEVER BE NEEDED BUT IS DEFINED AS A FALLBACK IN CASE
#	Scope:	Post Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_NoPostsInTopicMessage($args='', $definedMessage='') {
	$defs = array('tagId'		=> 'spNoPostsInTopicMessage',
				  'tagClass'	=> 'spMessage',
				  'echo'		=> 1,
				  'get'			=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_NoPostsInTopicMessage_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$echo		= (int) $echo;
	$get		= (int) $get;

	if ($get) return $definedMessage;

	$out = "<div id='$tagId' class='$tagClass'>".sp_filter_title_display($definedMessage)."</div>\n";
	$out = apply_filters('sph_NoPostsInTopicMessage', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	PostForumToolButton()
#	Display Post Level Admin Tools Button
#	Scope:	Post Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_PostForumToolButton($args='', $label='', $toolTip='') {
	global $spThisTopic, $spThisPost, $spThisPostUser, $spThisUser, $spGuestCookie, $spGlobals;

	if ($spGlobals['lockdown'] == true && $spThisUser->admin == false) return;

	$show = false;
	if ($spThisUser->admin || $spThisUser->moderator) {
		$show = true;
	} else {
    	$edit_days = sp_get_option('editpostdays');
        $post_date = strtotime(sp_date('d', $spThisPost->post_date));
        $date_diff = floor((time() - $post_date) / (60 * 60 * 24));

		if (sp_get_auth('view_email', $spThisTopic->forum_id) ||
			sp_get_auth('pin_posts', $spThisTopic->forum_id) ||
			sp_get_auth('edit_any_post', $spThisTopic->forum_id) ||
			(sp_get_auth('edit_own_posts_forever', $spThisTopic->forum_id) && $spThisUser->member && $spThisPostUser->ID == $spThisUser->ID) ||
			(sp_get_auth('edit_own_posts_forever', $spThisTopic->forum_id) && $spThisUser->guest && $spThisPost->guest_email == $spGuestCookie->guest_email) ||
			(sp_get_auth('edit_own_posts_reply', $spThisTopic->forum_id) && $spThisUser->member && $spThisPostUser->ID == $spThisUser->ID && $spThisPost->last_post) ||
			(sp_get_auth('edit_own_posts_reply', $spThisTopic->forum_id) && $spThisUser->guest && $spThisPost->guest_email == $spGuestCookie->guest_email && $spThisPost->last_post) ||
			(sp_get_auth('edit_own_posts_for_time', $spThisTopic->forum_id) && $spThisUser->member && $spThisPostUser->ID == $spThisUser->ID && $date_diff <= $edit_days) ||
			sp_get_auth('move_posts', $spThisTopic->forum_id) ||
			sp_get_auth('reassign_posts', $spThisTopic->forum_id) ||
			sp_get_auth('delete_any_post', $spThisTopic->forum_id) ||
			(sp_get_auth('delete_own_posts', $spThisTopic->forum_id) && $spThisPostUser->user_id == $spThisUser->ID) ||
			(sp_get_auth('moderate_posts', $spThisTopic->forum_id) && $spThisPost->post_status != 0)) {
			$show = true;
		}
	}
    $show = apply_filters('sph_forum_tools_topic_show', $show);
	if (!$show) return;

	$defs = array('tagId' 			=> 'spForumToolButton%ID%',
				  'tagClass' 		=> 'spToolsButton',
				  'icon' 			=> 'sp_ForumTools.png',
				  'iconClass'		=> 'spIcon',
				  'hide'			=> 1,
				  'containerClass'	=> 'spTopicPostSection'
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_PostForumToolButton_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId			= esc_attr($tagId);
	$tagClass		= esc_attr($tagClass);
	$icon			= sanitize_file_name($icon);
	$iconClass 		= esc_attr($iconClass);
	$containerClass	= esc_attr($containerClass);
	$hide			= (int) $hide;
	$toolTip		= esc_attr($toolTip);
	$label			= sp_filter_title_display($label);

	$tagId = str_ireplace('%ID%', $spThisPost->post_id, $tagId);

	$addStyle = '';
	if ($hide) $addStyle = " style='display: none;' ";

	$last = ($spThisPost->last_post) ? 1 : 0;
	$site = wp_nonce_url(SPAJAXURL."spForumPostTools&amp;targetaction=posttools&amp;post=$spThisPost->post_id&amp;page=$spThisTopic->display_page&amp;postnum=$spThisPost->post_index&amp;name=".urlencode($spThisPostUser->display_name)."&amp;forum=$spThisTopic->forum_id&amp;last=$last", 'spForumToolsMenu');
	$title = esc_attr(sp_text('Forum Tools'));
	$out = "<a class='$tagClass spForumPostTools' id='$tagId' title='$toolTip' rel='nofollow' $addStyle data-site='$site' data-label='$title' data-width='350' data-height='0' data-align='0'>";

	if (!empty($icon)) $out.= sp_paint_icon($iconClass, SPTHEMEICONSURL, $icon);
	if (!empty($label)) $out.= $label;
	$out.= "</a>\n";
	$out = apply_filters('sph_PostForumToolButton', $out, $a);

	echo $out;

	# Add script to hover admin buttons - just once
	if ($spThisTopic->tools_flag && $hide) {
?>
		<script type='text/javascript'>
		/* <![CDATA[ */
		var sptb = {
			toolclass : '.<?php echo($containerClass); ?>'
		};
		/* ]]> */
		</script>
<?php
		add_action('wp_footer', 'spjs_AddPostToolsHover');
		$spThisTopic->tools_flag = false;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_UsersAlsoViewing()
#	Display notice to user of other admins / mods viewing thread (and possibly posting)
#	Scope:	Post Loop
#	Version: 5.5.2
#
# --------------------------------------------------------------------------------------
function sp_UsersAlsoViewing ($args='', $messageLabel='') {
	global $spThisUser, $spVars;

	$defs = array('tagClass'			=> 'spAlsoViewingContainer',
				  'userHolder'			=> 'spBrowsingUserHolder',
				  'browsingClass'		=> 'spBrowsingTopic',
				  'messageClass'		=> 'spBrowsingMessage',
				  'avatarClass'			=> 'spAvatar',
				  'avatarSize'			=> 30,
				  'includeAdmins'		=> 1,
				  'includeMods'			=> 1,
				  'includeMembers'		=> 0,
				  'displayToAll'		=> 0,
				  'echo'				=> 1,
				  'get'					=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_UsersAlsoViewing_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagClass		= esc_attr($tagClass);
	$browsingClass	= esc_attr($browsingClass);
	$messageClass	= esc_attr($messageClass);
	$avatarClass	= esc_attr($avatarClass);
	$avatarSize		= (int) $avatarSize;
	$includeAdmins	= (int) $includeAdmins;
	$includeMods	= (int) $includeMods;
	$includeMembers = (int) $includeMembers;
	$displayToAll 	= (int) $displayToAll;
	$echo			= (int) $echo;
	$get			= (int) $get;

	# get online user data
	$members = sp_get_members_online();
	if ($get) {
		return $members;
	}

	$out = '';
	$tout = '';

	# get member info to check against members browsing topic
	if ($members) {
		foreach ($members as $user) {
			if ($spThisUser->ID != $user->trackuserid) {
				if (!empty($spVars['pageview'])) {
					if ($spVars['pageview'] == 'topic' && $user->topic_id == $spVars['topicid']) {

						# check to see if admin, mod, or user
						if (($displayToAll) || ($spThisUser->admin) && ($includeAdmins) && (sp_is_forum_admin($user->trackuserid)) ||
						    ($displayToAll) || ($spThisUser->admin) && ($includeMods) && (sp_is_forum_mod($user->trackuserid)) ||
						    ($displayToAll) || ($spThisUser->admin) && ($includeMembers) && (!sp_is_forum_mod($user->trackuserid !=0))) {
								$tout.= "<div class='$userHolder'>";
								$tout.= sp_UserAvatar("tagClass=$avatarClass&size=$avatarSize&link=none&context=user&echo=0", $user->trackuserid);
								$tout.= "<span class='$browsingClass'>";
								$tout.= sp_build_name_display($user->trackuserid,$user->display_name);
								$tout.= "</span>";
								$tout.= "<br><span> $messageLabel</span>";
								$tout.= "</div>";
						}
					}
				}
			}
		}
	}

	if(!empty($tout)) {
		$out = "<div class='$tagClass'>";
		$out.= $tout;

		$out.= "</div>";
	}

	# finish it up
	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_PostEditorWindow()
#	Placeholder for the new post editor window
#	Scope:	Topic View
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_PostEditorWindow($addPostForm, $editPostForm) {
	global $spThisTopic, $spThisPost, $spThisUser, $spGlobals, $spVars;

	$spVars['hiddeneditor'] = $addPostForm['hide'];

	# Are we editing a current post?
	if ($spThisTopic->editmode) {
		# Go into edit mode
		$out = '<a id="spEditFormAnchor"></a>'."\n";
		$out.= sp_edit_post($editPostForm, $spThisTopic->editpost_id, $spThisTopic->editpost_content);
		echo $out;

		# inline js to open post edit form
		add_action('wp_footer', 'spjs_OpenPostEditForm');

	} else {
		if (!$spThisTopic->topic_status && !$spGlobals['lockdown']) $allowed = true;
		$allowed = ($spThisTopic->reply_own_topics && $spThisTopic->topic_starter == $spThisUser->ID);
		if ($spThisTopic->reply_topics) $allowed = true;
		# New post form
		if ($allowed) {
			$out = '<a id="spEditFormAnchor"></a>'."\n";
			$out.= sp_add_post($addPostForm);
			echo $out;
		}
	}
}

# ======================================================================================
#
# INLINE SCRIPTS
#
# ======================================================================================

# --------------------------------------------------------------------------------------
# inline opens post edit window if topic is in edit post mode
# --------------------------------------------------------------------------------------
function spjs_OpenPostEditForm() {
?>
<script type="text/javascript">
	jQuery(document).ready(function() {
		spjOpenEditor('spPostForm', 'edit');
	});
</script>
<?php
}

# --------------------------------------------------------------------------------------
# inline adds hover show event to admin tools button if hidden
# --------------------------------------------------------------------------------------
function spjs_AddPostToolsHover() {
    global $spMobile;

    # on mobile devices just show forum tools. otherwise, show on hover over row
    if ($spMobile) {
?>
        <script type="text/javascript">
        	jQuery(document).ready(function() {
        		jQuery('.spToolsButton').css('left', 0);
        		jQuery('.spToolsButton').show();
        	});
        </script>
<?php
    } else {
?>
        <script type="text/javascript">
        	jQuery(document).ready(function() {
        		jQuery(sptb.toolclass).hover(function() {
        			jQuery('.spToolsButton', this).css('left', 0);
        			jQuery('.spToolsButton', this).delay(400).slideDown('normal');
        				}, function() {
        			jQuery('.spToolsButton', this).stop(true, true).delay(1200).slideUp('normal');
        		});
        	});
        </script>
<?php
    }
}

?>