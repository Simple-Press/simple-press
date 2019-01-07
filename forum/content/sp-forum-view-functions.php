<?php
/*
Simple:Press
TForum View Function Handler
$LastChangedDate: 2016-08-07 13:14:25 -0500 (Sun, 07 Aug 2016) $
$Rev: 14476 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');


# ======================================================================================
#
# FORUM VIEW
# Sub-Forum Head Functions
#
# ======================================================================================

# --------------------------------------------------------------------------------------
#
#	sp_SubForumHeaderDescription()
#	Display SubForum Description in Header
#	Scope:	SubForum Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_SubForumHeaderDescription($args='', $label='') {
	$defs = array('tagId' 		=> 'spSubForumHeaderDescription',
				  'tagClass' 	=> 'spHeaderDescription',
				  'echo'		=> 1,
				  'get'			=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_SubForumHeaderDescription_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$echo		= (int) $echo;
	$get		= (int) $get;

	$label = sp_filter_title_display($label);

	if ($get) return $label;

	$out = (empty($label)) ? '' : "<div id='$tagId' class='$tagClass'>$label</div>\n";
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
function sp_SubForumIndexIcon($args='') {
	global $spThisForum, $spThisSubForum;

	$defs = array('tagId' 		=> 'spSubForumIndexIcon%ID%',
				  'tagClass' 	=> 'spRowIcon',
				  'icon' 		=> 'sp_ForumIcon.png',
				  'iconUnread'	=> 'sp_ForumIcon.png',
				  'echo'		=> 1,
				  'get'			=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_SubForumIndexIcon_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$echo		= (int) $echo;
	$get		= (int) $get;

	$tagId = str_ireplace('%ID%', $spThisSubForum->forum_id, $tagId);

	# Check if a custom icon
	$path = SPTHEMEICONSDIR;
	$url = SPTHEMEICONSURL;
	if ($spThisSubForum->unread) {
		$fIcon = sanitize_file_name($iconUnread);
		if (!empty($spThisSubForum->forum_icon_new)) {
			$fIcon = sanitize_file_name($spThisSubForum->forum_icon_new);
			$path = SFCUSTOMDIR;
			$url = SFCUSTOMURL;
		}
	} else {
		$fIcon = sanitize_file_name($icon);
		if (!empty($spThisSubForum->forum_icon)) {
			$fIcon = sanitize_file_name($spThisSubForum->forum_icon);
			$path = SFCUSTOMDIR;
			$url = SFCUSTOMURL;
		}
	}
	if (!file_exists($path.$fIcon)) {
		$fIcon = sp_paint_icon($tagClass, SPTHEMEICONSURL, sanitize_file_name($fIcon));
	} else {
		$fIcon = sp_paint_custom_icon($tagClass, $url.$fIcon);
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
function sp_SubForumIndexName($args='', $toolTip='') {
	global $spThisForum, $spThisSubForum;
	$defs = array('tagId'    	=> 'spSubForumIndexName%ID%',
			      'tagClass' 	=> 'spRowName',
			      'truncate'	=> 0,
				  'echo'		=> 1,
				  'get'			=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_SubForumIndexName_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$toolTip	= esc_attr($toolTip);
	$truncate	= (int) $truncate;
	$echo		= (int) $echo;
	$get		= (int) $get;

	$tagId = str_ireplace('%ID%', $spThisSubForum->forum_id, $tagId);
	$toolTip = str_ireplace('%NAME%', htmlspecialchars($spThisSubForum->forum_name, ENT_QUOTES, SFCHARSET), $toolTip);

	if ($get) return sp_truncate($spThisSubForum->forum_name, $truncate);

	$out = "<a href='$spThisSubForum->forum_permalink' id='$tagId' class='$tagClass' title='$toolTip'>".sp_truncate($spThisSubForum->forum_name, $truncate)."</a>\n";
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
function sp_SubForumIndexDescription($args='') {
	global $spThisForum, $spThisSubForum;
	$defs = array('tagId'    	=> 'spSubForumIndexDescription%ID%',
			      'tagClass' 	=> 'spRowDescription',
				  'echo'		=> 1,
				  'get'			=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ForumDescription_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$echo		= (int) $echo;
	$get		= (int) $get;

	$tagId = str_ireplace('%ID%', $spThisSubForum->forum_id, $tagId);

	if ($get) return $spThisSubForum->forum_desc;

	$out = (empty($spThisSubForum->forum_desc)) ? '' : "<div id='$tagId' class='$tagClass'>$spThisSubForum->forum_desc</div>\n";
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
function sp_SubForumIndexPageLinks($args='', $toolTip='') {
	global $spThisForum, $spThisSubForum, $spGlobals;

	$topics_per_page = $spGlobals['display']['topics']['perpage'];
	if ($topics_per_page >= $spThisSubForum->topic_count) return '';

	$defs = array('tagId'    		=> 'spSubForumIndexPageLinks%ID%',
				  'tagClass' 		=> 'spInRowPageLinks',
				  'icon'			=> 'sp_ArrowRightSmall.png',
				  'iconClass'		=> 'spIconSmall',
				  'pageLinkClass'	=> 'spInRowForumPageLink',
				  'showLinks'		=> 4,
				  'echo'			=> 1,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_SubForumIndexPageLinks_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId			= esc_attr($tagId);
	$tagClass		= esc_attr($tagClass);
	$icon			= sanitize_file_name($icon);
	$iconClass		= esc_attr($iconClass);
	$pageLinkClass	= esc_attr($pageLinkClass);
	$showLinks		= (int) $showLinks;
	$toolTip		= esc_attr($toolTip);
	$echo			= (int) $echo;

	$tagId = str_ireplace('%ID%', $spThisSubForum->forum_id, $tagId);

	$out = "<div id='$tagId' class='$tagClass'>\n";
	$total_pages=($spThisSubForum->topic_count / $topics_per_page);
	if (!is_int($total_pages)) $total_pages=intval($total_pages) + 1;
	($total_pages > $showLinks ? $max_count = $showLinks : $max_count = $total_pages);

	for ($x = 1; $x <= $max_count; $x++) {
		$out.= "<a class='$pageLinkClass' href='".sp_build_url($spThisSubForum->forum_slug, '', $x, 0)."' title='".str_ireplace('%PAGE%', $x, $toolTip)."'>$x</a>\n";
	}
	if ($total_pages > $showLinks) {
		if (!empty($icon)) $out.= sp_paint_icon($iconClass, SPTHEMEICONSURL, $icon);
		$out.= "<a class='$pageLinkClass' href='".sp_build_url($spThisSubForum->forum_slug, '', $total_pages, 0)."' title='".str_ireplace('%PAGE%', $total_pages, $toolTip)."'>$total_pages</a>\n";
	}
	$out.= "</div>\n";

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
function sp_SubForumIndexModerators($args='', $label='') {
	global $spGlobals, $spThisForum;
	$defs = array('tagId'    		=> 'spSubForumModerators%ID%',
				  'tagClass' 		=> 'spSubForumModeratorList',
				  'listClass'		=> 'spInRowLabel',
				  'labelClass'		=> 'spRowDescription',
                  'showEmpty'       => 0,
				  'echo'			=> 1,
				  'get'				=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_SubForumIndexModerators_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId			= esc_attr($tagId);
	$tagClass		= esc_attr($tagClass);
	$listClass		= esc_attr($listClass);
	$labelClass		= esc_attr($labelClass);
	$showEmpty		= (int) $showEmpty;
	$echo			= (int) $echo;
	$get			= (int) $get;

	$tagId = str_ireplace('%ID%', $spThisForum->forum_id, $tagId);

	$mods = $spGlobals['forum_moderators']['users'][$spThisForum->forum_id];
	if ($get) return $mods;

    # build mods list with name display
    if (!empty($mods)) {
        $modList = '';
        $first = true;
        foreach ($mods as $mod) {
            if (!$first) $modList.= ', ';
            $first = false;
            $modList.= sp_build_name_display($mod['user_id'], $mod['display_name']);
        }
    } else if ($showEmpty) {
        $modList = 'none';
    } else {
        return '';
    }

	$out = "<div id='$tagId' class='$tagClass'>\n";
	$out.= "<span class='$labelClass'>".sp_filter_title_display($label)."</span>\n";
	$out.= "<span class='$listClass'>$modList</span>\n";
	$out.= "</div>\n";
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
function sp_SubForumIndexLastPost($args='', $lastPostLabel='', $noTopicsLabel='') {
	global $spThisForum, $spThisSubForum;

	if ($spThisSubForum->post_count == 0 && $spThisSubForum->post_count_sub == 0) return;

	$defs = array('tagId'    		=> 'spSubForumIndexLastPost%ID%',
				  'tagClass' 		=> 'spInRowPostLink',
				  'labelClass'		=> 'spInRowLabel',
				  'infoClass'		=> 'spInRowInfo',
				  'linkClass'		=> 'spInRowLastPostLink',
				  'labelLink'		=> 0,
				  'includeSubs'		=> 1,
				  'tip'   			=> 1,
				  'order'			=> 'UTD',
				  'nicedate'		=> 1,
				  'date'  			=> 0,
				  'time'  			=> 0,
				  'stackdate'		=> 0,
				  'user'  			=> 1,
				  'truncate'		=> 0,
				  'truncateUser'	=> 0,
				  'itemBreak'		=> '<br />',
				  'echo'			=> 1,
				  'get'				=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_SubForumIndexLastPost_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId			= esc_attr($tagId);
	$tagClass		= esc_attr($tagClass);
	$labelClass		= esc_attr($labelClass);
	$infoClass		= esc_attr($infoClass);
	$linkClass		= esc_attr($linkClass);
	$labelLink		= (int) $labelLink;
	$includeSubs	= (int) $includeSubs;
	$tip			= (int) $tip;
	$order			= esc_attr($order);
	$nicedate		= (int) $nicedate;
	$date			= (int) $date;
	$time			= (int) $time;
	$stackdate		= (int) $stackdate;
	$user			= (int) $user;
	$truncate		= (int) $truncate;
	$truncateUser	= (int) $truncateUser;
	$echo			= (int) $echo;
	$get			= (int) $get;

	if ($includeSubs && $spThisSubForum->forum_id_sub == 0) $includeSubs = 0;
	$postCount = ($includeSubs ? $spThisSubForum->post_count_sub : $spThisSubForum->post_count);

	$tagId = str_ireplace('%ID%', $spThisSubForum->forum_id, $tagId);
	$posttip = ($includeSubs ? $spThisSubForum->post_tip_sub : $spThisSubForum->post_tip);
	if ($tip && !empty($posttip)) {
		$title = "title='$posttip'";
		$linkClass.= '';
	} else {
		$title='';
	}

	($stackdate ? $dlb='<br />' : $dlb=' - ');

	# user
	$poster = ($includeSubs ? sp_build_name_display($spThisSubForum->user_id_sub, sp_truncate($spThisSubForum->display_name_sub, $truncateUser)) : sp_build_name_display($spThisSubForum->user_id, sp_truncate($spThisSubForum->display_name, $truncateUser)));
	if (empty($poster)) $poster = ($includeSubs ? sp_truncate($spThisSubForum->guest_name_sub, $truncateUser) : sp_truncate($spThisSubForum->guest_name, $truncateUser));

	# other items
	$permalink = ($includeSubs ? $spThisSubForum->post_permalink_sub : $spThisSubForum->post_permalink);
	$topicname = ($includeSubs ? sp_truncate($spThisSubForum->topic_name_sub, $truncate) : sp_truncate($spThisSubForum->topic_name, $truncate));
	$postdate  = ($includeSubs ? $spThisSubForum->post_date_sub : $spThisSubForum->post_date);

	if ($get) {
		$getData = new stdClass();
		$getData->permalink = $permalink;
		$getData->topic_name = $topicname;
		$getData->post_date = $postdate;
		$getData->user = $poster;
		return $getData;
	}

	$U = $poster;
	$T = "<a class='$linkClass' $title href='$permalink'>$topicname</a>";
	if ($nicedate) {
		$D = sp_nicedate($postdate);
	} else {
		if ($date) {
			$D = sp_date('d', $postdate);
			if ($time) {
				$D.= $dlb.sp_date('t', $postdate);
			}
		}
	}

	$out = "<div id='$tagId' class='$tagClass'>\n";
	if ($postCount) {
		$out.= "<span class='$labelClass'>\n";
		if($labelLink) {
			$out.= "<a class='$linkClass' $title href='$permalink'>\n";
		}
		$out.= sp_filter_title_display($lastPostLabel)." \n";
		if($labelLink) {
			$out.= "</a>\n";
		}

		for($x=0; $x<strlen($order); $x++) {
			$i = substr($order, $x, 1);
			switch($i) {
				case 'U':
					if ($user) {
						if ($x != 0) $out.= "<span class='$labelClass'>";
						$out.= $U. "</span>\n";
					}
					if ($x != (strlen($order)-1)) {
						if (substr($order, $x+1, 1) != 'L') {
							$out.= $itemBreak;
						}
					}
					break;
				case 'T':
					if ($x == 0) $out.= "</span>".$itemBreak;
					$out.= "<span class='$linkClass'>";

					$out.= $T. "</span>\n";
					if ($x != (strlen($order)-1)) {
						if (substr($order, $x+1, 1) != 'L') {
							$out.= $itemBreak;
						}
					}
					break;
				case 'D':
					if ($x != 0) $out.= "<span class='$labelClass'>";
					$out.= $D. "</span>\n";
					if ($x != (strlen($order)-1)) {
						if (substr($order, $x+1, 1) != 'L') {
							$out.= $itemBreak;
						}
					}
					break;
				case 'L':
					$out.= '<br />';
					break;
			}
		}
	} else {
		$out.= "<span class='$labelClass'>".sp_filter_title_display($noTopicsLabel)." \n";
		$out.= "</span>\n";
	}

	$out.= "</div>\n";
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
function sp_SubForumIndexTopicCount($args='', $label='') {
	global $spThisForum, $spThisSubForum;
	$defs = array('tagId'    		=> 'spSubForumIndexTopicCount%ID%',
				  'tagClass' 		=> 'spInRowCount',
				  'labelClass'		=> 'spInRowLabel',
				  'numberClass'		=> 'spInRowNumber',
				  'includeSubs'		=> 1,
				  'stack'			=> 0,
				  'echo'			=> 1,
				  'get'				=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_SubForumIndexTopicCount_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId			= esc_attr($tagId);
	$tagClass		= esc_attr($tagClass);
	$labelClass		= esc_attr($labelClass);
	$numberClass	= esc_attr($numberClass);
	$includeSubs	= (int) $includeSubs;
	$stack			= (int) $stack;
	$echo			= (int) $echo;
	$get			= (int) $get;

	if ($includeSubs && $spThisSubForum->forum_id_sub == 0) $includeSubs = 0;

	$tagId = str_ireplace('%ID%', $spThisSubForum->forum_id, $tagId);
	($stack ? $att='<br />' : $att= ' ');

	$data = ($includeSubs ? $spThisSubForum->topic_count_sub : $spThisSubForum->topic_count);
	if ($get) return $data;

	$out = "<div id='$tagId' class='$tagClass'>\n";
	$out.= "<span class='$labelClass'>".sp_filter_title_display($label)."$att</span>\n";
	$out.= "<span class='$numberClass'>$data</span>\n";
	$out.= "</div>\n";
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
function sp_SubForumIndexPostCount($args='', $label='') {
	global $spThisForum, $spThisSubForum;
	$defs = array('tagId'    		=> 'spSubForumIndexPostCount%ID%',
				  'tagClass' 		=> 'spInRowCount',
				  'labelClass'		=> 'spInRowLabel',
				  'numberClass'		=> 'spInRowNumber',
				  'includeSubs'		=> 1,
				  'stack'			=> 0,
				  'echo'			=> 1,
				  'get'				=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_SubForumIndexPostCount_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId			= esc_attr($tagId);
	$tagClass		= esc_attr($tagClass);
	$labelClass		= esc_attr($labelClass);
	$numberClass	= esc_attr($numberClass);
	$includeSubs	= (int) $includeSubs;
	$stack			= (int) $stack;
	$echo			= (int) $echo;
	$get			= (int) $get;

	if ($includeSubs && $spThisSubForum->forum_id_sub == 0) $includeSubs = 0;

	$tagId = str_ireplace('%ID%', $spThisSubForum->forum_id, $tagId);
	($stack ? $att='<br />' : $att= ' ');

	$data = ($includeSubs ? $spThisSubForum->post_count_sub : $spThisSubForum->post_count);
	if ($get) return $data;

	$out = "<div id='$tagId' class='$tagClass'>\n";
	$out.= "<span class='$labelClass'>".sp_filter_title_display($label)."$att</span>\n";
	$out.= "<span class='$numberClass'>$data</span>\n";
	$out.= "</div>\n";
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
function sp_SubForumIndexStatusIcons($args='', $toolTipLock='', $toolTipPost='', $toolTipAdd='') {
	global $spThisForum, $spThisSubForum,$spGlobals;

	$defs = array('tagId' 			=> 'spForumIndexStatus%ID%',
				  'tagClass' 		=> 'spStatusIcon',
				  'iconLock'		=> 'sp_ForumStatusLock.png',
				  'iconPost'		=> 'sp_ForumStatusPost.png',
				  'iconAdd'		    => 'sp_ForumStatusAdd.png',
                  'first'           => 0,
				  'echo'			=> 1,
				  'get'				=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_SubForumIndexStatusIcons_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId			= esc_attr($tagId);
	$tagClass		= esc_attr($tagClass);
	$toolTipPost	= esc_attr($toolTipPost);
	$toolTipLock	= esc_attr($toolTipLock);
	$toolTipAdd	    = esc_attr($toolTipAdd);
	$first   	    = (int) $first;
	$echo			= (int) $echo;
	$get			= (int) $get;

	$tagId = str_ireplace('%ID%', $spThisSubForum->forum_id, $tagId);

	if ($get) return $spThisSubForum->forum_status;

	# Dislay if locked or new post
	$out = "<div id='$tagId' class='$tagClass'>\n";
	if ($spThisSubForum->forum_status == 1 || $spGlobals['lockdown'] == true) {
		$out.= sp_paint_icon('', SPTHEMEICONSURL, sanitize_file_name($iconLock), $toolTipLock);
	}

	# New Post Popup
	if ($spThisSubForum->unread) {
		$toolTipPost = str_ireplace('%COUNT%', $spThisSubForum->unread, $toolTipPost);
		$site = wp_nonce_url(SPAJAXURL."spUnreadPostsPopup&amp;targetaction=forum&amp;id=$spThisSubForum->forum_id&amp;first=$first", 'spUnreadPostsPopup');
		$linkId = 'spNewPostPopup'.$spThisSubForum->forum_id;
		$out.= "<a rel='nofollow' id='$linkId' class='spUnreadPostsPopup' data-popup='1' data-site='$site' data-label='$toolTipPost' data-width='600' data-height='0' data-align='0'>";
		$out.= sp_paint_icon('', SPTHEMEICONSURL, sanitize_file_name($iconPost), $toolTipPost);
	}

    # add new topic icon
	if (sp_get_auth('start_topics', $spThisSubForum->forum_id) && !$spThisSubForum->forum_status && !$spGlobals['lockdown']) {
		$url = sp_build_url($spThisSubForum->forum_slug, '', 1, 0).sp_add_get().'new=topic';
		$out.= "<a href='$url' title='$toolTipAdd'>\n";
		$out.= sp_paint_icon('', SPTHEMEICONSURL, sanitize_file_name($iconAdd));
		$out.= "</a>\n";
	}

	$out = apply_filters('sph_SubForumIndexStatusIconsLast', $out);

	$out.= "</div>\n";

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
function sp_SubForumIndexLockIcon($args='', $toolTip='') {
	global $spThisSubForum, $spGlobals, $spThisUser;

	$defs = array('tagId' 			=> 'spForumIndexLockIcon%ID%',
				  'tagClass' 		=> 'spIcon',
				  'statusClass'		=> 'spIconNoAction',
				  'icon'			=> 'sp_ForumStatusLock.png',
				  'echo'			=> 1,
				  'get'				=> 0
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_SubForumIndexLockIcon_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId			= esc_attr($tagId);
	$tagClass		= esc_attr($tagClass);
	$statusClass	= esc_attr($statusClass);
	$icon			= sanitize_file_name($icon);
	$echo			= (int) $echo;
	$get			= (int) $get;

	$tagId = str_ireplace('%ID%', $spThisSubForum->forum_id, $tagId);

	if ($get) return $spThisSubForum->forum_status;
	$out='';

	if ($spGlobals['lockdown'] || $spThisSubForum->forum_status)  {
		$out = "<div id='$tagId' class='$tagClass $statusClass' title='$toolTip' >\n";
		# Dislay if global lock down or forum locked
		if (!empty($icon)) $out.= sp_paint_icon('', SPTHEMEICONSURL, $icon);
		$out.= "</div>\n";
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
function sp_SubForumIndexAddIcon($args='', $toolTip='') {
	global $spThisSubForum, $spGlobals, $spThisUser;

	$defs = array('tagId' 			=> 'spForumIndexAddIcon%ID%',
				  'tagClass' 		=> 'spIcon',
				  'icon'			=> 'sp_ForumStatusAdd.png',
				  'echo'			=> 1,
				  'get'				=> 0
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_SubForumIndexAddIcon_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId			= esc_attr($tagId);
	$tagClass		= esc_attr($tagClass);
	$icon			= sanitize_file_name($icon);
	$echo			= (int) $echo;

	$tagId = str_ireplace('%ID%', $spThisSubForum->forum_id, $tagId);
	$out='';

    # add new topic icon
	if (sp_get_auth('start_topics', $spThisSubForum->forum_id) && ((!$spThisSubForum->forum_status && !$spGlobals['lockdown']) || $spThisUser->admin)) {
		$url = sp_build_url($spThisSubForum->forum_slug, '', 1, 0).sp_add_get().'new=topic';
		$out.= "<a id='$tagId' class='$tagClass' title='$toolTip' href='$url'>\n";
		if (!empty($icon)) $out.= sp_paint_icon('', SPTHEMEICONSURL, $icon);
		$out.= "</a>\n";
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
function sp_SubForumIndexPostsIcon($args='', $toolTip='') {
	global $spThisSubForum, $spGlobals, $spThisUser;

	if (!$spThisSubForum->unread) return;

	$defs = array('tagId' 		=> 'spForumIndexPostsIcon%ID%',
				  'tagClass' 	=> 'spIcon',
				  'icon'		=> 'sp_ForumStatusPost.png',
				  'openIcon' 	=> 'sp_GroupOpen.png',
				  'closeIcon' 	=> 'sp_GroupClose.png',
				  'popup'		=> 1,
                  'first'       => 0,
				  'echo'		=> 1
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_SubForumIndexPostsIcon_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId			= esc_attr($tagId);
	$tagClass		= esc_attr($tagClass);
	$popupIcon		= sp_paint_icon('', SPTHEMEICONSURL, sanitize_file_name($icon));
	$openIcon		= sp_paint_file_icon(SPTHEMEICONSURL, sanitize_file_name($openIcon));
	$closeIcon		= sp_paint_file_icon(SPTHEMEICONSURL, sanitize_file_name($closeIcon));
	$popup			= (int) $popup;
	$first   	    = (int) $first;
	$echo			= (int) $echo;

	$tagId = str_ireplace('%ID%', $spThisSubForum->forum_id, $tagId);
	$out='';

    # show new posts icon
	if ($spThisSubForum->unread) {
		$toolTip = esc_attr(str_ireplace('%COUNT%', $spThisSubForum->unread, $toolTip));
		$site = wp_nonce_url(SPAJAXURL."spUnreadPostsPopup&amp;targetaction=forum&amp;id=$spThisSubForum->forum_id&amp;popup=$popup&amp;first=$first", 'spUnreadPostsPopup');
		$linkId = 'spNewPostPopup'.$spThisSubForum->forum_id;
		$target = 'spInlineTopics'.$spThisSubForum->forum_id;
		$spinner = SFCOMMONIMAGES.'working.gif';

		if ($popup) {
            $out.= "<a  id='$tagId' class='$tagClass' title='$toolTip' rel='nofollow' id='$linkId' data-popup='1' data-site='$site' data-label='$toolTip' data-width='600' data-height='0' data-align='0'";
			$out.= $popupIcon;
		} else {
            $out.= "<a id='$tagId' class='$tagClass spUnreadPostsPopup' title='$toolTip' rel='nofollow' id='$linkId' data-popup='0' data-site='$site' data-target='$target' data-spinner='$spinner' data-id='$tagId' data-open='$openIcon' data-close='$closeIcon'>";
			$out.= "<img src='$openIcon' alt=''>";
		}
		$out.= "</a>\n";
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
	global $spThisSubForum;
	echo "<div class='spInlineTopics' id='spInlineTopics".$spThisSubForum->forum_id."' style='display:none;'></div>";
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
function sp_ForumHeaderSubForums($args='', $label='', $toolTip='') {
	global $spThisForum, $spThisForumSubs, $spThisSubForum;

	if (empty($spThisForumSubs) || empty($spThisSubForum->children)) return;

	$defs = array('tagId'    		=> 'spForumHeaderSubForums%ID%',
				  'tagClass' 		=> 'spInRowSubForums',
				  'labelClass'		=> 'spInRowLabel',
				  'linkClass'		=> 'spInRowSubForumlink',
				  'icon'			=> 'sp_SubForumIcon.png',
				  'unreadIcon'		=> 'sp_SubForumIcon.png',
				  'iconClass'		=> 'spIconSmall',
				  'topicCount'		=> 1,
				  'allNested'		=> 1,
				  'stack'			=> 0,
				  'truncate'		=> 0,
				  'echo'			=> 1,
				  'get'				=> 0
				 );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ForumHeaderSubForums_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId			= esc_attr($tagId);
	$tagClass		= esc_attr($tagClass);
	$labelClass		= esc_attr($labelClass);
	$linkClass		= esc_attr($linkClass);
	$icon			= sanitize_file_name($icon);
	$unreadIcon		= sanitize_file_name($unreadIcon);
	$iconClass		= esc_attr($iconClass);
	$topicCount		= (int) $topicCount;
	$allNested		= (int) $allNested;
	$stack			= (int) $stack;
	$truncate		= (int) $truncate;
	$echo			= (int) $echo;
	$get			= (int) $get;
	$toolTip		= esc_attr($toolTip);

	$thisTagId = str_ireplace('%ID%', $spThisSubForum->forum_id, $tagId);

	if ($get) return $spThisForumSubs;

	$out = "<div id='$thisTagId' class='$tagClass'>\n";
	if ($stack) {
		$out.= "<ul class='$labelClass'><li>".sp_filter_title_display($label)."<ul>";
	} else {
		$out.= "<span class='$labelClass'>".sp_filter_title_display($label)."</span>\n";
	}

	$tout = '';

	foreach ($spThisForumSubs as $sub) {
		if ($spThisSubForum->forum_id != $sub->top_sub_parent) {
			# skip this one - not in this subforum branch
			continue;
		}
		if (($allNested == 0 && $sub->parent == $spThisSubForum->forum_id)
			|| ($allNested == 1 && $sub->top_parent == $spThisSubForum->parent && $sub->forum_id != $spThisSubForum->forum_id)) {
			$thisToolTip = str_ireplace('%NAME%', htmlspecialchars($sub->forum_name, ENT_QUOTES, SFCHARSET), $toolTip);
			if ($stack) $tout.= "<li>";

            if ($sub->unread) {
    			if (!empty($unreadIcon)) $tout.= sp_paint_icon($iconClass, SPTHEMEICONSURL, $unreadIcon);
            } else {
    			if (!empty($icon)) $tout.= sp_paint_icon($iconClass, SPTHEMEICONSURL, $icon);
            }

			$thisTagId = str_ireplace('%ID%', $sub->forum_id, $tagId);
			$tout.= "<a href='$sub->forum_permalink' id='$thisTagId' class='$linkClass' title='$thisToolTip'>".sp_truncate($sub->forum_name, $truncate)."</a>\n";
			if ($topicCount) $tout.= " ($sub->topic_count)\n";
			if ($stack) $tout.= "</li>";
		}
	}

	if (empty($tout)) return;
	$out.= $tout;

	if ($stack) $out.= "</ul></li></ul>";
	$out.= "</div>\n";
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
function sp_ForumHeaderIcon($args='') {
	global $spThisForum;
	$defs = array('tagId' 		=> 'spForumHeaderIcon',
				  'tagClass' 	=> 'spHeaderIcon',
				  'icon' 		=> 'sp_ForumIcon.png',
				  'echo'		=> 1,
				  'get'			=> 0
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ForumHeaderIcon_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$echo		= (int) $echo;
	$get		= (int) $get;

	# Check if a custom icon
	if (!empty($spThisForum->forum_icon)) {
		$icon = sp_paint_custom_icon($tagClass, SFCUSTOMURL.$spThisForum->forum_icon);
	} else {
		$icon = sp_paint_icon($tagClass, SPTHEMEICONSURL, sanitize_file_name($icon));
	}

	if ($get) return $icon;

	if (!empty($icon)) $out = sp_paint_icon_id($icon, $tagId);
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
function sp_ForumHeaderName($args='') {
	global $spThisForum;
	$defs = array('tagId' 		=> 'spForumHeaderName',
				  'tagClass' 	=> 'spHeaderName',
				  'truncate'	=> 0,
				  'echo'		=> 1,
				  'get'			=> 0
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ForumHeaderName_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$truncate	= (int) $truncate;
	$echo		= (int) $echo;
	$get		= (int) $get;

	if ($get) return $spThisForum->forum_name;

	$out = (empty($spThisForum->forum_name)) ? '' : "<div id='$tagId' class='$tagClass'>".sp_truncate($spThisForum->forum_name, $truncate)."</div>\n";
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
function sp_ForumHeaderDescription($args='') {
	global $spThisForum;
	$defs = array('tagId' 		=> 'spForumHeaderDescription',
				  'tagClass' 	=> 'spHeaderDescription',
				  'echo'		=> 1,
				  'get'			=> 0
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ForumHeaderDescription_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$echo		= (int) $echo;
	$get		= (int) $get;

	if ($get) return $spThisForum->forum_desc;

	$out = (empty($spThisForum->forum_desc)) ? '' : "<div id='$tagId' class='$tagClass'>$spThisForum->forum_desc</div>\n";
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
function sp_ForumHeaderRSSButton($args='', $label='', $toolTip='') {
	global $spThisForum, $spThisUser;

    if (!sp_get_auth('view_forum', $spThisForum->forum_id) || $spThisForum->forum_rss_private) return;

	$defs = array('tagId' 		=> 'spForumHeaderRSSButton',
				  'tagClass' 	=> 'spButton',
				  'icon' 		=> 'sp_Feed.png',
				  'iconClass'	=> 'spIcon',
				  'echo'		=> 1,
				  'get'			=> 0
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ForumHeaderRSSButton_args', $a);
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
	if (empty($spThisForum->rss)) {
		$rssOpt = sp_get_option('sfrss');
		if ($rssOpt['sfrssfeedkey'] && isset($spThisUser->feedkey)) {
			$rssUrl = trailingslashit(sp_build_url($spThisForum->forum_slug, '', 0, 0, 0, 1)).user_trailingslashit($spThisUser->feedkey);
		} else {
			$rssUrl = sp_build_url($spThisForum->forum_slug, '', 0, 0, 0, 1);
		}
	} else {
		$rssUrl = $spThisForum->rss;
	}

	if ($get) return $rssUrl;

	$out = "<a class='$tagClass' id='$tagId' title='$toolTip' rel='nofollow' href='$rssUrl'>";
	if (!empty($icon)) $out.= sp_paint_icon($iconClass, SPTHEMEICONSURL, $icon);
	if (!empty($label)) $out.= sp_filter_title_display($label);
	$out.= "</a>\n";
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
function sp_ForumHeaderMessage($args='') {
	global $spThisForum;
	$defs = array('tagId' 		=> 'spForumHeaderMessage%ID%',
				  'tagClass' 	=> 'spHeaderMessage',
				  'echo'		=> 1,
				  'get'			=> 0
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ForumHeaderMessage_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$echo		= (int) $echo;
	$get		= (int) $get;

	$tagId = str_ireplace('%ID%', $spThisForum->forum_id, $tagId);

	if ($get) return $spThisForum->forum_message;

	$out = (empty($spThisForum->forum_message)) ? '' : "<div id='$tagId' class='$tagClass'>$spThisForum->forum_message</div>\n";
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
function sp_NoForumMessage($args='', $deniedMessage='', $definedMessage='') {
	global $spForumView;
	$defs = array('tagId'		=> 'spNoForumMessage',
				  'tagClass'	=> 'spMessage',
				  'echo'		=> 1,
				  'get'			=> 0
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_NoForumMessage_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$echo		= (int) $echo;
	$get		= (int) $get;

	# is Access denied
	if ($spForumView->forumViewStatus == 'no access') {
		$m = sp_filter_title_display($deniedMessage);
	} elseif ($spForumView->forumViewStatus == 'no data') {
		$m = sp_filter_title_display($definedMessage);
	} elseif ($spForumView->forumViewStatus == 'sneak peek') {
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
function sp_TopicIndexPageLinks($args='', $label='', $toolTip='', $jumpToolTip='') {
	global $spThisForum, $spGlobals, $jumpID;

	# can be empty if request is for a bogus forunm slug
	if(empty($spThisForum)) return;

	$topics_per_page = $spGlobals['display']['topics']['perpage'];
	if (!$topics_per_page) $topics_per_page = 20;

	$defs = array('tagClass' 		=> 'spPageLinks',
				  'prevIcon'		=> 'sp_ArrowLeft.png',
				  'nextIcon'		=> 'sp_ArrowRight.png',
				  'jumpIcon'		=> 'sp_Jump.png',
				  'iconClass'		=> 'spIcon',
				  'pageLinkClass'	=> 'spPageLinks',
				  'curPageClass'	=> 'spCurrent',
				  'linkClass'	    => 'spLink',
				  'showLinks'		=> 4,
				  'showEmpty'		=> 0,
				  'showJump'		=> 1,
				  'echo'			=> 1
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_TopicIndexPageLinks_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagClass		= esc_attr($tagClass);
	$iconClass		= esc_attr($iconClass);
	$pageLinkClass	= esc_attr($pageLinkClass);
	$curPageClass	= esc_attr($curPageClass);
	$linkClass		= esc_attr($linkClass);
	$showLinks		= (int) $showLinks;
	$showEmpty		= (int) $showEmpty;
	$showJump		= (int) $showJump;
	$label			= sp_filter_title_display($label);
	$toolTip		= esc_attr($toolTip);
	$jumpToolTip	= esc_attr($jumpToolTip);
	$echo			= (int) $echo;

	if (!empty($prevIcon)) $prevIcon	= sp_paint_icon($iconClass, SPTHEMEICONSURL, sanitize_file_name($prevIcon), $toolTip);
	if (!empty($nextIcon)) $nextIcon	= sp_paint_icon($iconClass, SPTHEMEICONSURL, sanitize_file_name($nextIcon), $toolTip);
	if (!empty($jumpIcon)) $jumpIcon	= sp_paint_icon($iconClass, SPTHEMEICONSURL, sanitize_file_name($jumpIcon), $jumpToolTip);

	if ($topics_per_page >= $spThisForum->topic_count) {
		if ($showEmpty) echo "<div class='$tagClass'></div>";
		return;
	}

	global $spVars;
	$curToolTip = str_ireplace('%PAGE%', $spVars['page'], $toolTip);

	if (isset($jumpID) ? $jumpID++ : $jumpID=1);

	$out = "<div class='$tagClass'>";
	$totalPages = ($spThisForum->topic_count / $topics_per_page);
	if (!is_int($totalPages)) $totalPages = (intval($totalPages) + 1);
	$out.= "<span class='$pageLinkClass'>$label</span>";
	$out.= sp_page_prev($spVars['page'], $showLinks, $spThisForum->forum_permalink, $pageLinkClass, $iconClass, $prevIcon, $nextIcon, $toolTip, '');

	$url = $spThisForum->forum_permalink;
	if ($spVars['page'] > 1) $url = user_trailingslashit(trailingslashit($url).'page-'.$spVars['page']);
	$url = apply_filters('sph_page_link', $url, $spVars['page']);

	$out.= "<a href='$url' class='$pageLinkClass $curPageClass' title='$curToolTip'>".$spVars['page'].'</a>';

	$out.= sp_page_next($spVars['page'], $totalPages, $showLinks, $spThisForum->forum_permalink, $pageLinkClass, $iconClass, $prevIcon, $nextIcon, $toolTip, '');

    if ($showJump) {
		$out.= '<span class="spPageJump">';
		$site = wp_nonce_url(SPAJAXURL.'spForumPageJump&amp;targetaction=page-popup&amp;url='.$spThisForum->forum_permalink.'&amp;max='.$totalPages, 'spPageJump');
		$out.= "<a id='jump-$jumpID' rel='nofollow' class='$linkClass spForumPageJump' title='$jumpToolTip' data-site='$site' data-label='$jumpToolTip' data-width='250' data-height='0' data-align='0'>";
        $out.= $jumpIcon;
        $out.= '</a>';
        $out.= '</span>';
    }

	$out.= "</div>\n";
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
function sp_TopicModeratorList($args='', $label='') {
	global $spGlobals, $spThisForum, $spGlobals, $spThisUser;

	global $spGlobals, $spThisForum;
	$defs = array('tagId'    		=> 'spForumModerators%ID%',
				  'tagClass' 		=> 'spForumModeratorList',
				  'listClass'		=> 'spForumModeratorList',
				  'labelClass'		=> 'spForumModeratorLabel',
                  'showEmpty'       => 0,
				  'echo'			=> 1,
				  'get'				=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_TopicModeratorList_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId			= esc_attr($tagId);
	$tagClass		= esc_attr($tagClass);
	$listClass		= esc_attr($listClass);
	$labelClass		= esc_attr($labelClass);
	$showEmpty		= (int) $showEmpty;
	$echo			= (int) $echo;
	$get			= (int) $get;

	$tagId = str_ireplace('%ID%', $spThisForum->forum_id, $tagId);

	$mods = $spGlobals['forum_moderators']['users'][$spThisForum->forum_id];
	if ($get) return $mods;

    # build mods list with name display
    if (!empty($mods)) {
        $modList = '';
        $first = true;
        foreach ($mods as $mod) {
            if (!$first) $modList.= ', ';
            $first = false;
            $modList.= sp_build_name_display($mod['user_id'], $mod['display_name']);
        }
    } else if ($showEmpty) {
        $modList = 'none';
    } else {
        return '';
    }

	$out = "<div id='$tagId' class='$tagClass'>\n";
	$out.= "<span class='$labelClass'>".sp_filter_title_display($label)."</span>\n";
	$out.= "<span class='$listClass'>$modList</span>\n";
	$out.= "</div>\n";

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
function sp_TopicNewButton($args='', $label='', $toolTip='', $toolTipLock='', $toolTipDenied='') {
	global $spThisForum, $spGlobals, $spThisUser;

	# can be empty if request is for a bogus forunm slug
	if(empty($spThisForum)) return;

	$defs = array('tagId' 		=> 'spTopicNewButton',
				  'tagClass' 	=> 'spButton',
				  'icon' 		=> 'sp_NewTopic.png',
				  'iconLock'	=> 'sp_ForumStatusLock.png',
				  'iconDenied'	=> 'sp_WriteDenied.png',
				  'iconClass'	=> 'spIcon',
				  'statusClass'	=> 'spIconNoAction',
				  'echo'		=> 1
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_TopicNewButton_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId			= esc_attr($tagId);
	$tagClass		= esc_attr($tagClass);
	$icon			= sanitize_file_name($icon);
	$iconClass 		= esc_attr($iconClass);
	$statusClass	= esc_attr($statusClass);
	$toolTip		= esc_attr($toolTip);
	$toolTipLock	= esc_attr($toolTipLock);
	$echo			= (int) $echo;

	# is the forum locked?
	$out = '';
    $lock = false;
	if ($spGlobals['lockdown'] || $spThisForum->forum_status) {
		if (!empty($iconLock)) {
			$iconLock = sp_paint_icon('$tagClass $iconClass $statusClass', SPTHEMEICONSURL, sanitize_file_name($iconLock), $toolTipLock);
			$out.= sp_paint_icon_id($iconLock, $tagId);
		}
        if (!$spThisUser->admin) $lock = true;
	}
    if (!$lock && sp_get_auth('start_topics', $spThisForum->forum_id)) {
		$out.= "<a class='$tagClass spNewTopicButton' id='$tagId' title='$toolTip' data-form='spPostForm' data-type='topic'>\n";
		if (!empty($icon)) $out.= sp_paint_icon($iconClass, SPTHEMEICONSURL, $icon);
		if (!empty($label)) $out.= sp_filter_title_display($label);
		$out.= "</a>\n";
	}
	# Display if user not allowed to start topics
	if (!$spThisForum->start_topics && !empty($toolTipDenied)) {
		if (!empty($iconDenied)) {
			$out.= "<a href='#' class='$tagClass' id='$tagId' title='$toolTipDenied'>\n";
			$out.= sp_paint_icon($iconClass, SPTHEMEICONSURL, sanitize_file_name($iconDenied));
			$out.= "</a>\n";
		}
	}

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
function sp_TopicIndexIcon($args='') {
	global $spThisForum, $spThisTopic;
	$defs = array('tagId' 		=> 'spTopicIndexIcon%ID%',
				  'tagClass'	=> 'spRowIcon',
				  'icon' 		=> 'sp_TopicIcon.png',
				  'iconUnread'	=> 'sp_TopicIconPosts.png',
				  'iconLocked'	=> 'sp_TopicIconLocked.png',
				  'iconPinned'	=> 'sp_TopicIconPinned.png',
				  'iconPinnedNew' => 'sp_TopicIconPinnedNew.png',
				  'echo'		=> 1,
				  'get'			=> 0
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_TopicIndexIcon_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$echo		= (int) $echo;
	$get		= (int) $get;

	$tagId = str_ireplace('%ID%', $spThisTopic->topic_id, $tagId);

	$path = SPTHEMEICONSDIR;
	$url = SPTHEMEICONSURL;

	if ($spThisTopic->topic_status || $spThisForum->forum_status) {
		$tIcon = sanitize_file_name($iconLocked);
        if (!empty($spThisForum->topic_icon_locked)) {
    		$tIcon = sanitize_file_name($spThisForum->topic_icon_locked);
    		$path = SFCUSTOMDIR;
    		$url = SFCUSTOMURL;
        }
	} elseif ($spThisTopic->topic_pinned && $spThisTopic->unread) {
		$tIcon = sanitize_file_name($iconPinnedNew);
        if (!empty($spThisForum->topic_icon_pinned_new)) {
    		$tIcon = sanitize_file_name($spThisForum->topic_icon_pinned_new);
    		$path = SFCUSTOMDIR;
    		$url = SFCUSTOMURL;
        }
	} elseif ($spThisTopic->topic_pinned) {
		$tIcon = sanitize_file_name($iconPinned);
        if (!empty($spThisForum->topic_icon_pinned)) {
    		$tIcon = sanitize_file_name($spThisForum->topic_icon_pinned);
    		$path = SFCUSTOMDIR;
    		$url = SFCUSTOMURL;
        }
	} elseif ($spThisTopic->unread) {
		$tIcon = sanitize_file_name($iconUnread);
		if (!empty($spThisForum->topic_icon_new)) {
			$tIcon = sanitize_file_name($spThisForum->topic_icon_new);
			$path = SFCUSTOMDIR;
			$url = SFCUSTOMURL;
		}
	} else {
		$tIcon = sanitize_file_name($icon);
		if (!empty($spThisForum->topic_icon)) {
			$tIcon = sanitize_file_name($spThisForum->topic_icon);
			$path = SFCUSTOMDIR;
			$url = SFCUSTOMURL;
		}
	}

	if (!file_exists($path.$tIcon)) {
		$tIcon = sp_paint_icon($tagClass, SPTHEMEICONSURL, $icon);
	} else {
		$tIcon = sp_paint_custom_icon($tagClass, $url.$tIcon);
	}

	if ($get) return $tIcon;

	$out = sp_paint_icon_id($tIcon, $tagId);
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
function sp_TopicIndexName($args='', $toolTip='') {
	global $spThisTopic;
	$defs = array('tagId'    	=> 'spTopicIndexName%ID%',
			      'tagClass' 	=> 'spRowName',
			      'truncate'	=> 0,
				  'echo'		=> 1,
				  'get'			=> 0
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_TopicIndexName_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$truncate	= (int) $truncate;
	$toolTip	= esc_attr($toolTip);
	$echo		= (int) $echo;
	$get		= (int) $get;

	$tagId = str_ireplace('%ID%', $spThisTopic->topic_id, $tagId);
	$toolTip = str_ireplace('%NAME%', htmlspecialchars($spThisTopic->topic_name, ENT_QUOTES, SFCHARSET), $toolTip);

	if ($get) return sp_truncate($spThisTopic->topic_name, $truncate);

	$out = "<a href='$spThisTopic->topic_permalink' id='$tagId' class='$tagClass' title='$toolTip'>".sp_truncate($spThisTopic->topic_name, $truncate)."</a>\n";
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
function sp_TopicIndexPostPageLinks($args='', $toolTip='') {
	global $spThisForum, $spThisTopic, $spGlobals;

	$posts_per_page = $spGlobals['display']['posts']['perpage'];
	if ($posts_per_page >= $spThisTopic->post_count) return '';

	$defs = array('tagId'    		=> 'spTopicIndexPostPageLinks%ID%',
				  'tagClass' 		=> 'spInRowPageLinks',
				  'icon'			=> 'sp_ArrowRightSmall.png',
				  'iconClass'		=> 'spIconSmall',
				  'pageLinkClass'	=> 'spInRowForumPageLink',
				  'showLinks'		=> 4,
				  'echo'			=> 1
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_TopicIndexPostPageLinks_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId			= esc_attr($tagId);
	$tagClass		= esc_attr($tagClass);
	$icon			= sanitize_file_name($icon);
	$iconClass		= esc_attr($iconClass);
	$pageLinkClass	= esc_attr($pageLinkClass);
	$showLinks		= (int) $showLinks;
	$toolTip		= esc_attr($toolTip);
	$echo			= (int) $echo;

	$tagId = str_ireplace('%ID%', $spThisTopic->topic_id, $tagId);

	$out = "<div id='$tagId' class='$tagClass'>\n";

	$total_pages=($spThisTopic->post_count / $posts_per_page);
	if (!is_int($total_pages)) $total_pages=intval($total_pages) + 1;
	($total_pages > $showLinks ? $max_count = $showLinks : $max_count = $total_pages);
	for ($x = 1; $x <= $max_count; $x++) {
		$out.= "<a class='$pageLinkClass' href='".sp_build_url($spThisForum->forum_slug, $spThisTopic->topic_slug, $x, 0)."' title='".str_ireplace('%PAGE%', $x, $toolTip)."'>$x</a>\n";
	}
	if ($total_pages > $showLinks) {
		if (!empty($icon)) $out.= sp_paint_icon($iconClass, SPTHEMEICONSURL, $icon);
		$out.= "<a class='$pageLinkClass' href='".sp_build_url($spThisForum->forum_slug, $spThisTopic->topic_slug, $total_pages, 0)."' title='".str_ireplace('%PAGE%', $total_pages, $toolTip)."'>$total_pages</a>\n";
	}
	$out.= "</div>\n";

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
function sp_TopicIndexPostCount($args='', $label='', $rtlLabel='') {
	global $spThisTopic;
	$defs = array('tagId'    		=> 'spTopicIndexPostCount%ID%',
				  'tagClass' 		=> 'spInRowCount',
				  'labelClass'		=> 'spInRowLabel',
				  'numberClass'		=> 'spInRowNumber',
				  'stack'			=> 0,
				  'before'			=> '',
				  'after'			=> '',
				  'echo'			=> 1,
				  'get'				=> 0
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_TopicIndexPostCount_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId			= esc_attr($tagId);
	$tagClass		= esc_attr($tagClass);
	$labelClass		= esc_attr($labelClass);
	$numberClass	= esc_attr($numberClass);
	$stack			= (int) $stack;
	$before			= esc_attr($before);
	$after			= esc_attr($after);
	$echo			= (int) $echo;
	$get			= (int) $get;

	$tagId = str_ireplace('%ID%', $spThisTopic->topic_id, $tagId);
	($stack ? $att='<br />' : $att= ' ');

	if ($get) return $spThisTopic->post_count;

	if(is_rtl() && $spThisTopic->post_count == 1) $label = $rtlLabel;

	$out = "<div id='$tagId' class='$tagClass'>\n";
	$out.= "<span class='$labelClass'>".sp_filter_title_display($before).sp_filter_title_display($label)."$att</span>\n";
	$out.= "<span class='$numberClass'>$spThisTopic->post_count</span>\n";
	$out.= "<span class='$labelClass'>".sp_filter_title_display($after)."</span>\n";
	$out.= "</div>\n";
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
function sp_TopicIndexReplyCount($args='', $label='', $rtlLabel='') {
	global $spThisTopic;
	$defs = array('tagId'    		=> 'spTopicIndexReplyCount%ID%',
				  'tagClass' 		=> 'spInRowCount',
				  'labelClass'		=> 'spInRowLabel',
				  'numberClass'		=> 'spInRowNumber',
				  'stack'			=> 0,
				  'before'			=> '',
				  'after'			=> '',
				  'echo'			=> 1,
				  'get'				=> 0
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_TopicIndexReplyCount_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId			= esc_attr($tagId);
	$tagClass		= esc_attr($tagClass);
	$labelClass		= esc_attr($labelClass);
	$numberClass	= esc_attr($numberClass);
	$stack			= (int) $stack;
	$before			= esc_attr($before);
	$after			= esc_attr($after);
	$echo			= (int) $echo;
	$get			= (int) $get;

	$tagId = str_ireplace('%ID%', $spThisTopic->topic_id, $tagId);
	($stack ? $att='<br />' : $att= ' ');

	$replies = ($spThisTopic->post_count-1);

	if ($get) return $replies;

	if(is_rtl() && $replies == 1) $label = $rtlLabel;

	$out = "<div id='$tagId' class='$tagClass'>\n";
	$out.= "<span class='$labelClass'>".sp_filter_title_display($before).sp_filter_title_display($label)."$att</span>\n";
	$out.= "<span class='$numberClass'>$replies</span>\n";
	$out.= "<span class='$labelClass'>".sp_filter_title_display($after)."</span>\n";
	$out.= "</div>\n";
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
function sp_TopicIndexViewCount($args='', $label='', $rtlLabel='') {
	global $spThisTopic;
	$defs = array('tagId'    		=> 'spTopicIndexViewCount%ID%',
				  'tagClass' 		=> 'spInRowCount',
				  'labelClass'		=> 'spInRowLabel',
				  'numberClass'		=> 'spInRowNumber',
				  'stack'			=> 0,
				  'before'			=> '',
				  'after'			=> '',
				  'echo'			=> 1,
				  'get'				=> 0
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_TopicIndexViewCount_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId			= esc_attr($tagId);
	$tagClass		= esc_attr($tagClass);
	$labelClass		= esc_attr($labelClass);
	$numberClass	= esc_attr($numberClass);
	$stack			= (int) $stack;
	$echo			= (int) $echo;
	$get			= (int) $get;

	$tagId = str_ireplace('%ID%', $spThisTopic->topic_id, $tagId);
	($stack ? $att='<br />' : $att= ' ');

	if ($get) return $spThisTopic->topic_opened;

	if(is_rtl() && $spThisTopic->topic_opened == 1) $label = $rtlLabel;

	$out = "<div id='$tagId' class='$tagClass'>\n";
	$out.= "<span class='$labelClass'>".sp_filter_title_display($before).sp_filter_title_display($label)."$att</span>\n";
	$out.= "<span class='$numberClass'>$spThisTopic->topic_opened</span>\n";
	$out.= "<span class='$labelClass'>".sp_filter_title_display($after)."</span>\n";
	$out.= "</div>\n";
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
function sp_TopicIndexStatusIcons($args='', $toolTipLock='', $toolTipPin='', $toolTipPost='', $toolTipDenied='') {
	global $spThisTopic, $spGlobals, $spThisUser;

	$defs = array('tagId' 			=> 'spTopicIndexStatus%ID%',
				  'tagClass' 		=> 'spStatusIcon',
				  'iconClass'		=> 'spIcon spIconNoAction',
				  'iconLock'		=> 'sp_TopicStatusLock.png',
				  'iconPin'			=> 'sp_TopicStatusPin.png',
				  'iconPost'		=> 'sp_TopicStatusPost.png',
				  'iconDenied'		=> 'sp_WriteDenied.png',
				  'echo'			=> 1
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_TopicIndexStatusIcons_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId			= esc_attr($tagId);
	$tagClass		= esc_attr($tagClass);
	$iconClass		= esc_attr($iconClass);
	$toolTipLock	= esc_attr($toolTipLock);
	$toolTipPin		= esc_attr($toolTipPin);
	$toolTipPost	= esc_attr($toolTipPost);
	$toolTipDenied	= esc_attr($toolTipDenied);
	$echo			= (int) $echo;

	$tagId = str_ireplace('%ID%', $spThisTopic->topic_id, $tagId);

	# Display if locked, pinned or new posts
	$out = "<div id='$tagId' class='$tagClass'>\n";

	if ($spGlobals['lockdown'] || $spThisTopic->topic_status) {
		if (!empty($iconLock)) {
			$out.= sp_paint_icon($iconClass, SPTHEMEICONSURL, sanitize_file_name($iconLock), $toolTipLock);
		}
	}

	if ($spThisTopic->topic_pinned) {
		if (!empty($iconPin)) {
			$out.= sp_paint_icon($iconClass, SPTHEMEICONSURL, sanitize_file_name($iconPin), $toolTipPin);
		}
	}

	if ($spThisTopic->unread) {
		if (!empty($iconPost)) {
			$out.= sp_paint_icon($iconClass, SPTHEMEICONSURL, sanitize_file_name($iconPost), $toolTipPost);
		}
	}

	if (!$spThisTopic->reply_topics && !empty($toolTipDenied)) {
		$out.= sp_paint_icon($iconClass, SPTHEMEICONSURL, sanitize_file_name($iconDenied), $toolTipDenied);
	}

	$out = apply_filters('sph_TopicIndexStatusIconsLast', $out);
	$out.= "</div>\n";

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
#
# --------------------------------------------------------------------------------------
function sp_TopicIndexFirstPost($args='', $label='') {
	global $spThisTopic;

	$defs = array('tagId'    		=> 'spTopicIndexFirstPost%ID%',
				  'tagClass' 		=> 'spInRowPostLink',
				  'labelClass'		=> 'spInRowLabel',
				  'infoClass'		=> 'spInRowInfo',
				  'linkClass'		=> 'spInRowLastPostLink',
				  'iconClass'		=> 'spIcon',
				  'icon'			=> 'sp_ArrowRight.png',
				  'labelLink'		=> 0,
				  'tip'   			=> 1,
				  'nicedate'		=> 1,
				  'date'  			=> 0,
				  'time'  			=> 0,
				  'user'  			=> 1,
				  'stackuser'		=> 1,
				  'stackdate'		=> 0,
				  'truncateUser'	=> 0,
				  'itemBreak'		=> '<br />',
				  'beforeUser'		=> '&nbsp;',
				  'beforeDate'		=> '',
				  'echo'			=> 1,
				  'get'				=> 0
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_TopicIndexFirstPost_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId			= esc_attr($tagId);
	$tagClass		= esc_attr($tagClass);
	$labelClass		= esc_attr($labelClass);
	$infoClass		= esc_attr($infoClass);
	$linkClass		= esc_attr($linkClass);
	$iconClass		= esc_attr($iconClass);
	$icon			= sanitize_file_name($icon);
	$labelLink		= (int) $labelLink;
	$tip			= (int) $tip;
	$nicedate		= (int) $nicedate;
	$date			= (int) $date;
	$time			= (int) $time;
	$user			= (int) $user;
	$stackuser		= (int) $stackuser;
	$stackdate		= (int) $stackdate;
	$truncateUser	= (int) $truncateUser;
	$icon			= $icon;
	$echo			= (int) $echo;
	$get			= (int) $get;

	$tagId = str_ireplace('%ID%', $spThisTopic->topic_id, $tagId);
	if ($tip && !empty($spThisTopic->first_post_tip)) {
		$title = "title='$spThisTopic->first_post_tip'";
		$linkClass.= '';
	} else {
		$title='';
	}

	($stackuser ? $ulb='<br />' : $ulb=$beforeUser);
	($stackdate ? $dlb='<br />' : $dlb=' - ');

	$out = "<div id='$tagId' class='$tagClass'>\n";
	$out.= "<span class='$labelClass'>\n";
	if($labelLink) {
		$out.= "<a class='$linkClass' $title href='$spThisTopic->first_post_permalink'>\n";
	}
	$out.= sp_filter_title_display($label);
	if($labelLink) {
		$out.= "</a>\n";
	}

	# Link to post
	$out.= "<a class='$linkClass' $title href='$spThisTopic->first_post_permalink'>\n";
	if (!empty($icon)) $out.= sp_paint_icon($iconClass, SPTHEMEICONSURL, $icon);
	$out.= "</a></span>\n";

	# user
	$poster = sp_build_name_display($spThisTopic->first_user_id, sp_truncate($spThisTopic->first_display_name, $truncateUser));
	if (empty($poster)) $poster = sp_truncate($spThisTopic->first_guest_name, $truncateUser);
	if ($user) $out.= "<span class='$labelClass'>$ulb$poster</span>";
    $out.= $itemBreak;

	if ($get) {
		$getData = new stdClass();
		$getData->permalink = $spThisTopic->first_post_permalink;
		$getData->topic_name = $spThisTopic->topic_name;
		$getData->post_date = $spThisTopic->first_post_date;
		$getData->tooltip = $spThisTopic->first_post_tip;
		$getData->user = $poster;
		return $getData;
	}

	# date/time
	if ($nicedate) {
		$out.= $beforeDate."<span class='$labelClass'>".sp_nicedate($spThisTopic->first_post_date)."</span>\n";
	} else {
		if ($date) {
			$out.= $beforeDate."<span class='$labelClass'>".sp_date('d', $spThisTopic->first_post_date);
			if ($time) {
				$out.= $dlb.sp_date('t', $spThisTopic->first_post_date);
			}
			$out.= "</span>\n";
		}
	}

	$out.= "</div>\n";
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
#
# --------------------------------------------------------------------------------------
function sp_TopicIndexLastPost($args='', $label='') {
	global $spThisTopic;

	# should never happen but check anyway
	if(empty($spThisTopic->last_post_permalink)) return;

	$defs = array('tagId'    		=> 'spTopicIndexLastPost%ID%',
				  'tagClass' 		=> 'spInRowPostLink',
				  'labelClass'		=> 'spInRowLabel',
				  'infoClass'		=> 'spInRowInfo',
				  'linkClass'		=> 'spInRowLastPostLink',
				  'iconClass'		=> 'spIcon',
				  'icon'			=> 'sp_ArrowRight.png',
				  'labelLink'		=> 0,
				  'tip'   			=> 1,
				  'nicedate'		=> 1,
				  'date'  			=> 0,
				  'time'  			=> 0,
				  'user'  			=> 1,
				  'stackuser'		=> 1,
				  'stackdate'		=> 0,
				  'truncateUser'	=> 0,
				  'itemBreak'		=> '<br />',
				  'beforeUser'		=> '&nbsp;',
				  'beforeDate'		=> '',
				  'echo'			=> 1,
				  'get'				=> 0
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_TopicIndexLastPost_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId			= esc_attr($tagId);
	$tagClass		= esc_attr($tagClass);
	$labelClass		= esc_attr($labelClass);
	$infoClass		= esc_attr($infoClass);
	$linkClass		= esc_attr($linkClass);
	$iconClass		= esc_attr($iconClass);
	$icon			= sanitize_file_name($icon);
	$labelLink		= (int) $labelLink;
	$tip			= (int) $tip;
	$nicedate		= (int) $nicedate;
	$date			= (int) $date;
	$time			= (int) $time;
	$user			= (int) $user;
	$stackuser		= (int) $stackuser;
	$stackdate		= (int) $stackdate;
	$truncateUser	= (int) $truncateUser;
	$icon			= $icon;
	$echo			= (int) $echo;
	$get			= (int) $get;

	$tagId = str_ireplace('%ID%', $spThisTopic->topic_id, $tagId);
	if ($tip && !empty($spThisTopic->last_post_tip)) {
		$title = "title='$spThisTopic->last_post_tip'";
		$linkClass.= '';
	} else {
		$title='';
	}

	($stackuser ? $ulb='<br />' : $ulb=$beforeUser);
	($stackdate ? $dlb='<br />' : $dlb=' - ');

	$out = "<div id='$tagId' class='$tagClass'>\n";
	$out.= "<span class='$labelClass'>\n";

	if($labelLink) {
		$out.= "<a class='$linkClass' $title href='$spThisTopic->last_post_permalink'>\n";
	}
	$out.= sp_filter_title_display($label);
	if($labelLink) {
		$out.= "</a>\n";
	}

	# Link to post
	$out.= "<a class='$linkClass' $title href='$spThisTopic->last_post_permalink'>\n";
	if (!empty($icon)) $out.= sp_paint_icon($iconClass, SPTHEMEICONSURL, $icon);
	$out.= "</a></span>\n";

	# user
	$poster = sp_build_name_display($spThisTopic->last_user_id, sp_truncate($spThisTopic->last_display_name, $truncateUser));
	if (empty($poster)) $poster = sp_truncate($spThisTopic->last_guest_name, $truncateUser);
	if ($user) $out.= "<span class='$labelClass'>$ulb$poster</span>";
    $out.= $itemBreak;

	if ($get) {
		$getData = new stdClass();
		$getData->permalink = $spThisTopic->last_post_permalink;
		$getData->topic_name = $spThisTopic->topic_name;
		$getData->post_date = $spThisTopic->last_post_date;
		$getData->tooltip = $spThisTopic->last_post_tip;
		$getData->user = $poster;
		return $getData;
	}

	# date/time
	if ($nicedate && isset($spThisTopic->last_post_date)) {
		$out.= $beforeDate."<span class='$labelClass'>".sp_nicedate($spThisTopic->last_post_date)."</span>\n";
	} else {
		if ($date && isset($spThisTopic->last_post_date)) {
			$out.= $beforeDate."<span class='$labelClass'>".sp_date('d', $spThisTopic->last_post_date);
			if ($time) {
				$out.= $dlb.sp_date('t', $spThisTopic->last_post_date);
			}
			$out.= "</span>\n";
		}
	}
	$out.= "</div>\n";
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
function sp_NoTopicsInForumMessage($args='', $definedMessage='') {
	global $spTopicView;
	$defs = array('tagId'		=> 'spNoTopicsInForumMessage',
				  'tagClass'	=> 'spMessage',
				  'echo'		=> 1,
				  'get'			=> 0
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_NoTopicsInForumMessage_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$echo		= (int) $echo;
	$get		= (int) $get;

	if ($get) return $definedMessage;

	$out = "<div id='$tagId' class='$tagClass'>".sp_filter_title_display($definedMessage)."</div>\n";
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
function sp_TopicForumToolButton($args='', $label='', $toolTip='') {
	global $spThisForum, $spThisTopic, $spThisUser, $spGlobals;

	if ($spGlobals['lockdown'] == true && $spThisUser->admin == false) return;

	$show = false;
	if ($spThisUser->admin || $spThisUser->moderator) {
		$show = true;
	} else {
		if (sp_get_auth('lock_topics', $spThisForum->forum_id) ||
			sp_get_auth('pin_topics', $spThisForum->forum_id) ||
			sp_get_auth('edit_any_topic_titles', $spThisForum->forum_id) ||
			sp_get_auth('delete_topics', $spThisForum->forum_id) ||
			sp_get_auth('move_topics', $spThisForum->forum_id) ||
			(sp_get_auth('edit_own_topic_titles', $spThisForum->forum_id) && $spThisTopic->first_user_id == $spThisUser->ID)) {
			$show = true;
		}
	}
    $show = apply_filters('sph_forum_tools_forum_show', $show);
	if (!$show) return;

	$defs = array('tagId' 			=> 'spForumToolButton%ID%',
				  'tagClass' 		=> 'spToolsButton',
				  'icon' 			=> 'sp_ForumTools.png',
				  'iconClass'		=> 'spIcon',
				  'hide'			=> 1,
				  'containerClass'	=> 'spForumTopicSection'
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_TopicForumToolButton_args', $a);
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

	$tagId = str_ireplace('%ID%', $spThisTopic->topic_id, $tagId);

	$addStyle = '';
	if ($hide) $addStyle = " style='display:none;' ";

	$site = wp_nonce_url(SPAJAXURL."spForumTopicTools&amp;targetaction=topictools&amp;topic=$spThisTopic->topic_id&amp;forum=$spThisForum->forum_id&amp;page=$spThisForum->display_page", 'spForumToolsMenu');
	$title = esc_attr(sp_text('Forum Tools'));
	$out = "<a class='$tagClass spForumTopicTools' id='$tagId' title='$toolTip' rel='nofollow' $addStyle data-site='$site' data-label='$title' data-width='350' data-height='0' data-align='0'>";

	if (!empty($icon)) $out.= sp_paint_icon($iconClass, SPTHEMEICONSURL, $icon);
	if (!empty($label)) $out.= $label;
	$out.= "</a>\n";
	$out = apply_filters('sph_TopicForumToolButton', $out, $a);

	echo $out;

	# Add script to hover admin buttons - just once
	if ($spThisForum->tools_flag && $hide) {
		?>
		<script type='text/javascript'>
		/* <![CDATA[ */
		var sptb = {
			toolclass : '.<?php echo($containerClass); ?>'
		};
		/* ]]> */
		</script>
		<?php
		add_action('wp_footer', 'spjs_AddTopicToolsHover');
		$spThisForum->tools_flag = false;
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
	global $spThisUser, $spThisForum, $spGlobals, $spVars;

	$spVars['hiddeneditor'] = $addTopicForm['hide'];

	if ((sp_get_auth('start_topics', $spThisForum->forum_id)) && (!$spThisForum->forum_status) && (!$spGlobals['lockdown']) || $spThisUser->admin) {
		$out = '<a id="spEditFormAnchor"></a>'."\n";
		$out.= sp_add_topic($addTopicForm);
		echo $out;

		# inline js to open topic form if from the topic view (script below)
		if (isset($_GET['new']) && $_GET['new'] == 'topic') add_action('wp_footer', 'spjs_OpenTopicForm');
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
<script type="text/javascript">
	jQuery(document).ready(function() {
		spjOpenEditor('spPostForm', 'topic');
	});
</script>
<?php
}

# --------------------------------------------------------------------------------------
# inline adds hover show event to admin tools button if hidden
# --------------------------------------------------------------------------------------
function spjs_AddTopicToolsHover() {
    global $spMobile;

    # on mobile devices just show forum tools. otherwise, show on hover over row
    if ($spMobile) {
?>
        <script type="text/javascript">
        	jQuery(document).ready(function() {
        		var p = jQuery(sptb.toolclass).position();
        		jQuery('.spToolsButton').css('left', p.left);
        		jQuery('.spToolsButton').show();
        	});
        </script>
<?php
    } else {
?>
        <script type="text/javascript">
        	jQuery(document).ready(function() {
        		jQuery(sptb.toolclass).hover(function() {
        			var p = jQuery(this).position();
        			jQuery('.spToolsButton', this).css('left', p.left);
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