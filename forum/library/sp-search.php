<?php
/*
Simple:Press
Forum Search url creation
$LastChangedDate: 2018-11-02 16:17:56 -0500 (Fri, 02 Nov 2018) $
$Rev: 15797 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# ------------------------------------------------------------------------------------------
# 								POST variables			URL contruct		pageData
# ------------------------------------------------------------------------------------------
#
# Search (Standard)				-						search = 1			searchpage
#
# Search Value					searchvalue				value='???'			searchvalue
#
# Search Option:
#	search current forum		searchoption = 1		forum=forum_slug	forumslug
#	search all forums			searchoption = 2		forum=all			forumslug ('all')
#
# Type =
#	Match any word				searchtype = 1			type = 1			searchtype
#	Match all words				searchtype = 2			type = 2				"
#	Match phrase				searchtype = 3			type = 3				"
# ------------------------------------------------------------------------------------------
#	Member 'posted in'			searchtype = 4			type = 4				"
#	Member 'started'			searchtype = 5			type = 5				"
#
# Include =
#	Posts Only					encompass = 1			include = 1			searchinclude
#	Topic Titles only			encompass = 2			include = 2				"
#	Posts and Topic Titles		encompass = 3			include = 3				'
#
# ------------------------------------------------------------------------------------------
# NOTE FOR PLUGINS:
#	Each plugin must use a unique 'type' -
#	core SP and core SP plugins reserves 1 to 20
#		Plugin:	Topic Status:			uses type 10
#
# ------------------------------------------------------------------------------------------

// ===== needs to be used by a plugion so uncomment when plugins are done
// if (!sp_nonce('search')) die();
//==========================================================================

$param           = array();
$param['search'] = 1;
$param['new']    = 1;

if (empty($_POST['searchoption'])) {
	wp_redirect(SP()->spPermalinks->get_url());
	die();
}

if ((int) $_POST['searchoption'] == 2) {
	$param['forum'] = 'all';
} else {
	$param['forum'] = SP()->filters->str($_POST['forumslug']);
}

if (!empty($_POST['searchvalue'])) {
	# standard search
	$searchvalue      = trim(stripslashes($_POST['searchvalue']));
	$searchvalue      = trim($searchvalue, '"');
	$searchvalue      = trim($searchvalue, "'");
	$param['value']   = urlencode($searchvalue);
	$param['type']    = (empty($_POST['searchtype'])) ? 1 : SP()->filters->integer($_POST['searchtype']);
	$param['include'] = SP()->filters->integer($_POST['encompass']);
} elseif (isset($_POST['memberstarted']) && !empty($_POST['memberstarted'])) {
	# member 'started' search
	$id             = SP()->filters->integer($_POST['userid']);
	$param['value'] = $id;
	$param['type']  = 5;
} elseif (isset($_POST['membersearch']) && !empty($_POST['membersearch'])) {
	# member 'posted in' search
	$id             = SP()->filters->integer($_POST['userid']);
	$param['value'] = $id;
	$param['type']  = 4;
} else {
	# Available for plugins to REPLACE search query vars
	$param = apply_filters('sph_prepare_search', $param);
}

# Available for plugins to ADD TO search query vars
$param = apply_filters('sph_add_prepare_search', $param);

$url = add_query_arg($param, SP()->spPermalinks->get_url());
wp_redirect($url);

die();
