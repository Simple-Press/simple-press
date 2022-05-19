<?php
/*
Simple:Press
TForum View Function Handler
$LastChangedDate: 2018-12-16 12:27:05 -0600 (Sun, 16 Dec 2018) $
$Rev: 15855 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# --------------------------------------------------------------------------------------
#
#	sp_SubForumHeaderDescription()
#	Display SubForum Description in Header
#	Scope:	SubForum Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_SubForumHeaderDescription($args = '', $label = '') {
	$defs = array('tagId'    => 'spSubForumHeaderDescription',
	              'tagClass' => 'spHeaderDescription',
	              'echo'     => 1,
	              'get'      => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_SubForumHeaderDescription_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId    = esc_attr($tagId);
	$tagClass = esc_attr($tagClass);
	$echo     = (int) $echo;
	$get      = (int) $get;

	$label = SP()->displayFilters->title($label);

	if ($get) return $label;

	$out = (empty($label)) ? '' : "<div id='$tagId' class='$tagClass'>$label</div>";
	$out = apply_filters('sph_SubForumHeaderDescription', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_SubForumIndexIcon()
#	Display Forum Icon
#	Scope:	Forum sub Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_SubForumIndexIcon($args = '') {
	$defs = array('tagId'      => 'spSubForumIndexIcon%ID%',
	              'tagClass'   => 'spRowIcon',
	              'icon'       => 'sp_ForumIcon.png',
	              'iconUnread' => 'sp_ForumIcon.png',
	              'echo'       => 1,
	              'get'        => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_SubForumIndexIcon_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId    = esc_attr($tagId);
	$tagClass = esc_attr($tagClass);
	$echo     = (int) $echo;
	$get      = (int) $get;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisSubForum->forum_id, $tagId);
	$fIconType = 'file';

	# Check if a custom icon
	$path = SPTHEMEICONSDIR;
	$url  = SPTHEMEICONSURL;
	if (SP()->forum->view->thisSubForum->unread) {
		$fIcon = sanitize_file_name($iconUnread);
		$forum_icon = spa_get_saved_icon( SP()->forum->view->thisSubForum->forum_icon_new );
		
		if ( !empty( $forum_icon['icon'] ) ) {
			$fIconType = $forum_icon['type'];
			$fIcon = 'file' === $forum_icon['type'] ? $forum_icon['icon'] : $forum_icon;
			$path  = SPCUSTOMDIR;
			$url   = SPCUSTOMURL;
		}
	} else {
		$fIcon = sanitize_file_name($icon);
		$forum_icon = spa_get_saved_icon( SP()->forum->view->thisSubForum->forum_icon );
		
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
	$out = apply_filters('sph_SubForumIndexIcon', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_SubForumIndexName()
#	Display Forum Name/Title in Header
#	Scope:	Forumn sub Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_SubForumIndexName($args = '', $toolTip = '') {
	$defs = array('tagId'    => 'spSubForumIndexName%ID%',
	              'tagClass' => 'spRowName',
	              'truncate' => 0,
	              'echo'     => 1,
	              'get'      => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_SubForumIndexName_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId    = esc_attr($tagId);
	$tagClass = esc_attr($tagClass);
	$toolTip  = esc_attr($toolTip);
	$truncate = (int) $truncate;
	$echo     = (int) $echo;
	$get      = (int) $get;

	$tagId   = str_ireplace('%ID%', SP()->forum->view->thisSubForum->forum_id, $tagId);
	$toolTip = str_ireplace('%NAME%', htmlspecialchars(SP()->forum->view->thisSubForum->forum_name, ENT_QUOTES, SPCHARSET), $toolTip);

	if ($get) return SP()->primitives->truncate_name(SP()->forum->view->thisSubForum->forum_name, $truncate);

	$out = "<a href='".SP()->forum->view->thisSubForum->forum_permalink."' id='$tagId' class='$tagClass' title='$toolTip'>".SP()->primitives->truncate_name(SP()->forum->view->thisSubForum->forum_name, $truncate)."</a>";
	$out = apply_filters('sph_SubForumIndexName', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_SubForumIndexDescription()
#	Display Forum Description in Header
#	Scope:	Forum sub Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_SubForumIndexDescription($args = '') {
	$defs = array('tagId'    => 'spSubForumIndexDescription%ID%',
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

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisSubForum->forum_id, $tagId);

	if ($get) return SP()->forum->view->thisSubForum->forum_desc;

	$out = (empty(SP()->forum->view->thisSubForum->forum_desc)) ? '' : "<div id='$tagId' class='$tagClass'>".SP()->forum->view->thisSubForum->forum_desc."</div>";
	$out = apply_filters('sph_SubForumIndexDescription', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_SubForumIndexPageLinks()
#	Display Forum 'in row' page links
#	Scope:	Forum sub Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_SubForumIndexPageLinks($args = '', $toolTip = '') {
	$topics_per_page = SP()->core->forumData['display']['topics']['perpage'];
	if ($topics_per_page >= SP()->forum->view->thisSubForum->topic_count) return '';

	$defs = array('tagId'         => 'spSubForumIndexPageLinks%ID%',
	              'tagClass'      => 'spInRowPageLinks',
	              'icon'          => 'sp_ArrowRightSmall.png',
	              'iconClass'     => 'spIconSmall',
	              'pageLinkClass' => 'spInRowForumPageLink',
	              'showLinks'     => 4,
	              'echo'          => 1,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_SubForumIndexPageLinks_args', $a);
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

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisSubForum->forum_id, $tagId);

	$out         = "<div id='$tagId' class='$tagClass'>";
	$total_pages = (SP()->forum->view->thisSubForum->topic_count / $topics_per_page);
	if (!is_int($total_pages)) $total_pages = intval($total_pages) + 1;
	($total_pages > $showLinks ? $max_count = $showLinks : $max_count = $total_pages);

	for ($x = 1; $x <= $max_count; $x++) {
		$out .= "<a class='$pageLinkClass' href='".SP()->spPermalinks->build_url(SP()->forum->view->thisSubForum->forum_slug, '', $x, 0)."' title='".str_ireplace('%PAGE%', $x, $toolTip)."'>$x</a>\n";
	}
	if ($total_pages > $showLinks) {
		if (!empty($icon)) $out .= SP()->theme->paint_icon($iconClass, SPTHEMEICONSURL, $icon);
		$out .= "<a class='$pageLinkClass' href='".SP()->spPermalinks->build_url(SP()->forum->view->thisSubForum->forum_slug, '', $total_pages, 0)."' title='".str_ireplace('%PAGE%', $total_pages, $toolTip)."'>$total_pages</a>";
	}
	$out .= "</div>";

	$out = apply_filters('sph_SubForumIndexPageLinks', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_SubForumIndexModerators()
#	Display Forum moderators
#	Scope:	Forum sub Loop
#	Version: 5.2
#
# --------------------------------------------------------------------------------------
function sp_SubForumIndexModerators($args = '', $label = '') {
	$defs = array('tagId'      => 'spSubForumModerators%ID%',
	              'tagClass'   => 'spSubForumModeratorList',
	              'listClass'  => 'spInRowLabel',
	              'labelClass' => 'spRowDescription',
	              'showEmpty'  => 0,
	              'echo'       => 1,
	              'get'        => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_SubForumIndexModerators_args', $a);
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
	$out = apply_filters('sph_SubForumIndexModerators', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_SubForumIndexLastPost()
#	Display Forum 'in row' link to the last post made to a topic in this forum
#	Scope:	Forum sub Loop
#	Version: 5.0
#
#	Changelog:
#	5.2 - 'Order' argument added
#	5.2	- 'ItemBreak' argument added
#	5.2.3 - 'L' Linebreak - added to Order argument
#	5.5.3 - 'labelLink' argument added
#
# --------------------------------------------------------------------------------------
function sp_SubForumIndexLastPost($args = '', $lastPostLabel = '', $noTopicsLabel = '') {
	if (SP()->forum->view->thisSubForum->post_count == 0 && SP()->forum->view->thisSubForum->post_count_sub == 0) return;

	$defs = array('tagId'        => 'spSubForumIndexLastPost%ID%',
	              'tagClass'     => 'spInRowPostLink',
	              'labelClass'   => 'spInRowLabel',
	              'infoClass'    => 'spInRowInfo',
	              'linkClass'    => 'spInRowLastPostLink',
	              'labelLink'    => 0,
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
	$a    = apply_filters('sph_SubForumIndexLastPost_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId        = esc_attr($tagId);
	$tagClass     = esc_attr($tagClass);
	$labelClass   = esc_attr($labelClass);
	$infoClass    = esc_attr($infoClass);
	$linkClass    = esc_attr($linkClass);
	$labelLink    = (int) $labelLink;
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

	if ($includeSubs && SP()->forum->view->thisSubForum->forum_id_sub == 0) $includeSubs = 0;
	$postCount = ($includeSubs ? SP()->forum->view->thisSubForum->post_count_sub : SP()->forum->view->thisSubForum->post_count);

	$tagId   = str_ireplace('%ID%', SP()->forum->view->thisSubForum->forum_id, $tagId);
	$posttip = ($includeSubs ? SP()->forum->view->thisSubForum->post_tip_sub : SP()->forum->view->thisSubForum->post_tip);
	if ($tip && !empty($posttip)) {
		$title = "title='$posttip'";
		$linkClass .= '';
	} else {
		$title = '';
	}

	($stackdate ? $dlb = '<br />' : $dlb = ' - ');

	# user
	$poster = ($includeSubs ? SP()->user->name_display(SP()->forum->view->thisSubForum->user_id_sub, SP()->primitives->truncate_name(SP()->forum->view->thisSubForum->display_name_sub, $truncateUser)) : SP()->user->name_display(SP()->forum->view->thisSubForum->user_id, SP()->primitives->truncate_name(SP()->forum->view->thisSubForum->display_name, $truncateUser)));
	if (empty($poster)) $poster = ($includeSubs ? SP()->primitives->truncate_name(SP()->forum->view->thisSubForum->guest_name_sub, $truncateUser) : SP()->primitives->truncate_name(SP()->forum->view->thisSubForum->guest_name, $truncateUser));

	# other items
	$permalink = ($includeSubs ? SP()->forum->view->thisSubForum->post_permalink_sub : SP()->forum->view->thisSubForum->post_permalink);
	$topicname = ($includeSubs ? SP()->primitives->truncate_name(SP()->forum->view->thisSubForum->topic_name_sub, $truncate) : SP()->primitives->truncate_name(SP()->forum->view->thisSubForum->topic_name, $truncate));
	$postdate  = ($includeSubs ? SP()->forum->view->thisSubForum->post_date_sub : SP()->forum->view->thisSubForum->post_date);

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
			if ($time) {
				$D .= $dlb.SP()->dateTime->format_date('t', $postdate);
			}
		}
	}

	$out = "<div id='$tagId' class='$tagClass'>";
	if ($postCount) {
		$out .= "<span class='$labelClass'>";
		if ($labelLink) {
			$out .= "<a class='$linkClass' $title href='$permalink'>";
		}
		$out .= SP()->displayFilters->title($lastPostLabel)." ";
		if ($labelLink) {
			$out .= "</a>";
		}

		for ($x = 0; $x < strlen($order); $x++) {
			$i = substr($order, $x, 1);
			switch ($i) {
				case 'U':
					if ($user) {
						if ($x != 0) $out .= "<span class='$labelClass'>";
						$out .= $U."</span>\n";
					}
					if ($x != (strlen($order) - 1)) {
						if (substr($order, $x + 1, 1) != 'L') {
							$out .= $itemBreak;
						}
					}
					break;
				case 'T':
					if ($x == 0) $out .= "</span>".$itemBreak;
					$out .= "<span class='$linkClass'>";

					$out .= $T."</span>\n";
					if ($x != (strlen($order) - 1)) {
						if (substr($order, $x + 1, 1) != 'L') {
							$out .= $itemBreak;
						}
					}
					break;
				case 'D':
					if ($x != 0) $out .= "<span class='$labelClass'>";
					$out .= $D."</span>\n";
					if ($x != (strlen($order) - 1)) {
						if (substr($order, $x + 1, 1) != 'L') {
							$out .= $itemBreak;
						}
					}
					break;
				case 'L':
					$out .= '<br />';
					break;
			}
		}
	} else {
		$out .= "<span class='$labelClass'>".SP()->displayFilters->title($noTopicsLabel)."";
		$out .= "</span>";
	}

	$out .= "</div>";
	$out = apply_filters('sph_SubForumIndexLastPost', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_SubForumIndexTopicCount()
#	Display Forum 'in row' total topic count
#	Scope:	Forum sub Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_SubForumIndexTopicCount($args = '', $label = '') {
	$defs = array('tagId'       => 'spSubForumIndexTopicCount%ID%',
	              'tagClass'    => 'spInRowCount',
	              'labelClass'  => 'spInRowLabel',
	              'numberClass' => 'spInRowNumber',
	              'includeSubs' => 1,
	              'stack'       => 0,
	              'echo'        => 1,
	              'get'         => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_SubForumIndexTopicCount_args', $a);
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

	if ($includeSubs && SP()->forum->view->thisSubForum->forum_id_sub == 0) $includeSubs = 0;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisSubForum->forum_id, $tagId);
	($stack ? $att = '<br />' : $att = ' ');

	$data = ($includeSubs ? SP()->forum->view->thisSubForum->topic_count_sub : SP()->forum->view->thisSubForum->topic_count);
	if ($get) return $data;

	$out = "<div id='$tagId' class='$tagClass'>";
	$out .= "<span class='$labelClass'>".SP()->displayFilters->title($label)."$att</span>";
	$out .= "<span class='$numberClass'>$data</span>";
	$out .= "</div>";
	$out = apply_filters('sph_SubForumIndexTopicCount', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_SubForumIndexPostCount()
#	Display Forum 'in row' total post count
#	Scope:	Forum sub Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_SubForumIndexPostCount($args = '', $label = '') {
	$defs = array('tagId'       => 'spSubForumIndexPostCount%ID%',
	              'tagClass'    => 'spInRowCount',
	              'labelClass'  => 'spInRowLabel',
	              'numberClass' => 'spInRowNumber',
	              'includeSubs' => 1,
	              'stack'       => 0,
	              'echo'        => 1,
	              'get'         => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_SubForumIndexPostCount_args', $a);
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

	if ($includeSubs && SP()->forum->view->thisSubForum->forum_id_sub == 0) $includeSubs = 0;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisSubForum->forum_id, $tagId);
	($stack ? $att = '<br />' : $att = ' ');

	$data = ($includeSubs ? SP()->forum->view->thisSubForum->post_count_sub : SP()->forum->view->thisSubForum->post_count);
	if ($get) return $data;

	$out = "<div id='$tagId' class='$tagClass'>";
	$out .= "<span class='$labelClass'>".SP()->displayFilters->title($label)."$att</span>";
	$out .= "<span class='$numberClass'>$data</span>";
	$out .= "</div>";
	$out = apply_filters('sph_SubForumIndexPostCount', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_SubForumIndexStatusIcons()
#	Display Forum Status (Locked/New Post/Blank)
#	Scope:	Forum sub Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_SubForumIndexStatusIcons($args = '', $toolTipLock = '', $toolTipPost = '', $toolTipAdd = '') {
	$defs = array('tagId'    => 'spForumIndexStatus%ID%',
	              'tagClass' => 'spStatusIcon',
	              'iconLock' => 'sp_ForumStatusLock.png',
	              'iconPost' => 'sp_ForumStatusPost.png',
	              'iconAdd'  => 'sp_ForumStatusAdd.png',
	              'first'    => 0,
	              'echo'     => 1,
	              'get'      => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_SubForumIndexStatusIcons_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId       = esc_attr($tagId);
	$tagClass    = esc_attr($tagClass);
	$toolTipPost = esc_attr($toolTipPost);
	$toolTipLock = esc_attr($toolTipLock);
	$toolTipAdd  = esc_attr($toolTipAdd);
	$first       = (int) $first;
	$echo        = (int) $echo;
	$get         = (int) $get;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisSubForum->forum_id, $tagId);

	if ($get) return SP()->forum->view->thisSubForum->forum_status;

	# Dislay if locked or new post
	$out = "<div id='$tagId' class='$tagClass'>";
	if (SP()->forum->view->thisSubForum->forum_status == 1 || SP()->core->forumData['lockdown'] == true) {
		$out .= SP()->theme->paint_icon('', SPTHEMEICONSURL, sanitize_file_name($iconLock), $toolTipLock);
	}

	# New Post Popup
	if (SP()->forum->view->thisSubForum->unread) {
		$toolTipPost = str_ireplace('%COUNT%', SP()->forum->view->thisSubForum->unread, $toolTipPost);
		$site        = wp_nonce_url(SPAJAXURL."spUnreadPostsPopup&amp;targetaction=forum&amp;id=".SP()->forum->view->thisSubForum->forum_id."&amp;first=$first", 'spUnreadPostsPopup');
		$linkId      = 'spNewPostPopup'.SP()->forum->view->thisSubForum->forum_id;
		$out .= "<a rel='nofollow' id='$linkId' class='spUnreadPostsPopup' data-popup='1' data-site='$site' data-label='$toolTipPost' data-width='600' data-height='0' data-align='0'>";
		$out .= SP()->theme->paint_icon('', SPTHEMEICONSURL, sanitize_file_name($iconPost), $toolTipPost);
	}

	# add new topic icon
	if (SP()->auths->get('start_topics', SP()->forum->view->thisSubForum->forum_id) && !SP()->forum->view->thisSubForum->forum_status && !SP()->core->forumData['lockdown']) {
		$url = SP()->spPermalinks->build_url(SP()->forum->view->thisSubForum->forum_slug, '', 1, 0).SP()->spPermalinks->get_query_char().'new=topic';
		$out .= "<a href='$url' title='$toolTipAdd'>";
		$out .= SP()->theme->paint_icon('', SPTHEMEICONSURL, sanitize_file_name($iconAdd));
		$out .= "</a>";
	}

	$out = apply_filters('sph_SubForumIndexStatusIconsLast', $out);

	$out .= "</div>";

	$out = apply_filters('sph_SubForumIndexStatusIcons', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_SubForumIndexLockIcon()
#	Display Forum Status (Locked)
#	Scope:	Sub Forum sub Loop
#	Version: 5.2
#
#	Changelog
#	5.2.3	Added 'statusClass' to icons with no action
#
# --------------------------------------------------------------------------------------
function sp_SubForumIndexLockIcon($args = '', $toolTip = '') {
	$defs = array('tagId'       => 'spForumIndexLockIcon%ID%',
	              'tagClass'    => 'spIcon',
	              'statusClass' => 'spIconNoAction',
	              'icon'        => 'sp_ForumStatusLock.png',
	              'echo'        => 1,
	              'get'         => 0);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_SubForumIndexLockIcon_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId       = esc_attr($tagId);
	$tagClass    = esc_attr($tagClass);
	$statusClass = esc_attr($statusClass);
	$icon        = sanitize_file_name($icon);
	$echo        = (int) $echo;
	$get         = (int) $get;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisSubForum->forum_id, $tagId);

	if ($get) return SP()->forum->view->thisSubForum->forum_status;
	$out = '';

	if (SP()->core->forumData['lockdown'] || SP()->forum->view->thisSubForum->forum_status) {
		$out = "<div id='$tagId' class='$tagClass $statusClass' title='$toolTip' >";
		# Dislay if global lock down or forum locked
		if (!empty($icon)) $out .= SP()->theme->paint_icon('', SPTHEMEICONSURL, $icon);
		$out .= "</div>";
		$out = apply_filters('sph_SubForumIndexLockIcon', $out, $a);
	}

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_SubForumIndexAddIcon()
#	Display Forum Status (Add Topic)
#	Scope:	Sub Forum sub Loop
#	Version: 5.2
#
# --------------------------------------------------------------------------------------
function sp_SubForumIndexAddIcon($args = '', $toolTip = '') {
	$defs = array('tagId'    => 'spForumIndexAddIcon%ID%',
	              'tagClass' => 'spIcon',
	              'icon'     => 'sp_ForumStatusAdd.png',
	              'echo'     => 1,
	              'get'      => 0);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_SubForumIndexAddIcon_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId    = esc_attr($tagId);
	$tagClass = esc_attr($tagClass);
	$icon     = sanitize_file_name($icon);
	$echo     = (int) $echo;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisSubForum->forum_id, $tagId);
	$out   = '';

	# add new topic icon
	if (SP()->auths->get('start_topics', SP()->forum->view->thisSubForum->forum_id) && ((!SP()->forum->view->thisSubForum->forum_status && !SP()->core->forumData['lockdown']) || SP()->user->thisUser->admin)) {
		$url = SP()->spPermalinks->build_url(SP()->forum->view->thisSubForum->forum_slug, '', 1, 0).SP()->spPermalinks->get_query_char().'new=topic';
		$out .= "<a id='$tagId' class='$tagClass' title='$toolTip' href='$url'>";
		if (!empty($icon)) $out .= SP()->theme->paint_icon('', SPTHEMEICONSURL, $icon);
		$out .= "</a>";
		$out = apply_filters('sph_SubForumIndexAddIcon', $out, $a);
	}

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_SubForumIndexPostsIcon()
#	Display Forum Status (Show Posts)
#	Scope:	Sub Forum sub Loop
#	Version: 5.2
#
# --------------------------------------------------------------------------------------
function sp_SubForumIndexPostsIcon($args = '', $toolTip = '') {
	if (!SP()->forum->view->thisSubForum->unread) return;

	$defs = array('tagId'     => 'spForumIndexPostsIcon%ID%',
	              'tagClass'  => 'spIcon',
	              'icon'      => 'sp_ForumStatusPost.png',
	              'openIcon'  => 'sp_GroupOpen.png',
	              'closeIcon' => 'sp_GroupClose.png',
	              'popup'     => 1,
	              'first'     => 0,
	              'echo'      => 1);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_SubForumIndexPostsIcon_args', $a);
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

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisSubForum->forum_id, $tagId);
	$out   = '';

	# show new posts icon
	if (SP()->forum->view->thisSubForum->unread) {
		$toolTip = esc_attr(str_ireplace('%COUNT%', SP()->forum->view->thisSubForum->unread, $toolTip));
		$site    = wp_nonce_url(SPAJAXURL."spUnreadPostsPopup&amp;targetaction=forum&amp;id=".SP()->forum->view->thisSubForum->forum_id."&amp;popup=$popup&amp;first=$first", 'spUnreadPostsPopup');
		$linkId  = 'spNewPostPopup'.SP()->forum->view->thisSubForum->forum_id;
		$target  = 'spInlineTopics'.SP()->forum->view->thisSubForum->forum_id;
		$spinner = SPCOMMONIMAGES.'working.gif';

		if ($popup) {
			$out .= "<a  id='$tagId' class='$tagClass' title='$toolTip' rel='nofollow' id='$linkId' data-popup='1' data-site='$site' data-label='$toolTip' data-width='600' data-height='0' data-align='0'";
			$out .= $popupIcon;
		} else {
			$out .= "<a id='$tagId' class='$tagClass spUnreadPostsPopup' title='$toolTip' rel='nofollow' id='$linkId' data-popup='0' data-site='$site' data-target='$target' data-spinner='$spinner' data-id='$tagId' data-open='$openIcon' data-close='$closeIcon'>";
			$out .= "<img src='$openIcon' alt=''>";
		}
		$out .= "</a>";
		$out = apply_filters('sph_SubForumIndexPostsIcon', $out, $a);
	}

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_SubForumIndexInlinePosts()
#	Display inline dropdopwn posts section (Show Posts)
#	Scope:	Sub Forum sub Loop
#	Version: 5.2
#
# --------------------------------------------------------------------------------------
function sp_SubForumIndexInlinePosts() {
	echo "<div class='spInlineTopics' id='spInlineTopics".SP()->forum->view->thisSubForum->forum_id."' style='display:none;'></div>";
	sp_InsertBreak();
}

# --------------------------------------------------------------------------------------
#
#	sp_ForumHeaderSubForums()
#	Display Sub Forums below parent
#	Scope:	Forum View
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_ForumHeaderSubForums($args = '', $label = '', $toolTip = '') {
	if (empty(SP()->forum->view->thisForumSubs) || empty(SP()->forum->view->thisSubForum->children)) return;

	$defs = array('tagId'      => 'spForumHeaderSubForums%ID%',
	              'tagClass'   => 'spInRowSubForums',
	              'labelClass' => 'spInRowLabel',
	              'linkClass'  => 'spInRowSubForumlink',
	              'icon'       => 'sp_SubForumIcon.png',
	              'unreadIcon' => 'sp_SubForumIcon.png',
	              'iconClass'  => 'spIconSmall',
	              'topicCount' => 1,
				  'rule'	   => 1,
	              'allNested'  => 1,
	              'stack'      => 0,
	              'truncate'   => 0,
	              'echo'       => 1,
	              'get'        => 0);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_ForumHeaderSubForums_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId      = esc_attr($tagId);
	$tagClass   = esc_attr($tagClass);
	$labelClass = esc_attr($labelClass);
	$linkClass  = esc_attr($linkClass);
	$icon       = sanitize_file_name($icon);
	$unreadIcon = sanitize_file_name($unreadIcon);
	$iconClass  = esc_attr($iconClass);
	$topicCount = (int) $topicCount;
	$rule		= (int) $rule;
	$allNested  = (int) $allNested;
	$stack      = (int) $stack;
	$truncate   = (int) $truncate;
	$echo       = (int) $echo;
	$get        = (int) $get;
	$toolTip    = esc_attr($toolTip);

	$thisTagId = str_ireplace('%ID%', SP()->forum->view->thisSubForum->forum_id, $tagId);

	if ($get) return SP()->forum->view->thisForumSubs;

	$out = '';
	if ($rule) $out = '<hr />';

	$out.= "<div id='$thisTagId' class='$tagClass'>";
	if ($stack) {
		$out .= "<ul class='$labelClass'><li>".SP()->displayFilters->title($label)."<ul>";
	} else {
		$out .= "<span class='$labelClass'>".SP()->displayFilters->title($label)."</span>";
	}

	$tout = '';

	foreach (SP()->forum->view->thisForumSubs as $sub) {
		if (SP()->forum->view->thisSubForum->forum_id != $sub->top_sub_parent) {
			# skip this one - not in this subforum branch
			continue;
		}
		if (($allNested == 0 && $sub->parent == SP()->forum->view->thisSubForum->forum_id) || ($allNested == 1 && $sub->top_parent == SP()->forum->view->thisSubForum->parent && $sub->forum_id != SP()->forum->view->thisSubForum->forum_id)) {
			$thisToolTip = str_ireplace('%NAME%', htmlspecialchars($sub->forum_name, ENT_QUOTES, SPCHARSET), $toolTip);
			if ($stack) $tout .= "<li>";

			if ($sub->unread) {
				if (!empty($unreadIcon)) $tout .= SP()->theme->paint_icon($iconClass, SPTHEMEICONSURL, $unreadIcon);
			} else {
				if (!empty($icon)) $tout .= SP()->theme->paint_icon($iconClass, SPTHEMEICONSURL, $icon);
			}

			$thisTagId = str_ireplace('%ID%', $sub->forum_id, $tagId);
			$tout .= "<a href='$sub->forum_permalink' id='$thisTagId' class='$linkClass' title='$thisToolTip'>".SP()->primitives->truncate_name($sub->forum_name, $truncate)."</a>";
			if ($topicCount) $tout .= " ($sub->topic_count)\n";
			if ($stack) $tout .= "</li>";
		}
	}

	if (empty($tout)) return;
	$out .= $tout;

	if ($stack) $out .= "</ul></li></ul>";
	$out .= "</div>";
	$out = apply_filters('sph_ForumHeaderSubForums', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# ======================================================================================
#
# FORUM VIEW
# Forum Head Functions
#
# ======================================================================================

# --------------------------------------------------------------------------------------
#
#	sp_ForumHeaderIcon()
#	Display Forum Icon
#	Scope:	Forum View
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_ForumHeaderIcon($args = '') {
	$defs = array('tagId'    => 'spForumHeaderIcon',
	              'tagClass' => 'spHeaderIcon',
	              'icon'     => 'sp_ForumIcon.png',
	              'echo'     => 1,
	              'get'      => 0);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_ForumHeaderIcon_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId    = esc_attr($tagId);
	$tagClass = esc_attr($tagClass);
	$echo     = (int) $echo;
	$get      = (int) $get;

	# Check if a custom icon
	
	$forum_icon = spa_get_saved_icon( SP()->forum->view->thisForum->forum_icon );
	
	if (!empty( $forum_icon['icon'] ) ) {
		$icon = $forum_icon['icon'];

		if( 'file' === $forum_icon['type'] ) {
			$icon = SP()->theme->paint_custom_icon($tagClass, SPCUSTOMURL. $icon );
		} else {
			$icon = SP()->theme->sp_paint_iconset_icon( $forum_icon, $tagClass );
		}

	} else {
		$icon = SP()->theme->paint_icon($tagClass, SPTHEMEICONSURL, sanitize_file_name($icon));
	}

	if ($get) return $icon;

	if (!empty($icon)) $out = SP()->theme->paint_icon_id($icon, $tagId);
	$out = apply_filters('sph_ForumHeaderIcon', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_ForumHeaderName()
#	Display Forum Name/Title in Header
#	Scope:	Forum View
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_ForumHeaderName($args = '') {
	$defs = array('tagId'    => 'spForumHeaderName',
	              'tagClass' => 'spHeaderName',
	              'truncate' => 0,
	              'echo'     => 1,
	              'get'      => 0);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_ForumHeaderName_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId    = esc_attr($tagId);
	$tagClass = esc_attr($tagClass);
	$truncate = (int) $truncate;
	$echo     = (int) $echo;
	$get      = (int) $get;

	if ($get) return SP()->forum->view->thisForum->forum_name;

	$out = (empty(SP()->forum->view->thisForum->forum_name)) ? '' : "<div id='$tagId' class='$tagClass'>".SP()->primitives->truncate_name(SP()->forum->view->thisForum->forum_name, $truncate)."</div>";
	$out = apply_filters('sph_ForumHeaderName', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_ForumHeaderDescription()
#	Display Forum Description in Header
#	Scope:	Forum View
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_ForumHeaderDescription($args = '') {
	$defs = array('tagId'    => 'spForumHeaderDescription',
	              'tagClass' => 'spHeaderDescription',
	              'echo'     => 1,
	              'get'      => 0);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_ForumHeaderDescription_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId    = esc_attr($tagId);
	$tagClass = esc_attr($tagClass);
	$echo     = (int) $echo;
	$get      = (int) $get;

	if ($get) return SP()->forum->view->thisForum->forum_desc;

	$out = (empty(SP()->forum->view->thisForum->forum_desc)) ? '' : "<div id='$tagId' class='$tagClass'>".SP()->forum->view->thisForum->forum_desc."</div>";
	$out = apply_filters('sph_ForumHeaderDescription', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_ForumHeaderRSSButton()
#	Display Forum Level RSS Button
#	Scope:	Forum View
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_ForumHeaderRSSButton($args = '', $label = '', $toolTip = '') {
	if (!SP()->auths->get('view_forum', SP()->forum->view->thisForum->forum_id) || SP()->forum->view->thisForum->forum_rss_private) return;

	$defs = array('tagId'     => 'spForumHeaderRSSButton',
	              'tagClass'  => 'spButton',
	              'icon'      => 'sp_Feed.png',
	              'iconClass' => 'spIcon',
	              'echo'      => 1,
	              'get'       => 0);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_ForumHeaderRSSButton_args', $a);
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
	if (empty(SP()->forum->view->thisForum->rss)) {
		$rssOpt = SP()->options->get('sfrss');
		if ($rssOpt['sfrssfeedkey'] && isset(SP()->user->thisUser->feedkey)) {
			$rssUrl = trailingslashit(SP()->spPermalinks->build_url(SP()->forum->view->thisForum->forum_slug, '', 0, 0, 0, 1)).user_trailingslashit(SP()->user->thisUser->feedkey);
		} else {
			$rssUrl = SP()->spPermalinks->build_url(SP()->forum->view->thisForum->forum_slug, '', 0, 0, 0, 1);
		}
	} else {
		$rssUrl = SP()->forum->view->thisForum->rss;
	}

	if ($get) return $rssUrl;

	$out = "<a class='$tagClass' id='$tagId' title='$toolTip' rel='nofollow' href='$rssUrl'>";
	if (!empty($icon)) $out .= SP()->theme->paint_icon($iconClass, SPTHEMEICONSURL, $icon);
	if (!empty($label)) $out .= SP()->displayFilters->title($label);
	$out .= "</a>";
	$out = apply_filters('sph_ForumHeaderRSSButton', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_ForumHeaderMessage()
#	Display Special Forum Message in Header
#	Scope:	Forum View
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_ForumHeaderMessage($args = '') {
	$defs = array('tagId'    => 'spForumHeaderMessage%ID%',
	              'tagClass' => 'spHeaderMessage',
				  'fontClass'=> '',
	              'echo'     => 1,
	              'get'      => 0);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_ForumHeaderMessage_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId    = esc_attr($tagId);
	$tagClass = esc_attr($tagClass);
	$fontClass= esc_attr($fontClass);
	$echo     = (int) $echo;
	$get      = (int) $get;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisForum->forum_id, $tagId);

	if ($get) return SP()->forum->view->thisForum->forum_message;

	$out = (empty(SP()->forum->view->thisForum->forum_message)) ? '' : "<div id='$tagId' class='$tagClass'><span class='$fontClass'>".SP()->forum->view->thisForum->forum_message."</span></div>";
	$out = apply_filters('sph_ForumHeaderMessage', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_NoForumMessage()
#	Display Message when no Forum can be displayed
#	Scope:	Forum View
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_NoForumMessage($args = '', $deniedMessage = '', $definedMessage = '') {
	$defs = array('tagId'    => 'spNoForumMessage',
	              'tagClass' => 'spMessage',
	              'echo'     => 1,
	              'get'      => 0);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_NoForumMessage_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId    = esc_attr($tagId);
	$tagClass = esc_attr($tagClass);
	$echo     = (int) $echo;
	$get      = (int) $get;

	# is Access denied
	if (SP()->forum->view->forums->forumViewStatus == 'no access') {
		$m = SP()->displayFilters->title($deniedMessage);
	} elseif (SP()->forum->view->forums->forumViewStatus == 'no data') {
		$m = SP()->displayFilters->title($definedMessage);
	} elseif (SP()->forum->view->forums->forumViewStatus == 'sneak peek') {
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
	$out = apply_filters('sph_NoForumMessage', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# ======================================================================================
#
# FORUM VIEW
# Topic Loop Functions
#
# ======================================================================================

# --------------------------------------------------------------------------------------
#
#	sp_TopicIndexPageLinks()
#	Display page links for topic list
#	Scope:	Topic List Loop
#	Version: 5.0
#		5.2:	showEmpty added to display div even when empty
#
# --------------------------------------------------------------------------------------
function sp_TopicIndexPageLinks($args = '', $label = '', $toolTip = '', $jumpToolTip = '') {
	global $jumpID;

	# can be empty if request is for a bogus forunm slug
	if (empty(SP()->forum->view->thisForum)) return;

	$topics_per_page = SP()->core->forumData['display']['topics']['perpage'];
	if (!$topics_per_page) $topics_per_page = 20;

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
	              'showEmpty'     => 0,
	              'showJump'      => 1,
	              'echo'          => 1);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_TopicIndexPageLinks_args', $a);
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
	$showEmpty     = (int) $showEmpty;
	$showJump      = (int) $showJump;
	$label         = SP()->displayFilters->title($label);
	$toolTip       = esc_attr($toolTip);
	$jumpToolTip   = esc_attr($jumpToolTip);
	$echo          = (int) $echo;

	if (!empty($prevIcon)) $prevIcon = SP()->theme->paint_icon($iconClass, SPTHEMEICONSURL, sanitize_file_name($prevIcon), $toolTip);
	if (!empty($nextIcon)) $nextIcon = SP()->theme->paint_icon($iconClass, SPTHEMEICONSURL, sanitize_file_name($nextIcon), $toolTip);
	if (!empty($jumpIcon)) $jumpIcon = SP()->theme->paint_icon($iconClass, SPTHEMEICONSURL, sanitize_file_name($jumpIcon), $jumpToolTip);

	if ($topics_per_page >= SP()->forum->view->thisForum->topic_count) {
		if ($showEmpty) echo "<div class='$tagClass'></div>";
		return;
	}

	$curToolTip = str_ireplace('%PAGE%', SP()->rewrites->pageData['page'], $toolTip);

	if (isset($jumpID) ? $jumpID++ : $jumpID = 1) ;

	$out        = "<div class='$tagClass'>";
	$totalPages = (SP()->forum->view->thisForum->topic_count / $topics_per_page);
	if (!is_int($totalPages)) $totalPages = (intval($totalPages) + 1);
	if ($label) $out .= "<span class='$pageLinkClass'>$label</span>";
	$out .= sp_page_prev(SP()->rewrites->pageData['page'], $showLinks, SP()->forum->view->thisForum->forum_permalink, $pageLinkClass, $iconClass, $prevIcon, $nextIcon, $toolTip, '');

	$url = SP()->forum->view->thisForum->forum_permalink;
	if (SP()->rewrites->pageData['page'] > 1) $url = user_trailingslashit(trailingslashit($url).'page-'.SP()->rewrites->pageData['page']);
	$url = apply_filters('sph_page_link', $url, SP()->rewrites->pageData['page']);

	$out .= "<a href='$url' class='$pageLinkClass $curPageClass' title='$curToolTip'>".SP()->rewrites->pageData['page'].'</a>';

	$out .= sp_page_next(SP()->rewrites->pageData['page'], $totalPages, $showLinks, SP()->forum->view->thisForum->forum_permalink, $pageLinkClass, $iconClass, $prevIcon, $nextIcon, $toolTip, '');

	if ($showJump) {
		$out .= '<span class="spPageJump">';
		$site = wp_nonce_url(SPAJAXURL.'spForumPageJump&amp;targetaction=page-popup&amp;url='.SP()->forum->view->thisForum->forum_permalink.'&amp;max='.$totalPages, 'spPageJump');
		$out .= "<a id='jump-$jumpID' rel='nofollow' class='$jumpClass spForumPageJump' title='$jumpToolTip' data-site='$site' data-label='$jumpToolTip' data-width='200' data-height='0' data-align='0'>";
		$out .= $jumpIcon;
		$out .= '</a>';
		$out .= '</span>';
	}

	$out .= "</div>";
	$out = apply_filters('sph_TopicIndexPageLinks', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_TopicModeratorList()
#	Display the list of forum moderators
#	Scope:	Forum View
#	Version: 5.2
#
# --------------------------------------------------------------------------------------
function sp_TopicModeratorList($args = '', $label = '') {
	$defs = array('tagId'      => 'spForumModerators%ID%',
	              'tagClass'   => 'spForumModeratorList',
	              'listClass'  => 'spForumModeratorList',
	              'labelClass' => 'spForumModeratorLabel',
	              'showEmpty'  => 0,
	              'echo'       => 1,
	              'get'        => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_TopicModeratorList_args', $a);
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

	$out = apply_filters('sph_TopicModeratorList', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_TopicNewButton()
#	Display The New Topic Button
#	Scope:	Forum View
#	Version: 5.0
#
#	Changelog
#	5.2.3	Added 'statusClass' to icons with no action
#	5.4.2	Added 'iconDenied' argument
#			Added 'toolTipDenied' parameter
#
# --------------------------------------------------------------------------------------
function sp_TopicNewButton($args = '', $label = '', $toolTip = '', $toolTipLock = '', $toolTipDenied = '') {
	# can be empty if request is for a bogus forunm slug
	if (empty(SP()->forum->view->thisForum)) return;

	$defs = array('tagId'       => 'spTopicNewButton',
	              'tagClass'    => 'spButton',
	              'icon'        => 'sp_NewTopic.png',
	              'iconLock'    => 'sp_ForumStatusLock.png',
	              'iconDenied'  => 'sp_WriteDenied.png',
	              'iconClass'   => 'spIcon',
	              'statusClass' => 'spIconNoAction',
	              'echo'        => 1);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_TopicNewButton_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId       = esc_attr($tagId);
	$tagClass    = esc_attr($tagClass);
	$icon        = sanitize_file_name($icon);
	$iconClass   = esc_attr($iconClass);
	$statusClass = esc_attr($statusClass);
	$toolTip     = esc_attr($toolTip);
	$toolTipLock = esc_attr($toolTipLock);
	$echo        = (int) $echo;

	# is the forum locked?
	$out  = "<div id='$tagId'>";

	$lock = false;
	if (SP()->core->forumData['lockdown'] || SP()->forum->view->thisForum->forum_status) {
		if (!empty($iconLock)) {
			$iconLock = SP()->theme->paint_icon('$tagClass $iconClass $statusClass', SPTHEMEICONSURL, sanitize_file_name($iconLock), $toolTipLock);
			$out .= "<a class='$tagClass'>".SP()->theme->paint_icon_id($iconLock, $tagId).'</a>';
		}
		if (!SP()->user->thisUser->admin) $lock = true;
	}

	if (!$lock && SP()->auths->get('start_topics', SP()->forum->view->thisForum->forum_id)) {
		$out .= "<a class='$tagClass spNewTopicButton' title='$toolTip' data-form='spPostForm' data-type='topic'>";
		if (!empty($icon)) $out .= SP()->theme->paint_icon($iconClass, SPTHEMEICONSURL, $icon);
		if (!empty($label)) $out .= SP()->displayFilters->title($label);
		$out .= "</a>";
	}

	# Display if user not allowed to start topics
	if (!SP()->forum->view->thisForum->start_topics && !empty($toolTipDenied)) {
		if (!empty($iconDenied)) {
			$out .= "<a class='$tagClass' title='$toolTipDenied'>";
			$out .= SP()->theme->paint_icon($iconClass, SPTHEMEICONSURL, sanitize_file_name($iconDenied));
			$out .= "</a>";
		}
	}

	$out.= '</div>';

	$out = apply_filters('sph_TopicNewButton', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_TopicIndexIcon()
#	Display Topic Icon
#	Scope:	Topic Loop
#	Version: 5.0
#   Version: 5.5 added default locked topic icon
#
# --------------------------------------------------------------------------------------
function sp_TopicIndexIcon($args = '') {
	$defs = array('tagId'         => 'spTopicIndexIcon%ID%',
	              'tagClass'      => 'spRowIcon',
	              'icon'          => 'sp_TopicIcon.png',
	              'iconUnread'    => 'sp_TopicIconPosts.png',
	              'iconLocked'    => 'sp_TopicIconLocked.png',
	              'iconPinned'    => 'sp_TopicIconPinned.png',
	              'iconPinnedNew' => 'sp_TopicIconPinnedNew.png',
	              'echo'          => 1,
	              'get'           => 0);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_TopicIndexIcon_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId    = esc_attr($tagId);
	$tagClass = esc_attr($tagClass);
	$echo     = (int) $echo;
	$get      = (int) $get;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisTopic->topic_id, $tagId);

	$path = SPTHEMEICONSDIR;
	$url  = SPTHEMEICONSURL;
	
	$tIconType = 'file';
	
	if (SP()->forum->view->thisTopic->topic_status || SP()->forum->view->thisForum->forum_status) {
		$tIcon = sanitize_file_name($iconLocked);
		$topic_icon = spa_get_saved_icon( SP()->forum->view->thisForum->topic_icon_locked );
		
		if (!empty( $topic_icon['icon'] ) ) {
			$tIconType = $topic_icon['type'];
			$tIcon = 'file' === $topic_icon['type'] ? $topic_icon['icon'] : $topic_icon;
			$path  = SPCUSTOMDIR;
			$url   = SPCUSTOMURL;
		}
	} elseif (SP()->forum->view->thisTopic->topic_pinned && SP()->forum->view->thisTopic->unread) {
		$tIcon = sanitize_file_name($iconPinnedNew);
		$topic_icon = spa_get_saved_icon( SP()->forum->view->thisForum->topic_icon_pinned_new );
		if (!empty( $topic_icon['icon'] ) ) {
			$tIconType = $topic_icon['type'];
			$tIcon = 'file' === $topic_icon['type'] ? $topic_icon['icon'] : $topic_icon;
			$path  = SPCUSTOMDIR;
			$url   = SPCUSTOMURL;
		}
	} elseif (SP()->forum->view->thisTopic->topic_pinned) {
		$tIcon = sanitize_file_name($iconPinned);
		$topic_icon = spa_get_saved_icon( SP()->forum->view->thisForum->topic_icon_pinned );
		if (!empty( $topic_icon['icon'] ) ) {
			$tIconType = $topic_icon['type'];
			$tIcon = 'file' === $topic_icon['type'] ? $topic_icon['icon'] : $topic_icon;
			$path  = SPCUSTOMDIR;
			$url   = SPCUSTOMURL;
		}
	} elseif (SP()->forum->view->thisTopic->unread) {
		$tIcon = sanitize_file_name($iconUnread);
		$topic_icon = spa_get_saved_icon( SP()->forum->view->thisForum->topic_icon_new );
		if (!empty( $topic_icon['icon'] ) ) {
			$tIconType = $topic_icon['type'];
			$tIcon = 'file' === $topic_icon['type'] ? $topic_icon['icon'] : $topic_icon;
			$path  = SPCUSTOMDIR;
			$url   = SPCUSTOMURL;
		}
	} else {
		$tIcon = sanitize_file_name($icon);
		$topic_icon = spa_get_saved_icon( SP()->forum->view->thisForum->topic_icon );
		if (!empty( $topic_icon['icon'] ) ) {
			$tIconType = $topic_icon['type'];
			$tIcon = 'file' === $topic_icon['type'] ? $topic_icon['icon'] : $topic_icon;
			$path  = SPCUSTOMDIR;
			$url   = SPCUSTOMURL;
		}
	}

	
	if( 'file' ===  $tIconType ) {
		if (!file_exists($path.$tIcon)) {
			$tIcon = SP()->theme->paint_icon($tagClass, SPTHEMEICONSURL, $icon);
		} else {
			$tIcon = SP()->theme->paint_custom_icon($tagClass, $url.$tIcon);
		}
	} else {
		$tIcon = SP()->theme->sp_paint_iconset_icon( $tIcon, $tagClass );
	}
	
	if ($get) return $tIcon;

	$out = SP()->theme->paint_icon_id($tIcon, $tagId);
	$out = apply_filters('sph_TopicIndexIcon', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_TopicIndexName()
#	Display Topic Name/Title
#	Scope:	Topic Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_TopicIndexName($args = '', $toolTip = '') {
	$defs = array('tagId'    => 'spTopicIndexName%ID%',
	              'tagClass' => 'spRowName',
	              'truncate' => 0,
	              'echo'     => 1,
	              'get'      => 0);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_TopicIndexName_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId    = esc_attr($tagId);
	$tagClass = esc_attr($tagClass);
	$truncate = (int) $truncate;
	$toolTip  = esc_attr($toolTip);
	$echo     = (int) $echo;
	$get      = (int) $get;

	$tagId   = str_ireplace('%ID%', SP()->forum->view->thisTopic->topic_id, $tagId);
	$toolTip = str_ireplace('%NAME%', htmlspecialchars(SP()->forum->view->thisTopic->topic_name, ENT_QUOTES, SPCHARSET), $toolTip);

	if ($get) return SP()->primitives->truncate_name(SP()->forum->view->thisTopic->topic_name, $truncate);

	$out = "<a href='".SP()->forum->view->thisTopic->topic_permalink."' id='$tagId' class='$tagClass' title='$toolTip'>".SP()->primitives->truncate_name(SP()->forum->view->thisTopic->topic_name, $truncate)."</a>";
	$out = apply_filters('sph_TopicIndexName', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_TopicIndexPostPageLinks()
#	Display Topic 'in row' page links
#	Scope:	Topic sub Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_TopicIndexPostPageLinks($args = '', $toolTip = '') {
	$posts_per_page = SP()->core->forumData['display']['posts']['perpage'];
	if ($posts_per_page >= SP()->forum->view->thisTopic->post_count) return '';

	$defs = array('tagId'         => 'spTopicIndexPostPageLinks%ID%',
	              'tagClass'      => 'spInRowPageLinks',
	              'icon'          => 'sp_ArrowRightSmall.png',
	              'iconClass'     => 'spIconSmall',
	              'pageLinkClass' => 'spInRowForumPageLink',
	              'showLinks'     => 4,
	              'echo'          => 1);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_TopicIndexPostPageLinks_args', $a);
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

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisTopic->topic_id, $tagId);

	$out = "<div id='$tagId' class='$tagClass'>";

	$total_pages = (SP()->forum->view->thisTopic->post_count / $posts_per_page);
	if (!is_int($total_pages)) $total_pages = intval($total_pages) + 1;
	($total_pages > $showLinks ? $max_count = $showLinks : $max_count = $total_pages);
	for ($x = 1; $x <= $max_count; $x++) {
		$out .= "<a class='$pageLinkClass' href='".SP()->spPermalinks->build_url(SP()->forum->view->thisForum->forum_slug, SP()->forum->view->thisTopic->topic_slug, $x, 0)."' title='".str_ireplace('%PAGE%', $x, $toolTip)."'>$x</a>\n";
	}
	if ($total_pages > $showLinks) {
		if (!empty($icon)) $out .= SP()->theme->paint_icon($iconClass, SPTHEMEICONSURL, $icon);
		$out .= "<a class='$pageLinkClass' href='".SP()->spPermalinks->build_url(SP()->forum->view->thisForum->forum_slug, SP()->forum->view->thisTopic->topic_slug, $total_pages, 0)."' title='".str_ireplace('%PAGE%', $total_pages, $toolTip)."'>$total_pages</a>";
	}
	$out .= "</div>";

	$out = apply_filters('sph_TopicIndexPostPageLinks', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_TopicIndexPostCount()
#	Display Topic 'in row' total post count
#	Scope:	Topic Loop
#	Version: 5.0
#	Changelog:
#	5.2	0	Added 'before' and 'after' arguments
#	5.5.1   $rtlLabel parameter added
#
# --------------------------------------------------------------------------------------
function sp_TopicIndexPostCount($args = '', $label = '', $rtlLabel = '') {
	$defs = array('tagId'       => 'spTopicIndexPostCount%ID%',
	              'tagClass'    => 'spInRowCount',
	              'labelClass'  => 'spInRowLabel',
	              'numberClass' => 'spInRowNumber',
	              'stack'       => 0,
	              'before'      => '',
	              'after'       => '',
	              'echo'        => 1,
	              'get'         => 0);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_TopicIndexPostCount_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId       = esc_attr($tagId);
	$tagClass    = esc_attr($tagClass);
	$labelClass  = esc_attr($labelClass);
	$numberClass = esc_attr($numberClass);
	$stack       = (int) $stack;
	$before      = esc_attr($before);
	$after       = esc_attr($after);
	$echo        = (int) $echo;
	$get         = (int) $get;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisTopic->topic_id, $tagId);
	($stack ? $att = '<br />' : $att = ' ');

	if ($get) return SP()->forum->view->thisTopic->post_count;

	if (is_rtl() && SP()->forum->view->thisTopic->post_count == 1) $label = $rtlLabel;

	$out = "<div id='$tagId' class='$tagClass'>";
	$out .= "<span class='$labelClass'>".SP()->displayFilters->title($before).SP()->displayFilters->title($label)."$att</span>";
	$out .= "<span class='$numberClass'>".SP()->forum->view->thisTopic->post_count."</span>";
	$out .= "<span class='$labelClass'>".SP()->displayFilters->title($after)."</span>";
	$out .= "</div>";
	$out = apply_filters('sph_TopicIndexPostCount', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_TopicIndexReplyCount()
#	Display Topic 'in row' total reply count
#	Scope:	Topic Loop
#	Version: 5.5.2
#
# --------------------------------------------------------------------------------------
function sp_TopicIndexReplyCount($args = '', $label = '', $rtlLabel = '') {
	$defs = array('tagId'       => 'spTopicIndexReplyCount%ID%',
	              'tagClass'    => 'spInRowCount',
	              'labelClass'  => 'spInRowLabel',
	              'numberClass' => 'spInRowNumber',
	              'stack'       => 0,
	              'before'      => '',
	              'after'       => '',
	              'echo'        => 1,
	              'get'         => 0);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_TopicIndexReplyCount_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId       = esc_attr($tagId);
	$tagClass    = esc_attr($tagClass);
	$labelClass  = esc_attr($labelClass);
	$numberClass = esc_attr($numberClass);
	$stack       = (int) $stack;
	$before      = esc_attr($before);
	$after       = esc_attr($after);
	$echo        = (int) $echo;
	$get         = (int) $get;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisTopic->topic_id, $tagId);
	($stack ? $att = '<br />' : $att = ' ');

	$replies = (SP()->forum->view->thisTopic->post_count - 1);

	if ($get) return $replies;

	if (is_rtl() && $replies == 1) $label = $rtlLabel;

	$out = "<div id='$tagId' class='$tagClass'>";
	$out .= "<span class='$labelClass'>".SP()->displayFilters->title($before).SP()->displayFilters->title($label)."$att</span>";
	$out .= "<span class='$numberClass'>$replies</span>";
	$out .= "<span class='$labelClass'>".SP()->displayFilters->title($after)."</span>";
	$out .= "</div>";
	$out = apply_filters('sph_TopicIndexReplyCount', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_TopicIndexViewCount()
#	Display Topic 'in row' total view count
#	Scope:	Topic Loop
#	Version: 5.0
#	Changelog:
#	5.2.0	Added 'before' and 'after' arguments
#	5.5.1   $rtlLabel parameter added
#
# --------------------------------------------------------------------------------------
function sp_TopicIndexViewCount($args = '', $label = '', $rtlLabel = '') {
	$defs = array('tagId'       => 'spTopicIndexViewCount%ID%',
	              'tagClass'    => 'spInRowCount',
	              'labelClass'  => 'spInRowLabel',
	              'numberClass' => 'spInRowNumber',
	              'stack'       => 0,
	              'before'      => '',
	              'after'       => '',
	              'echo'        => 1,
	              'get'         => 0);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_TopicIndexViewCount_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId       = esc_attr($tagId);
	$tagClass    = esc_attr($tagClass);
	$labelClass  = esc_attr($labelClass);
	$numberClass = esc_attr($numberClass);
	$stack       = (int) $stack;
	$echo        = (int) $echo;
	$get         = (int) $get;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisTopic->topic_id, $tagId);
	($stack ? $att = '<br />' : $att = ' ');

	if ($get) return SP()->forum->view->thisTopic->topic_opened;

	if (is_rtl() && SP()->forum->view->thisTopic->topic_opened == 1) $label = $rtlLabel;

	$out = "<div id='$tagId' class='$tagClass'>";
	$out .= "<span class='$labelClass'>".SP()->displayFilters->title($before).SP()->displayFilters->title($label)."$att</span>";
	$out .= "<span class='$numberClass'>".SP()->forum->view->thisTopic->topic_opened."</span>";
	$out .= "<span class='$labelClass'>".SP()->displayFilters->title($after)."</span>";
	$out .= "</div>";
	$out = apply_filters('sph_TopicIndexViewCount', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_TopicIndexStatusIcons()
#	Display Topic Status (Locked/Pinned/New Posts)
#	Scope:	Topic Loop
#	Version: 5.0
#		5.2.3	added 'iconClass' argument
#		5.4.2	added 'iconDenied' argument
#				added 'toolTipDenied' parameter
#
# --------------------------------------------------------------------------------------
function sp_TopicIndexStatusIcons($args = '', $toolTipLock = '', $toolTipPin = '', $toolTipPost = '', $toolTipDenied = '') {
	$defs = array('tagId'      => 'spTopicIndexStatus%ID%',
	              'tagClass'   => 'spStatusIcon',
	              'iconClass'  => 'spIcon spIconNoAction',
	              'iconLock'   => 'sp_TopicStatusLock.png',
	              'iconPin'    => 'sp_TopicStatusPin.png',
	              'iconPost'   => 'sp_TopicStatusPost.png',
	              'iconDenied' => 'sp_WriteDenied.png',
	              'echo'       => 1);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_TopicIndexStatusIcons_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId         = esc_attr($tagId);
	$tagClass      = esc_attr($tagClass);
	$iconClass     = esc_attr($iconClass);
	$toolTipLock   = esc_attr($toolTipLock);
	$toolTipPin    = esc_attr($toolTipPin);
	$toolTipPost   = esc_attr($toolTipPost);
	$toolTipDenied = esc_attr($toolTipDenied);
	$echo          = (int) $echo;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisTopic->topic_id, $tagId);

	# Display if locked, pinned or new posts
	$out = "<div id='$tagId' class='$tagClass'>";

	if (SP()->core->forumData['lockdown'] || SP()->forum->view->thisTopic->topic_status) {
		if (!empty($iconLock)) {
			$out .= SP()->theme->paint_icon($iconClass, SPTHEMEICONSURL, sanitize_file_name($iconLock), $toolTipLock);
		}
	}

	if (SP()->forum->view->thisTopic->topic_pinned) {
		if (!empty($iconPin)) {
			$out .= SP()->theme->paint_icon($iconClass, SPTHEMEICONSURL, sanitize_file_name($iconPin), $toolTipPin);
		}
	}

	if (SP()->forum->view->thisTopic->unread) {
		if (!empty($iconPost)) {
			$out .= SP()->theme->paint_icon($iconClass, SPTHEMEICONSURL, sanitize_file_name($iconPost), $toolTipPost);
		}
	}

	if (!SP()->forum->view->thisTopic->reply_topics && !empty($toolTipDenied)) {
		$out .= SP()->theme->paint_icon($iconClass, SPTHEMEICONSURL, sanitize_file_name($iconDenied), $toolTipDenied);
	}

	$out = apply_filters('sph_TopicIndexStatusIconsLast', $out);
	$out .= "</div>";

	$out = apply_filters('sph_TopicIndexStatusIcons', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_TopicIndexFirstPost()
#	Display Topic 'in row' link to the first post made to a topic in this forum
#	Scope:	Topic Loop
#	Version: 5.0
#
#	Changelog:
#	5.1.0	- 'itemBreak' argument added
#	5.5.2	- 'labelLink' argument added
#	5.5.9	- 'beforeUser' argument added
#	5.5.9	- 'beforeDate' argument added
#	6.5.0	- 'stackLabelLink argument added - allows the label link to break after its painted so you can style it as a button without including the date and author.
#	6.5.0	= 'postIconLink' argument added - allows to disable the icon link.  The default is enabled or '1' for backwards compatibility.  
#	While You could have used the presence or absence of the 'icon' argument, that would mean potentially breaking backwards compatibility
#	or changing all other themes.  So we added this argument instead.
#
# --------------------------------------------------------------------------------------
function sp_TopicIndexFirstPost($args = '', $label = '') {
	$defs = array('tagId'        => 'spTopicIndexFirstPost%ID%',
	              'tagClass'     => 'spInRowPostLink',
	              'labelClass'   => 'spInRowLabel',
	              'infoClass'    => 'spInRowInfo',
	              'linkClass'    => 'spInRowFirstPostLink',
	              'iconClass'    => 'spIcon',
	              'icon'         => 'sp_ArrowRight.png',
	              'labelLink'    => 0,
				  'postIconLink' => 1,				  
	              'tip'          => 1,
	              'nicedate'     => 1,
	              'date'         => 0,
	              'time'         => 0,
	              'user'         => 1,
	              'stackuser'    => 1,
	              'stackdate'    => 0,
				  'stackLabelLink' => 0,
	              'truncateUser' => 0,
	              'itemBreak'    => '<br />',
	              'beforeUser'   => '&nbsp;',
	              'beforeDate'   => '',
	              'echo'         => 1,
	              'get'          => 0);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_TopicIndexFirstPost_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId        = esc_attr($tagId);
	$tagClass     = esc_attr($tagClass);
	$labelClass   = esc_attr($labelClass);
	$infoClass    = esc_attr($infoClass);
	$linkClass    = esc_attr($linkClass);
	$iconClass    = esc_attr($iconClass);
	$icon         = sanitize_file_name($icon);
	$labelLink    = (int) $labelLink;
	$postIconLink = (int) $postIconLink;
	$tip          = (int) $tip;
	$nicedate     = (int) $nicedate;
	$date         = (int) $date;
	$time         = (int) $time;
	$user         = (int) $user;
	$stackuser    = (int) $stackuser;
	$stackdate    = (int) $stackdate;
	$stackLabelink = (int) $stackLabelLink;
	$truncateUser = (int) $truncateUser;
	$echo         = (int) $echo;
	$get          = (int) $get;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisTopic->topic_id, $tagId);
	if ($tip && !empty(SP()->forum->view->thisTopic->first_post_tip)) {
		$title = "title='".SP()->forum->view->thisTopic->first_post_tip."'";
		$linkClass .= '';
	} else {
		$title = '';
	}

	($stackuser ? $ulb = '<br />' .$beforeUser : $ulb = $beforeUser);
	($stackdate ? $dlb = '<br />' .$beforeDate : $dlb = ' - ');
	
	($stackLabelLink ? $labelLinkBreak = '<br />' : $labelLinkBreak = '');

	$out = "<div id='$tagId' class='$tagClass'>";
	$out .= "<span class='$labelClass'>";
	
	# Link to post using label
	if ($labelLink) {
		$out .= "<a class='$linkClass' $title href='".SP()->forum->view->thisTopic->first_post_permalink."'> ";
	}
	$out .= SP()->displayFilters->title($label);
	if ($labelLink) {
		$out .= "</a>\n $labelLinkBreak";
	}

	# Link to post using icon
	if ($postIconLink) {
		$out .= "<a class='$linkClass' $title href='".SP()->forum->view->thisTopic->first_post_permalink."'>";
		if (!empty($icon)) $out .= SP()->theme->paint_icon($iconClass, SPTHEMEICONSURL, $icon);
		$out .= "</a></span>";
	}

	# user
	$poster = SP()->user->name_display(SP()->forum->view->thisTopic->first_user_id, SP()->primitives->truncate_name(SP()->forum->view->thisTopic->first_display_name, $truncateUser));
	if (empty($poster)) $poster = SP()->primitives->truncate_name(SP()->forum->view->thisTopic->first_guest_name, $truncateUser);
	if ($user) $out .= "<span class='$labelClass'>$ulb$poster</span>";
	$out .= $itemBreak;

	if ($get) {
		$getData             = new stdClass();
		$getData->permalink  = SP()->forum->view->thisTopic->first_post_permalink;
		$getData->topic_name = SP()->forum->view->thisTopic->topic_name;
		$getData->post_date  = SP()->forum->view->thisTopic->first_post_date;
		$getData->tooltip    = SP()->forum->view->thisTopic->first_post_tip;
		$getData->user       = $poster;

		return $getData;
	}

	# date/time
	if ($nicedate) {
		$out .= $beforeDate."<span class='$labelClass'>".SP()->dateTime->nice_date(SP()->forum->view->thisTopic->first_post_date)."</span>";
	} else {
		if ($date) {
			$out .= $beforeDate."<span class='$labelClass'>".SP()->dateTime->format_date('d', SP()->forum->view->thisTopic->first_post_date);
			if ($time) {
				$out .= $dlb.SP()->dateTime->format_date('t', SP()->forum->view->thisTopic->first_post_date);
			}
			$out .= "</span>";
		}
	}

	$out .= "</div>";
	$out = apply_filters('sph_TopicIndexFirstPost', $out, $a, $label);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_TopicIndexLastPost()
#	Display Topic 'in row' link to the last post made to a topic in this forum
#	Scope:	Topic Loop
#	Version: 5.0
#
#	Changelog:
#	5.1.0	- 'ItemBreak' argument added
#	5.5.2	- 'labelLink' argument added
#	5.5.9	- 'beforeUser' argument added
#	5.5.9	- 'beforeDate' argument added
#	6.5.0	- 'stackLabelLink argument added - allows the label link to break after its painted so you can style it as a button without including the date and author.
#	6.5.0	= 'postIconLink' argument added - allows to disable the icon link.  The default is enabled or '1' for backwards compatibility.  
#	While You could have used the presence or absence of the 'icon' argument, that would mean potentially breaking backwards compatibility
#	or changing all other themes.  So we added this argument instead.
#
# --------------------------------------------------------------------------------------
function sp_TopicIndexLastPost($args = '', $label = '') {
	# should never happen but check anyway
	if (empty(SP()->forum->view->thisTopic->last_post_permalink)) return;

	$defs = array('tagId'        => 'spTopicIndexLastPost%ID%',
	              'tagClass'     => 'spInRowPostLink',
	              'labelClass'   => 'spInRowLabel',
	              'infoClass'    => 'spInRowInfo',
	              'linkClass'    => 'spInRowLastPostLink',
	              'iconClass'    => 'spIcon',
	              'icon'         => 'sp_ArrowRight.png',
	              'labelLink'    => 0,
				  'postIconLink' => 1,
	              'tip'          => 1,
	              'nicedate'     => 1,
	              'date'         => 0,
	              'time'         => 0,
	              'user'         => 1,
	              'stackuser'    => 1,
	              'stackdate'    => 0,
				  'stackLabelLink' => 0,
	              'truncateUser' => 0,
	              'itemBreak'    => '<br />',
	              'beforeUser'   => '&nbsp;',
	              'beforeDate'   => '',
	              'echo'         => 1,
	              'get'          => 0);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_TopicIndexLastPost_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId        = esc_attr($tagId);
	$tagClass     = esc_attr($tagClass);
	$labelClass   = esc_attr($labelClass);
	$infoClass    = esc_attr($infoClass);
	$linkClass    = esc_attr($linkClass);
	$iconClass    = esc_attr($iconClass);
	$icon         = sanitize_file_name($icon);
	$labelLink    = (int) $labelLink;
	$postIconLink = (int) $postIconLink;
	$tip          = (int) $tip;
	$nicedate     = (int) $nicedate;
	$date         = (int) $date;
	$time         = (int) $time;
	$user         = (int) $user;
	$stackuser    = (int) $stackuser;
	$stackdate    = (int) $stackdate;
	$stackLabelink = (int) $stackLabelLink;
	$truncateUser = (int) $truncateUser;
	$echo         = (int) $echo;
	$get          = (int) $get;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisTopic->topic_id, $tagId);
	if ($tip && !empty(SP()->forum->view->thisTopic->last_post_tip)) {
		$title = "title='".SP()->forum->view->thisTopic->last_post_tip."'";
		$linkClass .= '';
	} else {
		$title = '';
	}

	($stackuser ? $ulb = '<br />' .$beforeUser : $ulb = $beforeUser);
	($stackdate ? $dlb = '<br />' .$beforeUser : $dlb = ' - ');

	($stackLabelLink ? $labelLinkBreak = '<br />' : $labelLinkBreak = '');
	
	$out = "<div id='$tagId' class='$tagClass'>";
	$out .= "<span class='$labelClass'>";

	# Link to post using the label
	if ($labelLink) {
		$out .= "<a class='$linkClass' $title href='".SP()->forum->view->thisTopic->last_post_permalink."'>";
	}
	$out .= SP()->displayFilters->title($label);
	if ($labelLink) {
		$out .= "</a>\n $labelLinkBreak";
	}

	# Link to post using icon
	if ($postIconLink) {
		$out .= "<a class='$linkClass' $title href='".SP()->forum->view->thisTopic->last_post_permalink."'>";
		if (!empty($icon)) $out .= SP()->theme->paint_icon($iconClass, SPTHEMEICONSURL, $icon);
		$out .= "</a></span>";
	}

	# user
	$poster = SP()->user->name_display(SP()->forum->view->thisTopic->last_user_id, SP()->primitives->truncate_name(SP()->forum->view->thisTopic->last_display_name, $truncateUser));
	if (empty($poster)) $poster = SP()->primitives->truncate_name(SP()->forum->view->thisTopic->last_guest_name, $truncateUser);
	if ($user) $out .= "<span class='$labelClass'>$ulb$poster</span>";
	$out .= $itemBreak;

	if ($get) {
		$getData             = new stdClass();
		$getData->permalink  = SP()->forum->view->thisTopic->last_post_permalink;
		$getData->topic_name = SP()->forum->view->thisTopic->topic_name;
		$getData->post_date  = SP()->forum->view->thisTopic->last_post_date;
		$getData->tooltip    = SP()->forum->view->thisTopic->last_post_tip;
		$getData->user       = $poster;

		return $getData;
	}

	# date/time
	if ($nicedate && isset(SP()->forum->view->thisTopic->last_post_date)) {
		$out .= $beforeDate."<span class='$labelClass'>".SP()->dateTime->nice_date(SP()->forum->view->thisTopic->last_post_date)."</span>";
	} else {
		if ($date && isset(SP()->forum->view->thisTopic->last_post_date)) {
			$out .= $beforeDate."<span class='$labelClass'>".SP()->dateTime->format_date('d', SP()->forum->view->thisTopic->last_post_date);
			if ($time) {
				$out .= $dlb.SP()->dateTime->format_date('t', SP()->forum->view->thisTopic->last_post_date);
			}
			$out .= "</span>";
		}
	}
	$out .= "</div>";
	$out = apply_filters('sph_TopicIndexLastPost', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_NoTopicsInForumMessage()
#	Display Message when no Topics are found in a Forum
#	Scope:	Topic Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_NoTopicsInForumMessage($args = '', $definedMessage = '') {
	$defs = array('tagId'    => 'spNoTopicsInForumMessage',
	              'tagClass' => 'spMessage',
	              'echo'     => 1,
	              'get'      => 0);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_NoTopicsInForumMessage_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId    = esc_attr($tagId);
	$tagClass = esc_attr($tagClass);
	$echo     = (int) $echo;
	$get      = (int) $get;

	if ($get) return $definedMessage;

	$out = "<div id='$tagId' class='$tagClass'>".SP()->displayFilters->title($definedMessage)."</div>";
	$out = apply_filters('sph_NoTopicsInForumMessage', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_TopicForumToolButton()
#	Display Topic Level Admin Tools Button
#	Scope:	Topic Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_TopicForumToolButton($args = '', $label = '', $toolTip = '') {
	if (SP()->core->forumData['lockdown'] == true && SP()->user->thisUser->admin == false) return;

	$show = false;
	if (SP()->user->thisUser->admin || SP()->user->thisUser->moderator) {
		$show = true;
	} else {
		if (SP()->auths->get('lock_topics', SP()->forum->view->thisForum->forum_id) || SP()->auths->get('pin_topics', SP()->forum->view->thisForum->forum_id) || SP()->auths->get('edit_any_topic_titles', SP()->forum->view->thisForum->forum_id) || SP()->auths->get('delete_topics', SP()->forum->view->thisForum->forum_id) || SP()->auths->get('move_topics', SP()->forum->view->thisForum->forum_id) || (SP()->auths->get('edit_own_topic_titles', SP()->forum->view->thisForum->forum_id) && SP()->forum->view->thisTopic->first_user_id == SP()->user->thisUser->ID)) {
			$show = true;
		}
	}
	$show = apply_filters('sph_forum_tools_forum_show', $show);
	if (!$show) return;

	$defs = array('tagId'          => 'spForumToolButton%ID%',
	              'tagClass'       => 'spToolsButton',
	              'icon'           => 'sp_ForumTools.png',
	              'iconClass'      => 'spIcon',
	              'hide'           => 1,
				  'stack'          => 0,
	              'containerClass' => 'spForumTopicSection');
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_TopicForumToolButton_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId          = esc_attr($tagId);
	$tagClass       = esc_attr($tagClass);
	$icon           = sanitize_file_name($icon);
	$iconClass      = esc_attr($iconClass);
	$containerClass = esc_attr($containerClass);
	$hide           = (int) $hide;
	$stack       = (int) $stack;
	$toolTip        = esc_attr($toolTip);
	$label          = SP()->displayFilters->title($label);

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisTopic->topic_id, $tagId);
	($stack ? $att = '<br />' : $att = ' ');

	$addStyle = '';
	if ($hide) $addStyle = " style='display:none;' ";

	$site  = wp_nonce_url(SPAJAXURL."spForumTopicTools&amp;targetaction=topictools&amp;topic=".SP()->forum->view->thisTopic->topic_id."&amp;forum=".SP()->forum->view->thisForum->forum_id."&amp;page=".SP()->forum->view->thisForum->display_page, 'spForumToolsMenu');
	$title = esc_attr(SP()->primitives->front_text('Forum Tools'));
	$out   = "$att" . "<a class='$tagClass spForumTopicTools' id='$tagId' title='$toolTip' rel='nofollow' $addStyle data-site='$site' data-label='$title' data-width='500' data-height='0' data-align='0'>";

	if (!empty($icon)) $out .= SP()->theme->paint_icon($iconClass, SPTHEMEICONSURL, $icon);
	if (!empty($label)) $out .= $label;
	$out .= "</a>";
	$out = apply_filters('sph_TopicForumToolButton', $out, $a);

	echo $out;

	# Add script to hover admin buttons - just once
	if (SP()->forum->view->thisForum->tools_flag && $hide) {
		?>
        <script>
			(function(spj, $, undefined) {
				spj.tool = {
					toolclass: '.<?php echo($containerClass); ?>'
				};
			}(window.spj = window.spj || {}, jQuery));
        </script>
		<?php
		add_action('wp_footer', 'spjs_AddTopicToolsHover');
		SP()->forum->view->thisForum->tools_flag = false;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_TopicEditorWindow()
#	Placeholder for the new topic editor window
#	Scope:	Forum View
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_TopicEditorWindow($addTopicForm) {
	SP()->rewrites->pageData['hiddeneditor'] = $addTopicForm['hide'];

	if ((SP()->auths->get('start_topics', SP()->forum->view->thisForum->forum_id)) && (!SP()->forum->view->thisForum->forum_status) && (!SP()->core->forumData['lockdown']) || SP()->user->thisUser->admin) {
		$out = '<a id="spEditFormAnchor"></a>'."";
		$out .= sp_add_topic($addTopicForm);
		echo $out;

		# inline js to open topic form if from the topic view (script below)
		if (isset($_GET['new']) && sanitize_text_field($_GET['new']) == 'topic') add_action('wp_footer', 'spjs_OpenTopicForm');
	}
}

# ======================================================================================
#
# INLINE SCRIPTS
#
# ======================================================================================

# --------------------------------------------------------------------------------------
# inline opens add topic window if called from topic view
# --------------------------------------------------------------------------------------
function spjs_OpenTopicForm() {
	?>
    <script>
 		(function(spj, $, undefined) {
			$(document).ready(function () {
				 spj.openEditor('spPostForm', 'topic');
			});
		}(window.spj = window.spj || {}, jQuery));
    </script>
	<?php
}

# --------------------------------------------------------------------------------------
# inline adds hover show event to admin tools button if hidden
# --------------------------------------------------------------------------------------
function spjs_AddTopicToolsHover() {
	# on mobile devices just show forum tools. otherwise, show on hover over row
	if (SP()->core->mobile) {
		?>
        <script>
			(function(spj, $, undefined) {
				$(document).ready(function () {
					var p = $(spj.tool.toolclass).position();
					$('.spToolsButton').css('left', p.left);
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
						var p = $(this).position();
						$('.spToolsButton', this).css('left', p.left);
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
