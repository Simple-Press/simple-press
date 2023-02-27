<?php
/**
 * Admin Notice
 * Loads when WP admin attempts to access Simple Press admin pages but has not been granted any capabilities.
 *
 * $LastChangedDate: 2017-02-11 15:35:37 -0600 (Sat, 11 Feb 2017) $
 * $Rev: 15187 $
 */
if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

if (!current_user_can('administrator')) die();

include_once SP_PLUGIN_DIR.'/admin/library/spa-tab-support.php';
include_once SP_PLUGIN_DIR.'/admin/panel-admins/support/spa-admins-prepare.php';

if (SP()->core->forumData != 'ok') {
	require_once SPLOADINSTALL;
	die();
}

spa_panel_header();
spa_paint_options_init();
spa_paint_open_tab(SP()->primitives->admin_text('Special WP Admin Notice').' - '.SP()->primitives->admin_text('Special WP Admin Notice'));
spa_paint_open_panel();
spa_paint_open_fieldset(SP()->primitives->admin_text('Special WP Admin Notice'), false);
echo '<p>';
SP()->primitives->admin_etext('Please note that while you are a WP admin, you are not currently an SP admin. By default, WP admins are not SP admins');
echo '</p>';
echo '<p>';
SP()->primitives->admin_etext('Contact one of the SP Admins listed below to see if they want to grant you SP admin access on the SP manage admins panel');
echo '</p>';

# list all current SPF Admins
$adminrecords = SP()->core->forumData['forum-admins'];
if ($adminrecords) {
	echo '<p>';
	echo '<ul>';
	foreach ($adminrecords as $admin => $name) {
		echo '<li>'.SP()->displayFilters->name($name).'</li>';
	}
	echo '</ul>';
	echo '</p>';
}
spa_paint_close_fieldset();
spa_paint_close_panel();
//echo '<div class="sfform-panel-spacer"></div>';
//spa_paint_close_container();
spa_paint_close_tab();
spa_panel_footer();
