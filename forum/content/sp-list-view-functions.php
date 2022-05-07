<?php
/*
Simple:Press
Forum View Function Handler
$LastChangedDate: 2017-02-21 14:49:40 -0600 (Tue, 21 Feb 2017) $
$Rev: 15229 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

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
function sp_ListForumName($args = '', $toolTip = '', $label = '') {
	# only display forum name if the first time a topic in the forum is shown
	if (!SP()->forum->view->thisListTopic->new_forum) return;

	$defs = array('tagId'    => 'spListForumName%POS%',
	              'tagClass' => 'spListForumRowName',
				  'prefix'   => '',  
	              'truncate' => 0,
	              'echo'     => 1,
	              'get'      => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_ListForumName_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId    = esc_attr($tagId);
	$tagClass = esc_attr($tagClass);
	$toolTip  = esc_attr($toolTip);
	$prefix   = esc_attr($prefix);
	$truncate = (int) $truncate;
	$echo     = (int) $echo;
	$get      = (int) $get;

	$label   = SP()->displayFilters->title($label);
	$tagId   = str_ireplace('%POS%', SP()->forum->view->thisListTopic->list_position, $tagId);
	$toolTip = str_ireplace('%NAME%', htmlspecialchars(SP()->forum->view->thisListTopic->forum_name, ENT_QUOTES, SPCHARSET), $toolTip);

	if ($get) return SP()->primitives->truncate_name(SP()->forum->view->thisListTopic->forum_name, $truncate);

	#Allow the new post list to substitute a label if running in line
	if (SP()->forum->view->listTopics->popup == false && !empty($label)) {
		$out = "<p id='$tagId' class='$tagClass'>$label</p>";
	} else {
		$out = (empty(SP()->forum->view->thisListTopic->forum_name)) ? '' : "<a href='".SP()->forum->view->thisListTopic->forum_permalink."' id='$tagId' class='$tagClass' title='$toolTip'>$prefix ".SP()->primitives->truncate_name(SP()->forum->view->thisListTopic->forum_name, $truncate)."</a>";
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
function sp_ListNewPostButton($args = '', $label = '', $toolTip = '') {
	if (!isset(SP()->forum->view->thisListTopic->new_post_count) || SP()->forum->view->thisListTopic->new_post_count == 0) {
		do_action('sph_ListNewPostButtonAlt', '');
		return;
	}

	$defs = array('tagId'     => 'spListNewPostButton%ID%',
	              'tagClass'  => 'spButton',
	              'icon'      => 'sp_NewPost.png',
	              'iconClass' => 'spIcon',
	              'echo'      => 1,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_ListNewPostButton_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId     = esc_attr($tagId);
	$tagClass  = esc_attr($tagClass);
	$link      = SP()->forum->view->thisListTopic->new_post_permalink;
	$icon      = sanitize_file_name($icon);
	$iconClass = esc_attr($iconClass);
	$toolTip   = esc_attr($toolTip);
	$echo      = (int) $echo;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisListTopic->topic_id, $tagId);

	$label = SP()->displayFilters->title(str_ireplace('%COUNT%', '<span class="badge">'.SP()->forum->view->thisListTopic->new_post_count.'</span>', $label));

	$out = "<a class='$tagClass' id='$tagId' title='$toolTip' href='$link'>";
	if (!empty($icon)) $out .= SP()->theme->paint_icon($iconClass, SPTHEMEICONSURL, $icon);
	if (!empty($label)) $out .= SP()->displayFilters->title($label);
	$out .= '</a>';

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
function sp_ListTopicIcon($args = '') {
	$defs = array('tagId'    => 'spListTopicIcon%ID%',
	              'tagClass' => 'spRowIconSmall',
	              'icon'     => 'sp_TopicIconSmall.png',
	              'echo'     => 1,
	              'get'      => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_ListTopicIcon_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId    = esc_attr($tagId);
	$tagClass = esc_attr($tagClass);
	$echo     = (int) $echo;
	$get      = (int) $get;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisListTopic->topic_id, $tagId);

	$path = SPTHEMEICONSDIR;
	$url  = SPTHEMEICONSURL;
	$tIconType = 'file';
	if (isset(SP()->forum->view->thisListTopic->new_post_count) && SP()->forum->view->thisListTopic->new_post_count > 0) {
		$tIcon = sanitize_file_name($icon);
		$topic_icon = spa_get_saved_icon( SP()->forum->view->thisListTopic->topic_icon_new );
		
		if ( !empty( $topic_icon['icon'] ) ) {
			
			$tIconType = $topic_icon['type'];
			$tIcon = 'file' === $topic_icon['type'] ? $topic_icon['icon'] : $topic_icon;
			$path  = SPCUSTOMDIR;
			$url   = SPCUSTOMURL;
		}
	} else {
		$tIcon = sanitize_file_name($icon);
		$topic_icon = spa_get_saved_icon( SP()->forum->view->thisListTopic->topic_icon );
		
		if ( !empty( $topic_icon['icon'] ) ) {
			
			$tIconType = $topic_icon['type'];
			$tIcon = 'file' === $topic_icon['type'] ? $topic_icon['icon'] : $topic_icon;
			$path  = SPCUSTOMDIR;
			$url   = SPCUSTOMURL;
		}
	}
	
	if( 'file' === $tIconType ) {
		
		if (!file_exists($path.$tIcon)) {
			$tIcon = SP()->theme->paint_icon($tagClass, SPTHEMEICONSURL, sanitize_file_name($icon));
		} else {
			$tIcon = SP()->theme->paint_custom_icon($tagClass, $url.$tIcon);
		}
		
	} else {
		$tIcon = SP()->theme->sp_paint_iconset_icon( $tIcon, $tagClass );
	}
	

	if ($get) return $tIcon;

	$out = SP()->theme->paint_icon_id($tIcon, $tagId);
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
function sp_ListTopicName($args = '', $toolTip = '') {
	$defs = array('tagId'     => 'spListTopicName%ID%',
	              'tagClass'  => 'spListTopicRowName',
	              'linkClass' => 'spLink',
				  'prefix'   => '',
	              'truncate'  => 0,
	              'echo'      => 1,
	              'get'       => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_ListTopicName_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId     = esc_attr($tagId);
	$tagClass  = esc_attr($tagClass);
	$linkClass = esc_attr($linkClass);
	$prefix    = esc_attr($prefix);
	$toolTip   = esc_attr($toolTip);
	$toolTip   = str_ireplace('%NAME%', htmlspecialchars(SP()->forum->view->thisListTopic->topic_name, ENT_QUOTES, SPCHARSET), $toolTip);
	$truncate  = (int) $truncate;
	$echo      = (int) $echo;
	$get       = (int) $get;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisListTopic->topic_id, $tagId);

	if ($get) return SP()->primitives->truncate_name(SP()->forum->view->thisListTopic->topic_name, $truncate);

	$out = "<div class='$tagClass'><a class='$linkClass' href='".SP()->forum->view->thisListTopic->topic_permalink."' id='$tagId' title='$toolTip'>$prefix ".SP()->primitives->truncate_name(SP()->forum->view->thisListTopic->topic_name, $truncate)."</a></div>";
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
function sp_ListLastPost($args = '', $label = '') {
	$defs = array('tagId'        => 'spListLastPost%ID%',
	              'tagClass'     => 'spListPostLink',
	              'labelClass'   => 'spListLabel',
	              'linkClass'    => 'spLink',
	              'iconClass'    => 'spIcon',
	              'icon'         => 'sp_ArrowRight.png',
	              'labelLink'    => 0,
	              'tip'          => 1,
	              'niceDate'     => 1,
	              'date'         => 0,
	              'time'         => 0,
	              'user'         => 1,
	              'truncateUser' => 0,
	              'break'        => 0,
	              'echo'         => 1,
	              'get'          => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_ListLastPost_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId        = esc_attr($tagId);
	$tagClass     = esc_attr($tagClass);
	$labelClass   = esc_attr($labelClass);
	$linkClass    = esc_attr($linkClass);
	$iconClass    = esc_attr($iconClass);
	$labelLink    = (int) $labelLink;
	$tip          = (int) $tip;
	$niceDate     = (int) $niceDate;
	$date         = (int) $date;
	$time         = (int) $time;
	$user         = (int) $user;
	$truncateUser = (int) $truncateUser;
	$break        = (int) $break;
	$icon         = sanitize_file_name($icon);
	$echo         = (int) $echo;
	$get          = (int) $get;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisListTopic->topic_id, $tagId);
	if ($tip && !empty(SP()->forum->view->thisListTopic->post_tip)) {
		$title = "title='".SP()->forum->view->thisListTopic->post_tip."'";
		$linkClass .= '';
	} else {
		$title = '';
	}
	$sp  = '&nbsp;';
	$out = "<div id='$tagId' class='$tagClass'>";
	if ($labelLink) {
		$out .= "<a class='$linkClass' $title href='".SP()->forum->view->thisListTopic->post_permalink."'>";
	}
	$out .= "<span class='$labelClass'>".SP()->displayFilters->title($label)." ";
	if ($labelLink) {
		$out .= "</a>";
	}

	# Link to post
	$out .= "<a class='$linkClass' $title href='".SP()->forum->view->thisListTopic->post_permalink."'>";
	if (!empty($icon)) $out .= SP()->theme->paint_icon($iconClass, SPTHEMEICONSURL, $icon);
	$out .= "</a></span>";

	# user
	$poster = SP()->user->name_display(SP()->forum->view->thisListTopic->user_id, SP()->primitives->truncate_name(SP()->forum->view->thisListTopic->display_name, $truncateUser));
	if (empty($poster)) $poster = SP()->primitives->truncate_name(SP()->forum->view->thisListTopic->guest_name, $truncateUser);
	if ($user) $out .= "<span class='$labelClass'>$poster</span>";

	if ($get) {
		$getData             = new stdClass();
		$getData->permalink  = SP()->forum->view->thisListTopic->post_permalink;
		$getData->topic_name = SP()->forum->view->thisListTopic->topic_name;
		$getData->post_date  = SP()->forum->view->thisListTopic->post_date;
		$getData->tooltip    = SP()->forum->view->thisListTopic->post_tip;
		$getData->user       = $poster;

		return $getData;
	}

	if ($break) $sp = "<br />";

	# date/time
	if ($niceDate) {
		$out .= "<span class='$labelClass'>$sp".SP()->dateTime->nice_date(SP()->forum->view->thisListTopic->post_date)."</span>";
	} else {
		if ($date) {
			$out .= "<span class='$labelClass'>$sp".SP()->dateTime->format_date('d', SP()->forum->view->thisListTopic->post_date);
			if ($time) {
				$out .= '-'.SP()->dateTime->format_date('t', SP()->forum->view->thisListTopic->post_date);
			}
			$out .= "</span>";
		}
	}
	$out .= "</div>";
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
function sp_ListFirstPost($args = '', $label = '') {
	$defs = array('tagId'        => 'spListFirstPost%ID%',
	              'tagClass'     => 'spListPostLink',
	              'labelClass'   => 'spListLabel',
	              'linkClass'    => 'spLink',
	              'iconClass'    => 'spIcon',
	              'icon'         => 'sp_ArrowRight.png',
	              'labelLink'    => 0,
	              'tip'          => 1,
	              'niceDate'     => 1,
	              'date'         => 0,
	              'time'         => 0,
	              'user'         => 1,
	              'truncateUser' => 0,
	              'break'        => 0,
	              'echo'         => 1,
	              'get'          => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_ListFirstPost_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId        = esc_attr($tagId);
	$tagClass     = esc_attr($tagClass);
	$labelClass   = esc_attr($labelClass);
	$linkClass    = esc_attr($linkClass);
	$iconClass    = esc_attr($iconClass);
	$labelLink    = (int) $labelLink;
	$tip          = (int) $tip;
	$niceDate     = (int) $niceDate;
	$date         = (int) $date;
	$time         = (int) $time;
	$user         = (int) $user;
	$truncateUser = (int) $truncateUser;
	$break        = (int) $break;
	$icon         = sanitize_file_name($icon);
	$echo         = (int) $echo;
	$get          = (int) $get;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisListTopic->topic_id, $tagId);
	if ($tip && !empty(SP()->forum->view->thisListTopic->first_post_tip)) {
		$title = "title='".SP()->forum->view->thisListTopic->first_post_tip."'";
		$linkClass .= '';
	} else {
		$title = '';
	}
	$sp  = '&nbsp;';
	$out = "<div id='$tagId' class='$tagClass'>";

	if ($labelLink) {
		$out .= "<a class='$linkClass' $title href='".SP()->forum->view->thisListTopic->first_post_permalink."'>";
	}
	$out .= "<span class='$labelClass'>".SP()->displayFilters->title($label)." ";
	if ($labelLink) {
		$out .= "</a>";
	}

	# Link to post
	$out .= "<a class='$linkClass' $title href='".SP()->forum->view->thisListTopic->first_post_permalink."'>";
	$out .= SP()->theme->paint_icon($iconClass, SPTHEMEICONSURL, $icon);
	$out .= "</a></span>";

	# user
	$poster = SP()->user->name_display(SP()->forum->view->thisListTopic->first_user_id, SP()->primitives->truncate_name(SP()->forum->view->thisListTopic->first_display_name, $truncateUser));
	if (empty($poster)) $poster = SP()->primitives->truncate_name(SP()->forum->view->thisListTopic->first_guest_name, $truncateUser);
	if ($user) $out .= "<span class='$labelClass'>$poster</span>";

	if ($get) {
		$getData             = new stdClass();
		$getData->permalink  = SP()->forum->view->thisListTopic->first_post_permalink;
		$getData->topic_name = SP()->forum->view->thisListTopic->topic_name;
		$getData->post_date  = SP()->forum->view->thisListTopic->first_post_date;
		$getData->tooltip    = SP()->forum->view->thisListTopic->first_post_tip;
		$getData->user       = $poster;

		return $getData;
	}

	if ($break) $sp = "<br />";

	# date/time
	if ($niceDate) {
		$out .= "<span class='$labelClass'>".$sp.SP()->dateTime->nice_date(SP()->forum->view->thisListTopic->first_post_date)."</span>";
	} else {
		if ($date) {
			$out .= "<span class='$labelClass'>".$sp.SP()->dateTime->format_date('d', SP()->forum->view->thisListTopic->first_post_date);
			if ($time) {
				$out .= '-'.SP()->dateTime->format_date('t', SP()->forum->view->thisListTopic->first_post_date);
			}
			$out .= "</span>";
		}
	}
	$out .= "</div>";
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
function sp_NoTopicsInListMessage($args = '', $definedMessage = '') {
	$defs = array('tagId'    => 'spNoTopicsInListMessage',
	              'tagClass' => 'spMessage',
	              'echo'     => 1,
	              'get'      => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_NoTopicsInListMessage_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId    = esc_attr($tagId);
	$tagClass = esc_attr($tagClass);
	$echo     = (int) $echo;
	$get      = (int) $get;

	if ($get) return $definedMessage;

	$out = "<div id='$tagId' class='$tagClass'>".SP()->displayFilters->title($definedMessage)."</div>";
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
