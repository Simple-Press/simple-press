<?php
/*
Simple:Press
Admin User Groups Panel Rendering
$LastChangedDate: 2017-08-05 17:36:04 -0500 (Sat, 05 Aug 2017) $
$Rev: 15488 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_render_usergroups_panel($formid) {
?>
	<div class="clearboth"></div>

	<div id="sf-root-wrap" class="wrap">
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
			require_once SP_PLUGIN_DIR.'/admin/panel-usergroups/spa-usergroups-display-main.php';
			spa_usergroups_usergroup_main();
			break;

		case 'createusergroup':
			require_once SP_PLUGIN_DIR.'/admin/panel-usergroups/forms/spa-usergroups-create-usergroup-form.php';
			spa_usergroups_create_usergroup_form();
			break;

		case 'editusergroup':
			require_once SP_PLUGIN_DIR.'/admin/panel-usergroups/forms/spa-usergroups-edit-usergroup-form.php';
			spa_usergroups_edit_usergroup_form(SP()->filters->integer($_GET['id']));
			break;

		case 'delusergroup':
			require_once SP_PLUGIN_DIR.'/admin/panel-usergroups/forms/spa-usergroups-delete-usergroup-form.php';
			spa_usergroups_delete_usergroup_form(SP()->filters->integer($_GET['id']));
			break;

		case 'addmembers':
			require_once SP_PLUGIN_DIR.'/admin/panel-usergroups/forms/spa-usergroups-add-members-form.php';
			spa_usergroups_add_members_form(SP()->filters->integer($_GET['id']));
			break;

		case 'delmembers':
			require_once SP_PLUGIN_DIR.'/admin/panel-usergroups/forms/spa-usergroups-delete-members-form.php';
			spa_usergroups_delete_members_form(SP()->filters->integer($_GET['id']));
			break;

		case 'mapusers':
			require_once SP_PLUGIN_DIR.'/admin/panel-usergroups/forms/spa-usergroups-map-users.php';
			spa_usergroups_map_users();
			break;

        # leave this for plugins to add to this panel
		case 'plugin':
			require_once SP_PLUGIN_DIR.'/admin/panel-plugins/forms/spa-plugins-user-form.php';
            $admin = (isset($_GET['admin'])) ? SP()->filters->str($_GET['admin']) : '';
            $save = (isset($_GET['save'])) ? SP()->filters->str($_GET['save']) : '';
            $form = (isset($_GET['form'])) ? SP()->filters->integer($_GET['form']) : '';
            $reload = (isset($_GET['reload'])) ? SP()->filters->str($_GET['reload']) : '';
			spa_plugins_user_form($admin, $save, $form, $reload);
			break;
	}
}
