<?php
/*
Simple:Press
Admin Permissions Panel Rendering
$LastChangedDate: 2017-08-05 17:36:04 -0500 (Sat, 05 Aug 2017) $
$Rev: 15488 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_render_permissions_panel($formid) {
?>
	<div class="clearboth"></div>

	<div id="sf-root-wrap" class="wrap">
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
			require_once SP_PLUGIN_DIR.'/admin/panel-permissions/spa-permissions-display-main.php';
			spa_permissions_permission_main();
			break;

		case 'createperm':
			require_once SP_PLUGIN_DIR.'/admin/panel-permissions/forms/spa-permissions-add-permission-form.php';
			spa_permissions_add_permission_form();
			break;

		case 'editperm':
			require_once SP_PLUGIN_DIR.'/admin/panel-permissions/forms/spa-permissions-edit-permission-form.php';
			spa_permissions_edit_permission_form(SP()->filters->integer($_GET['id']));
			break;

		case 'delperm':
			require_once SP_PLUGIN_DIR.'/admin/panel-permissions/forms/spa-permissions-delete-permission-form.php';
			spa_permissions_delete_permission_form(SP()->filters->integer($_GET['id']));
			break;

		case 'resetperms':
			require_once SP_PLUGIN_DIR.'/admin/panel-permissions/forms/spa-permissions-reset-permissions-form.php';
			spa_permissions_reset_perms_form();
			break;

		case 'newauth':
			require_once SP_PLUGIN_DIR.'/admin/panel-permissions/forms/spa-permissions-add-auth-form.php';
			spa_permissions_add_auth_form();
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
