<?php
/*
Simple:Press
DESC:
$LastChangedDate: 2016-03-23 05:06:05 -0500 (Wed, 23 Mar 2016) $
$Rev: 14074 $
*/

# ==========================================================================================
#
# 	FORUM PAGE
#	This file loads for forum page loads only
#
# ==========================================================================================

global $spStatus;

$redirect = (isset($_SERVER['REDIRECT_URL'])) ? $_SERVER['REDIRECT_URL'] : '';

if (!defined('SPMEMBERLIST')) define('SPMEMBERLIST', sp_url('members'));
if (!defined('SPSEARCHMIN')) define('SPSEARCHMIN', 3);
if (!defined('SPSEARCHMAX')) define('SPSEARCHMAX', 84);

# hack to get around wp_list_pages() bug
if ($spStatus == 'ok') {
	# go for whole row so it gets cached.
	$t = spdb_table(SFWPPOSTS, 'ID='.sp_get_option('sfpage'), 'row');
	if (!defined('SFPAGETITLE')) define('SFPAGETITLE', $t->post_title);
}

do_action('sph_forum_constants');

?>