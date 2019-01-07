<?php
/*
Simple:Press
Search View Function Handler
$LastChangedDate: 2017-02-05 12:07:45 -0600 (Sun, 05 Feb 2017) $
$Rev: 15167 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# ======================================================================================
#
# 	SEARCH VIEW
#	Version: 5.0
#
# ======================================================================================
function sp_Search($args='') {
	global $spSearchView;
	$defs = array('show' 	=> 30,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_Search_args', $a);
	extract($a, EXTR_SKIP);

	$show = (int) $show;

	$spSearchView = new spSearchView($show);
}

# --------------------------------------------------------------------------------------
#
#	sp_SearchHeaderName()
#	Search Heading text
#	Scope:	search view
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_SearchHeaderName($args='', $termLabel='', $postedLabel='', $startedLabel='') {
	global $spSearchView, $spVars;
	$defs = array('tagId' 		=> 'spSearchHeaderName',
				  'tagClass' 	=> 'spMessage',
				  'echo'		=> 1,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_SearchHeaderName_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$term		= "'".$spSearchView->searchTermRaw."'";
	$echo		= (int) $echo;

	if ($spVars['searchtype'] < 4) {
		$label = str_replace('%TERM%', $term, $termLabel);
	} elseif ($spVars['searchtype'] == 4) {
		$label = str_replace('%NAME%', $term, $postedLabel);
	} elseif ($spVars['searchtype'] == 5) {
		$label = str_replace('%NAME%', $term, $startedLabel);
	}
	$label = apply_filters('sph_search_label', $label, $spVars['searchtype'], $spVars['searchinclude'], $term);

	$out = "<div id='$tagId' class='$tagClass'>$label ($spSearchView->searchCount)</div>\n";
	$out = apply_filters('sph_SearchHeaderName', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_SearchPageLinks()
#	Search view page links
#	Scope:	search view
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_SearchPageLinks($args='', $label='', $toolTip='') {
	global $spSearchView, $spVars;

	$items_per_page = $spSearchView->searchShow;
	if (!$items_per_page) $items_per_page = 30;
	if ($items_per_page >= $spSearchView->searchCount) return '';

	$defs = array('tagClass' 		=> 'spPageLinks',
				  'prevIcon'		=> 'sp_ArrowLeft.png',
				  'nextIcon'		=> 'sp_ArrowRight.png',
				  'iconClass'		=> 'spIcon',
				  'pageLinkClass'	=> 'spPageLinks',
				  'curPageClass'	=> 'spCurrent',
				  'showLinks'		=> 4,
				  'echo'			=> 1,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_SearchPageLinks_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagClass		= esc_attr($tagClass);
	$iconClass		= esc_attr($iconClass);
	$pageLinkClass	= esc_attr($pageLinkClass);
	$curPageClass	= esc_attr($curPageClass);
	$showLinks		= (int) $showLinks;
	$label			= sp_filter_title_display($label);
	$toolTip		= esc_attr($toolTip);
	$echo			= (int) $echo;

	if (!empty($prevIcon)) $prevIcon	= sp_paint_icon($iconClass, SPTHEMEICONSURL, sanitize_file_name($prevIcon), $toolTip);
	if (!empty($nextIcon)) $nextIcon	= sp_paint_icon($iconClass, SPTHEMEICONSURL, sanitize_file_name($nextIcon), $toolTip);

	$curToolTip = str_ireplace('%PAGE%', $spVars['searchpage'], $toolTip);

	$out = "<div class='$tagClass'>";
	$totalPages = ($spSearchView->searchCount / $items_per_page);
	if (!is_int($totalPages)) $totalPages = (intval($totalPages) + 1);
	$out.= "<span class='$pageLinkClass'>$label</span>";
	$out.= sp_page_prev($spVars['searchpage'], $showLinks, $spSearchView->searchPermalink, $pageLinkClass, $iconClass, $prevIcon, $nextIcon, $toolTip, $spVars['searchpage']);

	$url = $spSearchView->searchPermalink;
	if ($spVars['searchpage'] > 1) $url = user_trailingslashit(trailingslashit($spSearchView->searchPermalink).'&amp;search='.$spVars['searchpage']);
	$url = apply_filters('sph_page_link', $url, $spVars['page']);

	$out.= "<a href='$url' class='$pageLinkClass $curPageClass' title='$curToolTip'>".$spVars['searchpage'].'</a>';

	$out.= sp_page_next($spVars['searchpage'], $totalPages, $showLinks, $spSearchView->searchPermalink, $pageLinkClass, $iconClass, $prevIcon, $nextIcon, $toolTip, $spVars['searchpage']);
	$out.= "</div>\n";
	$out = apply_filters('sph_SearchPageLinks', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_SearchResults()
#	Search results - uses the ListView template and template functions for display
#	Scope:	search view
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_SearchResults($args='') {
	global $spSearchView, $spThisUser, $spListView;
	$defs = array('tagId'		=> 'spSearchList',
				  'tagClass'	=> 'spSearchSection',
				  'template'	=> 'spListView.php',
                  'first'       => 0,
				  'get'			=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_SearchResults_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$template	= sp_filter_title_save($template);
	$first   	= (int) $first;
	$get   	    = (int) $get;

	if ($get) {
	   do_action('sph_search_results');
 	  return $spSearchView->searchData;
    }

	echo "<div id='$tagId' class='$tagClass'>\n";
	$spListView = new spTopicList($spSearchView->searchData, 0, false, '', $first, 1, 'search');
	sp_load_template($template);
	echo "</div>\n";
}

?>