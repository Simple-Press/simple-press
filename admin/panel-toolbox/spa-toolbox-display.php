<?php
/*
Simple:Press
Admin Toolbox Panel Rendering
$LastChangedDate: 2019-01-30 16:40:00 -0600 (Wed, 30 Jan 2019) $
$Rev: 15488 $
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
			require_once SP_PLUGIN_DIR.'/admin/panel-toolbox/forms/spa-toolbox-toolbox-form.php';
			spa_toolbox_toolbox_form();
			break;

		case 'environment':
			require_once SP_PLUGIN_DIR.'/admin/panel-toolbox/forms/spa-toolbox-environment-form.php';
			spa_toolbox_environment_form();
			break;

		case 'housekeeping':
			require_once SP_PLUGIN_DIR.'/admin/panel-toolbox/forms/spa-toolbox-housekeeping-form.php';
			spa_toolbox_housekeeping_form();
			break;

		case 'inspector':
			require_once SP_PLUGIN_DIR.'/admin/panel-toolbox/forms/spa-toolbox-inspector-form.php';
			spa_toolbox_inspector_form();
			break;

		case 'cron':
			require_once SP_PLUGIN_DIR.'/admin/panel-toolbox/forms/spa-toolbox-cron-form.php';
			spa_toolbox_cron_form();
			break;

		case 'log':
			require_once SP_PLUGIN_DIR.'/admin/panel-toolbox/forms/spa-toolbox-log-form.php';
			spa_toolbox_log_form();
			break;

		case 'errorlog':
			require_once SP_PLUGIN_DIR.'/admin/panel-toolbox/forms/spa-toolbox-errorlog-form.php';
			spa_toolbox_errorlog_form();
			break;

		case 'changelog':
			require_once SP_PLUGIN_DIR.'/admin/panel-toolbox/forms/spa-toolbox-changelog-form.php';
			spa_toolbox_changelog_form();
			break;
			
		case 'licensing':
			require_once SP_PLUGIN_DIR.'/admin/panel-toolbox/forms/spa-toolbox-licensing-form.php';
			spa_toolbox_licensing_form();
			break;

		case 'uninstall':
			require_once SP_PLUGIN_DIR.'/admin/panel-toolbox/forms/spa-toolbox-uninstall-form.php';
			spa_toolbox_uninstall_form();
			break;

        # leave this for plugins to add to this panel
		case 'plugin':
			require_once SP_PLUGIN_DIR.'/admin/panel-plugins/forms/spa-plugins-user-form.php' ;
            $admin = (isset($_GET['admin'])) ? SP()->filters->str($_GET['admin']) : '';
            $save = (isset($_GET['save'])) ? SP()->filters->str($_GET['save']) : '';
            $form = (isset($_GET['form'])) ? SP()->filters->integer($_GET['form']) : '';
            $reload = (isset($_GET['reload'])) ? SP()->filters->str($_GET['reload']) : '';
			spa_plugins_user_form($admin, $save, $form, $reload);
			break;
	}
}
