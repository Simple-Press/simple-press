<?php
/*
Simple:Press
Admin Options Panel Rendering
$LastChangedDate: 2017-08-05 17:36:04 -0500 (Sat, 05 Aug 2017) $
$Rev: 15488 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_render_options_panel($formid) {
?>
	<div class="clearboth"></div>

	<div id="sf-root-wrap" class="wrap">
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
			require_once SP_PLUGIN_DIR.'/admin/panel-options/forms/spa-options-global-form.php';
			spa_options_global_form();
			break;

		case 'display':
			require_once SP_PLUGIN_DIR.'/admin/panel-options/forms/spa-options-display-form.php';
			spa_options_display_form();
			break;

		case 'content':
			require_once SP_PLUGIN_DIR.'/admin/panel-options/forms/spa-options-content-form.php';
			spa_options_content_form();
			break;

		case 'members':
			require_once SP_PLUGIN_DIR.'/admin/panel-options/forms/spa-options-members-form.php';
			spa_options_members_form();
			break;

		case 'email':
			require_once SP_PLUGIN_DIR.'/admin/panel-options/forms/spa-options-email-form.php';
			spa_options_email_form();
			break;

		case 'newposts':
			require_once SP_PLUGIN_DIR.'/admin/panel-options/forms/spa-options-new-posts-form.php';
			spa_options_newposts_form();
			break;
		
		case 'iconsets':
			require_once SP_PLUGIN_DIR.'/admin/panel-options/forms/spa-options-icon-sets-form.php';
			spa_options_iconsets_form();
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
