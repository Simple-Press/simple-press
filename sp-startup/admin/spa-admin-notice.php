<?php
/*
Simple:Press
Special Admin Notice
$LastChangedDate: 2016-12-03 14:06:51 -0600 (Sat, 03 Dec 2016) $
$Rev: 14745 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# ==========================================================================================
#
# 	FORUM ADMIN
#	This file loads at Forum Admin if needed
#
# ==========================================================================================

# Check Whether User is WP Admin
if (!current_user_can('administrator')) die();

global $spStatus, $spGlobals;

include_once SF_PLUGIN_DIR.'/admin/library/spa-tab-support.php';
include_once SF_PLUGIN_DIR.'/admin/panel-admins/support/spa-admins-prepare.php';

if ($spStatus != 'ok') {
	include_once(SPLOADINSTALL);
	die();
}

spa_panel_header();
spa_paint_options_init();
spa_paint_open_tab(spa_text('Special WP Admin Notice').' - '.spa_text('Special WP Admin Notice'));
	spa_paint_open_panel();
		spa_paint_open_fieldset(spa_text('Special WP Admin Notice'), false);
            echo '<p>';
			spa_etext('Please note that while you are a WP admin, you are not currently an SP admin. By default, WP admins are not SP admins');
			echo '</p>';
            echo '<p>';
			spa_etext('Contact one of the SP Admins listed below to see if they want to grant you SP admin access on the SP manage admins panel');
			echo '</p>';

            # list all current SPF Admins
        	$adminrecords = $spGlobals['forum-admins'];
           	if ($adminrecords) {
                echo '<p>';
                echo '<ul>';
				foreach ($adminrecords as $admin => $name) {
				    echo '<li>'.sp_filter_name_display($name).'</li>';
                }
                echo '</ul>';
    			echo '</p>';
            }
		spa_paint_close_fieldset();
	spa_paint_close_panel();
	echo '<div class="sfform-panel-spacer"></div>';
	spa_paint_close_container();
spa_paint_close_tab();
spa_panel_footer();

?>