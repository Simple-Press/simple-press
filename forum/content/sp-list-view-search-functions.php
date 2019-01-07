<?php
/*
Simple:Press
Search Results List View Function Handler
$LastChangedDate: 2015-06-08 09:34:33 -0500 (Mon, 08 Jun 2015) $
$Rev: 12990 $
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
function sp_NoPostsInListMessage($args='', $definedMessage='') {
	global $spListView;
	$defs = array('tagId'		=> 'spNoPostsInListMessage',
				  'tagClass'	=> 'spMessage',
				  'echo'		=> 1,
				  'get'			=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_NoPostsInListMessage_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$echo		= (int) $echo;
	$get		= (int) $get;

	if ($get) return $definedMessage;

	$out = "<div id='$tagId' class='$tagClass'>".sp_filter_title_display($definedMessage)."</div>\n";
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
function sp_ListSearchTopicHeader($args='') {
	global $spSearchView, $spThisPostList;
	$defs = array('tagId'    	=> 'spListPostName%ID%',
			      'tagClass' 	=> 'spListPostRowName',
			      'linkClass'	=>	'spLink',
				  'echo'		=> 1,
				  'get'			=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ListSearchTopicHeader_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$linkClass	= esc_attr($linkClass);
	$echo		= (int) $echo;
	$get		= (int) $get;

	$tagId = str_ireplace('%ID%', $spThisPostList->post_id, $tagId);

	if ($get) return $spThisPostList->topic_name;

    # build the keywords for highlighting
    if ($spSearchView->searchInclude == 3) {
        if ($spSearchView->searchType == 1 || $spSearchView->searchType == 2) {
            if (strpos($spSearchView->searchTermRaw, ' ') === false) {
                $highlight = $spSearchView->searchTermRaw.'*';
            } else {
                $highlight = str_replace(' ', '*|', $spSearchView->searchTermRaw);
            }
        } else {
            $highlight = $spSearchView->searchTermRaw.'*';
        }

    	$topic_name = preg_replace('#(?!<.*)('.$highlight.')(?![^<>]*(?:</s(?:cript|tyle))?>)#is', '<span class="spSearchTermHighlight">$1</span>', $spThisPostList->topic_name);
    } else {
    	$topic_name = $spThisPostList->topic_name;
    }

	$out = "<div id='$tagId' class='$tagClass'><a class='$linkClass' href='$spThisPostList->post_permalink'>$topic_name</a></div>\n";
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
function sp_ListSearchPostContent($args='') {
	global $spSearchView, $spThisPostList;
	$defs = array('tagId'    	=> 'spListPostContent%ID%',
			      'tagClass' 	=> 'spPostContent',
			      'excerpt'	    => 400,
				  'echo'		=> 1,
				  'get'			=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ListPostContent_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$excerpt	= (int) $excerpt;
	$echo		= (int) $echo;
	$get		= (int) $get;

	$tagId = str_ireplace('%ID%', $spThisPostList->post_id, $tagId);

	if ($get) return $spThisPostList->post_content;

    # build the keywords for highlighting - add on wildcards to match mysql search
    if ($spSearchView->searchType == 1 || $spSearchView->searchType == 2) {
        if (strpos($spSearchView->searchTermRaw, ' ') === false) {
            $highlight = $spSearchView->searchTermRaw;
        } else {
            $highlight = str_replace(' ', '|', $spSearchView->searchTermRaw);
        }
    } else {
        $highlight = $spSearchView->searchTermRaw;
    }

    # get the excerpted post content prepared for editing but no mysql escaping
    $content = sp_filter_content_save($spThisPostList->post_content, 'new', false, SFPOSTS, 'post_content');

    # lets remove some html content we dont need to show in search results
    $content = strip_shortcodes($content);
    $content = preg_replace('/<img[^>]+\>/i', sp_text(' (* image not shown *) '), $content);
    $content = preg_replace('#<a.*?>.*?</a>#i', sp_text(' (* link not shown *) '), $content);
    $content = preg_replace('#<iframe.*?>.*?</iframe>#i', sp_text(' (* media not shown *) '), $content);
    $content = preg_replace('#<audio.*?>.*?</audio>#i', sp_text(' (* media not shown *) '), $content);
    $content = preg_replace('#<video.*?>.*?</video>#i', sp_text(' (* media not shown *) '), $content);

    # if we still have content left, lets highlight the search terms
    if (!empty($content)) {
        $content = sp_filter_content_excerpt($content, array_filter(explode('|', $highlight), 'strlen'), $excerpt);
        $content = preg_replace('#(?!<.*)('.$highlight.')(?![^<>]*(?:</s(?:cript|tyle))?>)#is', '<span class="spSearchTermHighlight">$1</span>', $content);
        $content = sp_filter_content_display($content);
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
function sp_ListSearchUserName($args='') {
	global $spThisPostList;

	$defs = array('tagId'    		=> 'spListPostUserName%ID%',
				  'tagClass' 		=> 'spPostUserName',
				  'truncateUser'	=> 0,
				  'echo'			=> 1,
				  'get'				=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ListSearchUserName_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId			= esc_attr($tagId);
	$tagClass		= esc_attr($tagClass);
	$truncateUser	= (int) $truncateUser;
	$echo			= (int) $echo;
	$get			= (int) $get;

	$tagId = str_ireplace('%ID%', $spThisPostList->post_id, $tagId);

	$out = "<div id='$tagId' class='$tagClass'>";
	if ($spThisPostList->user_id) {
		$name = sp_build_name_display($spThisPostList->user_id, sp_truncate($spThisPostList->display_name, $truncateUser));
	} else {
		$name = sp_truncate($spThisPostList->guest_name, $truncateUser);
	}
	$out.= $name;

	if ($get) return $name;

	$out.= "</div>\n";
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
function sp_ListSearchUserDate($args='') {
	global $spThisPostList;

	$defs = array('tagId'    		=> 'spListPostUserDate%ID%',
				  'tagClass' 		=> 'spPostUserDate',
				  'nicedate'		=> 0,
				  'date'  			=> 1,
				  'time'  			=> 1,
				  'stackdate'		=> 0,
				  'echo'			=> 1,
				  'get'				=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ListSearchUserDate_args', $a);
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

	$dlb = ($stackdate) ? '<br />' : ' ';

	$tagId = str_ireplace('%ID%', $spThisPostList->post_id, $tagId);

	if ($get) return $spThisPostList->post_date;

	$out = "<div id='$tagId' class='$tagClass'>";

	# date/time
	if ($nicedate) {
		$out.= sp_nicedate($spThisPostList->post_date);
	} else {
		if ($date) {
			$out.= sp_date('d', $spThisPostList->post_date);
			if ($time) $out.= $dlb.sp_date('t', $spThisPostList->post_date);
		}
	}
	$out.= "</div>\n";
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
function sp_ListSearchForumName($args='', $label='') {
	global $spThisPostList;

	$defs = array('tagId' 		=> 'spListPostForumName%ID%',
				  'tagClass' 	=> 'spListPostForumRowName',
			      'linkClass'	=>	'spLink',
				  'truncate'	=> 0,
				  'echo'		=> 1,
				  'get'			=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ListSearchForumName_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$linkClass	= esc_attr($linkClass);
	$truncate	= (int) $truncate;
	$echo		= (int) $echo;
	$get		= (int) $get;

	$label 		= sp_filter_title_display($label);
	$tagId = str_ireplace('%ID%', $spThisPostList->post_id, $tagId);

	if ($get) return sp_truncate($spThisPostList->forum_name, $truncate);

	$out = "<div id='$tagId' class='$tagClass'>$label\n";
	$out.= "<a href='$spThisPostList->forum_permalink' class='$linkClass'>".sp_truncate($spThisPostList->forum_name, $truncate)."</a>\n";
    $out.= "</div>\n";

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
function sp_ListSearchTopicName($args='', $label='') {
	global $spThisPostList;
	$defs = array('tagId'    	=> 'spListPostTopicName%ID%',
			      'tagClass' 	=> 'spListPostTopicRowName',
			      'linkClass'	=>	'spLink',
			      'truncate'	=> 0,
				  'echo'		=> 1,
				  'get'			=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ListSearchTopicName_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$linkClass	= esc_attr($linkClass);
	$truncate	= (int) $truncate;
	$echo		= (int) $echo;
	$get		= (int) $get;

	$label 		= sp_filter_title_display($label);
	$tagId = str_ireplace('%ID%', $spThisPostList->post_id, $tagId);

	if ($get) return sp_truncate($spThisListTopic->topic_name, $truncate);

	$out = "<div id='$tagId' class='$tagClass'>$label\n";
	$out.= "<a href='$spThisPostList->topic_permalink' class='$linkClass'>".sp_truncate($spThisPostList->topic_name, $truncate)."</a>\n";
    $out.= "</div>\n";

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
function sp_ListSearchTopicCount($args='', $label='') {
	global $spThisPostList;
	$defs = array('tagId'    	=> 'spListPostTopicCount%ID%',
			      'tagClass' 	=> 'spListPostCountRowName',
			      'truncate'	=> 0,
				  'echo'		=> 1,
				  'get'			=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ListSearchTopicCount_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$truncate	= (int) $truncate;
	$echo		= (int) $echo;
	$get		= (int) $get;

	$label 		= sp_filter_title_display($label);
	$tagId = str_ireplace('%ID%', $spThisPostList->post_id, $tagId);

	if ($get) return $spThisListTopic->topic_count;

	$out = "<div id='$tagId' class='$tagClass'>$label\n";
	$out.= "$spThisPostList->post_count\n";
    $out.= "</div>\n";

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
function sp_ListSearchTopicViews($args='', $label='') {
	global $spThisPostList;
	$defs = array('tagId'    	=> 'spListPostTopicViews%ID%',
			      'tagClass' 	=> 'spListPostViewsRowName',
			      'truncate'	=> 0,
				  'echo'		=> 1,
				  'get'			=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ListSearchTopicViews_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$truncate	= (int) $truncate;
	$echo		= (int) $echo;
	$get		= (int) $get;

	$label 		= sp_filter_title_display($label);
	$tagId = str_ireplace('%ID%', $spThisPostList->post_id, $tagId);

	if ($get) return $spThisListTopic->topic_count;

	$out = "<div id='$tagId' class='$tagClass'>$label\n";
	$out.= $spThisPostList->topic_opened;
    $out.= "</div>\n";

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
function sp_ListSearchGoToPost($args='', $label='') {
	global $spThisPostList;
	$defs = array('tagId'    	=> 'spListPostGoToPost%ID%',
			      'tagClass' 	=> 'spListPostGoToPostRowName',
			      'linkClass'	=>	'spLink',
				  'echo'		=> 1,
				  'get'			=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ListSearchGoToPost_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$linkClass	= esc_attr($linkClass);
	$echo		= (int) $echo;
	$get		= (int) $get;

	$label 		= sp_filter_title_display($label);
	$tagId = str_ireplace('%ID%', $spThisPostList->post_id, $tagId);

	if ($get) return $spThisListTopic->topic_count;

	$out = "<div id='$tagId' class='$tagClass'>\n";
	$out.= "<a href='$spThisPostList->post_permalink' class='$linkClass'>$label</a>\n";
    $out.= "</div>\n";

	$out = apply_filters('sph_ListSearchGoToPost', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}
?>