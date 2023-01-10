<?php
/*
Simple:Press
Template Function Handler
$LastChangedDate: 2017-02-24 03:27:10 -0600 (Fri, 24 Feb 2017) $
$Rev: 15232 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# --------------------------------------------------------------------------------------
#
#	sp_NoMembersListMessage()
#	Display Message when no Member Lists can be displayed
#	Scope:	Members Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_NoMembersListMessage($args = '', $deniedMessage = '', $definedMessage = '') {
	$defs = array('tagId'    => 'spNoMembersMessage',
	              'tagClass' => 'spMessage',
	              'echo'     => 1,
	              'get'      => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_NoMembersListMessage_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId    = esc_attr($tagId);
	$tagClass = esc_attr($tagClass);
	$echo     = (int) $echo;
	$get      = (int) $get;

	# check for no access or not data
	if (SP()->forum->view->members->membersListStatus == 'no access') {
		$m = SP()->displayFilters->title($deniedMessage);
	} elseif (SP()->forum->view->members->membersListStatus == 'no data') {
		$m = SP()->displayFilters->title($definedMessage);
	} else {
		return;
	}

	if ($get) return $m;

	$out = "<div id='$tagId' class='$tagClass'>$m</div>";
	$out = apply_filters('sph_NoMembersListMessage', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_NoMemberMessage()
#	Display Message when no Members can be displayed
#	Scope:	Members Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_NoMemberMessage($args = '', $definedMessage = '') {
	$defs = array('tagId'    => 'spNoMembersMessage',
	              'tagClass' => 'spMessage',
	              'echo'     => 1,
	              'get'      => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_NoMembersMessage_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId    = esc_attr($tagId);
	$tagClass = esc_attr($tagClass);
	$echo     = (int) $echo;
	$get      = (int) $get;

	$m = SP()->displayFilters->title($definedMessage);

	if ($get) return $m;

	$out = "<div id='$tagId' class='$tagClass'>$m</div>";
	$out = apply_filters('sph_NoMembersMessage', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# ======================================================================================
#
# Member Group Loop Functions
#
# ======================================================================================

# --------------------------------------------------------------------------------------
#
#	sp_UsergroupIcon()
#	Display Group Icon
#	Scope:	Members Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_MembersUsergroupIcon($args = '') {
	if (!SP()->auths->get('view_members_list')) return;

	$defs = array('tagId'    => 'spUsergroupIcon%ID%',
	              'tagClass' => 'spHeaderIcon',
	              'icon'     => 'sp_MembersIcon.png',
	              'echo'     => 1,
	              'get'      => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_UsergroupIcon_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId    = esc_attr($tagId);
	$tagClass = esc_attr($tagClass);
	$icon     = sanitize_file_name($icon);
	$echo     = (int) $echo;
	$get      = (int) $get;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisMemberGroup->usergroup_id, $tagId);

	$icon = SP()->theme->paint_icon($tagClass, SPTHEMEICONSURL, sanitize_file_name($icon));

	if ($get) return $icon;

	$out = SP()->theme->paint_icon_id($icon, $tagId);
	$out = apply_filters('sph_UsergroupIcon', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_UsergroupName()
#	Display Usergroup Name/Title in Header
#	Scope:	Members Group Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_MembersUsergroupName($args = '') {
	if (!SP()->auths->get('view_members_list')) return;

	$defs = array('tagId'    => 'spUsergroupname%ID%',
	              'tagClass' => 'spHeaderName',
	              'echo'     => 1,
	              'get'      => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_UsergroupName_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId    = esc_attr($tagId);
	$tagClass = esc_attr($tagClass);
	$echo     = (int) $echo;
	$get      = (int) $get;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisMemberGroup->usergroup_id, $tagId);

	if ($get) return SP()->forum->view->thisMemberGroup->usergroup_name;

	$out = (empty(SP()->forum->view->thisMemberGroup->usergroup_name)) ? '' : "<div id='$tagId' class='$tagClass'>".SP()->forum->view->thisMemberGroup->usergroup_name."</div>";
	$out = apply_filters('sph_UsergroupName', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_UsergroupDescription()
#	Display Usergroup Description in Header
#	Scope:	Usergroup Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_MembersUsergroupDescription($args = '') {
	if (!SP()->auths->get('view_members_list')) return;

	$defs = array('tagId'    => 'spUsergroupDescription%ID%',
	              'tagClass' => 'spHeaderDescription',
	              'echo'     => 1,
	              'get'      => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_UsergroupDescription_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId    = esc_attr($tagId);
	$tagClass = esc_attr($tagClass);
	$echo     = (int) $echo;
	$get      = (int) $get;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisMemberGroup->usergroup_id, $tagId);

	if ($get) return SP()->forum->view->thisMemberGroup->usergroup_desc;

	$out = (empty(SP()->forum->view->thisMemberGroup->usergroup_desc)) ? '' : "<div id='$tagId' class='$tagClass'>".SP()->forum->view->thisMemberGroup->usergroup_desc."</div>";
	$out = apply_filters('sph_UsergroupDescription', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_MembersListName()
#	Display user name with link
#	Scope:	Members List loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_MembersListName($args = '') {
	if (!SP()->auths->get('view_members_list')) return;

	$defs = array('tagId'    => 'spMembersListName%ID%',
	              'tagClass' => 'spRowName',
	              'echo'     => 1,
	              'get'      => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_MembersListName_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId    = esc_attr($tagId);
	$tagClass = esc_attr($tagClass);
	$echo     = (int) $echo;
	$get      = (int) $get;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisMember->user_id, $tagId);

	if ($get) return SP()->forum->view->thisMember->display_name;

	$out = "<span id='$tagId' class='$tagClass'>".SP()->user->name_display(SP()->forum->view->thisMember->user_id, SP()->forum->view->thisMember->display_name)."</span>";
	$out = apply_filters('sph_MembersListName', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_MemberListPostCount()
#	Display user post count for memebers list
#	Scope:	Members List Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_MemberListPostCount($args = '', $label = '') {
	if (!SP()->auths->get('view_members_list')) return;

	$defs = array('tagId'       => 'spMembersListPostCount%ID%',
	              'tagClass'    => 'spInRowCount',
	              'labelClass'  => 'spInRowLabel',
	              'numberClass' => 'spInRowNumber',
	              'stack'       => 1,
	              'echo'        => 1,
	              'get'         => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_MemberListPostCount_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId       = esc_attr($tagId);
	$tagClass    = esc_attr($tagClass);
	$labelClass  = esc_attr($labelClass);
	$numberClass = esc_attr($numberClass);
	$stack       = (int) $stack;
	$echo        = (int) $echo;
	$get         = (int) $get;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisMember->user_id, $tagId);
	$att   = ($stack) ? '<br />' : ': ';

	$count = max(SP()->forum->view->thisMember->posts, 0);

	if ($get) return $count;

	$out = "<div id='$tagId' class='$tagClass'>";
	$out .= "<span class='$labelClass'>".SP()->displayFilters->title($label)."$att</span>";
	$out .= "<span class='$numberClass'>$count</span>";
	$out .= "</div>";
	$out = apply_filters('sph_MemberListPostCount', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_MemberListLastVisit()
#	Display user last visit for memebers list
#	Scope:	Members List Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_MemberListLastVisit($args = '', $label = '') {
	if (!SP()->auths->get('view_members_list')) return;

	$defs = array('tagId'      => 'spMembersListLastVisit%ID%',
	              'tagClass'   => 'spInRowCount',
	              'labelClass' => 'spInRowLabel',
	              'dateClass'  => 'spInRowDate',
	              'stack'      => 1,
	              'echo'       => 1,
	              'get'        => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_MemberListLastVisit_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId      = esc_attr($tagId);
	$tagClass   = esc_attr($tagClass);
	$labelClass = esc_attr($labelClass);
	$dateClass  = esc_attr($dateClass);
	$stack      = (int) $stack;
	$echo       = (int) $echo;
	$get        = (int) $get;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisMember->user_id, $tagId);
	$att   = ($stack) ? '<br />' : ': ';

	if ($get) return SP()->forum->view->thisMember->lastvisit;

	$out = "<div id='$tagId' class='$tagClass'>";
	$out .= "<span class='$labelClass'>".SP()->displayFilters->title($label)."$att</span>";
	$out .= "<span class='$dateClass'>".SP()->dateTime->format_date('d', SP()->forum->view->thisMember->lastvisit).$att.SP()->dateTime->format_date('t', SP()->forum->view->thisMember->lastvisit).'</span>';
	$out .= "</div>";
	$out = apply_filters('sph_MemberListLastVisit', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_MemberListRegistered()
#	Display user registration date for memebers list
#	Scope:	Members List Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_MemberListRegistered($args = '', $label = '') {
	if (!SP()->auths->get('view_members_list')) return;

	$defs = array('tagId'      => 'spMembersListRegistration%ID%',
	              'tagClass'   => 'spInRowCount',
	              'labelClass' => 'spInRowLabel',
	              'dateClass'  => 'spInRowDate',
	              'stack'      => 1,
	              'echo'       => 1,
	              'get'        => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_MemberListRegistered_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId      = esc_attr($tagId);
	$tagClass   = esc_attr($tagClass);
	$labelClass = esc_attr($labelClass);
	$dateClass  = esc_attr($dateClass);
	$stack      = (int) $stack;
	$echo       = (int) $echo;
	$get        = (int) $get;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisMember->user_id, $tagId);
	$att   = ($stack) ? '<br />' : ': ';

	if ($get) return SP()->forum->view->thisMember->user_registered;

	$out = "<div id='$tagId' class='$tagClass'>";
	$out .= "<span class='$labelClass'>".SP()->displayFilters->title($label)."$att</span>";
	$out .= "<span class='$dateClass'>".SP()->dateTime->format_date('d', SP()->forum->view->thisMember->user_registered).'<br />'.SP()->dateTime->format_date('t', SP()->forum->view->thisMember->user_registered).'</span>';
	$out .= "</div>";
	$out = apply_filters('sph_MemberListRegistered', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_MemberListUrl()
#	Display user registration date for memebers list
#	Scope:	Members List Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_MemberListUrl($args = '', $label = '') {
	if (!SP()->auths->get('view_members_list')) return;

	$defs = array('tagId'      => 'spMembersListURL%ID%',
	              'tagClass'   => 'spInRowCount',
	              'labelClass' => 'spInRowLabel',
	              'textClass'  => 'spInRowText',
	              'stack'      => 1,
	              'showIcon'   => 0,
	              'icon'       => 'sp_UserWebsite.png',
	              'iconClass'  => 'spImg',
	              'targetNew'  => 1,
	              'noFollow'   => 0,
	              'echo'       => 1,
	              'get'        => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_MemberListUrl_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId      = esc_attr($tagId);
	$tagClass   = esc_attr($tagClass);
	$labelClass = esc_attr($labelClass);
	$textClass  = esc_attr($textClass);
	$stack      = (int) $stack;
	$icon       = sanitize_file_name($icon);
	$iconClass  = esc_attr($iconClass);
	$targetNew  = (int) $targetNew;
	$noFollow   = (int) $noFollow;
	$echo       = (int) $echo;
	$get        = (int) $get;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisMember->user_id, $tagId);
	$att   = ($stack) ? '<br />' : ': ';

	if ($get) return SP()->forum->view->thisMember->user_url;

	$out = "<div id='$tagId' class='$tagClass'>";
	$out .= "<span class='$labelClass'>".SP()->displayFilters->title($label)."$att</span>";
	if ($showIcon && !empty($icon) && SP()->forum->view->thisMember->user_url != '') {
		$target = ($targetNew) ? ' target="_blank"' : '';
		$follow = ($noFollow) ? ' rel="nofollow"' : '';
		$out .= "<a id='$tagId' class='$textClass' href='".SP()->forum->view->thisMember->user_url."' title=''$target$follow>";
		$out .= SP()->theme->paint_icon($iconClass, SPTHEMEICONSURL, $icon);
		$out .= "</a>";
	} else {
		$out .= "<span class='$textClass'>".make_clickable(SP()->forum->view->thisMember->user_url).'</span>';
	}
	$out .= "</div>";
	$out = apply_filters('sph_MemberListUrl', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_MemberListRank()
#	Display user badges/ranks for members list
#	Scope:	Members List Loop
#	Version: 5.0
#
#	Changelog:
#	5.6.0	Added argument:
#				order	default = 'S'pecial 'N'ormal 'U'serGroup
#				showAll	default = 0 (false)
#   6.0.7	Added argument:
#				title	default = 1 (true) - whether or not to show the title of the badge.
# --------------------------------------------------------------------------------------
function sp_MemberListRank($args = '', $label = '') {
	if (!SP()->auths->get('view_members_list')) return;

	$defs = array('tagId'      => 'spMembersListRank%ID%',
	              'tagClass'   => 'spInRowCount',
	              'labelClass' => 'spInRowLabel',
	              'rank'       => 1,
	              'rankClass'  => 'spInRowRank',
	              'badge'      => 1,
				  'title'      => 1,
	              'badgeClass' => 'spImg',
	              'stack'      => 1,
	              'order'      => 'SNU',
	              'showAll'    => 0,
	              'echo'       => 1,
	              'get'        => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_MemberListRank_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId      = esc_attr($tagId);
	$tagClass   = esc_attr($tagClass);
	$labelClass = esc_attr($labelClass);
	$rankClass  = esc_attr($rankClass);
	$badgeClass = esc_attr($badgeClass);
	$rank       = (int) $rank;
	$badge      = (int) $badge;
	$title      = (int) $title;
	$stack      = (int) $stack;
	$order      = esc_attr($order);
	$showAll    = (int) $showAll;
	$echo       = (int) $echo;
	$get        = (int) $get;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisMember->user_id, $tagId);
	$att   = ($stack) ? '<br />' : '';
	$ranks = array();
	$idx   = 0;

	for ($x = 0; $x < (strlen($order)); $x++) {
		$xRank = substr($order, $x, 1);
		switch ($xRank) {
			case 'S': # Special Rank
				$rankData = SP()->user->special_ranks(SP()->forum->view->thisMember->user_id);
				if ($rankData) {
					foreach ($rankData as $r) {
						$ranks[$idx]['name'] = $r['name'];
						if ($r['badge']) $ranks[$idx]['badge'] = $r['badge'];
						$idx++;
					}
				}
				break;
			case 'N': # Normal Rank
				$usertype = (SP()->forum->view->thisMember->admin) ? 'Admin' : 'User';
				$rankData = SP()->user->forum_rank($usertype, SP()->forum->view->thisMember->user_id, SP()->forum->view->thisMember->posts);
				if ($rankData) {
					$ranks[$idx]['name'] = $rankData[0]['name'];
					if ($rankData[0]['badge']) $ranks[$idx]['badge'] = $rankData[0]['badge'];
					$idx++;
				}
	 
				break;
			case 'U': # UserGroup badge
				$rankData = SP()->user->get_memberships(SP()->forum->view->thisMember->user_id);
				if ($rankData) {
					foreach ($rankData as $r) {
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
	if ( is_array($ranks) ) {
		foreach ($ranks as $thisRank) {
			if (!empty($thisRank['badge']) && !empty($thisRank['badge']['type']) && 'font' === $thisRank['badge']['type']) {
				# Looks like image is an icon so paint it.
				$out .= SP()->theme->sp_paint_iconset_icon($thisRank['badge'], $badgeClass);
			} else {
				# Looks like image is a file so grab it from the sp-resources/forum-badges folder
				if (!empty($thisRank['badge']['icon'])) {
					$out .= "<img class='$badgeClass aa' src='".esc_url(SPRANKSIMGURL . $thisRank['badge']['icon'])."' alt='' />$att";
				}
			}				
			// if ($badge && !empty($thisRank['badge'])) $out .= "<img class='$badgeClass' src='".$thisRank['badge']."' alt='' />$att";
			if ($rank && $title) $out .= "<span class='$rankClass'>".$thisRank['name']."</span>$att";
		}
	}
	$out .= "</div>";
	$out = apply_filters('sph_MemberListRank', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_MemberListActions()
#	Display user actions for members list
#	Scope:	Members List Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_MemberListActions($args = '', $label = '', $startedToolTip = '', $postedToolTip = '') {
	if (!SP()->auths->get('view_members_list')) return;

	$defs = array('tagId'        => 'spMembersListActions%ID%',
	              'tagClass'     => 'spInRowNumber',
	              'labelClass'   => 'spInRowLabel',
	              'started'      => 1,
	              'startedIcon'  => 'sp_TopicsStarted.png',
	              'startedClass' => 'spIcon',
	              'posted'       => 1,
	              'postedIcon'   => 'sp_TopicsPosted.png',
	              'postedClass'  => 'spIcon',
	              'profile'      => 1,
	              'profileIcon'  => 'sp_ProfileForm.png',
	              'profileClass' => 'spIcon',
	              'stack'        => 0,
	              'echo'         => 1,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_MemberListActions_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	if (!empty($startedToolTip)) $startedToolTip = esc_attr($startedToolTip);
	if (!empty($postedToolTip)) $postedToolTip = esc_attr($postedToolTip);
	$tagId        = esc_attr($tagId);
	$tagClass     = esc_attr($tagClass);
	$labelClass   = esc_attr($labelClass);
	$startedClass = esc_attr($startedClass);
	$postedClass  = esc_attr($postedClass);
	$profileClass = esc_attr($profileClass);
	$started      = (int) $started;
	$posted       = (int) $posted;
	$profile      = (int) $profile;
	$startedIcon  = SP()->theme->paint_icon($startedClass, SPTHEMEICONSURL, sanitize_file_name($startedIcon), $startedToolTip);
	$postedIcon   = SP()->theme->paint_icon($postedClass, SPTHEMEICONSURL, sanitize_file_name($postedIcon), $postedToolTip);
	$profileIcon  = SP()->theme->paint_icon($profileClass, SPTHEMEICONSURL, sanitize_file_name($profileIcon));
	$echo         = (int) $echo;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisMember->user_id, $tagId);
	$att   = ($stack) ? '<br />' : '';

	# now render it
	$out = "<div id='$tagId' class='$tagClass'>";
	if (!empty($label)) $out .= "<span class='$labelClass'>".SP()->displayFilters->title($label)."<br /></span>";
	if ($started) {
		$param['forum']  = 'all';
		$param['value']  = SP()->forum->view->thisMember->user_id;
		$param['type']   = 5;
		$param['search'] = 1;
		$url             = add_query_arg($param, SP()->spPermalinks->get_url());
		$url             = SP()->filters->ampersand($url);
		$out .= "<a href='".esc_url($url)."'>";
		$out .= $startedIcon;
		$out .= $att."</a>";
	}

	if ($posted) {
		$param['forum']  = 'all';
		$param['value']  = SP()->forum->view->thisMember->user_id;
		$param['type']   = 4;
		$param['search'] = 1;
		$url             = add_query_arg($param, SP()->spPermalinks->get_url());
		$url             = SP()->filters->ampersand($url);
		$out .= "<a href='".esc_url($url)."'>";
		$out .= $postedIcon;
		$out .= $att."</a>";
	}

	if ($profile) {
		$link = $profileIcon.$att;
		$out .= sp_attach_user_profile_link(SP()->forum->view->thisMember->user_id, $link);
	}
	$out .= "</div>";
	$out = apply_filters('sph_MemberListActions', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_MemberListSearchForm()
#	Display member search form for the memebers list
#	Scope:	Members List Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_MemberListSearchForm($args = '') {

	if (!SP()->auths->get('view_members_list')) return;

	$defs = array('tagId'             => 'spMembersListSearchForm',
			      'containerClass'	  => '',
	              'tagClass'          => 'spForm',
	              'controlFieldset'   => '',
	              'controlInput'      => 'spControl',
	              'controlInputSize'  => 30,
	              'controlSubmit'     => 'spSubmit',
	              'controlAllMembers' => 'spSubmit',
	              'classLabel'        => 'spLabel',
	              'labelFormTitle'    => '',
	              'labelSearch'       => '',
	              'labelSearchSubmit' => '',
	              'labelSearchAll'    => '',
	              'classWildcard'     => 'spSearchDetails',
	              'labelWildcard'     => '',
	              'labelWildcardAny'  => '',
	              'labelWildcardChar' => '',
	              'echo'              => 1,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_MemberListSearchForm_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagid             = esc_attr($tagId);
	$containerClass	   = esc_attr($containerClass);
	$tagClass          = esc_attr($tagClass);
	$controlFieldset   = esc_attr($controlFieldset);
	$controlInput      = esc_attr($controlInput);
	$controlInputSize  = (int) $controlInputSize;
	$controlSubmit     = esc_attr($controlSubmit);
	$controlAllMembers = esc_attr($controlAllMembers);
	$classLabel        = esc_attr($classLabel);
	$labelFormTitle    = SP()->displayFilters->title($labelFormTitle);
	$labelSearch       = SP()->displayFilters->title($labelSearch);
	$labelSearchSubmit = SP()->displayFilters->title($labelSearchSubmit);
	$labelSearchAll    = SP()->displayFilters->title($labelSearchAll);
	$labelWildcard     = SP()->displayFilters->title($labelWildcard);
	$labelWildcardAny  = SP()->displayFilters->title($labelWildcardAny);
	$labelWildcardChar = SP()->displayFilters->title($labelWildcardChar);
	$echo              = (int) $echo;

	$search = (!empty($_POST['msearch']) && !isset($_POST['allmembers'])) ? SP()->filters->str($_POST['msearch']) : '';
	$search = (!empty($_GET['msearch'])) ? SP()->filters->str($_GET['msearch']) : $search;
	$ug     = (!empty($_POST['ug']) && !isset($_POST['allmembers'])) ? SP()->filters->integer($_POST['ug']) : '';
	$ug     = (!empty($_GET['ug'])) ? SP()->filters->integer($_GET['ug']) : $ug;

	$out = "<div id='$tagId' class='$containerClass'>";
	$out .= "<form class='$tagClass' action='".SPMEMBERLIST."' method='post' name='searchmembers'>";
	$out .= "<fieldset class='$controlFieldset'><legend>$labelFormTitle</legend>";
	$out .= "<label class='$classLabel' for='msearch'>$labelSearch</label>";
	$out .= "<input type='hidden' class='$controlInput' name='ug' id='ug' value='$ug' />";
	$out .= "<input type='text' class='$controlInput' name='msearch' id='msearch' size='$controlInputSize' value='$search' />";
	$out .= "<input type='submit' class='$controlSubmit' name='membersearch' id='membersearch' value='$labelSearchSubmit' />";
	$out .= "<input type='submit' class='$controlAllMembers' name='allmembers' id='allmembers' value='$labelSearchAll' />";
	$out .= sp_InsertBreak('echo=0');
	$out .= "<div class='$classWildcard'>$labelWildcard<br />$labelWildcardAny<br />$labelWildcardChar</div>";
	$out .= '</fieldset>';
	$out .= '</form>';
	$out .= "</div>";
	$out = apply_filters('sph_MemberListSearchForm', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_MemberListPageLinks()
#	Display page links for memebers list
#	Scope:	Members List Loop
#	Version: 5.0
#		5.2 Added tagId argument
#
# --------------------------------------------------------------------------------------
function sp_MemberListPageLinks($args = '', $label = '', $toolTip = '') {
	if (!SP()->auths->get('view_members_list')) return;

	$defs = array('tagId'         => 'spMemberPageLinks',
	              'tagClass'      => 'spPageLinks',
	              'prevIcon'      => 'sp_ArrowLeft.png',
	              'nextIcon'      => 'sp_ArrowRight.png',
	              'iconClass'     => 'spIcon',
	              'pageLinkClass' => 'spPageLinks',
	              'curPageClass'  => 'spCurrent',
	              'showLinks'     => 4,
	              'echo'          => 1,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_MemberListPageLinks_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId         = esc_attr($tagId);
	$tagClass      = esc_attr($tagClass);
	$iconClass     = esc_attr($iconClass);
	$pageLinkClass = esc_attr($pageLinkClass);
	$curPageClass  = esc_attr($curPageClass);
	$showLinks     = (int) $showLinks;
	$label         = SP()->displayFilters->title($label);
	$toolTip       = esc_attr($toolTip);
	$echo          = (int) $echo;

	if (!empty($prevIcon)) $prevIcon = SP()->theme->paint_icon($iconClass, SPTHEMEICONSURL, sanitize_file_name($prevIcon), $toolTip);
	if (!empty($nextIcon)) $nextIcon = SP()->theme->paint_icon($iconClass, SPTHEMEICONSURL, sanitize_file_name($nextIcon), $toolTip);

	$curToolTip = str_ireplace('%PAGE%', SP()->rewrites->pageData['page'], $toolTip);

	if (isset($_POST['allmembers'])) {
		$search = '';
		$ug     = '';
	} else {
		if (isset($_GET['page'])) SP()->rewrites->pageData['page'] = SP()->filters->integer($_GET['page']);
		$search = (!empty($_POST['msearch'])) ? SP()->filters->str($_POST['msearch']) : '';
		$search = (!empty($_GET['msearch'])) ? SP()->filters->str($_GET['msearch']) : $search;
		$ug     = (!empty($_POST['ug'])) ? SP()->filters->integer($_POST['ug']) : '';
		$ug     = (!empty($_GET['ug'])) ? SP()->filters->integer($_GET['ug']) : $ug;
	}

	$out        = "<div id='$tagId' class='$tagClass'>";
	$totalPages = (SP()->forum->view->members->totalMemberCount / SP()->forum->view->members->membersNumber);
	if (!is_int($totalPages)) $totalPages = (intval($totalPages) + 1);
	if ($label) $out .= "<span class='$pageLinkClass'>$label</span>";
	$out .= sp_page_prev(SP()->rewrites->pageData['page'], $showLinks, SPMEMBERLIST, $pageLinkClass, $iconClass, $prevIcon, $nextIcon, $toolTip, $search, $ug);

	$url = SPMEMBERLIST;
	if (SP()->rewrites->pageData['page'] > 1) $url = user_trailingslashit(trailingslashit($url).'page-'.SP()->rewrites->pageData['page']);
	$url = apply_filters('sph_page_link', $url, SP()->rewrites->pageData['page']);

	if (!empty($search)) {
		$param['msearch'] = $search;
		$url              = add_query_arg($param, esc_url($url));
		$url              = SP()->filters->ampersand($url);
	}
	if (!empty($ug)) {
		$param['ug'] = $ug;
		$url         = add_query_arg($param, esc_url($url));
		$url         = SP()->filters->ampersand($url);
	}
	$out .= "<a href='$url' class='$pageLinkClass $curPageClass' title='$curToolTip'>".SP()->rewrites->pageData['page'].'</a>';

	$out .= sp_page_next(SP()->rewrites->pageData['page'], $totalPages, $showLinks, SPMEMBERLIST, $pageLinkClass, $iconClass, $prevIcon, $nextIcon, $toolTip, $search, $ug);
	$out .= "</div>";
	$out = apply_filters('sph_MemberListPageLinks', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_MemberListUsergroupSelect()
#	Display page links for memebers list
#	Scope:	Members List Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_MemberListUsergroupSelect($args = '') {
	if (empty(SP()->forum->view->members->userGroups)) return;
	if (!SP()->auths->get('view_members_list')) return;

	$defs = array('tagId'       => 'spUsergroupSelect',
	              'tagClass'    => 'spUsergroupSelect',
	              'selectClass' => 'spControl',
	              'echo'        => 1,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_MemberListUsergroupSelect_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId       = esc_attr($tagId);
	$tagClass    = esc_attr($tagClass);
	$selectClass = esc_attr($selectClass);
	$echo        = (int) $echo;

	$search  = (!empty($_POST['msearch']) && !isset($_POST['allmembers'])) ? '&amp;msearch='.SP()->filters->str($_POST['msearch']) : '';
	$search  = (!empty($_GET['msearch'])) ? '&amp;msearch='.SP()->filters->str($_GET['msearch']) : $search;
	$ug      = (!empty($_POST['ug']) && !isset($_POST['allmembers'])) ? SP()->filters->integer($_POST['ug']) : '';
	$ug      = (!empty($_GET['ug'])) ? SP()->filters->integer($_GET['ug']) : $ug;
	$guestUG = SP()->meta->get('default usergroup', 'sfguests');
	$out     = "<div id='$tagId' class='$tagClass'>";
	$out .= "<select class='$selectClass' name='sp_usergroup_select' id='sp_usergroup_select'>";
	$out .= "<option value='#'>".SP()->primitives->front_text('Select Specific Usergroup')."</option>";
	foreach (SP()->forum->view->members->userGroups as $usergroup) {
		if ($usergroup['usergroup_id'] != $guestUG[0]['meta_value']) {
			$selected = ($usergroup['usergroup_id'] == $ug) ? "selected='selected'" : '';
			$out .= "<option $selected value='".SP()->spPermalinks->get_query_url(SP()->spPermalinks->get_url('members')).'ug='.$usergroup['usergroup_id'].$search."'>".SP()->displayFilters->title($usergroup['usergroup_name']).'</option>';
		}
	}
	if (!empty($ug)) $out .= "<option value='".SP()->spPermalinks->get_query_url(SP()->spPermalinks->get_url('members')).$search."'>".SP()->primitives->front_text('Reset to Default Usergroups')."</option>";
	$out .= '</select>';
	$out .= "</div>";
	$out = apply_filters('sph_MemberListUsergroupSelect', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}
