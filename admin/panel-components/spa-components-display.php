<?php
/*
Simple:Press
Admin Components Display Rendering
$LastChangedDate: 2018-11-02 13:31:02 -0500 (Fri, 02 Nov 2018) $
$Rev: 15796 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_render_components_panel($formid) {
?>
	<div class="clearboth"></div>

	<div id="sf-root-wrap" class="wrap">
        <div id='sfmsgspot'></div>
		<?php
			spa_render_sidemenu();
		?>

		<div id="sfmaincontainer">
			<?php spa_render_components_container($formid); ?>
		</div>
			<div class="clearboth"></div>
	</div>
<?php
}

function spa_render_components_container($formid) {
	switch ($formid) {
		case 'smileys':
			require_once SP_PLUGIN_DIR.'/admin/panel-components/forms/spa-components-smileys-form.php';
			spa_components_smileys_form();
			break;

		case 'login':
			require_once SP_PLUGIN_DIR.'/admin/panel-components/forms/spa-components-login-form.php';
			spa_components_login_form();
			break;

		case 'seo':
			require_once SP_PLUGIN_DIR.'/admin/panel-components/forms/spa-components-seo-form.php';
			spa_components_seo_form();
			break;

		case 'forumranks':
			require_once SP_PLUGIN_DIR.'/admin/panel-components/forms/spa-components-forumranks-form.php';
			spa_components_forumranks_form();
			break;

		case 'addmembers':
			require_once SP_PLUGIN_DIR.'/admin/panel-components/forms/spa-components-special-ranks-add-form.php';
			spa_components_sr_add_members_form((int) $_GET['id']);
			break;

		case 'delmembers':
			require_once SP_PLUGIN_DIR.'/admin/panel-components/forms/spa-components-special-ranks-del-form.php';
			spa_components_sr_del_members_form((int) $_GET['id']);
			break;

		case 'messages':
			require_once SP_PLUGIN_DIR.'/admin/panel-components/forms/spa-components-messages-form.php';
			spa_components_messages_form();
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
