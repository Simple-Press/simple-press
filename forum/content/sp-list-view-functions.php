<?php
/*
Simple:Press
Forum View Function Handler
$LastChangedDate: 2016-07-09 11:40:02 -0500 (Sat, 09 Jul 2016) $
$Rev: 14400 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# ======================================================================================
#
# LIST VIEW
#
# ======================================================================================

# --------------------------------------------------------------------------------------
#
#	sp_ListViewHead()
#	Create a heading using the action hook
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_ListViewHead() {
	do_action('sph_ListViewHead');
}

# --------------------------------------------------------------------------------------
#
#	sp_ListForumName()
#	Display Forum Name/Title in Header
#	Scope:	Forum View
#	Version: 5.0
#		5.2 - New label parameter added for new posts in line in Group View
# --------------------------------------------------------------------------------------
function sp_ListForumName($args='', $toolTip='', $label='') {
	global $spThisListTopic, $spListView;

    # only display forum name if the first time a topic in the forum is shown
	if (!$spThisListTopic->new_forum) return;

	$defs = array('tagId' 		=> 'spListForumName%POS%',
				  'tagClass' 	=> 'spListForumRowName',
				  'truncate'	=> 0,
				  'echo'		=> 1,
				  'get'			=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ListForumName_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$toolTip	= esc_attr($toolTip);
	$truncate	= (int) $truncate;
	$echo		= (int) $echo;
	$get		= (int) $get;

	$label 		= sp_filter_title_display($label);
	$tagId 		= str_ireplace('%POS%', $spThisListTopic->list_position, $tagId);
	$toolTip 	= str_ireplace('%NAME%', htmlspecialchars($spThisListTopic->forum_name, ENT_QUOTES, SFCHARSET), $toolTip);

	if ($get) return sp_truncate($spThisListTopic->forum_name, $truncate);

	#Allow the new post list to substitute a label if running in line
	if ($spListView->popup == false && !empty($label)) {
		$out = "<p id='$tagId' class='$tagClass'>$label</p>\n";
	} else {
		$out = (empty($spThisListTopic->forum_name)) ? '' : "<a href='$spThisListTopic->forum_permalink' id='$tagId' class='$tagClass' title='$toolTip'>".sp_truncate($spThisListTopic->forum_name, $truncate)."</a>\n";
	}
	$out = apply_filters('sph_ListForumName', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_ListNewPostButton()
#	Display new post count with link to the first new post in the topic
#	Scope:	Site
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_ListNewPostButton($args='', $label='', $toolTip='') {
	global $spThisListTopic;
	if (!isset($spThisListTopic->new_post_count) || $spThisListTopic->new_post_count==0) {
		do_action('sph_ListNewPostButtonAlt', '');
		return;
	}

	$defs = array('tagId' 		=> 'spListNewPostButton%ID%',
				  'tagClass' 	=> 'spButton',
				  'icon' 		=> 'sp_NewPost.png',
				  'iconClass'	=> 'spIcon',
				  'echo'		=> 1,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ListNewPostButton_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$link		= $spThisListTopic->new_post_permalink;
	$icon		= sanitize_file_name($icon);
	$iconClass 	= esc_attr($iconClass);
	$toolTip	= esc_attr($toolTip);
	$echo		= (int) $echo;

	$tagId = str_ireplace('%ID%', $spThisListTopic->topic_id, $tagId);

	$label = sp_filter_title_display(str_ireplace('%COUNT%', $spThisListTopic->new_post_count, $label));

	$out = "<a class='$tagClass' id='$tagId' title='$toolTip' href='$link'>";
	if (!empty($icon)) $out.= sp_paint_icon($iconClass, SPTHEMEICONSURL, $icon);
	if (!empty($label)) $out.= sp_filter_title_display($label);
	$out.= '</a>';
	$out = apply_filters('sph_ListNewPostButton', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_ListTopicIcon()
#	Display Topic Icon
#	Scope:	Topic Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_ListTopicIcon($args='') {
	global $spThisListTopic;
	$defs = array('tagId' 		=> 'spListTopicIcon%ID%',
				  'tagClass' 	=> 'spRowIconSmall',
				  'icon' 		=> 'sp_TopicIconSmall.png',
				  'echo'		=> 1,
				  'get'			=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ListTopicIcon_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$echo		= (int) $echo;
	$get		= (int) $get;

	$tagId = str_ireplace('%ID%', $spThisListTopic->topic_id, $tagId);

	$path = SPTHEMEICONSDIR;
	$url = SPTHEMEICONSURL;
	if (isset($spThisListTopic->new_post_count) && $spThisListTopic->new_post_count > 0) {
		$tIcon = sanitize_file_name($icon);
		if (!empty($spThisListTopic->topic_icon_new)) {
			$tIcon = sanitize_file_name($spThisListTopic->topic_icon_new);
			$path = SFCUSTOMDIR;
			$url = SFCUSTOMURL;
		}
	} else {
		$tIcon = sanitize_file_name($icon);
		if (!empty($spThisListTopic->topic_icon)) {
			$tIcon = sanitize_file_name($spThisListTopic->topic_icon);
			$path = SFCUSTOMDIR;
			$url = SFCUSTOMURL;
		}
	}
	if (!file_exists($path.$tIcon)) {
		$tIcon = sp_paint_icon($tagClass, SPTHEMEICONSURL, sanitize_file_name($icon));
	} else {
		$tIcon = sp_paint_custom_icon($tagClass, $url.$tIcon);
	}

	if ($get) return $tIcon;

	$out = sp_paint_icon_id($tIcon, $tagId);
	$out = apply_filters('sph_ListTopicIcon', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_ListTopicName()
#	Display Topic Name/Title
#	Scope:	Topic Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_ListTopicName($args='', $toolTip='') {
	global $spThisListTopic;
	$defs = array('tagId'    	=> 'spListTopicName%ID%',
			      'tagClass' 	=> 'spListTopicRowName',
			      'linkClass'	=>	'spLink',
			      'truncate'	=> 0,
				  'echo'		=> 1,
				  'get'			=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ListTopicName_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$linkClass	= esc_attr($linkClass);
	$toolTip	= esc_attr($toolTip);
	$toolTip 	= str_ireplace('%NAME%', htmlspecialchars($spThisListTopic->topic_name, ENT_QUOTES, SFCHARSET), $toolTip);
	$truncate	= (int) $truncate;
	$echo		= (int) $echo;
	$get		= (int) $get;

	$tagId = str_ireplace('%ID%', $spThisListTopic->topic_id, $tagId);

	if ($get) return sp_truncate($spThisListTopic->topic_name, $truncate);

	$out = "<div class='$tagClass'><a class='$linkClass' href='$spThisListTopic->topic_permalink' id='$tagId' title='$toolTip'>".sp_truncate($spThisListTopic->topic_name, $truncate)."</a></div>\n";
	$out = apply_filters('sph_ListTopicName', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_ListLastPost()
#	Display Topic 'in row' link to the last post made to a topic in this forum
#	Scope:	Topic Loop
#	Version: 5.0
#	Changelog:
#		5.2.3	Added 'break'
#		5.5.3	'labelLink' argument added
#
# --------------------------------------------------------------------------------------
function sp_ListLastPost($args='', $label='') {
	global $spThisListTopic;

	$defs = array('tagId'    	=> 'spListLastPost%ID%',
				  'tagClass' 	=> 'spListPostLink',
				  'labelClass'	=> 'spListLabel',
				  'linkClass'	=> 'spLink',
				  'iconClass'	=> 'spIcon',
				  'icon'		=> 'sp_ArrowRight.png',
				  'labelLink'	=> 0,
				  'tip'   		=> 1,
				  'niceDate'	=> 1,
				  'date'  		=> 0,
				  'time'  		=> 0,
				  'user'  		=> 1,
				  'truncateUser'=> 0,
				  'break'		=> 0,
				  'echo'		=> 1,
				  'get'			=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ListLastPost_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId			= esc_attr($tagId);
	$tagClass		= esc_attr($tagClass);
	$labelClass		= esc_attr($labelClass);
	$linkClass		= esc_attr($linkClass);
	$iconClass		= esc_attr($iconClass);
	$labelLink		= (int) $labelLink;
	$tip			= (int) $tip;
	$niceDate		= (int) $niceDate;
	$date			= (int) $date;
	$time			= (int) $time;
	$user			= (int) $user;
	$truncateUser	= (int) $truncateUser;
	$break			= (int) $break;
	$icon			= sanitize_file_name($icon);
	$echo			= (int) $echo;
	$get			= (int) $get;

	$tagId = str_ireplace('%ID%', $spThisListTopic->topic_id, $tagId);
	if ($tip && !empty($spThisListTopic->post_tip)) {
		$title = "title='$spThisListTopic->post_tip'";
		$linkClass.= '';
	} else {
		$title='';
	}
	$sp = '&nbsp;';
	$out = "<div id='$tagId' class='$tagClass'>\n";
	if($labelLink) {
		$out.= "<a class='$linkClass' $title href='$spThisListTopic->post_permalink'>\n";
	}
	$out.= "<span class='$labelClass'>".sp_filter_title_display($label)." \n";
	if($labelLink) {
		$out.= "</a>\n";
	}

	# Link to post
	$out.= "<a class='$linkClass' $title href='$spThisListTopic->post_permalink'>\n";
	if (!empty($icon)) $out.= sp_paint_icon($iconClass, SPTHEMEICONSURL, $icon);
	$out.= "</a></span>\n";

	# user
	$poster = sp_build_name_display($spThisListTopic->user_id, sp_truncate($spThisListTopic->display_name, $truncateUser));
	if (empty($poster)) $poster = sp_truncate($spThisListTopic->guest_name, $truncateUser);
	if ($user) $out.= "<span class='$labelClass'>$poster</span>\n";

	if ($get) {
		$getData = new stdClass();
		$getData->permalink = $spThisListTopic->post_permalink;
		$getData->topic_name = $spThisListTopic->topic_name;
		$getData->post_date = $spThisListTopic->post_date;
		$getData->tooltip = $spThisListTopic->post_tip;
		$getData->user = $poster;
		return $getData;
	}

	if ($break) $sp = "<br />";

	# date/time
	if ($niceDate) {
		$out.= "<span class='$labelClass'>$sp".sp_nicedate($spThisListTopic->post_date)."</span>\n";
	} else {
		if ($date) {
			$out.= "<span class='$labelClass'>$sp".sp_date('d', $spThisListTopic->post_date);
			if ($time) {
				$out.= '-'.sp_date('t', $spThisListTopic->post_date);
			}
			$out.= "</span>\n";
		}
	}
	$out.= "</div>\n";
	$out = apply_filters('sph_ListLastPost', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_ListFirstPost()
#	Display Topic 'in row' link to the first post made to a topic in this forum
#	Scope:	Topic Loop
#	Version: 5.0
#	Changelog:
#		5.2.3	Added 'break'
#		5.5.3	'labelLink' argument added
#
# --------------------------------------------------------------------------------------
function sp_ListFirstPost($args='', $label='') {
	global $spThisListTopic;

	$defs = array('tagId'    	=> 'spListFirstPost%ID%',
				  'tagClass' 	=> 'spListPostLink',
				  'labelClass'	=> 'spListLabel',
				  'linkClass'	=> 'spLink',
				  'iconClass'	=> 'spIcon',
				  'icon'		=> 'sp_ArrowRight.png',
				  'labelLink'	=> 0,
				  'tip'   		=> 1,
				  'niceDate'	=> 1,
				  'date'  		=> 0,
				  'time'  		=> 0,
				  'user'  		=> 1,
				  'truncateUser'=> 0,
				  'break'		=> 0,
				  'echo'		=> 1,
				  'get'			=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ListFirstPost_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId			= esc_attr($tagId);
	$tagClass		= esc_attr($tagClass);
	$labelClass		= esc_attr($labelClass);
	$linkClass		= esc_attr($linkClass);
	$iconClass		= esc_attr($iconClass);
	$labelLink		= (int) $labelLink;
	$tip			= (int) $tip;
	$niceDate		= (int) $niceDate;
	$date			= (int) $date;
	$time			= (int) $time;
	$user			= (int) $user;
	$truncateUser	= (int) $truncateUser;
	$break			= (int) $break;
	$icon			= sanitize_file_name($icon);
	$echo			= (int) $echo;
	$get			= (int) $get;

	$tagId = str_ireplace('%ID%', $spThisListTopic->topic_id, $tagId);
	if ($tip && !empty($spThisListTopic->first_post_tip)) {
		$title = "title='$spThisListTopic->first_post_tip'";
		$linkClass.= '';
	} else {
		$title='';
	}
	$sp = '&nbsp;';
	$out = "<div id='$tagId' class='$tagClass'>\n";

	if($labelLink) {
		$out.= "<a class='$linkClass' $title href='$spThisListTopic->first_post_permalink'>\n";
	}
	$out.= "<span class='$labelClass'>".sp_filter_title_display($label)." \n";
	if($labelLink) {
		$out.= "</a>\n";
	}

	# Link to post
	$out.= "<a class='$linkClass' $title href='$spThisListTopic->first_post_permalink'>\n";
	$out.= sp_paint_icon($iconClass, SPTHEMEICONSURL, $icon);
	$out.= "</a></span>\n";

	# user
	$poster = sp_build_name_display($spThisListTopic->first_user_id, sp_truncate($spThisListTopic->first_display_name, $truncateUser));
	if (empty($poster)) $poster = sp_truncate($spThisListTopic->first_guest_name, $truncateUser);
	if ($user) $out.= "<span class='$labelClass'>$poster</span>\n";

	if ($get) {
		$getData = new stdClass();
		$getData->permalink = $spThisListTopic->first_post_permalink;
		$getData->topic_name = $spThisListTopic->topic_name;
		$getData->post_date = $spThisListTopic->first_post_date;
		$getData->tooltip = $spThisListTopic->first_post_tip;
		$getData->user = $poster;
		return $getData;
	}

	if ($break) $sp = "<br />";

	# date/time
	if ($niceDate) {
		$out.= "<span class='$labelClass'>".$sp.sp_nicedate($spThisListTopic->first_post_date)."</span>\n";
	} else {
		if ($date) {
			$out.= "<span class='$labelClass'>".$sp.sp_date('d', $spThisListTopic->first_post_date);
			if ($time) {
				$out.= '-'.sp_date('t', $spThisListTopic->first_post_date);
			}
			$out.= "</span>\n";
		}
	}
	$out.= "</div>\n";
	$out = apply_filters('sph_ListFirstPost', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_ListViewBodyStart()
#	Create some body content at startusing the action hook
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_ListViewBodyStart() {
	do_action('sph_ListViewBodyStart');
}

# --------------------------------------------------------------------------------------
#
#	sp_ListViewBodyEnd()
#	Create some body content at end using the action hook
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_ListViewBodyEnd() {
	do_action('sph_ListViewBodyEnd');
}

# --------------------------------------------------------------------------------------
#
#	sp_NoTopicsInListMessage()
#	Display Message when no Topics are found in a Forum
#	Scope:	Topic Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_NoTopicsInListMessage($args='', $definedMessage='') {
	global $spListView;
	$defs = array('tagId'		=> 'spNoTopicsInListMessage',
				  'tagClass'	=> 'spMessage',
				  'echo'		=> 1,
				  'get'			=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_NoTopicsInListMessage_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$echo		= (int) $echo;
	$get		= (int) $get;

	if ($get) return $definedMessage;

	$out = "<div id='$tagId' class='$tagClass'>".sp_filter_title_display($definedMessage)."</div>\n";
	$out = apply_filters('sph_NoTopicsInListMessage', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_ListViewFoot()
#	Create a footer using the action hook
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_ListViewFoot() {
	do_action('sph_ListViewFoot');
}

?>