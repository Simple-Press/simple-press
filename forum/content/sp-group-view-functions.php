<?php
/*
Simple:Press
Template Function Handler
$LastChangedDate: 2018-08-15 07:59:04 -0500 (Wed, 15 Aug 2018) $
$Rev: 15704 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# --------------------------------------------------------------------------------------
#
#	sp_GroupHeaderIcon()
#	Display Group Icon
#	Scope:	Group Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_GroupHeaderIcon($args = '') {
	$defs = array('tagId'    => 'spGroupHeaderIcon%ID%',
	              'tagClass' => 'spHeaderIcon',
	              'icon'     => 'sp_GroupIcon.png',
	              'echo'     => 1,
	              'get'      => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_GroupHeaderIcon_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId    = esc_attr($tagId);
	$tagClass = esc_attr($tagClass);
	$echo     = (int) $echo;
	$get      = (int) $get;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisGroup->group_id, $tagId);

	$group_icon = spa_get_saved_icon( SP()->forum->view->thisGroup->group_icon );
	
	# Check if a custom icon
	if ( !empty( $group_icon['icon'] ) ) {
		
		$icon = $group_icon['icon'];

		if( 'file' === $group_icon['type'] ) {
			$icon = SP()->theme->paint_custom_icon($tagClass, SPCUSTOMURL. $icon );
		} else {
			$icon = SP()->theme->sp_paint_iconset_icon( $group_icon, $tagClass );
		}
	} else {
		$icon = SP()->theme->paint_icon($tagClass, SPTHEMEICONSURL, sanitize_file_name($icon));
	}
	
	if ($get) return $icon;

	$out = $icon;
	$out = apply_filters('sph_GroupHeaderIcon', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_GroupHeaderName()
#	Display Group Name/Title in Header
#	Scope:	Group Loop
#	Version: 5.0
#
#	Changelog:
#	5.2.3 - 'toggleTagId' argument added
#			'collapse' argument added
#
# --------------------------------------------------------------------------------------
function sp_GroupHeaderName($args = '') {
	$defs = array('tagId'       => 'spGroupHeaderName%ID%',
	              'tagClass'    => 'spHeaderName',
	              'toggleTagId' => 'spGroupOpenClose%ID%',
	              'collapse'    => 1,
	              'truncate'    => 0,
	              'echo'        => 1,
	              'get'         => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_GroupHeaderName_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId       = esc_attr($tagId);
	$tagClass    = esc_attr($tagClass);
	$toggleTagId = esc_attr($toggleTagId);
	$collapse    = (int) $collapse;
	$truncate    = (int) $truncate;
	$echo        = (int) $echo;
	$get         = (int) $get;

	$tagId       = str_ireplace('%ID%', SP()->forum->view->thisGroup->group_id, $tagId);
	$toggleTagId = '#'.str_ireplace('%ID%', SP()->forum->view->thisGroup->group_id, $toggleTagId);

	if ($get) return SP()->primitives->truncate_name(SP()->forum->view->thisGroup->group_name, $truncate);

	$out = '';
	if (!empty(SP()->forum->view->thisGroup->group_name)) {
		$out .= "<div id='$tagId' class='$tagClass spGroupHeaderOpen' data-id='$toggleTagId' data-collapse='$collapse'";
		if ($collapse) $out .= " style='cursor: pointer;'";
		$out .= ">".SP()->primitives->truncate_name(SP()->forum->view->thisGroup->group_name, $truncate)."</div>";
	}

	$out = apply_filters('sph_GroupHeaderName', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_GroupHeaderDescription()
#	Display Group Description in Header
#	Scope:	Group Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_GroupHeaderDescription($args = '') {
	$defs = array('tagId'    => 'spGroupHeaderDescription%ID%',
	              'tagClass' => 'spHeaderDescription',
	              'echo'     => 1,
	              'get'      => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_GroupHeaderDescription_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId    = esc_attr($tagId);
	$tagClass = esc_attr($tagClass);
	$echo     = (int) $echo;
	$get      = (int) $get;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisGroup->group_id, $tagId);

	if ($get) return SP()->forum->view->thisGroup->group_desc;

	$out = (empty(SP()->forum->view->thisGroup->group_desc)) ? '' : "<div id='$tagId' class='$tagClass'>".SP()->forum->view->thisGroup->group_desc."</div>";
	$out = apply_filters('sph_GroupHeaderDescription', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_GroupOpenClose()
#	Display Open and Close of forum listing
#	Scope:	Group Loop
#	Version: 5.1
#
#	default values= 'open', 'closed'
#
# --------------------------------------------------------------------------------------
function sp_GroupOpenClose($args = '', $toolTipOpen = '', $toolTipClose = '') {
	$defs = array('tagId'     => 'spGroupOpenClose%ID%',
	              'tagClass'  => 'spIcon',
	              'openIcon'  => 'sp_GroupOpen.png',
	              'closeIcon' => 'sp_GroupClose.png',
	              'default'   => 'open',
	              'echo'      => 1);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_GroupOpenClose_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId        = esc_attr($tagId);
	$tagClass     = esc_attr($tagClass);
	$openIcon     = SP()->theme->paint_file_icon(SPTHEMEICONSURL, sanitize_file_name($openIcon));
	$closeIcon    = SP()->theme->paint_file_icon(SPTHEMEICONSURL, sanitize_file_name($closeIcon));
	$toolTipOpen  = esc_attr($toolTipOpen);
	$toolTipClose = esc_attr($toolTipClose);
	$default      = esc_attr($default);
	$echo         = (int) $echo;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisGroup->group_id, $tagId);
	$div   = 'groupViewForums'.SP()->forum->view->thisGroup->group_id;

	if (isset($_COOKIE[$div])) $default = $_COOKIE[$div];

	($default == 'open') ? $icon = $closeIcon : $icon = $openIcon;
	($default == 'open') ? $tooltip = $toolTipClose : $tooltip = $toolTipOpen;

	if ($default == 'closed') {
		echo '<style>#'.$div.' {display:none;}</style>';
	}

	$out = "<span id='$tagId' class='spOpenCloseGroup' data-target='$div' data-tag='$tagId' data-tclass='$tagClass' data-open='$openIcon' data-close='$closeIcon' data-toolopen='$toolTipOpen' data-toolclose='$toolTipClose'><img class='$tagClass' title='$tooltip' src='$icon' alt='' /></span>";
	$out = apply_filters('sph_GroupOpenClose', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_GroupHeaderMessage()
#	Display Special Group Message in Header
#	Scope:	Group Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_GroupHeaderMessage($args = '') {
	$defs = array('tagId'    => 'spGroupHeaderMessage%ID%',
	              'tagClass' => 'spHeaderMessage',
				  'fontClass'=> '',
	              'echo'     => 1,
	              'get'      => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_GroupHeaderMessage_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId    = esc_attr($tagId);
	$tagClass = esc_attr($tagClass);
	$fontClass= esc_attr($fontClass);
	$echo     = (int) $echo;
	$get      = (int) $get;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisGroup->group_id, $tagId);

	if ($get) return SP()->forum->view->thisGroup->group_message;

	$out = (empty(SP()->forum->view->thisGroup->group_message)) ? '' : "<div id='$tagId' class='$tagClass'><span class='$fontClass'>".SP()->forum->view->thisGroup->group_message."</span></div>";
	$out = apply_filters('sph_GroupHeaderMessage', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_GroupHeaderRSSButton()
#	Display Group Level RSS Button
#	Scope:	Group Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_GroupHeaderRSSButton($args = '', $label = '', $toolTip = '') {
	if (!SP()->forum->view->thisGroup->group_rss_active) return;

	$defs = array('tagId'     => 'spGroupHeaderRSSButton%ID%',
	              'tagClass'  => 'spLink',
	              'icon'      => 'sp_Feed.png',
	              'iconClass' => 'spIcon',
	              'echo'      => 1,
	              'get'       => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_GroupHeaderRSSButton_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId     = esc_attr($tagId);
	$tagClass  = esc_attr($tagClass);
	$icon      = sanitize_file_name($icon);
	$iconClass = esc_attr($iconClass);
	$toolTip   = esc_attr($toolTip);
	$echo      = (int) $echo;
	$get       = (int) $get;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisGroup->group_id, $tagId);

	# Get or construct rss url
	if (empty(SP()->forum->view->thisGroup->rss)) {
		$rssOpt = SP()->options->get('sfrss');
		if ($rssOpt['sfrssfeedkey'] && isset(SP()->user->thisUser->feedkey)) {
			$rssUrl = SP()->spPermalinks->get_query_url(trailingslashit(SP()->spPermalinks->build_url('', '', 0, 0, 0, 1)).user_trailingslashit(SP()->user->thisUser->feedkey)).'group='.SP()->forum->view->thisGroup->group_id;
		} else {
			$sym    = (strpos(SP()->spPermalinks->get_url(), '?')) ? '&' : '?';
			$rssUrl = trailingslashit(SP()->spPermalinks->build_url('', '', 0, 0, 0, 1)).SP()->spPermalinks->get_query_char()."group=".SP()->forum->view->thisGroup->group_id;
		}
	} else {
		$rssUrl = SP()->forum->view->thisGroup->rss;
	}

	if ($get) return $rssUrl;

	$out = "<a class='$tagClass' id='$tagId' title='$toolTip' rel='nofollow' href='$rssUrl'>";
	if (!empty($icon)) $out .= SP()->theme->paint_icon($iconClass, SPTHEMEICONSURL, $icon);
	if (!empty($label)) $out .= SP()->displayFilters->title($label);
	$out .= "</a>";
	$out = apply_filters('sph_GroupHeaderRSSButton', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_NoGroupMessage()
#	Display Message when no Groups can be displayed
#	Scope:	Group Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_NoGroupMessage($args = '', $deniedMessage = '', $definedMessage = '') {
	$defs = array('tagId'    => 'spNoGroupMessage',
	              'tagClass' => 'spMessage',
	              'echo'     => 1,
	              'get'      => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_NoGroupMessage_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId    = esc_attr($tagId);
	$tagClass = esc_attr($tagClass);
	$echo     = (int) $echo;
	$get      = (int) $get;

	# is Access denied to all groups
	if (SP()->forum->view->groups->groupViewStatus == 'no access') {
		$m = SP()->displayFilters->title($deniedMessage);
	} elseif (SP()->forum->view->groups->groupViewStatus == 'no data') {
		$m = SP()->displayFilters->title($definedMessage);
	} else {
		return;
	}

	if ($get) return $m;

	$out = "<div id='$tagId' class='$tagClass'>".$m."</div>";
	$out = apply_filters('sph_NoGroupMessage', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# ======================================================================================
#
# GROUP VIEW
# Forum Loop Functions
#
# ======================================================================================

# --------------------------------------------------------------------------------------
#
#	sp_ForumIndexIcon()
#	Display Forum Icon
#	Scope:	Forum sub Loop
#	Version: 5.0
#   Version: 5.5 added default locked forum icon
#
# --------------------------------------------------------------------------------------
function sp_ForumIndexIcon($args = '') {
	$defs = array('tagId'      => 'spForumIndexIcon%ID%',
	              'tagClass'   => 'spRowIcon',
	              'icon'       => 'sp_ForumIcon.png',
	              'iconUnread' => 'sp_ForumIconPosts.png',
	              'iconLocked' => 'sp_ForumIconLocked.png',
	              'echo'       => 1,
	              'get'        => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_ForumIndexIcon_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId    = esc_attr($tagId);
	$tagClass = esc_attr($tagClass);
	$echo     = (int) $echo;
	$get      = (int) $get;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisForum->forum_id, $tagId);

	$fIconType = 'file';
	
	# Check if a custom icon
	$path = SPTHEMEICONSDIR;
	$url  = SPTHEMEICONSURL;
	if (SP()->forum->view->thisForum->forum_status) {
		$fIcon = sanitize_file_name($iconLocked);
		$forum_icon = spa_get_saved_icon( SP()->forum->view->thisForum->forum_icon_locked );
		
		if ( !empty( $forum_icon['icon'] ) ) {
			
			$fIconType = $forum_icon['type'];
			$fIcon = 'file' === $forum_icon['type'] ? $forum_icon['icon'] : $forum_icon;
			$path  = SPCUSTOMDIR;
			$url   = SPCUSTOMURL;
		}
	} elseif (SP()->forum->view->thisForum->unread) {
		$fIcon = sanitize_file_name($iconUnread);
		$forum_icon = spa_get_saved_icon( SP()->forum->view->thisForum->forum_icon_new );
		
		if ( !empty( $forum_icon['icon'] ) ) {
			
			$fIconType = $forum_icon['type'];
			$fIcon = 'file' === $forum_icon['type'] ? $forum_icon['icon'] : $forum_icon;
			$path  = SPCUSTOMDIR;
			$url   = SPCUSTOMURL;
		}
	} else {
		$fIcon = sanitize_file_name($icon);
		$forum_icon = spa_get_saved_icon( SP()->forum->view->thisForum->forum_icon );
		
		if ( !empty( $forum_icon['icon'] ) ) {
			
			$fIconType = $forum_icon['type'];
			$fIcon = 'file' === $forum_icon['type'] ? $forum_icon['icon'] : $forum_icon;
			$path  = SPCUSTOMDIR;
			$url   = SPCUSTOMURL;
		}
	}
	
	if( 'file' === $fIconType ) {
		
		if (!file_exists($path.$fIcon)) {
			$fIcon = SP()->theme->paint_icon($tagClass, SPTHEMEICONSURL, sanitize_file_name($fIcon));
		} else {
			$fIcon = SP()->theme->paint_custom_icon($tagClass, $url.$fIcon);
		}
	} else {
		$fIcon = SP()->theme->sp_paint_iconset_icon( $fIcon, $tagClass );
	}

	
	if ($get) return $fIcon;

	$out = $fIcon;
	$out = apply_filters('sph_ForumIndexIcon', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_ForumIndexLink()
#	Display a LINK to the forum
#	Scope:	Forum sub Loop
#	Version: 6.5
#
# --------------------------------------------------------------------------------------
function sp_ForumIndexLink($args = '', $toolTip = '') {
	$defs = array('tagId'    => 'spForumIndexLink%ID%',
	              'tagClass' => 'spRowLink',
				  'label'	 => __sp('View Forum'),
	              'truncate' => 0,
	              'echo'     => 1,
	              'get'      => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_ForumIndexName_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId    = esc_attr($tagId);
	$tagClass = esc_attr($tagClass);
	$label = esc_attr($label);
	$truncate = (int) $truncate;
	$toolTip  = esc_attr($toolTip);
	$echo     = (int) $echo;
	$get      = (int) $get;

	$tagId   = str_ireplace('%ID%', SP()->forum->view->thisForum->forum_id, $tagId);
	$toolTip = str_ireplace('%NAME%', htmlspecialchars(SP()->forum->view->thisForum->forum_name, ENT_QUOTES, SPCHARSET), $toolTip);

	if ($get) return SP()->primitives->truncate_name(SP()->forum->view->thisForum->forum_name, $truncate);

	$out = "<a href='".SP()->forum->view->thisForum->forum_permalink."' id='$tagId' class='$tagClass' title='$toolTip'>".$label."</a>";
	$out = apply_filters('sph_ForumIndexLink', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_ForumIndexName()
#	Display Forum Name/Title in Header
#	Scope:	Forum sub Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_ForumIndexName($args = '', $toolTip = '') {
	$defs = array('tagId'    => 'spForumIndexName%ID%',
	              'tagClass' => 'spRowName',
	              'truncate' => 0,
	              'echo'     => 1,
	              'get'      => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_ForumIndexName_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId    = esc_attr($tagId);
	$tagClass = esc_attr($tagClass);
	$truncate = (int) $truncate;
	$toolTip  = esc_attr($toolTip);
	$echo     = (int) $echo;
	$get      = (int) $get;

	$tagId   = str_ireplace('%ID%', SP()->forum->view->thisForum->forum_id, $tagId);
	$toolTip = str_ireplace('%NAME%', htmlspecialchars(SP()->forum->view->thisForum->forum_name, ENT_QUOTES, SPCHARSET), $toolTip);

	if ($get) return SP()->primitives->truncate_name(SP()->forum->view->thisForum->forum_name, $truncate);

	$out = "<a href='".SP()->forum->view->thisForum->forum_permalink."' id='$tagId' class='$tagClass' title='$toolTip'>".SP()->primitives->truncate_name(SP()->forum->view->thisForum->forum_name, $truncate)."</a>";
	$out = apply_filters('sph_ForumIndexName', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_ForumIndexDescription()
#	Display Forum Description in Header
#	Scope:	Forum sub Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_ForumIndexDescription($args = '') {
	$defs = array('tagId'    => 'spForumIndexDescription%ID%',
	              'tagClass' => 'spRowDescription',
	              'echo'     => 1,
	              'get'      => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_ForumDescription_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId    = esc_attr($tagId);
	$tagClass = esc_attr($tagClass);
	$echo     = (int) $echo;
	$get      = (int) $get;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisForum->forum_id, $tagId);

	if ($get) return SP()->forum->view->thisForum->forum_desc;

	$out = (empty(SP()->forum->view->thisForum->forum_desc)) ? '' : "<div id='$tagId' class='$tagClass'>".SP()->forum->view->thisForum->forum_desc."</div>";
	$out = apply_filters('sph_ForumIndexDescription', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_ForumIndexPageLinks()
#	Display Forum 'in row' page links
#	Scope:	Forum sub Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_ForumIndexPageLinks($args = '', $toolTip = '') {
	$topics_per_page = SP()->core->forumData['display']['topics']['perpage'];
	if ($topics_per_page >= SP()->forum->view->thisForum->topic_count) return '';

	$defs = array('tagId'         => 'spForumIndexPageLinks%ID%',
	              'tagClass'      => 'spInRowPageLinks',
	              'icon'          => 'sp_ArrowRightSmall.png',
	              'iconClass'     => 'spIconSmall',
	              'pageLinkClass' => 'spInRowForumPageLink',
	              'showLinks'     => 4,
	              'echo'          => 1,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_ForumIndexPageLinks_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId         = esc_attr($tagId);
	$tagClass      = esc_attr($tagClass);
	$icon          = sanitize_file_name($icon);
	$iconClass     = esc_attr($iconClass);
	$pageLinkClass = esc_attr($pageLinkClass);
	$showLinks     = (int) $showLinks;
	$toolTip       = esc_attr($toolTip);
	$echo          = (int) $echo;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisForum->forum_id, $tagId);

	$out         = "<div id='$tagId' class='$tagClass'>";
	$total_pages = (SP()->forum->view->thisForum->topic_count / $topics_per_page);
	if (!is_int($total_pages)) $total_pages = intval($total_pages) + 1;
	($total_pages > $showLinks ? $max_count = $showLinks : $max_count = $total_pages);
	for ($x = 1; $x <= $max_count; $x++) {
		$out .= "<a class='$pageLinkClass' href='".SP()->spPermalinks->build_url(SP()->forum->view->thisForum->forum_slug, '', $x, 0)."' title='".str_ireplace('%PAGE%', $x, $toolTip)."'>$x</a>";
	}
	if ($total_pages > $showLinks) {
		if (!empty($icon)) $out .= SP()->theme->paint_icon($iconClass, SPTHEMEICONSURL, $icon);
		$out .= "<a class='$pageLinkClass' href='".SP()->spPermalinks->build_url(SP()->forum->view->thisForum->forum_slug, '', $total_pages, 0)."' title='".str_ireplace('%PAGE%', $total_pages, $toolTip)."'>$total_pages</a>";
	}
	$out .= "</div>";

	$out = apply_filters('sph_ForumIndexPageLinks', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_ForumIndexStatusIcons()
#	Display Forum Status (Locked/New Post/Blank)
#	Scope:	Forum sub Loop
#	Version: 5.0
#
#	Changelog
#	5.4.2:	Added property and status 'iconDenied'
#			Added propery 'showDenied'
#			Added tooltip parameter toolTipDeneied
#
# --------------------------------------------------------------------------------------
function sp_ForumIndexStatusIcons($args = '', $toolTipLock = '', $toolTipPost = '', $toolTipAdd = '', $toolTipDenied = '') {
	$defs = array('tagId'        => 'spForumIndexStatus%ID%',
	              'tagClass'     => 'spStatusIcon',
	              'showLock'     => 1,
	              'showNewPost'  => 1,
	              'showAddTopic' => 1,
	              'showDenied'   => 1,
	              'iconLock'     => 'sp_ForumStatusLock.png',
	              'iconPost'     => 'sp_ForumStatusPost.png',
	              'iconAdd'      => 'sp_ForumStatusAdd.png',
	              'iconDenied'   => 'sp_WriteDenied.png',
	              'first'        => 0,
	              'echo'         => 1,
	              'get'          => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_ForumIndexStatusIcons_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId         = esc_attr($tagId);
	$tagClass      = esc_attr($tagClass);
	$showLock      = (int) $showLock;
	$showNewPost   = (int) $showNewPost;
	$showAddTopic  = (int) $showAddTopic;
	$showDenied    = (int) $showDenied;
	$toolTipPost   = esc_attr($toolTipPost);
	$toolTipLock   = esc_attr($toolTipLock);
	$toolTipAdd    = esc_attr($toolTipAdd);
	$toolTipDenied = esc_attr($toolTipDenied);
	$first         = (int) $first;
	$echo          = (int) $echo;
	$get           = (int) $get;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisForum->forum_id, $tagId);

	if ($get) return SP()->forum->view->thisForum->forum_status;

	$out = "<div id='$tagId' class='$tagClass'>";

	# Dislay if global lock down or forum locked
	if ($showLock && !empty($iconLock)) {
		if (SP()->core->forumData['lockdown'] || SP()->forum->view->thisForum->forum_status) {
			$out .= SP()->theme->paint_icon('', SPTHEMEICONSURL, sanitize_file_name($iconLock), $toolTipLock);
		}
	}

	# New Post Popup
	if ($showNewPost && !empty($iconPost)) {
		if (SP()->forum->view->thisForum->unread) {
			$toolTipPost = str_ireplace('%COUNT%', SP()->forum->view->thisForum->unread, $toolTipPost);
			$site        = wp_nonce_url(SPAJAXURL."spUnreadPostsPopup&amp;targetaction=forum&amp;id=".SP()->forum->view->thisForum->forum_id."&amp;first=$first", 'spUnreadPostsPopup');
			$linkId      = 'spNewPostPopup'.SP()->forum->view->thisForum->forum_id;
			$out .= "<a rel='nofollow' id='$linkId' class='spUnreadPostsPopup' data-popup='1' data-site='$site' data-label='$toolTipPost' data-width='600' data-height='0' data-align='0'>";
			$out .= SP()->theme->paint_icon('', SPTHEMEICONSURL, sanitize_file_name($iconPost), $toolTipPost);
			$out .= "</a>";
		}
	}

	# add new topic icon
	if ($showAddTopic && !empty($iconAdd)) {
		if (SP()->auths->get('start_topics', SP()->forum->view->thisForum->forum_id) && ((!SP()->forum->view->thisForum->forum_status && !SP()->core->forumData['lockdown']) || SP()->user->thisUser->admin)) {
			$url = SP()->spPermalinks->build_url(SP()->forum->view->thisForum->forum_slug, '', 1, 0).SP()->spPermalinks->get_query_char().'new=topic';
			$out .= "<a href='$url' title='$toolTipAdd'>";
			$out .= SP()->theme->paint_icon('', SPTHEMEICONSURL, sanitize_file_name($iconAdd));
			$out .= "</a>";
		}
	}

	# Display if user not allowed to start topics
	if ($showDenied && !SP()->forum->view->thisForum->start_topics && !empty($toolTipDenied)) {
		$out .= SP()->theme->paint_icon('', SPTHEMEICONSURL, sanitize_file_name($iconDenied), $toolTipDenied);
	}

	$out = apply_filters('sph_ForumIndexStatusIconsLast', $out, $a);

	$out .= "</div>";

	$out = apply_filters('sph_ForumIndexStatusIcons', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_ForumIndexLockIcon()
#	Display Forum Status (Locked)
#	Scope:	Forum sub Loop
#	Version: 5.1
#
#	Changelog
#	5.2.3	Added 'statusClass' to icons with no action
#
# --------------------------------------------------------------------------------------
function sp_ForumIndexLockIcon($args = '', $toolTip = '') {
	$defs = array('tagId'       => 'spForumIndexLockIcon%ID%',
	              'tagClass'    => 'spIcon',
	              'statusClass' => 'spIconNoAction',
	              'icon'        => 'sp_ForumStatusLock.png',
	              'echo'        => 1,
	              'get'         => 0);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_ForumIndexLockIcon_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId       = esc_attr($tagId);
	$tagClass    = esc_attr($tagClass);
	$statusClass = esc_attr($statusClass);
	$icon        = sanitize_file_name($icon);
	$echo        = (int) $echo;
	$get         = (int) $get;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisForum->forum_id, $tagId);

	if ($get) return SP()->forum->view->thisForum->forum_status;
	$out = '';

	if (SP()->core->forumData['lockdown'] || SP()->forum->view->thisForum->forum_status) {
		$out = "<div id='$tagId' class='$tagClass $statusClass' title='$toolTip' >";
		# Dislay if global lock down or forum locked
		if (!empty($icon)) $out .= SP()->theme->paint_icon('', SPTHEMEICONSURL, $icon);
		$out .= "</div>";
		$out = apply_filters('sph_ForumIndexLockIcon', $out, $a);
	}

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_ForumIndexDeniedIcon()
#	Display Forum Status (Write Denied)
#	Scope:	Forum sub Loop
#	Version: 5.4.2
#
#	New for 5.4.2
#
# --------------------------------------------------------------------------------------
function sp_ForumIndexDeniedIcon($args = '', $toolTip = '') {
	$defs = array('tagId'       => 'spForumIndexDeniedIcon%ID%',
	              'tagClass'    => 'spIcon',
	              'statusClass' => 'spIconNoAction',
	              'icon'        => 'sp_WriteDenied.png',
	              'echo'        => 1,
	              'get'         => 0);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_ForumIndexDeniedIcon_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId       = esc_attr($tagId);
	$tagClass    = esc_attr($tagClass);
	$statusClass = esc_attr($statusClass);
	$icon        = sanitize_file_name($icon);
	$echo        = (int) $echo;
	$get         = (int) $get;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisForum->forum_id, $tagId);

	if ($get) return SP()->forum->view->thisForum->start_topics;
	$out = '';

	if (!SP()->forum->view->thisForum->start_topics) {
		$out = "<div id='$tagId' class='$tagClass $statusClass' title='$toolTip' >";
		if (!empty($icon)) $out .= SP()->theme->paint_icon('', SPTHEMEICONSURL, $icon);
		$out .= "</div>";
		$out = apply_filters('sph_ForumIndexDeniedIcon', $out, $a);
	}

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_ForumIndexAddIcon()
#	Display Forum Status (Add Topic)
#	Scope:	Forum sub Loop
#	Version: 5.1
#
# --------------------------------------------------------------------------------------
function sp_ForumIndexAddIcon($args = '', $toolTip = '', $label = '') {
	$defs = array('tagId'    => 'spForumIndexAddIcon%ID%',
	              'tagClass' => 'spIcon',
	              'icon'     => 'sp_ForumStatusAdd.png',
	              'echo'     => 1,
	              'get'      => 0);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_ForumIndexAddIcon_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId    = esc_attr($tagId);
	$tagClass = esc_attr($tagClass);
	$icon     = sanitize_file_name($icon);
	$echo     = (int) $echo;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisForum->forum_id, $tagId);
	$out   = '';

	# add new topic icon
	if (SP()->auths->get('start_topics', SP()->forum->view->thisForum->forum_id) && ((!SP()->forum->view->thisForum->forum_status && !SP()->core->forumData['lockdown']) || SP()->user->thisUser->admin)) {
		$url = SP()->spPermalinks->build_url(SP()->forum->view->thisForum->forum_slug, '', 1, 0).SP()->spPermalinks->get_query_char().'new=topic';
		$out.= "<a id ='$tagId' class='$tagClass' title='$toolTip' href='$url'>";
		if (!empty($icon)) $out .= SP()->theme->paint_icon('', SPTHEMEICONSURL, "$icon");
		if (!empty($label)) $out .= SP()->displayFilters->title($label);
		$out.= "</a>";
		$out = apply_filters('sph_ForumIndexAddIcon', $out, $a);
	}

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_ForumIndexPostsIcon()
#	Display Forum Status (Show Posts)
#	Scope:	Forum sub Loop
#	Version: 5.2
#
# --------------------------------------------------------------------------------------
function sp_ForumIndexPostsIcon($args = '', $toolTip = '') {
	if (!SP()->forum->view->thisForum->unread) return;

	$defs = array('tagId'     => 'spForumIndexPostsIcon%ID%',
	              'tagClass'  => 'spIcon',
	              'icon'      => 'sp_ForumStatusPost.png',
	              'openIcon'  => 'sp_GroupOpen.png',
	              'closeIcon' => 'sp_GroupClose.png',
	              'popup'     => 1,
	              'first'     => 0,
	              'echo'      => 1,
	              'get'       => 0);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_ForumIndexPostsIcon_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId     = esc_attr($tagId);
	$tagClass  = esc_attr($tagClass);
	$popupIcon = SP()->theme->paint_icon('', SPTHEMEICONSURL, sanitize_file_name($icon));
	$openIcon  = SP()->theme->paint_file_icon(SPTHEMEICONSURL, sanitize_file_name($openIcon));
	$closeIcon = SP()->theme->paint_file_icon(SPTHEMEICONSURL, sanitize_file_name($closeIcon));
	$popup     = (int) $popup;
	$first     = (int) $first;
	$echo      = (int) $echo;
	$get       = (int) $get;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisForum->forum_id, $tagId);
	$out   = '';

	# show new posts icon
	if (SP()->forum->view->thisForum->unread) {
		$toolTip = str_ireplace('%COUNT%', SP()->forum->view->thisForum->unread, $toolTip);
		$site    = wp_nonce_url(SPAJAXURL."spUnreadPostsPopup&amp;targetaction=forum&amp;id=".SP()->forum->view->thisForum->forum_id."&amp;popup=$popup&amp;first=$first", 'spUnreadPostsPopup');
		$linkId  = 'spNewPostPopup'.SP()->forum->view->thisForum->forum_id;
		$target  = 'spInlineTopics'.SP()->forum->view->thisForum->forum_id;
		$spinner = SPCOMMONIMAGES.'working.gif';
		if ($popup) {
			$out .= "<a id='$tagId' class='$tagClass spUnreadPostsPopup' title='$toolTip' rel='nofollow' id='$linkId' data-popup='1' data-site='$site' data-label='$toolTip' data-width='600' data-height='0' data-align='0'>";
			$out .= $popupIcon;
		} else {
			$out .= "<a id='$tagId' class='$tagClass spUnreadPostsPopup' title='$toolTip' rel='nofollow' id='$linkId' data-popup='0' data-site='$site' data-target='$target' data-spinner='$spinner' data-id='$tagId' data-open='$openIcon' data-close='$closeIcon'>";
			$out .= "<img src='$openIcon' alt=''>";
		}
		$out .= "</a>";
		$out = apply_filters('sph_ForumIndexPostsIcon', $out, $a);
	}

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_ForumIndexInlinePosts()
#	Display inline dropdopwn posts section (Show Posts)
#	Scope:	Forum sub Loop
#	Version: 5.2
#
# --------------------------------------------------------------------------------------
function sp_ForumIndexInlinePosts() {
	echo "<div class='spInlineTopics' id='spInlineTopics".SP()->forum->view->thisForum->forum_id."' style='display:none;'></div>";
	sp_InsertBreak();
}

# --------------------------------------------------------------------------------------
#
#	sp_ForumIndexPostCount()
#	Display Forum 'in row' total post count
#	Scope:	Forum sub Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_ForumIndexPostCount($args = '', $label = '', $rtlLabel = '', $labelAfter = '', $rtlLabelAfter = '') {
	$defs = array('tagId'       => 'spForumIndexPostCount%ID%',
	              'tagClass'    => 'spInRowCount',
	              'labelClass'  => 'spInRowLabel',
	              'numberClass' => 'spInRowNumber',
	              'includeSubs' => 1,
	              'stack'       => 0,
	              'echo'        => 1,
	              'get'         => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_ForumIndexPostCount_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId       = esc_attr($tagId);
	$tagClass    = esc_attr($tagClass);
	$labelClass  = esc_attr($labelClass);
	$numberClass = esc_attr($numberClass);
	$includeSubs = (int) $includeSubs;
	$stack       = (int) $stack;
	$echo        = (int) $echo;
	$get         = (int) $get;

	$labelAfter = SP()->displayFilters->title($labelAfter);	
	$rtlLabelAfter = SP()->displayFilters->title($rtlLabelAfter);
	
	if ($includeSubs && SP()->forum->view->thisForum->forum_id_sub == 0) $includeSubs = 0;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisForum->forum_id, $tagId);
	($stack ? $att = '<br />' : $att = ' ');

	$data = ($includeSubs ? SP()->forum->view->thisForum->post_count_sub : SP()->forum->view->thisForum->post_count);
	if ($get) return $data;

	if (is_rtl() && $data == 1) $label = $rtlLabel;
	if (is_rtl() && $data == 1) $labelAfter = $rtlLabelAfter;

	$out = "<div id='$tagId' class='$tagClass'>";
	$out .= "<span class='$labelClass'>".SP()->displayFilters->title($label)."$att</span>";
	$out .= "<span class='$numberClass'>$data $labelAfter</span>";
	$out .= "</div>";
	$out = apply_filters('sph_ForumIndexPostCount', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_ForumIndexTopicCount()
#	Display Forum 'in row' total topic count
#	Scope:	Forum sub Loop
#	Version: 5.0
#	Changelog:
#	5.5.1 = $rtlLabel parameter added
#
# --------------------------------------------------------------------------------------
function sp_ForumIndexTopicCount($args = '', $label = '', $rtlLabel = '', $labelAfter = '', $rtlLabelAfter = '') {
	$defs = array('tagId'       => 'spForumIndexTopicCount%ID%',
	              'tagClass'    => 'spInRowCount',
	              'labelClass'  => 'spInRowLabel',
	              'numberClass' => 'spInRowNumber',
	              'includeSubs' => 1,
	              'stack'       => 0,
	              'echo'        => 1,
	              'get'         => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_ForumIndexTopicCount_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId       = esc_attr($tagId);
	$tagClass    = esc_attr($tagClass);
	$labelClass  = esc_attr($labelClass);
	$numberClass = esc_attr($numberClass);
	$includeSubs = (int) $includeSubs;
	$stack       = (int) $stack;
	$echo        = (int) $echo;
	$get         = (int) $get;
	
	$labelAfter = SP()->displayFilters->title($labelAfter);	
	$rtlLabelAfter = SP()->displayFilters->title($rtlLabelAfter);	

	if ($includeSubs && SP()->forum->view->thisForum->forum_id_sub == 0) $includeSubs = 0;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisForum->forum_id, $tagId);
	($stack ? $att = '<br />' : $att = ' ');

	$data = ($includeSubs ? SP()->forum->view->thisForum->topic_count_sub : SP()->forum->view->thisForum->topic_count);
	if ($get) return $data;

	if (is_rtl() && $data == 1) $label = $rtlLabel;
	if (is_rtl() && $data == 1) $labelAfter = $rtlLabelAfter;

	$out = "<div id='$tagId' class='$tagClass'>";
	$out .= "<span class='$labelClass'>".SP()->displayFilters->title($label)."$att</span>";
	$out .= "<span class='$numberClass'>$data $labelAfter</span>";
	$out .= "</div>";
	$out = apply_filters('sph_ForumIndexTopicCount', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_ForumIndexLastPost()
#	Display Forum 'in row' link to the last post made to a topic in this forum
#	Scope:	Forum sub Loop
#	Version: 5.0
#
#	Changelog:
#	5.1.0 - 'Order' argument added
#	5.1.0 - 'ItemBreak' argument added
#	5.2.3 - 'L' Linebreak - added to Order argument
#	5.5.1 - $rtlLabel parameter added
#	6.0.0 - Added 'icon' and 'iconclass' to bring into line with forum view
#
# --------------------------------------------------------------------------------------
function sp_ForumIndexLastPost($args = '', $lastPostLabel = '', $noTopicsLabel = '') {
	$defs = array('tagId'        => 'spForumIndexLastPost%ID%',
	              'tagClass'     => 'spInRowPostLink',
	              'labelClass'   => 'spInRowLabel',
	              'infoClass'    => 'spInRowInfo',
	              'linkClass'    => 'spInRowLastPostLink',
	              'iconClass'    => 'spIcon',
	              'icon'         => 'sp_ArrowRight.png',
	              'includeSubs'  => 1,
	              'tip'          => 1,
	              'order'        => 'UTD',
	              'nicedate'     => 1,
	              'date'         => 0,
	              'time'         => 0,
	              'stackdate'    => 0,
	              'user'         => 1,
	              'truncate'     => 0,
	              'truncateUser' => 0,
	              'itemBreak'    => '<br />',
	              'echo'         => 1,
	              'get'          => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_ForumIndexLastPost_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId        = esc_attr($tagId);
	$tagClass     = esc_attr($tagClass);
	$labelClass   = esc_attr($labelClass);
	$infoClass    = esc_attr($infoClass);
	$linkClass    = esc_attr($linkClass);
	$iconClass    = esc_attr($iconClass);
	$icon         = sanitize_file_name($icon);
	$includeSubs  = (int) $includeSubs;
	$tip          = (int) $tip;
	$order        = esc_attr($order);
	$nicedate     = (int) $nicedate;
	$date         = (int) $date;
	$time         = (int) $time;
	$stackdate    = (int) $stackdate;
	$user         = (int) $user;
	$truncate     = (int) $truncate;
	$truncateUser = (int) $truncateUser;
	$echo         = (int) $echo;
	$get          = (int) $get;

	if ($includeSubs && SP()->forum->view->thisForum->forum_id_sub == 0) $includeSubs = 0;
	$postCount = ($includeSubs ? SP()->forum->view->thisForum->post_count_sub : SP()->forum->view->thisForum->post_count);

	if ($postCount) {
		$tagId   = str_ireplace('%ID%', SP()->forum->view->thisForum->forum_id, $tagId);
		$posttip = ($includeSubs ? SP()->forum->view->thisForum->post_tip_sub : SP()->forum->view->thisForum->post_tip);
		if ($tip && !empty($posttip)) {
			$title = "title='$posttip'";
			$linkClass .= '';
		} else {
			$title = '';
		}

		($stackdate ? $dlb = '<br />' : $dlb = ' - ');

		# user
		$poster = ($includeSubs ? SP()->user->name_display(SP()->forum->view->thisForum->user_id_sub, SP()->primitives->truncate_name(SP()->forum->view->thisForum->display_name_sub, $truncateUser)) : SP()->user->name_display(SP()->forum->view->thisForum->user_id, SP()->primitives->truncate_name(SP()->forum->view->thisForum->display_name, $truncateUser)));
		if (empty($poster)) $poster = ($includeSubs ? SP()->primitives->truncate_name(SP()->forum->view->thisForum->guest_name_sub, $truncateUser) : SP()->primitives->truncate_name(SP()->forum->view->thisForum->guest_name, $truncateUser));

		# other items
		$permalink = ($includeSubs ? SP()->forum->view->thisForum->post_permalink_sub : SP()->forum->view->thisForum->post_permalink);
		$topicname = ($includeSubs ? SP()->primitives->truncate_name(SP()->forum->view->thisForum->topic_name_sub, $truncate) : SP()->primitives->truncate_name(SP()->forum->view->thisForum->topic_name, $truncate));
		$postdate  = ($includeSubs ? SP()->forum->view->thisForum->post_date_sub : SP()->forum->view->thisForum->post_date);

		if ($get) {
			$getData             = new stdClass();
			$getData->permalink  = $permalink;
			$getData->topic_name = $topicname;
			$getData->post_date  = $postdate;
			$getData->user       = $poster;

			return $getData;
		}

		$U = $poster;
		$T = "<a class='$linkClass' $title href='$permalink'>$topicname</a>";
		if ($nicedate) {
			$D = SP()->dateTime->nice_date($postdate);
		} else {
			if ($date) {
				$D = SP()->dateTime->format_date('d', $postdate);
				if ($time) $D .= $dlb.SP()->dateTime->format_date('t', $postdate);
			}
		}
	} else {
		if ($get) {
			$getData = new stdClass();

			return $getData;
		}
	}

	$out = "<div id='$tagId' class='$tagClass'>";
	if ($postCount) {
		$out .= "<span class='$labelClass'>".SP()->displayFilters->title($lastPostLabel)." ";
		$out .= "<a class='$linkClass' $title href='$permalink'>";
		if (!empty($icon)) $out .= SP()->theme->paint_icon($iconClass, SPTHEMEICONSURL, $icon);
		$out .= "</a>";

		for ($x = 0; $x < strlen($order); $x++) {
			$i = substr($order, $x, 1);
			switch ($i) {
				case 'U':
					if ($user) {
						if ($x != 0) $out .= "<span class='$labelClass'>";
						$out .= $U."</span>";
					}
					if ($x != (strlen($order) - 1)) {
						if (substr($order, $x + 1, 1) != 'L') {
							$out .= "<span class='$labelClass'>$itemBreak</span>";
						}
					}
					break;
				case 'T':
					if ($x == 0) $out .= $itemBreak."</span>";
					$out .= "<span class='$linkClass'>";

					$out .= $T."</span>";
					if ($x != (strlen($order) - 1)) {
						if (substr($order, $x + 1, 1) != 'L') {
							$out .= "<span class='$labelClass'>$itemBreak</span>";
						}
					}
					break;
				case 'D':
					if ($x != 0) $out .= "<span class='$labelClass'>";
					$out .= $D."</span>";
					if ($x != (strlen($order) - 1)) {
						if (substr($order, $x + 1, 1) != 'L') {
							$out .= "<span class='$labelClass'>$itemBreak</span>";
						}
					}
					break;
				case 'L':
					$out .= '<br />';
					break;
			}
		}
	} else {
		$out .= "<span class='$labelClass'>".SP()->displayFilters->title($noTopicsLabel)." ";
		$out .= "</span>";
	}

	$out .= "</div>";
	$out = apply_filters('sph_ForumIndexLastPost', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_ForumIndexModerators()
#	Display Forum moderators
#	Scope:	Forum sub Loop
#	Version: 5.2
#
# --------------------------------------------------------------------------------------
function sp_ForumIndexModerators($args = '', $label = '') {
	$defs = array('tagId'      => 'spForumModerators%ID%',
	              'tagClass'   => 'spForumModeratorList',
	              'listClass'  => 'spInRowLabel',
	              'labelClass' => 'spRowDescription',
	              'showEmpty'  => 0,
	              'echo'       => 1,
	              'get'        => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_ForumIndexModerators_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId      = esc_attr($tagId);
	$tagClass   = esc_attr($tagClass);
	$listClass  = esc_attr($listClass);
	$labelClass = esc_attr($labelClass);
	$showEmpty  = (int) $showEmpty;
	$echo       = (int) $echo;
	$get        = (int) $get;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisForum->forum_id, $tagId);

	$mods_data = SP()->meta->get_value('forum_moderators', 'users');
	$mods      = $mods_data[SP()->forum->view->thisForum->forum_id];

	if ($get) return $mods;

	# build mods list with name display
	if (!empty($mods)) {
		$modList = '';
		$first   = true;
		foreach ($mods as $mod) {
			if (!$first) $modList .= ', ';
			$first = false;
			$modList .= SP()->user->name_display($mod['user_id'], $mod['display_name']);
		}
	} else if ($showEmpty) {
		$modList = 'none';
	} else {
		return '';
	}

	$out = "<div id='$tagId' class='$tagClass'>";
	$out .= "<span class='$labelClass'>".SP()->displayFilters->title($label)."</span>";
	$out .= "<span class='$listClass'>$modList</span>";
	$out .= "</div>";
	$out = apply_filters('sph_ForumIndexModerators', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_ForumIndexSubForums()
#	Display Sub Forums below parent
#	Scope:	Forum sub Loop
#	Version: 5.0
#
#	Changelog:
#	5.4.2 - 'iconWidth' argument added
#
# --------------------------------------------------------------------------------------
function sp_ForumIndexSubForums($args = '', $label = '', $toolTip = '') {
	if (empty(SP()->forum->view->thisForumSubs)) return;

	$defs = array('tagId'      => 'spForumIndexSubForums%ID%',
	              'tagClass'   => 'spInRowSubForums',
	              'labelClass' => 'spInRowLabel',
	              'linkClass'  => 'spInRowSubForumlink',
	              'icon'       => 'sp_SubForumIcon.png',
	              'unreadIcon' => 'sp_SubForumIcon.png',
	              'iconClass'  => 'spIconSmall',
	              'iconWidth'  => 20,
	              'topicCount' => 1,
	              'allNested'  => 1,
	              'stack'      => 0,
				  'rule'	   => 0,
	              'truncate'   => 0,
	              'echo'       => 1,
	              'get'        => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_ForumIndexSubForums_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId      = esc_attr($tagId);
	$tagClass   = esc_attr($tagClass);
	$labelClass = esc_attr($labelClass);
	$linkClass  = esc_attr($linkClass);
	$icon       = sanitize_file_name($icon);
	$unreadIcon = sanitize_file_name($unreadIcon);
	$iconClass  = esc_attr($iconClass);
	$iconWidth  = (int) $iconWidth;
	$topicCount = (int) $topicCount;
	$allNested  = (int) $allNested;
	$stack      = (int) $stack;
	$rule		= (int) $rule;
	$truncate   = (int) $truncate;
	$echo       = (int) $echo;
	$get        = (int) $get;
	$toolTip    = esc_attr($toolTip);

	$thisTagId = str_ireplace('%ID%', SP()->forum->view->thisForum->forum_id, $tagId);

	if ($get) return SP()->forum->view->thisForumSubs;

	$out = '';
	if ($rule) $out.= '<hr />';
	$out.= "<div id='$thisTagId' class='$tagClass'>";
	if ($stack) {
		$out .= "<ul class='$labelClass'><li>".SP()->displayFilters->title($label)."<ul>";
	} else {
		$out .= "<span class='$labelClass'>".SP()->displayFilters->title($label)."</span>";
	}
	foreach (SP()->forum->view->thisForumSubs as $sub) {

		# Check if a custom icon
		$path = SPTHEMEICONSDIR;
		$url  = SPTHEMEICONSURL;
		
		
		$fIconType = 'file';
		
		if ($sub->unread) {
			$fIcon = sanitize_file_name($unreadIcon);
			$forum_icon = spa_get_saved_icon( $sub->forum_icon_new );
			
			if ( !empty( $forum_icon['icon'] ) ) {
				
				$fIconType = $forum_icon['type'];
				$fIcon = 'file' === $forum_icon['type'] ? $forum_icon['icon'] : $forum_icon;
				$path  = SPCUSTOMDIR;
				$url   = SPCUSTOMURL;
			}
		} else {
			$fIcon = sanitize_file_name($icon);
			$forum_icon = spa_get_saved_icon( $sub->forum_icon );
			
			if ( !empty( $forum_icon['icon'] ) ) {
				
				$fIconType = $forum_icon['type'];
				$fIcon = 'file' === $forum_icon['type'] ? $forum_icon['icon'] : $forum_icon;
				$path  = SPCUSTOMDIR;
				$url   = SPCUSTOMURL;
			}
		}
		
		
		if( 'file' === $fIconType ) {
			
			if (!file_exists($path.$fIcon)) {
				$fIcon = SP()->theme->paint_icon($iconClass, SPTHEMEICONSURL, sanitize_file_name($fIcon));
			} else {
				$fIcon = SP()->theme->paint_custom_icon($iconClass, $url.$fIcon);
			}
		
		} else {
			$fIcon = SP()->theme->sp_paint_iconset_icon( $fIcon, $iconClass );
		}

		if ($sub->parent == SP()->forum->view->thisForum->forum_id || $allNested == true) {
			$thisToolTip = str_ireplace('%NAME%', htmlspecialchars($sub->forum_name, ENT_QUOTES, SPCHARSET), $toolTip);
			if ($stack) $out .= "<li>";

			$out .= str_replace("<img ", "<img width='".$iconWidth."' ", $fIcon);
			$out = str_replace(array("\n", "\t", "\r"), '', $out);  // Get rid of carriage returns, line feeds etc.

			$thisTagId = str_ireplace('%ID%', $sub->forum_id, $tagId);
			$out .= "<a href='$sub->forum_permalink' id='$thisTagId' class='$linkClass' title='$thisToolTip'>".SP()->primitives->truncate_name($sub->forum_name, $truncate)."</a>";
			if ($topicCount) $out .= " ($sub->topic_count)";
			if ($stack) $out .= "</li>";
		}
	}
	if ($stack) $out .= "</ul></li></ul>";

	$out .= "</div>";
	$out = apply_filters('sph_ForumIndexSubForums', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_NoForumsInGroupMessage()
#	Display Message when no Forums are found in a Group
#	Scope:	Forum Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_NoForumsInGroupMessage($args = '', $definedMessage = '') {
	$defs = array('tagId'    => 'spNoForumsInGroupMessage',
	              'tagClass' => 'spMessage',
	              'echo'     => 1,
	              'get'      => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_NoForumsInGroupMessage_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId    = esc_attr($tagId);
	$tagClass = esc_attr($tagClass);
	$echo     = (int) $echo;
	$get      = (int) $get;

	if ($get) return SP()->displayFilters->title($definedMessage);

	$out = "<div id='$tagId' class='$tagClass'>".SP()->displayFilters->title($definedMessage)."</div>";
	$out = apply_filters('sph_NoForumsInGroupMessage', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}
