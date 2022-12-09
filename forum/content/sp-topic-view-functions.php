<?php
/*
Simple:Press
Template Function Handler
$LastChangedDate: 2018-12-16 12:27:05 -0600 (Sun, 16 Dec 2018) $
$Rev: 15855 $
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
#	sp_TopicHeaderName()
#	Display Topic Name/Title in Header
#	Scope:	Topic View
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_TopicForumName($args = '') {
	$defs = array('tagId'    => 'spTopicForumName',
	              'tagClass' => 'spTopicForumName',
	              'truncate' => 0,
	              'echo'     => 1,
	              'get'      => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_TopicForumName_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId    = esc_attr($tagId);
	$tagClass = esc_attr($tagClass);
	$truncate = (int) $truncate;
	$echo     = (int) $echo;
	$get      = (int) $get;

	if ($get) return SP()->primitives->truncate_name(SP()->forum->view->thisTopic->forum_name, $truncate);

	$out = (empty(SP()->forum->view->thisTopic->forum_name)) ? '' : "<div id='$tagId' class='$tagClass'>".SP()->primitives->truncate_name(SP()->forum->view->thisTopic->forum_name, $truncate)."</div>";
	$out = apply_filters('sph_TopicForumName', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_TopicHeaderIcon()
#	Display Topic Icon
#	Scope:	Topic View
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_TopicHeaderIcon($args = '') {
	$defs = array('tagId'    => 'spTopicHeaderIcon',
	              'tagClass' => 'spHeaderIcon',
	              'icon'     => 'sp_TopicIcon.png',
	              'echo'     => 1,
	              'get'      => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_TopicHeaderIcon_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId    = esc_attr($tagId);
	$tagClass = esc_attr($tagClass);
	$echo     = (int) $echo;
	$get      = (int) $get;

	$topic_icon = spa_get_saved_icon( SP()->forum->view->thisTopic->topic_icon );
	
	# Check if a custom icon
	if ( !empty( $topic_icon['icon'] ) ) {
		
		$icon = $topic_icon['icon'];

		if( 'file' === $topic_icon['type'] ) {
			$icon = SP()->theme->paint_custom_icon($tagClass, SPCUSTOMURL. $icon );
		} else {
			$icon = SP()->theme->sp_paint_iconset_icon( $topic_icon, $tagClass );
		}
		
	} else {
		$icon = SP()->theme->paint_icon($tagClass, SPTHEMEICONSURL, sanitize_file_name($icon));
	}

	if ($get) return $icon;

	$out = SP()->theme->paint_icon_id($icon, $tagId);
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
function sp_TopicHeaderName($args = '') {
	$defs = array('tagId'    => 'spTopicHeaderName',
	              'tagClass' => 'spHeaderName',
	              'truncate' => 0,
	              'echo'     => 1,
	              'get'      => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_TopicHeaderName_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId    = esc_attr($tagId);
	$tagClass = esc_attr($tagClass);
	$truncate = (int) $truncate;
	$echo     = (int) $echo;
	$get      = (int) $get;

	if ($get) return SP()->primitives->truncate_name(SP()->forum->view->thisTopic->topic_name, $truncate);

	$out = (empty(SP()->forum->view->thisTopic->topic_name)) ? '' : "<div id='$tagId' class='$tagClass'>".SP()->primitives->truncate_name(SP()->forum->view->thisTopic->topic_name, $truncate)."</div>";
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
function sp_TopicHeaderRSSButton($args = '', $label = '', $toolTip = '') {
	if (!SP()->auths->get('view_forum', SP()->forum->view->thisTopic->forum_id) || SP()->forum->view->thisTopic->forum_rss_private) return;

	$defs = array('tagId'     => 'spTopicHeaderRSSButton',
	              'tagClass'  => 'spButton',
	              'icon'      => 'sp_Feed.png',
	              'iconClass' => 'spIcon',
	              'echo'      => 1,
	              'get'       => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_TopicHeaderRSSButton_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId     = esc_attr($tagId);
	$tagClass  = esc_attr($tagClass);
	$icon      = sanitize_file_name($icon);
	$iconClass = esc_attr($iconClass);
	$toolTip   = esc_attr($toolTip);
	$echo      = (int) $echo;
	$get       = (int) $get;

	# Get or construct rss url
	$rssOpt = SP()->options->get('sfrss');
	if ($rssOpt['sfrssfeedkey'] && isset(SP()->user->thisUser->feedkey)) {
		$rssUrl = trailingslashit(SP()->spPermalinks->build_url(SP()->forum->view->thisTopic->forum_slug, SP()->forum->view->thisTopic->topic_slug, 0, 0, 0, 1)).user_trailingslashit(SP()->user->thisUser->feedkey);
	} else {
		$rssUrl = SP()->spPermalinks->build_url(SP()->forum->view->thisTopic->forum_slug, SP()->forum->view->thisTopic->topic_slug, 0, 0, 0, 1);
	}

	if ($get) return $rssUrl;

	$out = "<a class='$tagClass' id='$tagId' title='$toolTip' rel='nofollow' href='$rssUrl'>";
	if (!empty($icon)) $out .= SP()->theme->paint_icon($iconClass, SPTHEMEICONSURL, $icon);
	if (!empty($label)) $out .= SP()->displayFilters->title($label);
	$out .= "</a>";
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
function sp_NoTopicMessage($args = '', $deniedMessage = '', $definedMessage = '') {
	$defs = array('tagId'    => 'spNoTopicMessage',
	              'tagClass' => 'spMessage',
	              'echo'     => 1,
	              'get'      => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_NoTopicMessage_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId    = esc_attr($tagId);
	$tagClass = esc_attr($tagClass);
	$echo     = (int) $echo;
	$get      = (int) $get;

	# is Access denied
	if (SP()->forum->view->topics->topicViewStatus == 'no access') {
		$m = SP()->displayFilters->title($deniedMessage);
	} elseif (SP()->forum->view->topics->topicViewStatus == 'no data') {
		$m = SP()->displayFilters->title($definedMessage);
	} elseif (SP()->forum->view->topics->topicViewStatus == 'sneak peek') {
		$sflogin = SP()->options->get('sflogin');
		if (!empty($sflogin['sfsneakredirect'])) {
			SP()->primitives->redirect(apply_filters('sph_sneak_redirect', $sflogin['sfsneakredirect']));
		} else {
			$sneakpeek = SP()->meta->get('sneakpeek', 'message');
			$m         = ($sneakpeek) ? SP()->displayFilters->text($sneakpeek[0]['meta_value']) : '';
		}
	} else {
		return;
	}

	if ($get) return $m;

	$out = "<div id='$tagId' class='$tagClass'>$m</div>";
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
function sp_PostNewButton($args = '', $label = '', $toolTip = '', $toolTipLock = '', $toolTipDenied = '') {
	# can be empty if request is for a bogus topic slug
	if (empty(SP()->forum->view->thisTopic)) return;

	if (SP()->forum->view->thisTopic->editmode) return;

	$allowed = (SP()->forum->view->thisTopic->reply_own_topics && SP()->forum->view->thisTopic->topic_starter == SP()->user->thisUser->ID);
	if (SP()->forum->view->thisTopic->reply_topics) $allowed = true;

	$defs = array('tagId'           => 'spPostNewButton',
	              'tagClass'        => 'spButton',
	              'icon'            => 'sp_NewPost.png',
	              'iconLock'        => 'sp_TopicStatusLock.png',
	              'iconDenied'      => 'sp_WriteDenied.png',
	              'iconClass'       => 'spIcon',
	              'iconStatusClass' => 'spIcon',
	              'echo'            => 1,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_PostNewButton_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId           = esc_attr($tagId);
	$tagClass        = esc_attr($tagClass);
	$icon            = sanitize_file_name($icon);
	$iconClass       = esc_attr($iconClass);
	$iconStatusClass = esc_attr($iconStatusClass);
	$toolTip         = esc_attr($toolTip);
	$toolTipLock     = esc_attr($toolTipLock);
	$echo            = (int) $echo;

	# is the forum locked?
	$out  = '';
	$out  = "<div id='$tagId'>";
		
	$lock = false;
	if (SP()->core->forumData['lockdown'] || SP()->forum->view->thisTopic->forum_status || SP()->forum->view->thisTopic->topic_status) {
		if (!empty($iconLock)) {
			$out .= "<a class='$tagClass spLockIconWrap' title='$toolTipDenied'>";
			$iconLock = SP()->theme->paint_icon($iconClass.' '.$iconStatusClass.' spIconLockLink', SPTHEMEICONSURL, sanitize_file_name($iconLock), $toolTipLock);
			$out .= SP()->theme->paint_icon_id($iconLock, $tagId);
			$out.= "</a>";
		}
		if (!SP()->user->thisUser->admin) $lock = true;
	}

	if (!$lock && $allowed) {
		$out .= "<a class='$tagClass spNewPostButton' title='$toolTip' data-form='spPostForm' data-type='post'>";
		if (!empty($icon)) $out .= SP()->theme->paint_icon($iconClass, SPTHEMEICONSURL, $icon);
		if (!empty($label)) $out .= SP()->displayFilters->title($label);
		$out .= "</a>";
	}

	# Display if user not allowed to start topics
	if (!$allowed && !empty($toolTipDenied)) {
		if (!empty($iconDenied)) {
			$out .= "<a class='$tagClass spDeniedIconWrap' title='$toolTipDenied'>";
			$out .= SP()->theme->paint_icon($iconStatusClass.' spIconDeniedLink', SPTHEMEICONSURL, sanitize_file_name($iconDenied), $toolTipDenied);
			$out .= "</a>";
		}
	}
	
	$out .= '</div>';

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
function sp_PostNewTopicButton($args = '', $label = '', $toolTip = '', $toolTipLock = '') {
	# can be empty if request is for a bogus topic slug
	if (empty(SP()->forum->view->thisTopic)) return;

	if (!SP()->auths->get('start_topics', SP()->forum->view->thisTopic->forum_id)) return;

	$defs = array('tagId'           => 'spPostNewTopicButton',
	              'tagClass'        => 'spButton',
	              'icon'            => 'sp_NewTopic.png',
	              'iconLock'        => 'sp_ForumStatusLock.png',
	              'iconClass'       => 'spIcon',
	              'iconStatusClass' => 'spIcon',
	              'echo'            => 1,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_PostNewTopicButton_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId           = esc_attr($tagId);
	$tagClass        = esc_attr($tagClass);
	$icon            = sanitize_file_name($icon);
	$iconClass       = esc_attr($iconClass);
	$iconStatusClass = esc_attr($iconStatusClass);
	$toolTip         = esc_attr($toolTip);
	$toolTipLock     = esc_attr($toolTipLock);
	$echo            = (int) $echo;

	# is the forum locked?
	$out  = '';
	$lock = false;
	if (SP()->core->forumData['lockdown'] || SP()->forum->view->thisTopic->forum_status) {
		if (!empty($iconLock)) {
			$out .= SP()->theme->paint_icon($tagClass.' '.$iconStatusClass, SPTHEMEICONSURL, sanitize_file_name($iconLock), $toolTipLock);
		}
		if (!SP()->user->thisUser->admin) $lock = true;
	}
	if (!$lock && SP()->auths->get('start_topics', SP()->forum->view->thisTopic->forum_id)) {
		$url = SP()->spPermalinks->build_url(SP()->forum->view->thisTopic->forum_slug, '', 1, 0).SP()->spPermalinks->get_query_char().'new=topic';
		$out .= "<a href='$url' class='$tagClass' id='$tagId' title='$toolTip'>";
		if (!empty($icon)) $out .= SP()->theme->paint_icon($iconClass, SPTHEMEICONSURL, $icon);
		if (!empty($label)) $out .= SP()->displayFilters->title($label);
		$out .= "</a>";
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
function sp_PostIndexPageLinks($args = '', $label = '', $toolTip = '', $jumpToolTip = '') {
	global $jumpID;

	# can be empty if request is for a bogus topic slug
	if (empty(SP()->forum->view->thisTopic)) return;

	$defs = array('tagClass'      => 'spPageLinks',
	              'prevIcon'      => 'sp_ArrowLeft.png',
	              'nextIcon'      => 'sp_ArrowRight.png',
	              'jumpIcon'      => 'sp_Jump.png',
	              'iconClass'     => 'spIcon',
	              'pageLinkClass' => 'spPageLinks',
	              'curPageClass'  => 'spCurrent',
	              'linkClass'     => 'spLink',
				  'jumpClass'	  => '',
	              'showLinks'     => 4,
	              'showJump'      => 1,
	              'showEmpty'     => 0,
	              'echo'          => 1);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_PostIndexPageLinks_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	if (empty($jumpClass)) $jumpClass = $linkClass;
	$tagClass      = esc_attr($tagClass);
	$iconClass     = esc_attr($iconClass);
	$pageLinkClass = esc_attr($pageLinkClass);
	$curPageClass  = esc_attr($curPageClass);
	$linkClass     = esc_attr($linkClass);
	$jumpClass	   = esc_attr($jumpClass);
	$showLinks     = (int) $showLinks;
	$showJump      = (int) $showJump;
	$showEmpty     = (int) $showEmpty;
	$label         = SP()->displayFilters->title($label);
	$toolTip       = esc_attr($toolTip);
	$jumpToolTip   = esc_attr($jumpToolTip);
	$echo          = (int) $echo;

	if (SP()->forum->view->thisTopic->posts_per_page >= SP()->forum->view->thisTopic->post_count) {
		if ($showEmpty) echo "<div class='$tagClass'></div>";
		return;
	}

	if (!empty($prevIcon)) $prevIcon = SP()->theme->paint_icon($iconClass, SPTHEMEICONSURL, sanitize_file_name($prevIcon), $toolTip);
	if (!empty($nextIcon)) $nextIcon = SP()->theme->paint_icon($iconClass, SPTHEMEICONSURL, sanitize_file_name($nextIcon), $toolTip);
	if (!empty($jumpIcon)) $jumpIcon = SP()->theme->paint_icon($iconClass, SPTHEMEICONSURL, sanitize_file_name($jumpIcon), $jumpToolTip);

	$curToolTip = str_ireplace('%PAGE%', SP()->rewrites->pageData['page'], $toolTip);

	if (isset($jumpID) ? $jumpID++ : $jumpID = 1) ;

	$out = "<div class='$tagClass'>";
	if (!empty($label))	$out .= "<span class='$pageLinkClass'>$label</span>";
	$out .= sp_page_prev(SP()->rewrites->pageData['page'], $showLinks, SP()->forum->view->thisTopic->topic_permalink, $pageLinkClass, $iconClass, $prevIcon, $nextIcon, $toolTip, '');

	$url = SP()->forum->view->thisTopic->topic_permalink;
	if (SP()->rewrites->pageData['page'] > 1) $url = user_trailingslashit(trailingslashit($url).'page-'.SP()->rewrites->pageData['page']);
	$url = apply_filters('sph_page_link', $url, SP()->rewrites->pageData['page']);

	$out .= "<a href='$url' class='$pageLinkClass $curPageClass' title='$curToolTip'>".SP()->rewrites->pageData['page'].'</a>';

	$out .= sp_page_next(SP()->rewrites->pageData['page'], SP()->forum->view->thisTopic->total_pages, $showLinks, SP()->forum->view->thisTopic->topic_permalink, $pageLinkClass, $iconClass, $prevIcon, $nextIcon, $toolTip, '');

	if ($showJump) {
		$out .= '<span class="spPageJump">';
		$site = wp_nonce_url(SPAJAXURL.'spTopicPageJump&amp;targetaction=page-popup&amp;url='.SP()->forum->view->thisTopic->topic_permalink.'&amp;max='.SP()->forum->view->thisTopic->total_pages, 'spPageJump');
		$out .= "<a id='jump-$jumpID' rel='nofollow' class='$jumpClass spTopicPageJump' title='$jumpToolTip' data-site='$site' data-label='$jumpToolTip' data-width='200' data-height='0' data-align='0'>";
		$out .= $jumpIcon;
		$out .= '</a>';
		$out .= '</span>';
	}

	$out .= "</div>";
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
	# Define the post anchor here
	echo "<a id='p".SP()->forum->view->thisPost->post_id."'></a>";
}

# --------------------------------------------------------------------------------------
#
#	sp_PostIndexUserDate()
#	Display Post date
#	Scope:	Post Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_PostIndexUserDate($args = '') {
	$defs = array('tagId'     => 'spPostIndexUserDate%ID%',
	              'tagClass'  => 'spPostUserDate',
	              'nicedate'  => 0,
	              'date'      => 1,
	              'time'      => 1,
	              'stackdate' => 1,
				  'delimeter' => ' - ',
	              'echo'      => 1,
	              'get'       => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_PostIndexUserDate_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId     = esc_attr($tagId);
	$tagClass  = esc_attr($tagClass);
	$nicedate  = (int) $nicedate;
	$date      = (int) $date;
	$time      = (int) $time;
	$stackdate = (int) $stackdate;
	$delimeter = esc_attr($delimeter);
	$echo      = (int) $echo;
	$get       = (int) $get;

	($stackdate ? $dlb = '<br />' : $dlb = $delimeter);

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisPost->post_id, $tagId);

	if ($get) return SP()->forum->view->thisPost->post_date;

	$out = "<div id='$tagId' class='$tagClass'>";

	# date/time
	if ($nicedate) {
		$out .= SP()->dateTime->nice_date(SP()->forum->view->thisPost->post_date);
	} else {
		if ($date) {
			$out .= SP()->dateTime->format_date('d', SP()->forum->view->thisPost->post_date);
			if ($time) {
				$out .= $dlb.SP()->dateTime->format_date('t', SP()->forum->view->thisPost->post_date);
			}
		}
	}
	$out .= "</div>";
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
function sp_PostIndexUserName($args = '') {
	$defs = array('tagId'        => 'spPostIndexUserName%ID%',
	              'tagClass'     => 'spPostUserName',
	              'truncateUser' => 0,
	              'echo'         => 1,
	              'get'          => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_PostIndexUserName_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId        = esc_attr($tagId);
	$tagClass     = esc_attr($tagClass);
	$truncateUser = (int) $truncateUser;
	$echo         = (int) $echo;
	$get          = (int) $get;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisPost->post_id, $tagId);

	$out = "<div id='$tagId' class='$tagClass'>";
	if (SP()->forum->view->thisPostUser->member) {
		$name = SP()->user->name_display(SP()->forum->view->thisPostUser->ID, SP()->primitives->truncate_name(SP()->forum->view->thisPostUser->display_name, $truncateUser));
	} else {
		$name = SP()->primitives->truncate_name(SP()->forum->view->thisPost->guest_name, $truncateUser);
	}
	$out .= $name;

	if ($get) return $name;

	$out .= "</div>";
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
function sp_PostIndexUserPosts($args = '', $label = '') {
	if (SP()->forum->view->thisPostUser->guest) return;

	$defs = array('tagId'    => 'spPostIndexUserPosts%ID%',
	              'tagClass' => 'spPostUserPosts',
	              'echo'     => 1,
	              'get'      => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_PostIndexUserPosts_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId    = esc_attr($tagId);
	$tagClass = esc_attr($tagClass);
	$text     = SP()->displayFilters->title(str_replace('%COUNT%', SP()->forum->view->thisPostUser->posts, $label));
	$echo     = (int) $echo;
	$get      = (int) $get;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisPost->post_id, $tagId);

	if ($get) return SP()->forum->view->thisPostUser->posts;

	$out = "<div id='$tagId' class='$tagClass'>";
	$out .= $text;
	$out .= "</div>";
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
function sp_PostIndexUserRegistered($args = '', $label = '') {
	if (SP()->forum->view->thisPostUser->guest) return;

	$defs = array('tagId'      => 'spPostIndexUserRegistered%ID%',
	              'tagClass'   => 'spPostUserRegistered',
	              'dateFormat' => 'd',
	              'echo'       => 1,
	              'get'        => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_PostIndexUserRegistered_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId      = esc_attr($tagId);
	$tagClass   = esc_attr($tagClass);
	$dateFormat = esc_attr($dateFormat);
	$text       = SP()->displayFilters->title(str_replace('%DATE%', SP()->dateTime->format_date($dateFormat, SP()->forum->view->thisPostUser->user_registered), $label));
	$echo       = (int) $echo;
	$get        = (int) $get;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisPost->post_id, $tagId);

	if ($get) return SP()->forum->view->thisPostUser->posts;

	$out = "<div id='$tagId' class='$tagClass'>";
	$out .= $text;
	$out .= "</div>";
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
function sp_PostIndexUserBadges($args = '', $label = '') {
	$defs = array('tagId'      => 'spPostIndexUserBadges%ID%',
	              'tagClass'   => 'spPostUserRank',
	              'labelClass' => 'spInRowLabel',
	              'rank'       => 0,
	              'rankClass'  => 'spInRowRank',
	              'badge'      => 1,
	              'badgeClass' => 'spUserBadge',
	              'stack'      => 1,
	              'order'      => 'SNU',
	              'showAll'    => 1,
	              'echo'       => 1,
	              'get'        => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_PostIndexUserBadges_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId      = esc_attr($tagId);
	$tagClass   = esc_attr($tagClass);
	$labelClass = esc_attr($labelClass);
	$rankClass  = esc_attr($rankClass);
	$badgeClass = esc_attr($badgeClass);
	$rank       = (int) $rank;
	$badge      = (int) $badge;
	$stack      = (int) $stack;
	$order      = esc_attr($order);
	$showAll    = (int) $showAll;
	$echo       = (int) $echo;
	$get        = (int) $get;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisPost->post_id, $tagId);
	$att   = ($stack) ? '<br />' : '';
	$ranks = array();
	$idx   = 0;

	for ($x = 0; $x < (strlen($order)); $x++) {
		$xRank = substr($order, $x, 1);
		switch ($xRank) {
			case 'S': # Special Rank
				if (!empty(SP()->forum->view->thisPostUser->special_rank)) {
					foreach (SP()->forum->view->thisPostUser->special_rank as $thisRank) {
						$ranks[$idx]['name'] = $thisRank['name'];
						if ($thisRank['badge']) $ranks[$idx]['badge'] = $thisRank['badge'];
						$idx++;
					}
				}
				break;
			case 'N': # Normal Rank
				if (!empty(SP()->forum->view->thisPostUser->rank)) {
					$ranks[$idx]['name'] = SP()->forum->view->thisPostUser->rank[0]['name'];
					if (SP()->forum->view->thisPostUser->rank[0]['badge']) $ranks[$idx]['badge'] = SP()->forum->view->thisPostUser->rank[0]['badge'];
					$idx++;
				}
				break;
			case 'U': # UserGroup badge
				if (!empty(SP()->forum->view->thisPostUser->memberships)) {
					foreach (SP()->forum->view->thisPostUser->memberships as $r) {
						if ($r['usergroup_badge']) $ranks[$idx]['badge'] = SP_STORE_URL.'/'.SP()->plugin->storage['ranks'].'/'.$r['usergroup_badge'];
						$ranks[$idx]['name'] = $r['usergroup_name'];
						$idx++;
					}
				}
				break;
		}
		if (!$showAll) {
			if (!empty($ranks)) break;
		}
	}

	if ($get) return $ranks;

	# now render it
	$out = "<div id='$tagId' class='$tagClass'>";
	if (!empty($label)) $out .= "<span class='$labelClass'>".SP()->displayFilters->title($label)."$att</span>";
	foreach ($ranks as $thisRank) {
		if ($badge && !empty($thisRank['badge'])) {
			if(is_array($thisRank['badge'])) {
				# Array structure looks something like this:
				# [badge] => Array
				#	(
				#		[icon] => fa-glass
				#		[color] => #8224e3
				#		[size] => 
				#		[size_type] => 
				#		[type] => font
				#	)	
				#
				# or, for a file
				#
				# [badge] => Array
				#	(
				#		[icon] => icons8-o-512.png
				#		[color] => 
				#		[size] => 
				#		[size_type] => 
				#		[type] => file
				#	)				
				if (!empty( $thisRank['badge']['type'] && 'font' === $thisRank['badge']['type'])) {
					# Looks like image is an icon so paint it.
					$out .= SP()->theme->sp_paint_iconset_icon($thisRank['badge'], $badgeClass);
				} else {
					# Looks like image is a file so grab it from the sp-resources/forum-badges folder
					$out .= "<img class='$badgeClass aa' src='".esc_url(SPRANKSIMGURL . $thisRank['badge']['icon'])."' alt='' />$att";
				}
			} else {
				# We likely only have a single string that is the url to a file.
				$out .= "<img class='$badgeClass aa' src='".$thisRank['badge']."' alt='' />$att";
			}
		}
		if ($rank) $out .= "<span class='$rankClass'>".$thisRank['name']."</span>$att";
	}
	$out .= "</div>";
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
function sp_PostIndexUserRank($args = '') {
	$defs = array('tagId'             => 'spPostIndexUserRank%ID%',
	              'tagClass'          => 'spPostUserRank',
	              'imgClass'          => 'spUserBadge',
	              'showBadge'         => 1,
	              'showTitle'         => 1,
	              'hideIfSpecialRank' => 1,
	              'echo'              => 1,
	              'get'               => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_PostIndexUserRank_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId             = esc_attr($tagId);
	$tagClass          = esc_attr($tagClass);
	$imgClass          = esc_attr($imgClass);
	$showBadge         = (int) $showBadge;
	$showTitle         = (int) $showTitle;
	$hideIfSpecialRank = (int) $hideIfSpecialRank;
	$echo              = (int) $echo;
	$get               = (int) $get;

	if ($hideIfSpecialRank && !empty(SP()->forum->view->thisPostUser->special_rank)) return;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisPost->post_id, $tagId);

	if ($get) return SP()->forum->view->thisPostUser->rank[0];

	$show = false;
	$tout = "<div id='$tagId' class='$tagClass'>";
	if ($showBadge && !empty(SP()->forum->view->thisPostUser->rank[0]['badge'])) {
		$show = true;
		
		if( is_array( SP()->forum->view->thisPostUser->rank[0]['badge'] ) ) {
			$tout .= SP()->theme->sp_paint_iconset_icon( SP()->forum->view->thisPostUser->rank[0]['badge'], $imgClass );
		} else {
			$tout .= "<img class='$imgClass' src='".SP()->forum->view->thisPostUser->rank[0]['badge']."' alt='' />";
		}
		
		$tout .= "<br />";
	}
	if ($showTitle && !empty(SP()->forum->view->thisPostUser->rank[0]['name'])) {
		$show = true;
		$tout .= '<span class="spRank-'.sp_create_slug(SP()->forum->view->thisPost->postUser->rank[0]['name'], false).'">'.SP()->forum->view->thisPostUser->rank[0]['name'].'</span>';
	}
	$tout .= "</div>";

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
function sp_PostIndexUserSpecialRank($args = '') {
	if (SP()->forum->view->thisPostUser->guest) return;

	$defs = array('tagId'     => 'spPostIndexUserSpecialRank%ID%',
	              'tagClass'  => 'spPostUserSpecialRank',
	              'imgClass'  => 'spUserBadge',
	              'showBadge' => 1,
	              'showTitle' => 1,
	              'stacked'   => 1,
	              'echo'      => 1,
	              'get'       => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_PostIndexUserSpecialRank_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId     = esc_attr($tagId);
	$tagClass  = esc_attr($tagClass);
	$imgClass  = esc_attr($imgClass);
	$showBadge = (int) $showBadge;
	$showTitle = (int) $showTitle;
	$stacked   = (int) $stacked;
	$echo      = (int) $echo;
	$get       = (int) $get;

	if (!$showTitle && !$showBadge) return;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisPost->post_id, $tagId);

	if ($get) return SP()->forum->view->thisPostUser->special_rank;

	$show = false;
	$tout = "<div id='$tagId' class='$tagClass'>";
	if (($showBadge || $showTitle) && !empty(SP()->forum->view->thisPostUser->special_rank)) {
		foreach (SP()->forum->view->thisPostUser->special_rank as $rank) {
			if ($showBadge && !empty($rank['badge'])) {
				$show = true;
				
				if( is_array( $rank['badge'] ) ) {
					$tout .= SP()->theme->sp_paint_iconset_icon( $rank['badge'], $imgClass );
				} else {
					$tout .= "<img class='$imgClass' src='".$rank['badge']."' alt='' />";
				}
				
				$tout .= ($stacked) ? '<br />' : ' ';
			}
			if ($showTitle && !empty($rank['name'])) {
				$show = true;
				$tout .= '<span class="spSpecialRank-'.sp_create_slug($rank['name'], false).'">'.$rank['name'].'</span>';
				$tout .= ($stacked) ? '<br />' : ' ';
			}
		}
	}
	$tout .= "</div>";

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
function sp_PostIndexUserMemberships($args = '', $noMembershipLabel = '', $adminLabel = '') {
	$defs = array('tagId'     => 'spPostIndexUserMemberships%ID%',
	              'tagClass'  => 'spPostUserMemberships',
	              'stacked'   => 1,
	              'showTitle' => 1,
	              'showBadge' => 1,
	              'echo'      => 1,
	              'get'       => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_PostIndexUserMemberships_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId     = esc_attr($tagId);
	$tagClass  = esc_attr($tagClass);
	$stacked   = (int) $stacked;
	$showTitle = (int) $showTitle;
	$showBadge = (int) $showBadge;
	$echo      = (int) $echo;
	$get       = (int) $get;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisPost->post_id, $tagId);

	if ($get) return SP()->forum->view->thisPostUser->memberships;

	$show = false;
	$tout = "<div id='$tagId' class='$tagClass'>";
	if (!empty(SP()->forum->view->thisPostUser->memberships)) {
		$first = true;
		$split = ($stacked) ? '<br />' : ', ';
		foreach (SP()->forum->view->thisPostUser->memberships as $membership) {
			if (!$first) $tout .= $split;
			if ($showBadge && !empty($membership['usergroup_badge'])) {
				$show = true;
				$tout .= "<img src='".SP_STORE_URL.'/'.SP()->plugin->storage['ranks'].'/'.$membership['usergroup_badge']."' alt='' />";
				$tout .= '<br />';
			}
			if ($showTitle) {
				$show = true;
				$tout .= '<span class="spUserGroup-'.sp_create_slug($membership['usergroup_name'], false).'">'.$membership['usergroup_name'].'</span><br />';
			}
			$first = false;
		}
	} else if (SP()->forum->view->thisPostUser->admin) {
		if ($showTitle && !empty($adminLabel)) {
			$show = true;
			$tout .= SP()->displayFilters->title($adminLabel);
		}
	} else {
		if (!empty($noMembershipLabel)) {
			$show = true;
			$tout .= SP()->displayFilters->title($noMembershipLabel);
		}
	}
	$tout .= "</div>";

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
function sp_PostIndexNumber($args = '') {
	$defs = array('tagId'    => 'spPostIndexNumber%ID%',
	              'tagClass' => 'spLabelBordered',
	              'echo'     => 1,
	              'get'      => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_PostIndexNumber_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId    = esc_attr($tagId);
	$tagClass = esc_attr($tagClass);
	$echo     = (int) $echo;
	$get      = (int) $get;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisPost->post_id, $tagId);

	if ($get) return SP()->forum->view->thisPost->post_index;

	$out = "<span id='$tagId' class='$tagClass'>".SP()->forum->view->thisPost->post_index."</span>";
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
function sp_PostIndexPinned($args = '', $toolTip = '') {
	if (!SP()->forum->view->thisPost->post_pinned) return;

	$defs = array('tagId'    => 'spPostIndexPinned%ID%',
	              'tagClass' => 'spStatusIcon',
	              'iconPin'  => 'sp_TopicStatusPin.png',
	              'echo'     => 1,
	              'get'      => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_PostIndexPinned_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId    = esc_attr($tagId);
	$tagClass = esc_attr($tagClass);
	$echo     = (int) $echo;
	$get      = (int) $get;
	$icon     = sanitize_file_name($iconPin);
	$toolTip  = SP()->displayFilters->title($toolTip);

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisPost->post_id, $tagId);

	if ($get) return SP()->forum->view->thisPost->post_pinned;

	$out = "<span id='$tagId' class='$tagClass'>";
	if (!empty($icon)) $out .= SP()->theme->paint_icon('', SPTHEMEICONSURL, $icon, $toolTip);
	$out .= "</span>";
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
function sp_PostIndexNewPost($args = '', $label = '') {
	if (!SP()->user->thisUser->member || empty($label)) return;
	if (!SP()->forum->view->thisPost->new_post) return;
	if (SP()->forum->view->thisPost->user_id == SP()->user->thisUser->ID) return;

	$defs = array('tagId'    => 'spPostIndexNewPost%ID%',
	              'tagClass' => 'spLabelBordered',
	              'echo'     => 1,
	              'get'      => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_PostIndexNewPost_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId    = esc_attr($tagId);
	$tagClass = esc_attr($tagClass);
	$echo     = (int) $echo;
	$get      = (int) $get;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisPost->post_id, $tagId);

	if ($get) return true;

	$out = "<span id='$tagId' class='$tagClass'>".SP()->displayFilters->title($label)."</span>";
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
function sp_PostIndexEditHistory($args = '', $label = '', $legend = '', $toolTip = '') {
	if (empty(SP()->forum->view->thisPost->edits) || empty($legend)) return;

	$defs = array('tagId'     => 'spPostIndexEditHistory%ID%',
	              'tagClass'  => 'spButton',
	              'icon'      => 'sp_EditHistory.png',
	              'iconClass' => 'spIcon',
	              'popup'     => 1,
	              'count'     => 0,
	              'echo'      => 1,
	              'get'       => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_PostIndexEditHistory_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId     = esc_attr($tagId);
	$tagClass  = esc_attr($tagClass);
	$icon      = sanitize_file_name($icon);
	$iconClass = esc_attr($iconClass);
	$toolTip   = SP()->displayFilters->title($toolTip);
	$popup     = (int) $popup;
	$count     = (int) $count;
	$echo      = (int) $echo;
	$get       = (int) $get;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisPost->post_id, $tagId);

	if ($get) return SP()->forum->view->thisPost->edits;

	# build history to show
	$edits = (empty($count)) ? SP()->forum->view->thisPost->edits : array_slice(SP()->forum->view->thisPost->edits, max(count(SP()->forum->view->thisPost->edits) - $count, 0), $count);

	# Construct text
	if ($edits) {
		$history = '<p>';
		foreach ($edits as $edit) {
			$thisLegend = str_replace('%USER%', $edit->by, $legend);
			$thisLegend = str_replace('%DATE%', SP()->dateTime->apply_timezone($edit->at), $thisLegend);
			$history .= $thisLegend.'<br />';
		}
		$history .= '</p>';
	}

	if ($popup) {
		$out = "<a class='$tagClass spEditPostHistory' id='$tagId' title='$toolTip' rel='nofollow' data-html='".esc_attr($history)."' data-label='$toolTip' data-width='400' data-height='0' data-align='0'>";
		if (!empty($icon)) $out .= SP()->theme->paint_icon($iconClass, SPTHEMEICONSURL, $icon);
		if (!empty($label)) $out .= SP()->displayFilters->title($label);
		$out .= "</a>";
	} else {
		$out .= "<div id='$tagId' class='$tagClass'>$history</div>";
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
function sp_PostIndexPermalink($args = '', $label = '', $toolTip = '') {
	$defs = array('tagId'     => 'spPostIndexPermalink%ID%',
	              'tagClass'  => 'spButton',
	              'icon'      => 'sp_Permalink.png',
	              'iconClass' => 'spIcon',
	              'echo'      => 1,
	              'get'       => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_PostIndexPermalink_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId     = esc_attr($tagId);
	$tagClass  = esc_attr($tagClass);
	$icon      = sanitize_file_name($icon);
	$iconClass = esc_attr($iconClass);
	$toolTip   = esc_attr($toolTip);
	$echo      = (int) $echo;
	$get       = (int) $get;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisPost->post_id, $tagId);

	if ($get) return SP()->forum->view->thisPost->post_permalink;

	$out = "<a class='$tagClass' id='$tagId' title='$toolTip' rel='nofollow' href='".SP()->forum->view->thisPost->post_permalink."'>";
	if (!empty($icon)) $out .= SP()->theme->paint_icon($iconClass, SPTHEMEICONSURL, $icon);
	if (!empty($label)) $out .= SP()->displayFilters->title($label);
	$out .= "</a>";
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
function sp_PostIndexPrint($args = '', $label = '', $toolTip = '') {
	if (SP()->forum->view->thisPost->post_status != 0) return;

	$defs = array('tagId'     => 'spPostIndexPrint%ID%',
	              'tagClass'  => 'spButton',
	              'icon'      => 'sp_Print.png',
	              'iconClass' => 'spIcon',
	              'echo'      => 1,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_PostIndexPrint_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId     = esc_attr($tagId);
	$tagClass  = esc_attr($tagClass);
	$icon      = sanitize_file_name($icon);
	$iconClass = esc_attr($iconClass);
	$toolTip   = esc_attr($toolTip);
	$echo      = (int) $echo;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisPost->post_id, $tagId);
	$out   = "<a class='$tagClass spPrintThisPost' id='$tagId' title='$toolTip' rel='nofollow' data-postid='spPostIndexContent".SP()->forum->view->thisPost->post_id."'>";
	if (!empty($icon)) $out .= SP()->theme->paint_icon($iconClass, SPTHEMEICONSURL, $icon);
	if (!empty($label)) $out .= SP()->displayFilters->title($label);
	$out .= "</a>";
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
function sp_PostIndexQuote($args = '', $label = '', $toolTip = '') {
	# checks for displaying button
	if (SP()->forum->view->thisTopic->editmode) return;
	if (SP()->forum->view->thisPost->post_status != 0 && !SP()->user->thisUser->admin) return;
	if (!SP()->auths->get('reply_topics', SP()->forum->view->thisTopic->forum_id)) return;
	if ((SP()->core->forumData['lockdown'] || SP()->forum->view->thisTopic->forum_status || SP()->forum->view->thisTopic->topic_status) && !SP()->user->thisUser->admin) return;
	if (!SP()->auths->get('view_admin_posts', SP()->forum->view->thisTopic->forum_id) && SP()->auths->forum_admin(SP()->forum->view->thisPost->user_id)) return;
	if (SP()->auths->get('view_own_admin_posts', SP()->forum->view->thisTopic->forum_id) && !SP()->auths->forum_admin(SP()->forum->view->thisPost->user_id) && !SP()->auths->forum_mod(SP()->forum->view->thisPost->user_id) && SP()->user->thisUser->ID != SP()->forum->view->thisPost->user_id) return;

	$defs = array('tagId'     => 'spPostIndexQuote%ID%',
	              'tagClass'  => 'spButton',
	              'icon'      => 'sp_QuotePost.png',
	              'iconClass' => 'spIcon',
	              'echo'      => 1,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_PostIndexQuote_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId     = esc_attr($tagId);
	$tagClass  = esc_attr($tagClass);
	$icon      = sanitize_file_name($icon);
	$iconClass = esc_attr($iconClass);
	$toolTip   = esc_attr($toolTip);
	$echo      = (int) $echo;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisPost->post_id, $tagId);

	$quoteUrl = wp_nonce_url(SPAJAXURL.'spQuotePost', 'spQuotePost');
	if (SP()->forum->view->thisPostUser->member) {
		$name = SP()->forum->view->thisPostUser->display_name;
	} else {
		$name = SP()->forum->view->thisPost->guest_name;
	}
	$intro = esc_attr($name.' '.SP()->primitives->front_text('said').' ');
	$out   = "<a class='$tagClass spQuotePost' id='$tagId' title='$toolTip' rel='nofollow' data-postid='".SP()->forum->view->thisPost->post_id."' data-intro='$intro' data-forumid='".SP()->forum->view->thisTopic->forum_id."' data-url='$quoteUrl'>";
	if (!empty($icon)) $out .= SP()->theme->paint_icon($iconClass, SPTHEMEICONSURL, $icon);
	if (!empty($label)) $out .= SP()->displayFilters->title($label);
	$out .= "</a>";
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
function sp_PostIndexEdit($args = '', $label = '', $toolTip = '') {
	if (SP()->forum->view->thisTopic->editmode) return;
	if (SP()->core->forumData['lockdown']) return;

	$canEdit = false;
	if (SP()->auths->get('edit_any_post', SP()->forum->view->thisTopic->forum_id)) {
		$canEdit = true;
	} else {
		if (SP()->forum->view->thisPostUser->ID == SP()->user->thisUser->ID) {
			$edit_days = SP()->options->get('editpostdays');
			$post_date = strtotime(SP()->dateTime->format_date('d', SP()->forum->view->thisPost->post_date));
			$date_diff = floor((time() - $post_date) / (60 * 60 * 24));
			if (SP()->auths->get('edit_own_posts_forever', SP()->forum->view->thisTopic->forum_id) || (SP()->auths->get('edit_own_posts_reply', SP()->forum->view->thisTopic->forum_id) && SP()->forum->view->thisPost->last_post) || (SP()->auths->get('edit_own_posts_for_time', SP()->forum->view->thisTopic->forum_id) && $date_diff <= $edit_days)) {
				$canEdit = true;
			}
		}
	}
	if (!$canEdit) return;

	$defs = array('tagId'     => 'spPostIndexEdit%ID%',
	              'tagClass'  => 'spButton',
	              'icon'      => 'sp_EditPost.png',
	              'iconClass' => 'spIcon',
	              'echo'      => 1,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_PostIndexEdit_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId     = esc_attr($tagId);
	$tagClass  = esc_attr($tagClass);
	$icon      = sanitize_file_name($icon);
	$iconClass = esc_attr($iconClass);
	$toolTip   = esc_attr($toolTip);
	$echo      = (int) $echo;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisPost->post_id, $tagId);

	$action = SP()->forum->view->thisTopic->topic_permalink;
	if (SP()->forum->view->thisTopic->display_page > 1) $action = user_trailingslashit(trailingslashit($action).'page-'.SP()->forum->view->thisTopic->display_page);
	$out = "<form class='spButtonForm' action='$action' method='post' name='usereditpost".SP()->forum->view->thisPost->post_id."'>";
	$out .= "<input type='hidden' name='postedit' value='".SP()->forum->view->thisPost->post_id."' />";
	$out .= "<a class='$tagClass' id='$tagId' title='$toolTip' rel='nofollow' href='javascript:document.usereditpost".SP()->forum->view->thisPost->post_id.".submit();'>";
	if (!empty($icon)) $out .= SP()->theme->paint_icon($iconClass, SPTHEMEICONSURL, $icon);
	if (!empty($label)) $out .= SP()->displayFilters->title($label);
	$out .= "</a>";
	$out .= '</form>';

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
function sp_PostIndexDelete($args = '', $label = '', $toolTip = '') {
	if (SP()->forum->view->thisTopic->editmode) return;
	if (SP()->core->forumData['lockdown']) return;

	if (!SP()->auths->get('delete_any_post', SP()->forum->view->thisTopic->forum_id) && !(SP()->auths->get('delete_own_posts', SP()->forum->view->thisTopic->forum_id) && SP()->user->thisUser->ID == SP()->forum->view->thisPost->user_id)) return;

	$defs = array('tagId'     => 'spPostIndexDelete%ID%',
	              'tagClass'  => 'spButton',
	              'icon'      => 'sp_DeletePost.png',
	              'iconClass' => 'spIcon',
	              'echo'      => 1,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_PostIndexDelete_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId     = esc_attr($tagId);
	$tagClass  = esc_attr($tagClass);
	$icon      = sanitize_file_name($icon);
	$iconClass = esc_attr($iconClass);
	$toolTip   = esc_attr($toolTip);
	$echo      = (int) $echo;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisPost->post_id, $tagId);

	$out = '';

	$ajaxUrl = wp_nonce_url(SPAJAXURL."spForumTools&targetaction=delete-post&killpost=".SP()->forum->view->thisPost->post_id."&killposttopic=".SP()->forum->view->thisTopic->topic_id."&killpostforum=".SP()->forum->view->thisTopic->forum_id."&killpostposter=".SP()->forum->view->thisPost->user_id."&page=".SP()->rewrites->pageData['page'], 'spForumTools');
	$out .= "<a class='$tagClass spDeletePost' id='$tagId' title='$toolTip' rel='nofollow' data-url='$ajaxUrl' data-postid='".SP()->forum->view->thisPost->post_id."' data-topicid='".SP()->forum->view->thisTopic->topic_id."'>";
	if (!empty($icon)) $out .= SP()->theme->paint_icon($iconClass, SPTHEMEICONSURL, $icon);
	if (!empty($label)) $out .= SP()->displayFilters->title($label);
	$out .= "</a>";

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
function sp_PostIndexContent($args = '', $label = '') {
	$defs = array('tagId'    => 'spPostIndexContent%ID%',
	              'tagClass' => 'spPostContent',
	              'modClass' => 'spMessage',
	              'echo'     => 1,
	              'get'      => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_PostIndexContent_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId    = esc_attr($tagId);
	$tagClass = esc_attr($tagClass);
	$modClass = esc_attr($modClass);
	$echo     = (int) $echo;
	$get      = (int) $get;

	($label ? SP()->displayFilters->title($label) : SP()->displayFilters->title(SP()->primitives->front_text('Awaiting Moderation')));
	$tagId = str_ireplace('%ID%', SP()->forum->view->thisPost->post_id, $tagId);

	if ($get) return SP()->forum->view->thisPost->post_content;

	$out = "<div id='$tagId' class='$tagClass'>";

	# Check moderation status
	if (SP()->forum->view->thisPost->post_status == false) {
		$post_content = SP()->forum->view->thisPost->post_content;
	} else {
		$modLabel = "<div class='$modClass'>$label</div>";
		if (SP()->auths->get('moderate_posts', SP()->forum->view->thisTopic->forum_id) || (SP()->user->thisUser->member && SP()->user->thisUser->ID == SP()->forum->view->thisPostUser->ID) || (SP()->user->thisUser->guest && !empty(SP()->user->guest_cookie->email) && SP()->user->guest_cookie->email == SP()->forum->view->thisPost->guest_email)) {
			$post_content = $modLabel.'<hr />'.SP()->forum->view->thisPost->post_content;
		} else {
			$post_content = $modLabel.'<hr />';
		}
	}

	# Hook: Used in sp-startup/core/sp-core-compatibility.php
	do_action( 'sph_before_PostIndexContent' );

	$ob = SP()->options->get('sfuseob');
	if (!$ob) {
		# strict use of the wp api is NOT enabled.
		remove_filter('the_content', 'sp_render_forum', 1);
		$out .= apply_filters('the_content', $post_content);
		add_filter('the_content', 'sp_render_forum', 1);
	} else {
		# strict use of the wp api is enabled.
		$out .= $post_content;
	}

	# Hook: Used in sp-startup/core/sp-core-compatibility.php	
	do_action( 'sph_after_PostIndexContent' );
	
	$out .= "</div>";
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
function sp_PostIndexUserSignature($args = '') {
	if (SP()->forum->view->thisPostUser->guest) return;

	if (empty(SP()->forum->view->thisPostUser->signature)) return;
	$defs = array('tagId'           => 'spPostIndexUserSignature%ID%',
	              'tagClass'        => 'spPostUserSignature',
	              'containerClass'  => 'spPostSignatureSection',
	              'maxHeightBottom' => 55,
	              'echo'            => 1,
	              'get'             => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_PostIndexUserSignature_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId           = esc_attr($tagId);
	$tagClass        = esc_attr($tagClass);
	$containerClass  = esc_attr($containerClass);
	$maxHeightBottom = (int) $maxHeightBottom;
	$echo            = (int) $echo;
	$get             = (int) $get;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisPost->post_id, $tagId);

	if ($get) return SP()->forum->view->thisPostUser->signature;

	# force sig to have no follow in links and follow size limits
	$sig = SP()->saveFilters->nofollow(SP()->forum->view->thisPostUser->signature);

	$containerStyle = (empty($maxHeightBottom)) ? '' : ' style="width:inherit; margin-top:'.($maxHeightBottom + 25).'px"';
	$tagStyle       = (empty($maxHeightBottom)) ? '' : ' style="max-height:'.$maxHeightBottom.'px; position:absolute; bottom: 0; width:inherit;"';
	$out            = "<div class='$containerClass'$containerStyle>";
	$out .= "<div id='$tagId' class='$tagClass'$tagStyle>";
	$out .= $sig."";
	$out .= "</div>";
	$out .= "</div>";
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
function sp_PostIndexUserFlexSignature($args = '') {
	if (SP()->forum->view->thisPostUser->guest) return;

	if (empty(SP()->forum->view->thisPostUser->signature)) return;
	$defs = array('tagId'    => 'spPostIndexUserFlexSignature%ID%',
	              'tagClass' => 'spPostUserSignature',
				  'rule'	 => 0,
	              'echo'     => 1,
	              'get'      => 0,

	);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_PostIndexUserSignature_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId    = esc_attr($tagId);
	$tagClass = esc_attr($tagClass);
	$rule	  = (int) $rule;
	$echo     = (int) $echo;
	$get      = (int) $get;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisPost->post_id, $tagId);

	if ($get) return SP()->forum->view->thisPostUser->signature;

	# force sig to have no follow in links and follow size limits
	$sig = SP()->saveFilters->nofollow(SP()->forum->view->thisPostUser->signature);

	if (empty($sig)) return;

	$out = '';
	if ($rule) $out.= '<hr />';
	$out .= "<div id='$tagId' class='$tagClass'>";
	$out .= $sig."";
	$out .= "</div>";

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
function sp_PostIndexUserTwitter($args = '', $toolTip = '') {
	if (SP()->forum->view->thisPostUser->guest) return;
	if (empty(SP()->forum->view->thisPostUser->twitter)) return;

	$defs = array('tagId'     => 'spPostIndexUserTwitter%ID%',
	              'tagClass'  => 'spPostUserTwitter',
	              'icon'      => 'sp_Twitter.png',
	              'iconClass' => 'spImg',
	              'targetNew' => 1,
	              'noFollow'  => 0,
	              'echo'      => 1,
	              'get'       => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_PostIndexUserTwitter_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId     = esc_attr($tagId);
	$tagClass  = esc_attr($tagClass);
	$icon      = sanitize_file_name($icon);
	$iconClass = esc_attr($iconClass);
	$toolTip   = esc_attr($toolTip);
	$targetNew = (int) $targetNew;
	$noFollow  = (int) $noFollow;
	$echo      = (int) $echo;
	$get       = (int) $get;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisPost->post_id, $tagId);

	if (filter_var(SP()->forum->view->thisPostUser->twitter, FILTER_VALIDATE_URL)) {
		$url = SP()->displayFilters->url(SP()->forum->view->thisPostUser->twitter);
	} else {
		$url = 'http://twitter.com/'.SP()->forum->view->thisPostUser->twitter;
	}

	if ($get) return $url;

	$target = ($targetNew) ? ' target="_blank"' : '';
	$follow = ($noFollow) ? ' rel="nofollow"' : '';
	if (!empty($icon)) {
		$out = "<a id='$tagId' class='$tagClass' href='$url' title='$toolTip'$target$follow>";
		$out .= SP()->theme->paint_icon($iconClass, SPTHEMEICONSURL, $icon);
		$out .= "</a>";
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
function sp_PostIndexUserFacebook($args = '', $toolTip = '') {
	if (SP()->forum->view->thisPostUser->guest) return;
	if (empty(SP()->forum->view->thisPostUser->facebook)) return;

	$defs = array('tagId'     => 'spPostIndexUserFacebook%ID%',
	              'tagClass'  => 'spPostUserFacebook',
	              'icon'      => 'sp_Facebook.png',
	              'iconClass' => 'spImg',
	              'targetNew' => 1,
	              'noFollow'  => 0,
	              'echo'      => 1,
	              'get'       => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_PostIndexUserFacebook_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId     = esc_attr($tagId);
	$tagClass  = esc_attr($tagClass);
	$icon      = sanitize_file_name($icon);
	$iconClass = esc_attr($iconClass);
	$toolTip   = esc_attr($toolTip);
	$targetNew = (int) $targetNew;
	$noFollow  = (int) $noFollow;
	$echo      = (int) $echo;
	$get       = (int) $get;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisPost->post_id, $tagId);

	if (filter_var(SP()->forum->view->thisPostUser->facebook, FILTER_VALIDATE_URL)) {
		$url = SP()->displayFilters->url(SP()->forum->view->thisPostUser->facebook);
	} else {
		$url = 'http://facebook.com/'.SP()->forum->view->thisPostUser->facebook;
	}

	if ($get) return $url;

	$target = ($targetNew) ? ' target="_blank"' : '';
	$follow = ($noFollow) ? ' rel="nofollow"' : '';
	if (!empty($icon)) {
		$out = "<a id='$tagId' class='$tagClass' href='$url' title='$toolTip'$target$follow>";
		$out .= SP()->theme->paint_icon($iconClass, SPTHEMEICONSURL, $icon);
		$out .= "</a>";
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
function sp_PostIndexUserMySpace($args = '', $toolTip = '') {
	if (SP()->forum->view->thisPostUser->guest) return;
	if (empty(SP()->forum->view->thisPostUser->myspace)) return;

	$defs = array('tagId'     => 'spPostIndexUserMySpace%ID%',
	              'tagClass'  => 'spPostUserMySpace',
	              'icon'      => 'sp_MySpace.png',
	              'iconClass' => 'spImg',
	              'targetNew' => 1,
	              'noFollow'  => 0,
	              'echo'      => 1,
	              'get'       => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_PostIndexUserMySpace_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId     = esc_attr($tagId);
	$tagClass  = esc_attr($tagClass);
	$icon      = sanitize_file_name($icon);
	$iconClass = esc_attr($iconClass);
	$toolTip   = esc_attr($toolTip);
	$targetNew = (int) $targetNew;
	$noFollow  = (int) $noFollow;
	$echo      = (int) $echo;
	$get       = (int) $get;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisPost->post_id, $tagId);

	if (filter_var(SP()->forum->view->thisPostUser->myspace, FILTER_VALIDATE_URL)) {
		$url = SP()->displayFilters->url(SP()->forum->view->thisPostUser->myspace);
	} else {
		$url = 'http://myspace.com/'.SP()->forum->view->thisPostUser->myspace;
	}

	if ($get) return $url;

	$target = ($targetNew) ? ' target="_blank"' : '';
	$follow = ($noFollow) ? ' rel="nofollow"' : '';
	if (!empty($icon)) {
		$out = "<a id='$tagId' class='$tagClass' href='$url' title='$toolTip'$target$follow>";
		$out .= SP()->theme->paint_icon($iconClass, SPTHEMEICONSURL, $icon);
		$out .= "</a>";
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
function sp_PostIndexUserLinkedIn($args = '', $toolTip = '') {
	if (SP()->forum->view->thisPostUser->guest) return;
	if (empty(SP()->forum->view->thisPostUser->linkedin)) return;

	$defs = array('tagId'     => 'spPostIndexUserLinkedIn%ID%',
	              'tagClass'  => 'spPostUserLinkedIn',
	              'icon'      => 'sp_LinkedIn.png',
	              'iconClass' => 'spImg',
	              'targetNew' => 1,
	              'noFollow'  => 0,
	              'echo'      => 1,
	              'get'       => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_PostIndexUserLinkedIn_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId     = esc_attr($tagId);
	$tagClass  = esc_attr($tagClass);
	$icon      = sanitize_file_name($icon);
	$iconClass = esc_attr($iconClass);
	$toolTip   = esc_attr($toolTip);
	$targetNew = (int) $targetNew;
	$noFollow  = (int) $noFollow;
	$echo      = (int) $echo;
	$get       = (int) $get;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisPost->post_id, $tagId);

	if (filter_var(SP()->forum->view->thisPostUser->linkedin, FILTER_VALIDATE_URL)) {
		$url = SP()->displayFilters->url(SP()->forum->view->thisPostUser->linkedin);
	} else {
		$url = 'http://linkedin.com/'.SP()->forum->view->thisPostUser->linkedin;
	}

	if ($get) return $url;

	$target = ($targetNew) ? ' target="_blank"' : '';
	$follow = ($noFollow) ? ' rel="nofollow"' : '';
	if (!empty($icon)) {
		$out = "<a id='$tagId' class='$tagClass' href='$url' title='$toolTip'$target$follow>";
		$out .= SP()->theme->paint_icon($iconClass, SPTHEMEICONSURL, $icon);
		$out .= "</a>";
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
function sp_PostIndexUserYouTube($args = '', $toolTip = '') {
	if (SP()->forum->view->thisPostUser->guest) return;
	if (empty(SP()->forum->view->thisPostUser->youtube)) return;

	$defs = array('tagId'     => 'spPostIndexUserYouTube%ID%',
	              'tagClass'  => 'spPostUserYouTube',
	              'icon'      => 'sp_YouTube.png',
	              'iconClass' => 'spImg',
	              'targetNew' => 1,
	              'noFollow'  => 0,
	              'echo'      => 1,
	              'get'       => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_PostIndexUserYouTube_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId     = esc_attr($tagId);
	$tagClass  = esc_attr($tagClass);
	$icon      = sanitize_file_name($icon);
	$iconClass = esc_attr($iconClass);
	$toolTip   = esc_attr($toolTip);
	$targetNew = (int) $targetNew;
	$noFollow  = (int) $noFollow;
	$echo      = (int) $echo;
	$get       = (int) $get;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisPost->post_id, $tagId);

	if (filter_var(SP()->forum->view->thisPostUser->youtube, FILTER_VALIDATE_URL)) {
		$url = SP()->displayFilters->url(SP()->forum->view->thisPostUser->youtube);
	} else {
		$url = 'http://youtube.com/user/'.SP()->forum->view->thisPostUser->youtube;
	}

	if ($get) return $url;

	$target = ($targetNew) ? ' target="_blank"' : '';
	$follow = ($noFollow) ? ' rel="nofollow"' : '';
	if (!empty($icon)) {
		$out = "<a id='$tagId' class='$tagClass' href='$url' title='$toolTip'$target$follow>";
		$out .= SP()->theme->paint_icon($iconClass, SPTHEMEICONSURL, $icon);
		$out .= "</a>";
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
function sp_PostIndexUserGooglePlus($args = '', $toolTip = '') {
	if (SP()->forum->view->thisPostUser->guest) return;
	if (empty(SP()->forum->view->thisPostUser->googleplus)) return;

	$defs = array('tagId'     => 'spPostIndexUserGooglePlus%ID%',
	              'tagClass'  => 'spPostUserGooglePlus',
	              'icon'      => 'sp_GooglePlus.png',
	              'iconClass' => 'spImg',
	              'targetNew' => 1,
	              'noFollow'  => 0,
	              'echo'      => 1,
	              'get'       => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_PostIndexUserGooglePlus_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId     = esc_attr($tagId);
	$tagClass  = esc_attr($tagClass);
	$icon      = sanitize_file_name($icon);
	$iconClass = esc_attr($iconClass);
	$toolTip   = esc_attr($toolTip);
	$targetNew = (int) $targetNew;
	$noFollow  = (int) $noFollow;
	$echo      = (int) $echo;
	$get       = (int) $get;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisPost->post_id, $tagId);

	if (filter_var(SP()->forum->view->thisPostUser->googleplus, FILTER_VALIDATE_URL)) {
		$url = SP()->displayFilters->url(SP()->forum->view->thisPostUser->googleplus);
	} else {
		$url = 'https://plus.google.com/u/'.SP()->forum->view->thisPostUser->googleplus.'/'.SP()->forum->view->thisPostUser->googleplus;
	}

	if ($get) return $url;

	$target = ($targetNew) ? ' target="_blank"' : '';
	$follow = ($noFollow) ? ' rel="nofollow"' : '';
	if (!empty($icon)) {
		$out = "<a id='$tagId' class='$tagClass' href='$url' title='$toolTip'$target$follow>";
		$out .= SP()->theme->paint_icon($iconClass, SPTHEMEICONSURL, $icon);
		$out .= "</a>";
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
#	sp_PostIndexUserInstagram()
#	Display User's Instagram icon and link
#	Scope:	Post Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_PostIndexUserInstagram($args = '', $toolTip = '') {
	if (SP()->forum->view->thisPostUser->guest) return;
	if (empty(SP()->forum->view->thisPostUser->instagram)) return;

	$defs = array('tagId'     => 'spPostIndexUserInstagram%ID%',
	              'tagClass'  => 'spPostUserInstagram',
	              'icon'      => 'sp_instagram.png',
	              'iconClass' => 'spImg',
	              'targetNew' => 1,
	              'noFollow'  => 0,
	              'echo'      => 1,
	              'get'       => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_PostIndexUserInstagram_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId     = esc_attr($tagId);
	$tagClass  = esc_attr($tagClass);
	$icon      = sanitize_file_name($icon);
	$iconClass = esc_attr($iconClass);
	$toolTip   = esc_attr($toolTip);
	$targetNew = (int) $targetNew;
	$noFollow  = (int) $noFollow;
	$echo      = (int) $echo;
	$get       = (int) $get;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisPost->post_id, $tagId);

	if (filter_var(SP()->forum->view->thisPostUser->instagram, FILTER_VALIDATE_URL)) {
		$url = SP()->displayFilters->url(SP()->forum->view->thisPostUser->instagram);
	} else {
		$url = 'https://instagram.com/'.SP()->forum->view->thisPostUser->instagram.'/';
	}

	if ($get) return $url;

	$target = ($targetNew) ? ' target="_blank"' : '';
	$follow = ($noFollow) ? ' rel="nofollow"' : '';
	if (!empty($icon)) {
		$out = "<a id='$tagId' class='$tagClass' href='$url' title='$toolTip'$target$follow>";
		$out .= SP()->theme->paint_icon($iconClass, SPTHEMEICONSURL, $icon);
		$out .= "</a>";
	}
	$out = apply_filters('sph_PostIndexUserInstagram', $out, $a);

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
function sp_PostIndexUserWebsite($args = '', $toolTip = '') {
	if (SP()->forum->view->thisPostUser->guest) return;
	if (empty(SP()->forum->view->thisPostUser->user_url)) return;

	$defs = array('tagId'     => 'spPostIndexUserWebsite%ID%',
	              'tagClass'  => 'spPostUserWebsite',
	              'icon'      => 'sp_UserWebsite.png',
	              'iconClass' => 'spImg',
	              'targetNew' => 1,
	              'noFollow'  => 0,
	              'echo'      => 1,
	              'get'       => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_PostIndexUserWebsite_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId     = esc_attr($tagId);
	$tagClass  = esc_attr($tagClass);
	$icon      = sanitize_file_name($icon);
	$iconClass = esc_attr($iconClass);
	$toolTip   = esc_attr($toolTip);
	$targetNew = (int) $targetNew;
	$noFollow  = (int) $noFollow;
	$echo      = (int) $echo;
	$get       = (int) $get;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisPost->post_id, $tagId);

	if ($get) return SP()->forum->view->thisPostUser->user_url;

	$target = ($targetNew) ? ' target="_blank"' : '';
	$follow = ($noFollow) ? ' rel="nofollow"' : '';
	if (!empty($icon)) {
		$out = "<a id='$tagId' class='$tagClass' href='".SP()->forum->view->thisPostUser->user_url."' title='$toolTip'$target$follow>";
		$out .= SP()->theme->paint_icon($iconClass, SPTHEMEICONSURL, $icon);
		$out .= "</a>";
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
function sp_PostIndexUserStatus($args = '', $onlineLabel = '', $offlineLabel = '') {
	if (SP()->forum->view->thisPostUser->guest) return;

	$defs = array('tagId'             => 'spPostIndexUserStatus%ID%',
	              'tagClass'          => 'spPostUserStatus',
	              'iconClass'         => 'spIcon',
	              'onlineLabelClass'  => 'spPostUserStatusOnline',
	              'offlineLabelClass' => 'spPostUserStatusOffline',
	              'onlineIcon'        => 'sp_UserOnlineSmall.png',
	              'offlineIcon'       => 'sp_UserOfflineSmall.png',
	              'echo'              => 1,
	              'get'               => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_PostIndexUserStatus_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId             = esc_attr($tagId);
	$tagClass          = esc_attr($tagClass);
	$iconClass         = esc_attr($iconClass);
	$onlineLabelClass  = esc_attr($onlineLabelClass);
	$offlineLabelClass = esc_attr($offlineLabelClass);
	$onlineIcon        = sanitize_file_name($onlineIcon);
	$offlineIcon       = sanitize_file_name($offlineIcon);
	$onlineLabel       = SP()->displayFilters->title($onlineLabel);
	$offlineLabel      = SP()->displayFilters->title($offlineLabel);
	$echo              = (int) $echo;
	$get               = (int) $get;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisPost->post_id, $tagId);

	$spMemberOpts = SP()->options->get('sfmemberopts');
	$icon         = '';
	if ((SP()->user->thisUser->admin || (!$spMemberOpts['sfhidestatus'] || (!isset(SP()->forum->view->thisPostUser->hidestatus) || !SP()->forum->view->thisPostUser->hidestatus))) && sp_is_online(SP()->forum->view->thisPostUser->ID)) {
		if (!empty($onlineIcon)) $icon = SP()->theme->paint_icon($iconClass, SPTHEMEICONSURL, sanitize_file_name($onlineIcon));
		$label      = $onlineLabel;
		$labelClass = $onlineLabelClass;
		$status     = true;
	} else {
		if (!empty($offlineIcon)) $icon = SP()->theme->paint_icon($iconClass, SPTHEMEICONSURL, sanitize_file_name($offlineIcon));
		$label      = $offlineLabel;
		$labelClass = $offlineLabelClass;
		$status     = false;
	}

	if ($get) return $status;

	$out = "<div id='$tagId' class='$tagClass'><span class='$labelClass'>";
	if (!empty($icon)) $out .= $icon;
	$out .= $label;
	$out .= "</span></div>";

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
function sp_PostIndexUserLocation($args = '', $label = '') {
	if (SP()->forum->view->thisPostUser->guest) return;
	if (empty(SP()->forum->view->thisPostUser->location)) return;

	$defs = array('tagId'    => 'spPostIndexUserLocatin%ID%',
	              'tagClass' => 'spPostUserLocation',
	              'echo'     => 1,
	              'get'      => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_PostIndexUserLocation_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId    = esc_attr($tagId);
	$tagClass = esc_attr($tagClass);
	$label    = (!empty($label)) ? SP()->displayFilters->title($label) : '';
	$echo     = (int) $echo;
	$get      = (int) $get;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisPost->post_id, $tagId);

	if ($get) return SP()->forum->view->thisPostUser->posts;

	$out = "<div id='$tagId' class='$tagClass'>";
	$out .= $label.SP()->forum->view->thisPostUser->location;
	$out .= "</div>";
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
function sp_NoPostsInTopicMessage($args = '', $definedMessage = '') {
	$defs = array('tagId'    => 'spNoPostsInTopicMessage',
	              'tagClass' => 'spMessage',
	              'echo'     => 1,
	              'get'      => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_NoPostsInTopicMessage_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId    = esc_attr($tagId);
	$tagClass = esc_attr($tagClass);
	$echo     = (int) $echo;
	$get      = (int) $get;

	if ($get) return $definedMessage;

	$out = "<div id='$tagId' class='$tagClass'>".SP()->displayFilters->title($definedMessage)."</div>";
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
function sp_PostForumToolButton($args = '', $label = '', $toolTip = '') {
	if (SP()->core->forumData['lockdown'] == true && SP()->user->thisUser->admin == false) return;

	$show = false;
	if (SP()->user->thisUser->admin || SP()->user->thisUser->moderator) {
		$show = true;
	} else {
		$edit_days = SP()->options->get('editpostdays');
		$post_date = strtotime(SP()->dateTime->format_date('d', SP()->forum->view->thisPost->post_date));
		$date_diff = floor((time() - $post_date) / (60 * 60 * 24));

		if (SP()->auths->get('view_email', SP()->forum->view->thisTopic->forum_id) ||
			SP()->auths->get('pin_posts', SP()->forum->view->thisTopic->forum_id) ||
			SP()->auths->get('edit_any_post', SP()->forum->view->thisTopic->forum_id) ||
			(SP()->auths->get('edit_own_posts_forever', SP()->forum->view->thisTopic->forum_id) &&
			 SP()->user->thisUser->member &&
			 SP()->forum->view->thisPostUser->ID == SP()->user->thisUser->ID) ||
			(SP()->auths->get('edit_own_posts_forever', SP()->forum->view->thisTopic->forum_id) &&
			 SP()->user->thisUser->guest &&
			 SP()->forum->view->thisPost->guest_email == SP()->user->guest_cookie->email) ||
			(SP()->auths->get('edit_own_posts_reply', SP()->forum->view->thisTopic->forum_id) &&
			 SP()->user->thisUser->member &&
			 SP()->forum->view->thisPostUser->ID == SP()->user->thisUser->ID &&
			 SP()->forum->view->thisPost->last_post) ||
			(SP()->auths->get('edit_own_posts_reply', SP()->forum->view->thisTopic->forum_id) &&
			 SP()->user->thisUser->guest &&
			 SP()->forum->view->thisPost->guest_email == SP()->user->guest_cookie->email &&
			 SP()->forum->view->thisPost->last_post) ||
			(SP()->auths->get('edit_own_posts_for_time', SP()->forum->view->thisTopic->forum_id) &&
			 SP()->user->thisUser->member &&
			 SP()->forum->view->thisPostUser->ID == SP()->user->thisUser->ID &&
			 $date_diff <= $edit_days) ||
			SP()->auths->get('move_posts', SP()->forum->view->thisTopic->forum_id) ||
			SP()->auths->get('reassign_posts', SP()->forum->view->thisTopic->forum_id) ||
			SP()->auths->get('delete_any_post', SP()->forum->view->thisTopic->forum_id) ||
			(SP()->auths->get('delete_own_posts', SP()->forum->view->thisTopic->forum_id) &&
			 SP()->forum->view->thisPostUser->ID == SP()->user->thisUser->ID) ||
			(SP()->auths->get('moderate_posts', SP()->forum->view->thisTopic->forum_id) &&
			 SP()->forum->view->thisPost->post_status != 0)) {
			$show = true;
		}
	}
	$show = apply_filters('sph_forum_tools_topic_show', $show);
	if (!$show) return;

	$defs = array('tagId'          => 'spForumToolButton%ID%',
	              'tagClass'       => 'spToolsButton',
	              'icon'           => 'sp_ForumTools.png',
	              'iconClass'      => 'spIcon',
	              'hide'           => 1,
	              'containerClass' => 'spTopicPostSection');
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_PostForumToolButton_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId          = esc_attr($tagId);
	$tagClass       = esc_attr($tagClass);
	$icon           = sanitize_file_name($icon);
	$iconClass      = esc_attr($iconClass);
	$containerClass = esc_attr($containerClass);
	$hide           = (int) $hide;
	$toolTip        = esc_attr($toolTip);
	$label          = SP()->displayFilters->title($label);

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisPost->post_id, $tagId);

	$addStyle = '';
	if ($hide) $addStyle = " style='display: none;' ";

	$last  = (SP()->forum->view->thisPost->last_post) ? 1 : 0;
	$site  = wp_nonce_url(SPAJAXURL."spForumPostTools&amp;targetaction=posttools&amp;post=".SP()->forum->view->thisPost->post_id."&amp;page=".SP()->forum->view->thisTopic->display_page."&amp;postnum=".SP()->forum->view->thisPost->post_index."&amp;name=".urlencode(SP()->forum->view->thisPostUser->display_name)."&amp;forum=".SP()->forum->view->thisTopic->forum_id."&amp;last=$last", 'spForumToolsMenu');
	$title = esc_attr(SP()->primitives->front_text('Forum Tools'));
	$out   = "<a class='$tagClass spForumPostTools' id='$tagId' title='$toolTip' rel='nofollow' $addStyle data-site='$site' data-label='$title' data-width='650' data-height='0' data-align='0'>";

	if (!empty($icon)) $out .= SP()->theme->paint_icon($iconClass, SPTHEMEICONSURL, $icon);
	if (!empty($label)) $out .= $label;
	$out .= "</a>";
	$out = apply_filters('sph_PostForumToolButton', $out, $a);

	echo $out;

	# Add script to hover admin buttons - just once
	if (SP()->forum->view->thisTopic->tools_flag && $hide) {
		?>
       <script>
			(function(spj, $, undefined) {
				spj.tool = {
					toolclass: '.<?php echo($containerClass); ?>'
				};
			}(window.spj = window.spj || {}, jQuery));
        </script>
		<?php
		add_action('wp_footer', 'spjs_AddPostToolsHover');
		SP()->forum->view->thisTopic->tools_flag = false;
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
function sp_UsersAlsoViewing($args = '', $messageLabel = '') {
	$defs = array('tagClass'       => 'spAlsoViewingContainer',
	              'userHolder'     => 'spBrowsingUserHolder',
	              'browsingClass'  => 'spBrowsingTopic',
	              'messageClass'   => 'spBrowsingMessage',
	              'avatarClass'    => 'spAvatar',
	              'avatarSize'     => 30,
	              'includeAdmins'  => 1,
	              'includeMods'    => 1,
	              'includeMembers' => 0,
	              'displayToAll'   => 0,
	              'echo'           => 1,
	              'get'            => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_UsersAlsoViewing_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagClass       = esc_attr($tagClass);
	$browsingClass  = esc_attr($browsingClass);
	$messageClass   = esc_attr($messageClass);
	$avatarClass    = esc_attr($avatarClass);
	$avatarSize     = (int) $avatarSize;
	$includeAdmins  = (int) $includeAdmins;
	$includeMods    = (int) $includeMods;
	$includeMembers = (int) $includeMembers;
	$displayToAll   = (int) $displayToAll;
	$echo           = (int) $echo;
	$get            = (int) $get;

	# get online user data
	$members = sp_get_members_online();
	if ($get) {
		return $members;
	}

	$out  = '';
	$tout = '';

	# get member info to check against members browsing topic
	if ($members) {
		foreach ($members as $user) {
			if (SP()->user->thisUser->ID != $user->trackuserid) {
				if (!empty(SP()->rewrites->pageData['pageview'])) {
					if (SP()->rewrites->pageData['pageview'] == 'topic' && $user->topic_id == SP()->rewrites->pageData['topicid']) {

						# check to see if admin, mod, or user
						if (($displayToAll) || (SP()->user->thisUser->admin) && ($includeAdmins) && (SP()->auths->forum_admin($user->trackuserid)) || ($displayToAll) || (SP()->user->thisUser->admin) && ($includeMods) && (SP()->auths->forum_mod($user->trackuserid)) || ($displayToAll) || (SP()->user->thisUser->admin) && ($includeMembers) && (!SP()->auths->forum_mod($user->trackuserid != 0))) {
							$tout .= "<div class='$userHolder'>";
							$tout .= sp_UserAvatar("tagClass=$avatarClass&size=$avatarSize&link=none&context=user&echo=0", $user->trackuserid);
							$tout .= "<span class='$browsingClass'>";
							$tout .= SP()->user->name_display($user->trackuserid, $user->display_name);
							$tout .= "</span>";
							$tout .= "<br><span> $messageLabel</span>";
							$tout .= "</div>";
						}
					}
				}
			}
		}
	}

	if (!empty($tout)) {
		$out = "<div class='$tagClass'>";
		$out .= $tout;

		$out .= "</div>";
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
	SP()->rewrites->pageData['hiddeneditor'] = $addPostForm['hide'];

	# Are we editing a current post?
	if (SP()->forum->view->thisTopic->editmode) {
		# Go into edit mode
		$out = '<a id="spEditFormAnchor"></a>'."";
		$out .= sp_edit_post($editPostForm, SP()->forum->view->thisTopic->editpost_id, SP()->forum->view->thisTopic->editpost_content);
		echo $out;

		# inline js to open post edit form
		add_action('wp_footer', 'spjs_OpenPostEditForm');
	} else {
		if (!SP()->forum->view->thisTopic->topic_status && !SP()->core->forumData['lockdown']) $allowed = true;
		$allowed = (SP()->forum->view->thisTopic->reply_own_topics && SP()->forum->view->thisTopic->topic_starter == SP()->user->thisUser->ID);
		if (SP()->forum->view->thisTopic->reply_topics) $allowed = true;
		# New post form
		if ($allowed) {
			$out = '<a id="spEditFormAnchor"></a>'."";
			$out .= sp_add_post($addPostForm);
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
    <script>
		(function(spj, $, undefined) {
			$(document).ready(function () {
				spj.openEditor('spPostForm', 'edit');
			});
		}(window.spj = window.spj || {}, jQuery));
    </script>
	<?php
}

# --------------------------------------------------------------------------------------
# inline adds hover show event to admin tools button if hidden
# --------------------------------------------------------------------------------------
function spjs_AddPostToolsHover() {
	# on mobile devices just show forum tools. otherwise, show on hover over row
	if (SP()->core->mobile) {
		?>
        <script>
			(function(spj, $, undefined) {
				$(document).ready(function () {
					$('.spToolsButton').css('left', 0);
					$('.spToolsButton').show();
				});
			}(window.spj = window.spj || {}, jQuery));
        </script>
		<?php
	} else {
		?>
        <script>
			(function(spj, $, undefined) {
				$(document).ready(function () {
					$(spj.tool.toolclass).hover(function () {
						$('.spToolsButton', this).css('left', 0);
						$('.spToolsButton', this).delay(400).slideDown('normal');
					}, function () {
						$('.spToolsButton', this).stop(true, true).delay(1200).slideUp('normal');
					});
				});
			}(window.spj = window.spj || {}, jQuery));
        </script>
		<?php
	}
}
