<?php
/*
Simple:Press
Admin Permissions Panel Rendering
$LastChangedDate: 2014-06-20 22:47:00 -0500 (Fri, 20 Jun 2014) $
$Rev: 11582 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_render_permissions_panel($formid) {
?>
	<div class="clearboth"></div>

	<div class="wrap sfatag">
		<?php
			spa_render_sidemenu();
		?>
		<div id='sfmsgspot'></div>
		<div id="sfmaincontainer">
			<?php spa_render_permissions_container($formid); ?>
		</div>
			<div class="clearboth"></div>
	</div>
<?php
}

function spa_render_permissions_container($formid) {
	switch ($formid) {
		case 'permissions':
			include_once SF_PLUGIN_DIR.'/admin/panel-permissions/spa-permissions-display-main.php';
			spa_permissions_permission_main();
			break;

		case 'createperm':
			include_once SF_PLUGIN_DIR.'/admin/panel-permissions/forms/spa-permissions-add-permission-form.php';
			spa_permissions_add_permission_form();
			break;

		case 'editperm':
			include_once SF_PLUGIN_DIR.'/admin/panel-permissions/forms/spa-permissions-edit-permission-form.php';
			spa_permissions_edit_permission_form(sp_esc_int($_GET['id']));
			break;

		case 'delperm':
			include_once SF_PLUGIN_DIR.'/admin/panel-permissions/forms/spa-permissions-delete-permission-form.php';
			spa_permissions_delete_permission_form(sp_esc_int($_GET['id']));
			break;

		case 'resetperms':
			include_once SF_PLUGIN_DIR.'/admin/panel-permissions/forms/spa-permissions-reset-permissions-form.php';
			spa_permissions_reset_perms_form();
			break;

		case 'newauth':
			include_once SF_PLUGIN_DIR.'/admin/panel-permissions/forms/spa-permissions-add-auth-form.php';
			spa_permissions_add_auth_form();
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