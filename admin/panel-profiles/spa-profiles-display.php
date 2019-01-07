<?php
/*
Simple:Press
Admin Profiles Panel Rendering
$LastChangedDate: 2015-12-20 04:40:58 -0600 (Sun, 20 Dec 2015) $
$Rev: 13724 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_render_profiles_panel($formid) {
?>
	<div class="clearboth"></div>

	<div class="wrap sfatag">
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
			include_once SF_PLUGIN_DIR.'/admin/panel-profiles/forms/spa-profiles-options-form.php';
			spa_profiles_options_form();
			break;

		case 'tabsmenus':
			include_once SF_PLUGIN_DIR.'/admin/panel-profiles/forms/spa-profiles-tabs-menus-form.php';
			spa_profiles_tabs_menus_form();
			break;

		case 'avatars':
			include_once SF_PLUGIN_DIR.'/admin/panel-profiles/forms/spa-profiles-avatars-form.php';
			spa_profiles_avatars_form();
			break;

		case 'pool':
			include_once SF_PLUGIN_DIR.'/admin/panel-profiles/forms/spa-profiles-avatars-pool-form.php';
			spa_profiles_avatars_pool_form();
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