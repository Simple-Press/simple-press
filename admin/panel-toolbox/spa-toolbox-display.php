<?php
/*
Simple:Press
Admin Toolbox Panel Rendering
$LastChangedDate: 2014-06-20 22:47:00 -0500 (Fri, 20 Jun 2014) $
$Rev: 11582 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_render_toolbox_panel($formid) {
?>
	<div class="clearboth"></div>

	<div class="wrap sfatag">
		<?php
			spa_render_sidemenu();
		?>
		<div id='sfmsgspot'></div>
		<div id="sfmaincontainer">
			<?php spa_render_toolbox_container($formid); ?>
		</div>
			<div class="clearboth"></div>
	</div>
<?php
}

function spa_render_toolbox_container($formid) {
	switch($formid) {
		case 'toolbox':
			include_once SF_PLUGIN_DIR.'/admin/panel-toolbox/forms/spa-toolbox-toolbox-form.php';
			spa_toolbox_toolbox_form();
			break;

		case 'environment':
			include_once SF_PLUGIN_DIR.'/admin/panel-toolbox/forms/spa-toolbox-environment-form.php';
			spa_toolbox_environment_form();
			break;

		case 'housekeeping':
			include_once SF_PLUGIN_DIR.'/admin/panel-toolbox/forms/spa-toolbox-housekeeping-form.php';
			spa_toolbox_housekeeping_form();
			break;

		case 'inspector':
			include_once SF_PLUGIN_DIR.'/admin/panel-toolbox/forms/spa-toolbox-inspector-form.php';
			spa_toolbox_inspector_form();
			break;

		case 'cron':
			include_once SF_PLUGIN_DIR.'/admin/panel-toolbox/forms/spa-toolbox-cron-form.php';
			spa_toolbox_cron_form();
			break;

		case 'log':
			include_once SF_PLUGIN_DIR.'/admin/panel-toolbox/forms/spa-toolbox-log-form.php';
			spa_toolbox_log_form();
			break;

		case 'errorlog':
			include_once SF_PLUGIN_DIR.'/admin/panel-toolbox/forms/spa-toolbox-errorlog-form.php';
			spa_toolbox_errorlog_form();
			break;

		case 'changelog':
			include_once(SF_PLUGIN_DIR.'/admin/panel-toolbox/forms/spa-toolbox-changelog-form.php');
			spa_toolbox_changelog_form();
			break;

		case 'uninstall':
			include_once SF_PLUGIN_DIR.'/admin/panel-toolbox/forms/spa-toolbox-uninstall-form.php';
			spa_toolbox_uninstall_form();
			break;

        # leave this for plugins to add to this panel
		case 'plugin':
			include_once SF_PLUGIN_DIR.'/admin/panel-plugins/forms/spa-plugins-user-form.php' ;
            $admin = (isset($_GET['admin'])) ? sp_esc_str($_GET['admin']) : '';
            $save = (isset($_GET['save'])) ? sp_esc_str($_GET['save']) : '';
            $form = (isset($_GET['form'])) ? sp_esc_int($_GET['form']) : '';
            $reload = (isset($_GET['reload'])) ? sp_esc_str($_GET['reload']) : '';
			spa_plugins_user_form($admin, $save, $form, $reload);
			break;
	}
}
?>