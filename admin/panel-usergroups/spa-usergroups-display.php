<?php
/*
Simple:Press
Admin User Groups Panel Rendering
$LastChangedDate: 2014-06-20 22:47:00 -0500 (Fri, 20 Jun 2014) $
$Rev: 11582 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_render_usergroups_panel($formid) {
?>
	<div class="clearboth"></div>

	<div class="wrap sfatag">
		<?php
			spa_render_sidemenu();
		?>
		<div id='sfmsgspot'></div>
		<div id="sfmaincontainer">
			<?php spa_render_usergroups_container($formid); ?>
		</div>
			<div class="clearboth"></div>
	</div>
<?php
}

function spa_render_usergroups_container($formid) {
	switch ($formid) {
		case 'usergroups':
			include_once SF_PLUGIN_DIR.'/admin/panel-usergroups/spa-usergroups-display-main.php';
			spa_usergroups_usergroup_main();
			break;

		case 'createusergroup':
			include_once SF_PLUGIN_DIR.'/admin/panel-usergroups/forms/spa-usergroups-create-usergroup-form.php';
			spa_usergroups_create_usergroup_form();
			break;

		case 'editusergroup':
			include_once SF_PLUGIN_DIR.'/admin/panel-usergroups/forms/spa-usergroups-edit-usergroup-form.php';
			spa_usergroups_edit_usergroup_form(sp_esc_int($_GET['id']));
			break;

		case 'delusergroup':
			include_once SF_PLUGIN_DIR.'/admin/panel-usergroups/forms/spa-usergroups-delete-usergroup-form.php';
			spa_usergroups_delete_usergroup_form(sp_esc_int($_GET['id']));
			break;

		case 'addmembers':
			include_once SF_PLUGIN_DIR.'/admin/panel-usergroups/forms/spa-usergroups-add-members-form.php';
			spa_usergroups_add_members_form(sp_esc_int($_GET['id']));
			break;

		case 'delmembers':
			include_once SF_PLUGIN_DIR.'/admin/panel-usergroups/forms/spa-usergroups-delete-members-form.php';
			spa_usergroups_delete_members_form(sp_esc_int($_GET['id']));
			break;

		case 'mapusers':
			include_once SF_PLUGIN_DIR.'/admin/panel-usergroups/forms/spa-usergroups-map-users.php';
			spa_usergroups_map_users();
			break;

        # leave this for plugins to add to this panel
		case 'plugin':
			include_once SF_PLUGIN_DIR.'/admin/panel-plugins/forms/spa-plugins-user-form.php';
            $admin = (isset($_GET['admin'])) ? sp_esc_str($_GET['admin']) : '';
            $save = (isset($_GET['save'])) ? sp_esc_str($_GET['save']) : '';
            $form = (isset($_GET['form'])) ? sp_esc_int($_GET['form']) : '';
            $reload = (isset($_GET['reload'])) ? sp_esc_str($_GET['reload']) : '';
			spa_plugins_user_form($admin, $save, $form, $reload);
			break;
	}
}
?>