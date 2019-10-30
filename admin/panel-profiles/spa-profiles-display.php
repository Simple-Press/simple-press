<?php
/*
Simple:Press
Admin Profiles Panel Rendering
$LastChangedDate: 2017-08-05 17:36:04 -0500 (Sat, 05 Aug 2017) $
$Rev: 15488 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_render_profiles_panel($formid) {
?>
	<div class="clearboth"></div>

	<div id="sf-root-wrap" class="wrap">
		<?php
			spa_render_sidemenu();
		?>
		<div id='sfmsgspot'></div>
		<div id="sfmaincontainer">
			<?php spa_render_profiles_container($formid); ?>
		</div>
			<div class="clearboth"></div>
	</div>
<?php
}

function spa_render_profiles_container($formid) {
	switch($formid) {
		case 'options':
			require_once SP_PLUGIN_DIR.'/admin/panel-profiles/forms/spa-profiles-options-form.php';
			spa_profiles_options_form();
			break;

		case 'tabsmenus':
			require_once SP_PLUGIN_DIR.'/admin/panel-profiles/forms/spa-profiles-tabs-menus-form.php';
			spa_profiles_tabs_menus_form();
			break;

		case 'avatars':
			require_once SP_PLUGIN_DIR.'/admin/panel-profiles/forms/spa-profiles-avatars-form.php';
			spa_profiles_avatars_form();
			break;

		case 'pool':
			require_once SP_PLUGIN_DIR.'/admin/panel-profiles/forms/spa-profiles-avatars-pool-form.php';
			spa_profiles_avatars_pool_form();
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
