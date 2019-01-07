<?php
/**
 * Forum constants
 * This file loads at forum level - all Simple Press page loads on front end
 *
 *  $LastChangedDate: 2017-02-11 15:35:37 -0600 (Sat, 11 Feb 2017) $
 *  $Rev: 15187 $
 */
if (!defined('SPMEMBERLIST')) define('SPMEMBERLIST', SP()->spPermalinks->get_url('members'));
if (!defined('SPSEARCHMIN')) define('SPSEARCHMIN', 3);
if (!defined('SPSEARCHMAX')) define('SPSEARCHMAX', 84);

# hack to get around wp_list_pages() bug
if (SP()->core->status == 'ok') {
	# go for whole row so it gets cached.
	$t = SP()->DB->table(SPWPPOSTS, 'ID='.SP()->options->get('sfpage'), 'row');
	if (!defined('SPPAGETITLE')) define('SPPAGETITLE', $t->post_title);
}
