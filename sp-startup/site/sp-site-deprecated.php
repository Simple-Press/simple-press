<?php
/*
Simple:Press
Deprecated - global code
$LastChangedDate: 2016-07-11 20:16:45 -0500 (Mon, 11 Jul 2016) $
$Rev: 14429 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# ==========================================================================================
#
# 	SITE - This file loads at core level - all page loads
#	SP Deprecated Functions - where deprecated functions come to die
#   Wrappers for old deprecated functions which call the new, appropriate functions
#   Emit a deprecated warning
#   handled like wp deprecations (same logging)
#
# ==========================================================================================

# ----------------------------------------------
# sp_find_icon()
# Version: 5.0
# Deprecated" version 5.5.7
# Use sp_paint_file_icon() instead
# Checks in theme for icon file - returns path
# ----------------------------------------------
function sp_find_icon($path, $file) {

	trigger_error('The function sp_find_icon() has been deprecated and will be removed in a future update. Use the new function sp_paint_file_icon() instead.', E_USER_WARNING);

	return sp_paint_file_icon($path, $file);
}

# ----------------------------------------------
# spa_admin_ahah_support()
# Deprecated 5.7
# User spa_admin_ajax_support()
# ----------------------------------------------
function spa_admin_ahah_support() {

	trigger_error('The function spa_admin_ahah_support() has been deprecated and will be removed in a future update. Use the new function spa_admin_ajax_support() instead.', E_USER_WARNING);

	return spa_admin_ajax_support();
}

# ----------------------------------------------
# sp_forum_api_support()
# Deprecated 5.7
# User sp_forum_ajax_support()
# ----------------------------------------------
function sp_forum_api_support() {

	trigger_error('The function sp_forum_api_support() has been deprecated and will be removed in a future update. Use the new function sp_forum_ajax_support() instead.', E_USER_WARNING);

	return sp_forum_ajax_support();
}


?>