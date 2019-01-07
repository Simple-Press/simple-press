<?php
/*
Simple:Press
Desc:
$LastChangedDate: 2013-05-04 08:16:36 -0500 (Sat, 04 May 2013) $
$Rev: 10259 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# ==================================================================
#
# 	CORE: This file is loaded at CORE
#	Shared Form Component Routines
#
#
# ==================================================================

# Version: 5.0
function sp_create_nonce($action) {
	return '<input type="hidden" name="'.$action.'" value="'.wp_create_nonce($action).'" />'."\n";
}

?>