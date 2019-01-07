<?php
/*
Simple:Press
Admin plugins Update Support Functions
$LastChangedDate: 2017-02-11 15:35:37 -0600 (Sat, 11 Feb 2017) $
$Rev: 15187 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_save_plugin_activation() {
	check_admin_referer('forum-adminform_plugins', 'sfnonce');

	if (!SP()->auths->current_user_can('SPF Manage Plugins')) die();

    if (empty($_GET['action']) || empty($_GET['plugin'])) return SP()->primitives->admin_text('An error occurred activating/deactivating the plugin!');

    $action = SP()->filters->str($_GET['action']);
    $plugin = SP()->filters->str($_GET['plugin']);

    if ($action == 'activate') {
    	# activate the plugin
        SP()->plugin->activate($plugin);
        # reset all users plugin data in case new plugin adds elements to user object
        SP()->memberData->reset_plugin_data();
    } else if ($action == 'deactivate') {
    	# deactivate the plugin
        SP()->plugin->deactivate($plugin);
    } else if ($action == 'uninstall_confirmed') {
    	# fire uninstall action
    	do_action('sph_uninstall_plugin', trim($plugin));
		do_action('sph_uninstall_'.trim($plugin));
		do_action('sph_uninstalled_plugin', trim($plugin));

	    # now deactivate the plugin
        SP()->plugin->deactivate($plugin);
    } else if ($action == 'delete' && (!is_multisite() || is_super_admin())) {
    	# delete the plugin
        SP()->plugin->delete($plugin);
    }

    do_action('sph_plugins_save', $action, $plugin);
    return '';
}

function spa_save_plugin_list_actions() {
	check_admin_referer('forum-adminform_plugins', 'forum-adminform_plugins');

    if (!SP()->auths->current_user_can('SPF Manage Plugins')) die();

	if (empty($_POST['checked'])) return SP()->primitives->admin_text('Error - no plugins selected');

	$action = '';
	if (isset($_POST['action1']) && $_POST['action1'] != -1) $action = $_POST['action1'];
	if (isset($_POST['action2']) && $_POST['action2'] != -1) $action = $_POST['action2'];

	switch ($action) {
		case 'activate-selected':
			$activate = false;
			foreach ($_POST['checked'] as $plugin) {
                $plugin = SP()->saveFilters->name($plugin);
				if (!SP()->plugin->is_active($plugin)) {
					$activate = true;
			        SP()->plugin->activate($plugin);
   				}
			}
			if ($activate) {
				$msg = SP()->primitives->admin_text('Selected plugins activated');
			} else {
				$msg = SP()->primitives->admin_text('All selected plugins already active');
			}
			break;

		case 'deactivate-selected':
			$deactivate = false;
			foreach ($_POST['checked'] as $plugin) {
                $plugin = SP()->saveFilters->name($plugin);
				if (SP()->plugin->is_active($plugin)) {
					$deactivate = true;
			        SP()->plugin->deactivate($plugin);
   				}
			}
			if ($deactivate) {
				$msg = SP()->primitives->admin_text('Selected plugins deactivated');
			} else {
				$msg = SP()->primitives->admin_text('All selected plugins already deactived');
			}
			break;

		case 'delete-selected':
			$active = false;
			foreach ($_POST['checked'] as $plugin) {
                $plugin = SP()->saveFilters->name($plugin);
				if (!SP()->plugin->is_active($plugin)) {
			        SP()->plugin->delete($plugin);
   				} else {
					$active = true;
   				}
			}
			if ($active) {
				$msg = SP()->primitives->admin_text('Selected plugins deleted but any active plugins were not deleted');
			} else {
				$msg = SP()->primitives->admin_text('Selected plugins deleted');
			}
			break;

		default:
			$msg = SP()->primitives->admin_text('Error - no action selected');
			break;
	}
	return $msg;
}

function spa_save_plugin_userdata($func) {
	check_admin_referer('forum-adminform_userplugin', 'forum-adminform_userplugin');

    $mess = call_user_func($func);
    return $mess;
}
