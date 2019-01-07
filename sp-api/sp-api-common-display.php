<?php
/*
Simple:Press
Desc:
$LastChangedDate: 2016-07-03 11:08:57 -0500 (Sun, 03 Jul 2016) $
$Rev: 14376 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# ==================================================================
#
# 	CORE: This file is loaded at CORE
#	SP Common Display Elements - shared front/back end
#
# ==================================================================

# Version: 5.0
function sp_render_group_forum_select($goURL=false, $valueURL=false, $showSelects=true, $showFirstRow=true, $firstRowLabel='', $id='', $class='', $length=40) {
    include_once SF_PLUGIN_DIR.'/forum/content/classes/sp-group-view-class.php';
	if (empty($firstRowLabel)) $firstRowLabel = sp_text('Select Forum').':';
	if (empty($class)) {
		$class= 'spControl';
		$indent = '&nbsp;&nbsp;';
	} else {
		$indent = '';
	}

	# load data and check if empty or denied
	$groups = new spGroupView('', false);
	if ($groups->groupViewStatus == 'no access' || $groups->groupViewStatus == 'no data') return;
    $level = 0;
    $out = '';

	if ($groups->pageData) {
		if ($showSelects) {
			$out = "<select id='$id' class='$class' name='$id' ";
    		if ($goURL) $out.= 'onchange="javascript:spjChangeURL(this)"';
    		$out.= ">\n";
		}
		if ($showFirstRow && $firstRowLabel) $out.= '<option>'.$firstRowLabel."</option>\n";
		foreach ($groups->pageData as $group) {
			$out.= '<optgroup class="spList" label="'.$indent.sp_create_name_extract($group->group_name, $length).'">'."\n";
			if ($group->forums) {
				foreach ($group->forums as $forum) {
					if ($valueURL) {
						$out.= '<option value="'.$forum->forum_permalink.'">';
					} else {
						$out.= '<option value="'.$forum->forum_id.'">';
					}
					$out.= str_repeat($indent, $level).sp_create_name_extract($forum->forum_name, $length)."</option>\n";
					if (!empty($forum->subforums)) $out.= sp_compile_forums($forum->subforums, $forum->forum_id, 1, $valueURL);
				}
			}
			$out.= '</optgroup>';
		}
		if ($showSelects) $out.= "</select>\n";
	}
	return $out;
}

# Version: 5.0
function sp_compile_forums($forums, $parent=0, $level=0, $valueURL=false) {
	$out = '';
	$indent = '&nbsp;&rarr;&nbsp;';
	foreach ($forums as $forum) {
		if ($forum->parent == $parent && $forum->forum_id != $parent) {
			if ($valueURL) {
				$out.= '<option value="'.$forum->forum_permalink.'">';
			} else {
				$out.= '<option value="'.$forum->forum_id.'">';
			}
			$out.= str_repeat($indent, $level).sp_create_name_extract($forum->forum_name)."</option>\n";
			if (!empty($forum->children)) $out.= sp_compile_forums($forums, $forum->forum_id, $level+1, $valueURL);
		}
	}
	return $out;
}

# Version: 5.5.7
function sp_compile_forums_mobile($forums, $parent=0, $level=0, $valueURL=false) {
	$out = '';
	$indent = '&nbsp;&nbsp;&nbsp;&nbsp;';
	foreach ($forums as $forum) {
		if ($forum->parent == $parent && $forum->forum_id != $parent) {
			$out.= '<p><a href="'.$forum->forum_permalink.'">';
			$out.= str_repeat($indent, $level).sp_create_name_extract($forum->forum_name)."</a></p>\n";
			if (!empty($forum->children)) $out.= sp_compile_forums_mobile($forums, $forum->forum_id, $level+1, $valueURL);
		}
	}
	return $out;
}

# ------------------------------------------------------------------
# sp_create_name_extract()
#
# Version: 5.0
# truncates a forum or topic name for display in Quicklinks
#	$name:		name of forum or topic
#	$length:	optional length - defaults to 40 characters
# ------------------------------------------------------------------
function sp_create_name_extract($name, $length=40) {
	$name = sp_filter_title_display($name);
	if (strlen($name) > $length) $name = substr($name, 0, $length).'&#8230;';
	return $name;
}

# ------------------------------------------------------------------
# sp_truncate()
#
# Version: 5.0
# truncates a forum or topic name for display and adds ellipsis
#	$name:		name of forum or topic
#	$length:	length to truncate to (required)
# ------------------------------------------------------------------
function sp_truncate($name, $length) {
	if ($length > 0) {
		if (strlen($name) > $length) $name = substr($name, 0, $length).'&#8230;';
	}
	return $name;
}

# ------------------------------------------------------------------
# sp_get_user_special_ranks()
#
# Version: 5.0
# returns an array of user special ranks
#
#	$userid:	user id to get the special rank
# ------------------------------------------------------------------
function sp_get_user_special_ranks($userid) {
	global $spPaths, $spGlobals;

	$userRanks = array();
	$memberRanks = sp_get_special_rank($userid);
    if (empty($spGlobals['special_rank']) || empty($memberRanks)) return $userRanks;

	$count = 0;
	foreach ($spGlobals['special_rank'] as $key => $rank) {
		if (is_array($memberRanks) && in_array($key, $memberRanks)) {
			$userRanks[$count]['badge'] = '';
			if ($rank['badge'] && file_exists(SF_STORE_DIR.'/'.$spPaths['ranks'].'/'.$rank['badge'])) {
				$userRanks[$count]['badge'] = esc_url(SFRANKS.$rank['badge']);
			}
			$userRanks[$count]['name'] = $key;
			$count++;
		}
	}
	return $userRanks;
}

# ------------------------------------------------------------------
# sp_get_user_forum_rank()
#
# Version: 5.0
# returns an array (single element) of the user/guest forum rank
# based on the post count
#
#	$usertype:	use type - can be admin, user or guest
#	$userid:	user id to get the special rank
#	$userposts:	if user, number of posts made
# ------------------------------------------------------------------
function sp_get_user_forum_rank($usertype, $userid, $userposts) {
	global $spPaths, $spGlobals;

	$forumRank = array();
	$forumRank[0]['badge'] = '';

	switch ($usertype) {
		case 'Admin':
			$forumRank[0]['name'] = sp_text('Admin').' ';
			break;

		case 'Moderator':
			$forumRank[0]['name'] = sp_text('Moderator').' ';
			break;

		case 'User':
			$forumRank[0]['name'] = sp_text('Member').' ';
			break;

		case 'Guest':
			$forumRank[0]['name'] = sp_text('Guest').' ';
			break;
	}

	# check for forum rank
	$rankdata = array();
	if ($usertype == 'User' && !empty($spGlobals['forum_rank'])) {
		# put into arrays to make easy to sort
		$index = 0;
		foreach ($spGlobals['forum_rank'] as $x => $info) {
			$rankdata['title'][$index] = $x;
			$rankdata['posts'][$index] = $info['posts'];
			$rankdata['badge'][$index] = '';
			if (isset($info['badge'])) $rankdata['badge'][$index] = $info['badge'];
			$index++;
		}
		# sort rankings
		array_multisort($rankdata['posts'], SORT_ASC, $rankdata['title'], $rankdata['badge']);

		# find ranking of current user
		for ($x = 0; $x < count($rankdata['posts']); $x++) {
			if ($userposts <= $rankdata['posts'][$x]) {
				if ($rankdata['badge'][$x] && file_exists(SF_STORE_DIR.'/'.$spPaths['ranks'].'/'.$rankdata['badge'][$x])) {
					$forumRank[0]['badge'] = esc_url(SFRANKS.$rankdata['badge'][$x]);
				}
				$forumRank[0]['name'] = $rankdata['title'][$x];
				break;
			}
		}
	}

	return $forumRank;
}

# ------------------------------------------------------------------
# sp_build_avatar_display()
#
# Version: 5.0
# Will attach profile, website or nothing to avatar
#	userid:		id of the user
#	avatar:		Avatar display code
#   link:       attachment to make (profile, website, none)
# ------------------------------------------------------------------
function sp_build_avatar_display($userid, $avatar, $link) {
	global $spVars;

	switch ($link) {
		case 'profile':
			# for profiles, do we have a user and can current user view a profile?
			$forumid = (!empty($spVars['forumid'])) ? $spVars['forumid'] : '';
			if (!empty($userid) && sp_get_auth('view_profiles', $forumid)) $avatar = sp_attach_user_profile_link($userid, $avatar);
			break;

		case 'website':
			# for website, do we have a user?
			if (!empty($userid)) $avatar = sp_attach_user_web_link($userid, $avatar);
			break;

		default:
			# fire action for plugins that might add other display type
			$avatar = apply_filters('sph_BuildAvatarDisplay_'.$link, $avatar, $userid);
			break;
	}

	$avatar = apply_filters('sph_BuildAvatarDisplay', $avatar, $userid);
	return $avatar;
}

# ------------------------------------------------------------------
# sp_attach_user_web_link()
#
# Version: 5.0
# Create a link to a users website if they have entered one in their
# profile record.
#	userid:		id of the user
#	targetitem:	user name, avatar or web icon - sent as code
#	returnitem:	return targetitem if nothing found
# ------------------------------------------------------------------
function sp_attach_user_web_link($userid, $targetitem, $returnitem=true) {
	global $session_weblink;

	# is the website url cached?
	$webSite = (empty($session_weblink[$userid])) ? $webSite = spdb_table(SFUSERS, "ID=$userid", 'user_url') : $session_weblink[$userid];
	if (empty($webSite)) $webSite = '#';

	# update cache (may be same)
	$session_weblink[$userid] = $webSite;

	# now attach the website url - ignoring if not defined
	if ($webSite != '#') {
		$webSite = sp_check_url($webSite);
		if (!empty($webSite)) {
			$content = "<a href='$webSite' class='spLink spWebLink' title=''>$targetitem</a>";
			$sffilters = sp_get_option('sffilters');
			if ($sffilters['sftarget']) $content = sp_filter_save_target($content);
			if ($sffilters['sfnofollow']) $content = sp_filter_save_nofollow($content);
			return $content;
		}
	}

	# No wesbite link exists
	if ($returnitem) {
		return $targetitem;
	} else {
		return '';
	}
}

# ------------------------------------------------------------------
# sp_attach_user_profile_link()
#
# Version: 5.0
# Create a link to a users profile using the global profile display
# settings
#	userid:		id of the user
#	targetitem:	user name, avatar or web icon - sent as code
# ------------------------------------------------------------------
function sp_attach_user_profile_link($userid, $targetitem) {
	if (!sp_get_auth('view_profiles')) return $targetitem;

	global $spDevice;

	$title = esc_attr(sp_text('Profile'));

	$sfprofile = sp_get_option('sfprofile');
    $mode = $sfprofile['displaymode'];

    # if display mode is BP or Mingle but they are not active, switch back to popup profile
    include_once(ABSPATH.'wp-admin/includes/plugin.php');
    if (($mode == 3 && !is_plugin_active('buddypress/bp-loader.php')) || ($mode == 6 && !is_plugin_active('mingle/mingle.php'))) {
        $mode = 1;
    }

	# for mobiles force a new page if popup is preferred
	if($spDevice == 'mobile' && $mode == 1) $mode=2;

	switch ($mode) {
		case 1:
			# SF Popup profile
			$site = wp_nonce_url(SPAJAXURL."profile&amp;targetaction=popup&amp;user=$userid", 'profile');
			$position = 'center';
			return "<a rel='nofollow' class='spLink spOpenDialog' title='$title' data-site='$site' data-label='$title' data-width='750' data-height='0' data-align='$position'>$targetitem</a>";

		case 2:
			# SF Profile page
			$site = sp_url('profile/'.$userid);
			return "<a href='$site' class='spLink spProfilePage' title='$title'>$targetitem</a>";

		case 3:
			# BuddyPress profile page
			$user = new WP_User($userid);

            # try to handle BP switches between username and login ussge
    		$username = bp_is_username_compatibility_mode() ? $user->user_login : $user->user_nicename;
            if (strstr($username, ' ')) {
                $username = $user->user_nicename;
            } else {
                $username = urlencode($username);
            }

            # build BP user profile based on bp options
            $bp = get_option('bp-pages');
            $baseurl = get_permalink($bp['members']);

			$site = user_trailingslashit($baseurl.str_replace(' ', '', $username).'/profile');
            $site = apply_filters('sph_buddypress_profile', $site, $user);
			return "<a href='$site' class='spLink spBPProfile' title='$title'>$targetitem</a>";

		case 4:
			# WordPress authors page
			$userkey = spdb_table(SFUSERS, "ID=$userid", 'user_nicename');
			if ($userkey) {
				$site = SFSITEURL.user_trailingslashit('author/'.$userkey);
				return "<a href='$site' class='spLink spWPProfile' title='$title'>$targetitem</a>";
			} else {
				return $targetitem;
			}

		case 5:
			# Handoff to user specified page
			if ($sfprofile['displaypage']) {
				$title = esc_attr(sp_text('Profile'));
				$out = "<a href='".$sfprofile['displaypage'];
				if ($sfprofile['displayquery']) $out.= '?'.sp_filter_title_display($sfprofile['displayquery']).'='.$userid;
				$out.= "' class='spLink spUserDefinedProfile' title='$title'>$targetitem</a>";
			} else {
				$out = $targetitem;
			}
			return $out;

		case 6:
			# Mingle profile page
			$user = new WP_User($userid);
			$site = SFSITEURL.user_trailingslashit(urlencode($user->user_login));
            $site = apply_filters('sph_mingle_profile', $site, $user);
			return "<a href='$site' class='spLink spMingleProfile' title='$title'>$targetitem</a>";

		default:
			# plugins offering new type?
			$targetitem = apply_filters('AttachUserProfileLink_'.$sfprofile['displaymode'], $targetitem, $userid);
			return $targetitem;
	}
}

# ------------------------------------------------------------------
# sp_build_name_display()
#
# Version: 5.0
# Cleans user name and attaches profile or website link if set
#	$userid:		id of the user
#	$username:		name of the user or guest
#	$link:			optional override to name linking
# ------------------------------------------------------------------
function sp_build_name_display($userid, $username, $linkNames=1) {
	global $spThisUser, $spVars;

	$username = apply_filters('sph_build_name_display', $username, $userid);

	if ($userid) {
		$profile = sp_get_option('sfprofile');

		if (sp_get_auth('view_profiles') && ($profile['namelink'] == 2 && $linkNames == 1)) {
            # link to profile
			return sp_attach_user_profile_link($userid, $username);
		} else if ($profile['namelink'] == 3) {
			# link to website
			return sp_attach_user_web_link($userid, $username);
		} else {
            $username = apply_filters('sph_build_name_display_option', $username, $userid);
		}
	}

	# neither permission or profile/web link
	return $username;
}

# ------------------------------------------------------------------
# sp_build_profile_formlink()
#
# Version: 5.0
# Create a link to the profile form preferred
#	$userid:		id of the user
# ------------------------------------------------------------------
function sp_build_profile_formlink($userid) {
	global $spThisUser;

	$sfprofile = sp_get_option('sfprofile');
    $mode = $sfprofile['formmode'];

    # if profile mode is BP or Mingle but they are not active, switch back to popup profile
    include_once(ABSPATH.'wp-admin/includes/plugin.php');
    if (($mode == 3 && !is_plugin_active('buddypress/bp-loader.php')) || ($mode == 5 && !is_plugin_active('mingle/mingle.php'))) {
        $mode = 1;
    }

	switch ($mode) {
		case 1:
			# SPF form
			$edit = '';
			if ($userid != $spThisUser->ID) {
				$user = new WP_User($userid);
				$edit = $user->ID.'/edit';
			}
			$site = sp_url('profile/'.$edit);
			return $site;

		case 2:
			# WordPress form
			return SFHOMEURL.'wp-admin/user-edit.php?user_id='.$userid;

		case 3:
			# BuddyPress profile page
			$user = new WP_User($userid);

            # try to handle BP switches between username and login ussge
    		$username = bp_is_username_compatibility_mode() ? $user->user_login : $user->user_nicename;
            if (strstr($username, ' ')) {
                $username = $user->user_nicename;
            } else {
                $username = urlencode($username);
            }

            # build BP user profile based on bp options
            $bp = get_option('bp-pages');
            $baseurl = get_permalink($bp['members']);

			$site = user_trailingslashit($baseurl.str_replace(' ', '', $username).'/profile');
            $site = apply_filters('sph_buddypress_profile', $site, $user);
			return $site;

		case 4:
			# Handoff to user specified form
			if ($sfprofile['formpage']) {
				$out = $sfprofile['formpage'];
				if ($sfprofile['formquery']) $out.= '?'.sp_filter_title_display($sfprofile['formquery']).'='.$userid;
			} else {
				$out = '';
			}
			return $out;

		case 5:
			# Mingle account page
			$user = new WP_User($userid);
			$site = SFSITEURL.user_trailingslashit('account');
            $site = apply_filters('sph_mingle_profile', $site, $user);
			return $site;
	}
}

# ------------------------------------------------------------------
# sp_date()
#
# Version: 5.0
# Formats a date and time for display
#	$type	't'=time  'd'=date
#	$data	The actual date string
# ------------------------------------------------------------------
function sp_date($type, $data) {
	if ($type == 'd') {
		return date_i18n(SFDATES, mysql2date('U', $data, false));
	} else {
		return date_i18n(SFTIMES, mysql2date('U', $data, false));
	}
}

# ------------------------------------------------------------------
# sp_get_topic_url()
#
# Version: 5.0
# Builds a topic url including all icons etc
#	$forumslug:		forum slug for url
#	$topicslug:		topic slug for url
#	etc.
# ------------------------------------------------------------------
function sp_get_topic_url($forumslug, $topicslug, $topicname) {
	global $spVars;

    $out = '';
	$topicname=sp_filter_title_display($topicname);
	if (isset($spVars['searchvalue']) && $spVars['searchvalue']) {
		$out.= '<a href="'.sp_build_url($forumslug, $topicslug, 1, 0);
		if (strpos(sp_url(), '?') === false) {
			$out.= '?value';
		} else {
			$out.= '&amp;value';
		}
		$out.= '='.$spVars['searchvalue'].'&amp;type='.$spVars['searchtype'].'&amp;include='.$spVars['searchinclude'].'&amp;scope='.'&amp;search='.$spVars['searchpage'].'">'.$topicname."</a>\n";
	} else {
		$out = '<a href="'.sp_build_url($forumslug, $topicslug, 1, 0).'">'.sp_filter_title_display($topicname)."</a>\n";
	}
	return $out;
}

# ------------------------------------------------------------------
# sp_open_grid()
# sp_close_grid()
# sp_open_grid_cell()
# sp_close_grid_cell()
#
# Version: 5.6
# Opens and closes popup grid cell
# ------------------------------------------------------------------
function sp_open_grid() {
	if(current_theme_supports('sp-theme-responsive')) {
		global $spCell;
		$spCell = 1;
		return '<div id="spGrid">';
	}
}

function sp_close_grid() {
	if(current_theme_supports('sp-theme-responsive')) {
		return '</div>';
	}
}

function sp_open_grid_cell() {
	if(current_theme_supports('sp-theme-responsive')) {
		global $spCell;

		$out = '';
		$out.= '<div class="spGridCell">';
		return $out;
	}
}

function sp_close_grid_cell() {
	if(current_theme_supports('sp-theme-responsive')) {
		global $spCell;

		$out = '</div>';
		if($spCell == 3) $spCell = 0;
		$spCell++;
		return $out;
	}
}

?>