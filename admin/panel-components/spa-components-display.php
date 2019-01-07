<?php
/*
Simple:Press
Admin Components Display Rendering
$LastChangedDate: 2014-06-20 22:47:00 -0500 (Fri, 20 Jun 2014) $
$Rev: 11582 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_render_components_panel($formid) {
?>
	<div class="clearboth"></div>

	<div class="wrap sfatag">
		<?php
			spa_render_sidemenu();
		?>
		<div id='sfmsgspot'></div>
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
			include_once SF_PLUGIN_DIR.'/admin/panel-components/forms/spa-components-smileys-form.php';
			spa_components_smileys_form();
			break;

		case 'login':
			include_once SF_PLUGIN_DIR.'/admin/panel-components/forms/spa-components-login-form.php';
			spa_components_login_form();
			break;

		case 'seo':
			include_once SF_PLUGIN_DIR.'/admin/panel-components/forms/spa-components-seo-form.php';
			spa_components_seo_form();
			break;

		case 'forumranks':
			include_once SF_PLUGIN_DIR.'/admin/panel-components/forms/spa-components-forumranks-form.php';
			spa_components_forumranks_form();
			break;

		case 'addmembers':
			include_once SF_PLUGIN_DIR.'/admin/panel-components/forms/spa-components-special-ranks-add-form.php';
			spa_components_sr_add_members_form($_GET['id']);
			break;

		case 'delmembers':
			include_once SF_PLUGIN_DIR.'/admin/panel-components/forms/spa-components-special-ranks-del-form.php';
			spa_components_sr_del_members_form($_GET['id']);
			break;

		case 'messages':
			include_once SF_PLUGIN_DIR.'/admin/panel-components/forms/spa-components-messages-form.php';
			spa_components_messages_form();
			break;

		case 'policies':
			include_once SF_PLUGIN_DIR.'/admin/panel-components/forms/spa-components-policies-form.php';
			spa_components_policies_form();
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