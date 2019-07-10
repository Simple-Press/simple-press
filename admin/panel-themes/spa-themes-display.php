<?php
/*
Simple:Press
Admin Themes Display Rendering
$LastChangedDate: 2017-08-05 17:36:04 -0500 (Sat, 05 Aug 2017) $
$Rev: 15488 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_render_themes_panel($formid) {
?>
	<div class="clearboth"></div>

	<div id="sf-root-wrap" class="wrap">
<?php
	spa_render_sidemenu();
?>
		<div id='sfmsgspot'></div>
		<div id="sfmaincontainer">
<?php
	spa_render_themes_container($formid);
?>
		</div>
			<div class="clearboth"></div>
	</div>
<?php
}

function spa_render_themes_container($formid) {
	switch ($formid) {
		case 'theme-list':
			require_once SP_PLUGIN_DIR.'/admin/panel-themes/forms/spa-themes-list-form.php';
			spa_themes_list_form();
			break;

		case 'mobile':
			require_once SP_PLUGIN_DIR.'/admin/panel-themes/forms/spa-themes-mobile-form.php';
			spa_themes_mobile_form();
			break;

		case 'tablet':
			require_once SP_PLUGIN_DIR.'/admin/panel-themes/forms/spa-themes-tablet-form.php';
			spa_themes_tablet_form();
			break;

		case 'editor':
			require_once SP_PLUGIN_DIR.'/admin/panel-themes/forms/spa-themes-editor-form.php';
			spa_themes_editor_form();
			break;

		case 'css':
			require_once SP_PLUGIN_DIR.'/admin/panel-themes/forms/spa-themes-css-form.php';
			spa_themes_css_form();
			break;

		case 'theme-upload':
			require_once SP_PLUGIN_DIR.'/admin/panel-themes/forms/spa-themes-upload-form.php';
			spa_themes_upload_form();
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
