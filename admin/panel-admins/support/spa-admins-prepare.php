<?php
/*
Simple:Press
Admin Admins Update Global Options Support Functions
$LastChangedDate: 2017-02-11 15:35:37 -0600 (Sat, 11 Feb 2017) $
$Rev: 15187 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_get_admins_your_options_data() {
	$sfadminoptions = SP()->memberData->get(SP()->user->thisUser->ID, 'admin_options');
	$sfadminoptions['setmods'] = false;
	return $sfadminoptions;
}

function spa_get_admins_global_options_data() {
	$sfadminsettings = SP()->options->get('sfadminsettings');
	return $sfadminsettings;
}
