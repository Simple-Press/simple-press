<?php
/*
Simple:Press
Admin Admins Display Rendering
$LastChangedDate: 2017-08-05 17:36:04 -0500 (Sat, 05 Aug 2017) $
$Rev: 15488 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_render_admins_panel($formid) {
?>
	<div class="clearboth"></div>

	<div id="sf-root-wrap" class="wrap">
        <div id='sfmsgspot'></div>
<?php
			spa_render_sidemenu();
?>

		<div id="sfmaincontainer">
			<?php spa_render_admins_container($formid); ?>
		</div>
			<div class="clearboth"></div>
	</div>
<?php
}

function spa_render_admins_container($formid) {

	switch($formid) {
		case 'youradmin':
			require_once SP_PLUGIN_DIR.'/admin/panel-admins/forms/spa-admins-your-options-form.php';
			spa_admins_your_options_form();
			break;

		case 'globaladmin':
			require_once SP_PLUGIN_DIR.'/admin/panel-admins/forms/spa-admins-global-options-form.php';
			spa_admins_global_options_form();
			break;

		case 'manageadmin':
			require_once ABSPATH.'wp-admin/includes/admin.php';
			require_once SP_PLUGIN_DIR.'/admin/panel-admins/forms/spa-admins-manage-admins-form.php';
			spa_admins_manage_admins_form();
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
