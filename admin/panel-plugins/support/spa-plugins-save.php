<?php
/*
Simple:Press
Admin plugins Update Support Functions
$LastChangedDate: 2016-12-03 14:06:51 -0600 (Sat, 03 Dec 2016) $
$Rev: 14745 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_save_plugin_activation() {
	check_admin_referer('forum-adminform_plugins', 'sfnonce');

	if (!sp_current_user_can('SPF Manage Plugins')) die();

    if (empty($_GET['action']) || empty($_GET['plugin'])) return spa_text('An error occurred activating/deactivating the plugin!');

    $action = sp_esc_str($_GET['action']);
    $plugin = sp_esc_str($_GET['plugin']);

    if ($action == 'activate') {
    	# activate the plugin
        sp_activate_sp_plugin($plugin);
        # reset all users plugin data in case new plugin adds elements to user object
        sp_reset_member_plugindata();
    } else if ($action == 'deactivate') {
    	# deactivate the plugin
        sp_deactivate_sp_plugin($plugin);
    } else if ($action == 'uninstall_confirmed') {
    	# fire uninstall action
    	do_action('sph_uninstall_plugin', trim($plugin));
		do_action('sph_uninstall_'.trim($plugin));
		do_action('sph_uninstalled_plugin', trim($plugin));

	    # now deactivate the plugin
        sp_deactivate_sp_plugin($plugin);
    } else if ($action == 'delete' && (!is_multisite() || is_super_admin())) {
    	# delete the plugin
        sp_delete_sp_plugin($plugin);
    }

    do_action('sph_plugins_save', $action, $plugin);
}

function spa_save_plugin_list_actions() {
	check_admin_referer('forum-adminform_plugins', 'forum-adminform_plugins');

    if (!sp_current_user_can('SPF Manage Plugins')) die();

	if (empty($_POST['checked'])) return spa_text('Error - no plugins selected');

	$action = '';
	if (isset($_POST['action1']) && $_POST['action1'] != -1) $action = $_POST['action1'];
	if (isset($_POST['action2']) && $_POST['action2'] != -1) $action = $_POST['action2'];

	switch ($action) {
		case 'activate-selected':
			$activate = false;
			foreach ($_POST['checked'] as $plugin) {
                $plugin = sp_filter_name_save($plugin);
				if (!sp_is_plugin_active($plugin)) {
					$activate = true;
			        sp_activate_sp_plugin($plugin);
   				}
			}
			if ($activate) {
				$msg = spa_text('Selected plugins activated');
			} else {
				$msg = spa_text('All selected plugins already active');
			}
			break;

		case 'deactivate-selected':
			$deactivate = false;
			foreach ($_POST['checked'] as $plugin) {
                $plugin = sp_filter_name_save($plugin);
				if (sp_is_plugin_active($plugin)) {
					$deactivate = true;
			        sp_deactivate_sp_plugin($plugin);
   				}
			}
			if ($deactivate) {
				$msg = spa_text('Selected plugins deactivated');
			} else {
				$msg = spa_text('All selected plugins already deactived');
			}
			break;

		case 'delete-selected':
			$active = false;
			foreach ($_POST['checked'] as $plugin) {
                $plugin = sp_filter_name_save($plugin);
				if (!sp_is_plugin_active($plugin)) {
			        sp_delete_sp_plugin($plugin);
   				} else {
					$active = true;
   				}
			}
			if ($active) {
				$msg = spa_text('Selected plugins deleted but any active plugins were not deleted');
			} else {
				$msg = spa_text('Selected plugins deleted');
			}
			break;

		default:
			$msg = spa_text('Error - no action selected');
			break;
	}
	return $msg;
}

function spa_save_plugin_userdata($func) {
	check_admin_referer('forum-adminform_userplugin', 'forum-adminform_userplugin');

    $mess = call_user_func($func);
    return $mess;
}
?>