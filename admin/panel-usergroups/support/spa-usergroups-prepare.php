<?php
/*
Simple:Press
Admin User Groups Support Functions
$LastChangedDate: 2017-02-11 15:35:37 -0600 (Sat, 11 Feb 2017) $
$Rev: 15187 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_get_mapping_data() {
	# get default usergroups
	$sfoptions = array();
	$value = SP()->meta->get('default usergroup', 'sfmembers');
	$sfoptions['sfdefgroup'] = $value[0]['meta_value'];
	$value = SP()->meta->get('default usergroup', 'sfguests');
	$sfoptions['sfguestsgroup'] = $value[0]['meta_value'];

	$sfmemberopts = SP()->options->get('sfmemberopts');
	$sfoptions['sfsinglemembership'] = $sfmemberopts['sfsinglemembership'];

    return $sfoptions;
}
