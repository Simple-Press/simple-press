<?php
/*
Simple:Press
Search Results List View Function Handler
$LastChangedDate: 2017-02-11 15:35:37 -0600 (Sat, 11 Feb 2017) $
$Rev: 15187 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# ======================================================================================
#
# LIST SEARCH VIEW
#
# ======================================================================================
#	sp_ListViewSearchHead()
#	Create a heading using the action hook
#	Version: 5.5.1
# --------------------------------------------------------------------------------------
function sp_ListViewSearchHead() {
	do_action('sph_ListViewSearchHead');
}

# --------------------------------------------------------------------------------------
#
#	sp_ListViewSearchFoot()
#	Create a footer using the action hook
#	Version: 5.5.1
# --------------------------------------------------------------------------------------
function sp_ListViewSearchFoot() {
	do_action('sph_ListViewSearchFoot');
}

# --------------------------------------------------------------------------------------
#	sp_NoPostsInListMessage()
#	Display Message when no Topics are found in a Forum
#	Scope:	Topic Loop
#	Version: 5.5.1
# --------------------------------------------------------------------------------------
function sp_NoPostsInListMessage($args = '', $definedMessage = '') {
	$defs = array('tagId'    => 'spNoPostsInListMessage',
	              'tagClass' => 'spMessage',
	              'echo'     => 1,
	              'get'      => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_NoPostsInListMessage_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId    = esc_attr($tagId);
	$tagClass = esc_attr($tagClass);
	$echo     = (int) $echo;
	$get      = (int) $get;

	if ($get) return $definedMessage;

	$out = "<div id='$tagId' class='$tagClass'>".SP()->displayFilters->title($definedMessage)."</div>\n";
	$out = apply_filters('sph_NoPostsInListMessage', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#	sp_ListSearchTopicHeader()
#	Display Topic Name/Title
#	Scope:	post list loop
#	Version: 5.5.1
# --------------------------------------------------------------------------------------
function sp_ListSearchTopicHeader($args = '') {
	$defs = array('tagId'     => 'spListPostName%ID%',
	              'tagClass'  => 'spListPostRowName',
	              'linkClass' => 'spLink',
	              'echo'      => 1,
	              'get'       => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_ListSearchTopicHeader_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId     = esc_attr($tagId);
	$tagClass  = esc_attr($tagClass);
	$linkClass = esc_attr($linkClass);
	$echo      = (int) $echo;
	$get       = (int) $get;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisListPost->post_id, $tagId);

	if ($get) return SP()->forum->view->thisListPost->topic_name;

	# build the keywords for highlighting
	if (SP()->forum->view->thisSearch->searchInclude == 3) {
		if (SP()->forum->view->thisSearch->searchType == 1 || SP()->forum->view->thisSearch->searchType == 2) {
			if (strpos(SP()->forum->view->thisSearch->searchTermRaw, ' ') === false) {
				$highlight = SP()->forum->view->thisSearch->searchTermRaw.'*';
			} else {
				$highlight = str_replace(' ', '*|', SP()->forum->view->thisSearch->searchTermRaw);
			}
		} else {
			$highlight = SP()->forum->view->thisSearch->searchTermRaw.'*';
		}

		$topic_name = preg_replace('#(?!<.*)('.$highlight.')(?![^<>]*(?:</s(?:cript|tyle))?>)#is', '<span class="spSearchTermHighlight">$1</span>', SP()->forum->view->thisListPost->topic_name);
	} else {
		$topic_name = SP()->forum->view->thisListPost->topic_name;
	}

	$out = "<div id='$tagId' class='$tagClass'><a class='$linkClass' href='".SP()->forum->view->thisListPost->post_permalink."'>$topic_name</a></div>\n";
	$out = apply_filters('sph_ListSearchTopicHeaderName', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#	sp_ListSearchPostContent()
#	Display Search Post content
#	Scope:	post list loop
#	Version: 5.5.1
# --------------------------------------------------------------------------------------
function sp_ListSearchPostContent($args = '') {
	$defs = array('tagId'    => 'spListPostContent%ID%',
	              'tagClass' => 'spPostContent',
	              'excerpt'  => 400,
	              'echo'     => 1,
	              'get'      => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_ListPostContent_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId    = esc_attr($tagId);
	$tagClass = esc_attr($tagClass);
	$excerpt  = (int) $excerpt;
	$echo     = (int) $echo;
	$get      = (int) $get;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisListPost->post_id, $tagId);

	if ($get) return SP()->forum->view->thisListPost->post_content;

	# build the keywords for highlighting - add on wildcards to match mysql search
	if (SP()->forum->view->thisSearch->searchType == 1 || SP()->forum->view->thisSearch->searchType == 2) {
		if (strpos(SP()->forum->view->thisSearch->searchTermRaw, ' ') === false) {
			$highlight = SP()->forum->view->thisSearch->searchTermRaw;
		} else {
			$highlight = str_replace(' ', '|', SP()->forum->view->thisSearch->searchTermRaw);
		}
	} else {
		$highlight = SP()->forum->view->thisSearch->searchTermRaw;
	}

	# get the excerpted post content prepared for editing but no mysql escaping
	$content = SP()->saveFilters->content(SP()->forum->view->thisListPost->post_content, 'new', false, SPPOSTS, 'post_content');

	# lets remove some html content we dont need to show in search results
	$content = strip_shortcodes($content);
	$content = preg_replace('/<img[^>]+\>/i', SP()->primitives->front_text(' (* image not shown *) '), $content);
	$content = preg_replace('#<a.*?>.*?</a>#i', SP()->primitives->front_text(' (* link not shown *) '), $content);
	$content = preg_replace('#<iframe.*?>.*?</iframe>#i', SP()->primitives->front_text(' (* media not shown *) '), $content);
	$content = preg_replace('#<audio.*?>.*?</audio>#i', SP()->primitives->front_text(' (* media not shown *) '), $content);
	$content = preg_replace('#<video.*?>.*?</video>#i', SP()->primitives->front_text(' (* media not shown *) '), $content);

	# if we still have content left, lets highlight the search terms
	if (!empty($content)) {
		$content = SP()->filters->excerpt($content, array_filter(explode('|', $highlight), 'strlen'), $excerpt);
		$content = preg_replace('#(?!<.*)('.$highlight.')(?![^<>]*(?:</s(?:cript|tyle))?>)#is', '<span class="spSearchTermHighlight">$1</span>', $content);
		$content = SP()->displayFilters->content($content);
	}

	# display the excerpt with highlighting
	$out = "<div id='$tagId' class='$tagClass'>$content</div>\n";
	$out = apply_filters('sph_ListPostContent', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#	sp_ListSearchUserName()
#	Display Poster name
#	Scope:	post list loop
#	Version: 5.5.1
# --------------------------------------------------------------------------------------
function sp_ListSearchUserName($args = '') {
	$defs = array('tagId'        => 'spListPostUserName%ID%',
	              'tagClass'     => 'spPostUserName',
	              'truncateUser' => 0,
	              'echo'         => 1,
	              'get'          => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_ListSearchUserName_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId        = esc_attr($tagId);
	$tagClass     = esc_attr($tagClass);
	$truncateUser = (int) $truncateUser;
	$echo         = (int) $echo;
	$get          = (int) $get;

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisListPost->post_id, $tagId);

	$out = "<div id='$tagId' class='$tagClass'>";
	if (SP()->forum->view->thisListPost->user_id) {
		$name = SP()->user->name_display(SP()->forum->view->thisListPost->user_id, SP()->primitives->truncate_name(SP()->forum->view->thisListPost->display_name, $truncateUser));
	} else {
		$name = SP()->primitives->truncate_name(SP()->forum->view->thisListPost->guest_name, $truncateUser);
	}
	$out .= $name;

	if ($get) return $name;

	$out .= "</div>\n";
	$out = apply_filters('sph_ListSearchUserName', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#	sp_ListSearchUserDate()
#	Display Search Post date
#	Scope:	post list loop
#	Version: 5.5.1
# --------------------------------------------------------------------------------------
function sp_ListSearchUserDate($args = '') {
	$defs = array('tagId'     => 'spListPostUserDate%ID%',
	              'tagClass'  => 'spPostUserDate',
	              'nicedate'  => 0,
	              'date'      => 1,
	              'time'      => 1,
	              'stackdate' => 0,
	              'echo'      => 1,
	              'get'       => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_ListSearchUserDate_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId     = esc_attr($tagId);
	$tagClass  = esc_attr($tagClass);
	$nicedate  = (int) $nicedate;
	$date      = (int) $date;
	$time      = (int) $time;
	$stackdate = (int) $stackdate;
	$echo      = (int) $echo;
	$get       = (int) $get;

	$dlb = ($stackdate) ? '<br />' : ' ';

	$tagId = str_ireplace('%ID%', SP()->forum->view->thisListPost->post_id, $tagId);

	if ($get) return SP()->forum->view->thisListPost->post_date;

	$out = "<div id='$tagId' class='$tagClass'>";

	# date/time
	if ($nicedate) {
		$out .= SP()->dateTime->nice_date(SP()->forum->view->thisListPost->post_date);
	} else {
		if ($date) {
			$out .= SP()->dateTime->format_date('d', SP()->forum->view->thisListPost->post_date);
			if ($time) $out .= $dlb.SP()->dateTime->format_date('t', SP()->forum->view->thisListPost->post_date);
		}
	}
	$out .= "</div>\n";
	$out = apply_filters('sph_ListSearchUserDate', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#	sp_ListSearchForumName()
#	Display Forum Name/Title
#	Scope:	post list loop
#	Version: 5.5.1
# --------------------------------------------------------------------------------------
function sp_ListSearchForumName($args = '', $label = '') {
	$defs = array('tagId'     => 'spListPostForumName%ID%',
	              'tagClass'  => 'spListPostForumRowName',
	              'linkClass' => 'spLink',
	              'truncate'  => 0,
	              'echo'      => 1,
	              'get'       => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_ListSearchForumName_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId     = esc_attr($tagId);
	$tagClass  = esc_attr($tagClass);
	$linkClass = esc_attr($linkClass);
	$truncate  = (int) $truncate;
	$echo      = (int) $echo;
	$get       = (int) $get;

	$label = SP()->displayFilters->title($label);
	$tagId = str_ireplace('%ID%', SP()->forum->view->thisListPost->post_id, $tagId);

	if ($get) return SP()->primitives->truncate_name(SP()->forum->view->thisListPost->forum_name, $truncate);

	$out = "<div id='$tagId' class='$tagClass'>$label\n";
	$out .= "<a href='".SP()->forum->view->thisListPost->forum_permalink."' class='$linkClass'>".SP()->primitives->truncate_name(SP()->forum->view->thisListPost->forum_name, $truncate)."</a>\n";
	$out .= "</div>\n";

	$out = apply_filters('sph_ListSearchForumName', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#	sp_ListSearchTopicName()
#	Display Topic Name/Title
#	Scope:	post list loop
#	Version: 5.5.1
# --------------------------------------------------------------------------------------
function sp_ListSearchTopicName($args = '', $label = '') {
	$defs = array('tagId'     => 'spListPostTopicName%ID%',
	              'tagClass'  => 'spListPostTopicRowName',
	              'linkClass' => 'spLink',
	              'truncate'  => 0,
	              'echo'      => 1,
	              'get'       => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_ListSearchTopicName_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId     = esc_attr($tagId);
	$tagClass  = esc_attr($tagClass);
	$linkClass = esc_attr($linkClass);
	$truncate  = (int) $truncate;
	$echo      = (int) $echo;
	$get       = (int) $get;

	$label = SP()->displayFilters->title($label);
	$tagId = str_ireplace('%ID%', SP()->forum->view->thisListPost->post_id, $tagId);

	if ($get) return SP()->primitives->truncate_name(SP()->forum->view->thisListTopic->topic_name, $truncate);

	$out = "<div id='$tagId' class='$tagClass'>$label\n";
	$out .= "<a href='".SP()->forum->view->thisListPost->topic_permalink."' class='$linkClass'>".SP()->primitives->truncate_name(SP()->forum->view->thisListPost->topic_name, $truncate)."</a>\n";
	$out .= "</div>\n";

	$out = apply_filters('sph_ListSearchTopicName', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#	sp_ListSearchTopicCount()
#	Display Topic Post count
#	Scope:	post list loop
#	Version: 5.5.1
# --------------------------------------------------------------------------------------
function sp_ListSearchTopicCount($args = '', $label = '') {
	$defs = array('tagId'    => 'spListPostTopicCount%ID%',
	              'tagClass' => 'spListPostCountRowName',
	              'truncate' => 0,
	              'echo'     => 1,
	              'get'      => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_ListSearchTopicCount_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId    = esc_attr($tagId);
	$tagClass = esc_attr($tagClass);
	$truncate = (int) $truncate;
	$echo     = (int) $echo;
	$get      = (int) $get;

	$label = SP()->displayFilters->title($label);
	$tagId = str_ireplace('%ID%', SP()->forum->view->thisListPost->post_id, $tagId);

	if ($get) return SP()->forum->view->thisListTopic->topic_count;

	$out = "<div id='$tagId' class='$tagClass'>$label\n";
	$out .= SP()->forum->view->thisListPost->post_count."\n";
	$out .= "</div>\n";

	$out = apply_filters('sph_ListSearchTopicCount', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#	sp_ListSearchTopicViews()
#	Display Topic View count
#	Scope:	post list loop
#	Version: 5.5.1
# --------------------------------------------------------------------------------------
function sp_ListSearchTopicViews($args = '', $label = '') {
	$defs = array('tagId'    => 'spListPostTopicViews%ID%',
	              'tagClass' => 'spListPostViewsRowName',
	              'truncate' => 0,
	              'echo'     => 1,
	              'get'      => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_ListSearchTopicViews_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId    = esc_attr($tagId);
	$tagClass = esc_attr($tagClass);
	$truncate = (int) $truncate;
	$echo     = (int) $echo;
	$get      = (int) $get;

	$label = SP()->displayFilters->title($label);
	$tagId = str_ireplace('%ID%', SP()->forum->view->thisListPost->post_id, $tagId);

	if ($get) return SP()->forum->view->thisListTopic->topic_count;

	$out = "<div id='$tagId' class='$tagClass'>$label\n";
	$out .= SP()->forum->view->thisListPost->topic_opened;
	$out .= "</div>\n";

	$out = apply_filters('sph_ListSearchTopicViews', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#	sp_ListSearchGoToPost()
#	Display go to post link
#	Scope:	post list loop
#	Version: 5.5.1
# --------------------------------------------------------------------------------------
function sp_ListSearchGoToPost($args = '', $label = '') {
	$defs = array('tagId'     => 'spListPostGoToPost%ID%',
	              'tagClass'  => 'spListPostGoToPostRowName',
	              'linkClass' => 'spLink',
	              'echo'      => 1,
	              'get'       => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_ListSearchGoToPost_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId     = esc_attr($tagId);
	$tagClass  = esc_attr($tagClass);
	$linkClass = esc_attr($linkClass);
	$echo      = (int) $echo;
	$get       = (int) $get;

	$label = SP()->displayFilters->title($label);
	$tagId = str_ireplace('%ID%', SP()->forum->view->thisListPost->post_id, $tagId);

	if ($get) return SP()->forum->view->thisListTopic->topic_count;

	$out = "<div id='$tagId' class='$tagClass'>\n";
	$out .= "<a href='".SP()->forum->view->thisListPost->post_permalink."' class='$linkClass'>$label</a>\n";
	$out .= "</div>\n";

	$out = apply_filters('sph_ListSearchGoToPost', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}
