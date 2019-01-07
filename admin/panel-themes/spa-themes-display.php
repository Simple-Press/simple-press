<?php
/*
Simple:Press
Admin Themes Display Rendering
$LastChangedDate: 2015-11-18 10:41:07 -0600 (Wed, 18 Nov 2015) $
$Rev: 13579 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_render_themes_panel($formid) {
?>
	<div class="clearboth"></div>

	<div class="wrap sfatag">
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
			include_once SF_PLUGIN_DIR.'/admin/panel-themes/forms/spa-themes-list-form.php';
			spa_themes_list_form();
			break;

		case 'mobile':
			include_once SF_PLUGIN_DIR.'/admin/panel-themes/forms/spa-themes-mobile-form.php';
			spa_themes_mobile_form();
			break;

		case 'tablet':
			include_once SF_PLUGIN_DIR.'/admin/panel-themes/forms/spa-themes-tablet-form.php';
			spa_themes_tablet_form();
			break;

		case 'editor':
			include_once SF_PLUGIN_DIR.'/admin/panel-themes/forms/spa-themes-editor-form.php';
			spa_themes_editor_form();
			break;

		case 'css':
			include_once SF_PLUGIN_DIR.'/admin/panel-themes/forms/spa-themes-css-form.php';
			spa_themes_css_form();
			break;

		case 'theme-upload':
			include_once SF_PLUGIN_DIR.'/admin/panel-themes/forms/spa-themes-upload-form.php';
			spa_themes_upload_form();
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