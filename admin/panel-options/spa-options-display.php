<?php
/*
Simple:Press
Admin Options Panel Rendering
$LastChangedDate: 2016-09-05 16:47:07 -0500 (Mon, 05 Sep 2016) $
$Rev: 14547 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_render_options_panel($formid) {
?>
	<div class="clearboth"></div>

	<div class="wrap sfatag">
		<?php
			spa_render_sidemenu();
		?>
		<div id='sfmsgspot'></div>
		<div id="sfmaincontainer">
			<?php spa_render_options_container($formid); ?>
		</div>
			<div class="clearboth"></div>
	</div>
<?php
}

function spa_render_options_container($formid) {
	switch($formid) {
		case 'global':
			include_once SF_PLUGIN_DIR.'/admin/panel-options/forms/spa-options-global-form.php';
			spa_options_global_form();
			break;

		case 'display':
			include_once SF_PLUGIN_DIR.'/admin/panel-options/forms/spa-options-display-form.php';
			spa_options_display_form();
			break;

		case 'content':
			include_once SF_PLUGIN_DIR.'/admin/panel-options/forms/spa-options-content-form.php';
			spa_options_content_form();
			break;

		case 'members':
			include_once SF_PLUGIN_DIR.'/admin/panel-options/forms/spa-options-members-form.php';
			spa_options_members_form();
			break;

		case 'email':
			include_once SF_PLUGIN_DIR.'/admin/panel-options/forms/spa-options-email-form.php';
			spa_options_email_form();
			break;

		case 'newposts':
			include_once SF_PLUGIN_DIR.'/admin/panel-options/forms/spa-options-new-posts-form.php';
			spa_options_newposts_form();
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