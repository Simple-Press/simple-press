<?php
/*
Simple:Press
Search View Function Handler
$LastChangedDate: 2018-01-06 22:31:54 -0600 (Sat, 06 Jan 2018) $
$Rev: 15634 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# ======================================================================================
#
# 	SEARCH VIEW
#	Version: 5.0
#
# ======================================================================================
function sp_Search($args = '') {
	$defs = array('show' => 30,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_Search_args', $a);
	extract($a, EXTR_SKIP);

	$show = (int) $show;

	SP()->forum->view->thisSearch = new spcSearchView($show);
}

# --------------------------------------------------------------------------------------
#
#	sp_SearchHeaderName()
#	Search Heading text
#	Scope:	search view
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_SearchHeaderName($args = '', $termLabel = '', $postedLabel = '', $startedLabel = '') {
	$defs = array('tagId'    => 'spSearchHeaderName',
	              'tagClass' => 'spMessage',
				  'numClass' => '',
				  'braces'	 => 1,
	              'echo'     => 1,
				 );
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_SearchHeaderName_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId    = esc_attr($tagId);
	$tagClass = esc_attr($tagClass);
	$numClass = esc_attr($numClass);
	$braces	  = (int) $braces;
	$term     = "'".SP()->forum->view->thisSearch->searchTermRaw."'";
	$echo     = (int) $echo;

	$label = '';
	if (SP()->rewrites->pageData['searchtype'] < 4) {
		$label = str_replace('%TERM%', $term, $termLabel);
	} elseif (SP()->rewrites->pageData['searchtype'] == 4) {
		$label = str_replace('%NAME%', $term, $postedLabel);
	} elseif (SP()->rewrites->pageData['searchtype'] == 5) {
		$label = str_replace('%NAME%', $term, $startedLabel);
	}
	$label = apply_filters('sph_search_label', $label, SP()->rewrites->pageData['searchtype'], SP()->rewrites->pageData['searchinclude'], $term);

	$out = "<div id='$tagId' class='$tagClass'>$label <span class='$numClass'>";
	if($braces) $out.='(';
	$out.= SP()->forum->view->thisSearch->searchCount;
	if($braces) $out.= ')';
	$out.= '</span></div>';

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
function sp_SearchPageLinks($args = '', $label = '', $toolTip = '') {
	$items_per_page = SP()->forum->view->thisSearch->searchShow;
	if (!$items_per_page) $items_per_page = 30;
	if ($items_per_page >= SP()->forum->view->thisSearch->searchCount) return '';

	$defs = array('tagClass'      => 'spPageLinks',
	              'prevIcon'      => 'sp_ArrowLeft.png',
	              'nextIcon'      => 'sp_ArrowRight.png',
	              'iconClass'     => 'spIcon',
	              'pageLinkClass' => 'spPageLinks',
	              'curPageClass'  => 'spCurrent',
	              'showLinks'     => 4,
	              'echo'          => 1,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_SearchPageLinks_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
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

	$curToolTip = str_ireplace('%PAGE%', SP()->rewrites->pageData['searchpage'], $toolTip);

	$out        = "<div class='$tagClass'>";
	$totalPages = (SP()->forum->view->thisSearch->searchCount / $items_per_page);
	if (!is_int($totalPages)) $totalPages = (intval($totalPages) + 1);
	if(!empty($label)) $out .= "<span class='$pageLinkClass'>$label</span>";
	$out .= sp_page_prev(SP()->rewrites->pageData['searchpage'], $showLinks, SP()->forum->view->thisSearch->searchPermalink, $pageLinkClass, $iconClass, $prevIcon, $nextIcon, $toolTip, SP()->rewrites->pageData['searchpage']);

	$url = SP()->forum->view->thisSearch->searchPermalink;
	if (SP()->rewrites->pageData['searchpage'] > 1) $url = user_trailingslashit(trailingslashit(SP()->forum->view->thisSearch->searchPermalink).'&amp;search='.SP()->rewrites->pageData['searchpage']);
	$url = apply_filters('sph_page_link', $url, SP()->rewrites->pageData['page']);

	$out .= "<a href='$url' class='$pageLinkClass $curPageClass' title='$curToolTip'>".SP()->rewrites->pageData['searchpage'].'</a>';

	$out .= sp_page_next(SP()->rewrites->pageData['searchpage'], $totalPages, $showLinks, SP()->forum->view->thisSearch->searchPermalink, $pageLinkClass, $iconClass, $prevIcon, $nextIcon, $toolTip, SP()->rewrites->pageData['searchpage']);
	$out .= "</div>\n";
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
function sp_SearchResults($args = '') {
	$defs = array('tagId'    => 'spSearchList',
	              'tagClass' => 'spSearchSection',
	              'template' => 'spListView.php',
	              'first'    => 0,
	              'get'      => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_SearchResults_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId    = esc_attr($tagId);
	$tagClass = esc_attr($tagClass);
	$template = SP()->saveFilters->title($template);
	$first    = (int) $first;
	$get      = (int) $get;

	if ($get) {
		do_action('sph_search_results');

		return SP()->forum->view->thisSearch->searchData;
	}

	echo "<div id='$tagId' class='$tagClass'>\n";
	SP()->forum->view->listTopics = new spcTopicList(SP()->forum->view->thisSearch->searchData, 0, false, '', $first, 1, 'search');
	sp_load_template($template);
	echo "</div>\n";
}
