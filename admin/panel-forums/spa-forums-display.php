<?php
/*
Simple:Press
Admin Forums Display Rendering
$LastChangedDate: 2015-06-29 08:44:31 -0500 (Mon, 29 Jun 2015) $
$Rev: 13045 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_render_forums_panel($formid) {
?>
	<div class="clearboth"></div>

	<div class="wrap sfatag">
		<?php
			spa_render_sidemenu();
		?>
		<div id='sfmsgspot'></div>
		<div id="sfmaincontainer">
			<?php spa_render_forums_container($formid); ?>
		</div>
			<div class="clearboth"></div>
	</div>
<?php
}

function spa_render_forums_container($formid) {
	switch ($formid) {
		case 'forums':
			include_once SF_PLUGIN_DIR.'/admin/panel-forums/spa-forums-display-main.php';
			spa_forums_forums_main();
			break;
		case 'ordering':
			$g = (isset($_GET['id'])) ? sp_esc_int($_GET['id']) : 0;
			include_once SF_PLUGIN_DIR.'/admin/panel-forums/forms/spa-forums-ordering-form.php';
			spa_forums_ordering_form($g);
			break;
		case 'creategroup':
			include_once SF_PLUGIN_DIR.'/admin/panel-forums/forms/spa-forums-create-group-form.php';
			spa_forums_create_group_form();
			break;
		case 'createforum':
			include_once SF_PLUGIN_DIR.'/admin/panel-forums/forms/spa-forums-create-forum-form.php';
			spa_forums_create_forum_form();
			break;
		case 'globalperm':
			include_once SF_PLUGIN_DIR.'/admin/panel-forums/forms/spa-forums-global-perm-form.php';
			spa_forums_global_perm_form();
			break;
		case 'removeperms':
			include_once SF_PLUGIN_DIR.'/admin/panel-forums/forms/spa-forums-remove-perms-form.php';
			spa_forums_remove_perms_form();
			break;
		case 'mergeforums':
			include_once SF_PLUGIN_DIR.'/admin/panel-forums/forms/spa-forums-merge-forums-form.php';
			spa_forums_merge_form();
			break;
		case 'globalrss':
			include_once SF_PLUGIN_DIR.'/admin/panel-forums/forms/spa-forums-global-rss-form.php';
			spa_forums_global_rss_form();
			break;
		case 'globalrssset':
			include_once SF_PLUGIN_DIR.'/admin/panel-forums/forms/spa-forums-global-rssset-form.php';
			spa_forums_global_rssset_form(sp_esc_int($_GET['id']));
			break;
		case 'groupperm':
			include_once SF_PLUGIN_DIR.'/admin/panel-forums/forms/spa-forums-group-permission-form.php';
			spa_forums_add_group_permission_form(sp_esc_int($_GET['id']));
			break;
		case 'editgroup':
			include_once SF_PLUGIN_DIR.'/admin/panel-forums/forms/spa-forums-edit-group-form.php';
			spa_forums_edit_group_form(sp_esc_int($_GET['id']));
			break;
		case 'deletegroup':
			include_once SF_PLUGIN_DIR.'/admin/panel-forums/forms/spa-forums-delete-group-form.php';
			spa_forums_delete_group_form(sp_esc_int($_GET['id']));
			break;
		case 'forumperm':
			include_once SF_PLUGIN_DIR.'/admin/panel-forums/forms/spa-forums-forum-permissions-form.php';
			spa_forums_view_forums_permission_form(sp_esc_int($_GET['id']));
			break;
		case 'editforum':
			include_once SF_PLUGIN_DIR.'/admin/panel-forums/forms/spa-forums-edit-forum-form.php';
			spa_forums_edit_forum_form(sp_esc_int($_GET['id']));
			break;
		case 'deleteforum':
			include_once SF_PLUGIN_DIR.'/admin/panel-forums/forms/spa-forums-delete-forum-form.php';
			spa_forums_delete_forum_form(sp_esc_int($_GET['id']));
			break;
		case 'disableforum':
			include_once SF_PLUGIN_DIR.'/admin/panel-forums/forms/spa-forums-disable-forum-form.php';
			spa_forums_disable_forum_form(sp_esc_int($_GET['id']));
			break;
		case 'enableforum':
			include_once SF_PLUGIN_DIR.'/admin/panel-forums/forms/spa-forums-enable-forum-form.php';
			spa_forums_enable_forum_form(sp_esc_int($_GET['id']));
			break;
		case 'addperm':
			include_once SF_PLUGIN_DIR.'/admin/panel-forums/forms/spa-forums-add-permission-form.php';
			spa_forums_add_permission_form(sp_esc_int($_GET['id']));
			break;
		case 'editperm':
			include_once SF_PLUGIN_DIR.'/admin/panel-forums/forms/spa-forums-edit-permission-form.php';
			spa_forums_edit_permission_form(sp_esc_int($_GET['id']));
			break;
		case 'delperm':
			include_once SF_PLUGIN_DIR.'/admin/panel-forums/forms/spa-forums-delete-permission-form.php';
			spa_forums_delete_permission_form(sp_esc_int($_GET['id']));
			break;
		case 'customicons':
			include_once SF_PLUGIN_DIR.'/admin/panel-forums/forms/spa-forums-custom-icons-form.php';
			spa_forums_custom_icons_form();
			break;
		case 'featuredimages':
			include_once SF_PLUGIN_DIR.'/admin/panel-forums/forms/spa-forums-featured-image-form.php';
			spa_forums_featured_image_form();
			break;

        # leave this for plugins to add to this panel
		case 'plugin':
			include_once(SF_PLUGIN_DIR.'/admin/panel-plugins/forms/spa-plugins-user-form.php');
            $admin = (isset($_GET['admin'])) ? sp_esc_str($_GET['admin']) : '';
            $save = (isset($_GET['save'])) ? sp_esc_str($_GET['save']) : '';
            $form = (isset($_GET['form'])) ? sp_esc_int($_GET['form']) : '';
            $reload = (isset($_GET['reload'])) ? sp_esc_str($_GET['reload']) : '';
			spa_plugins_user_form($admin, $save, $form, $reload);
			break;
	}
}
?>