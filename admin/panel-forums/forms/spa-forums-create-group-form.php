<?php
/*
Simple:Press
Admin Forums Create Group Form
$LastChangedDate: 2017-12-28 11:37:41 -0600 (Thu, 28 Dec 2017) $
$Rev: 15601 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# function to display the create new group form. It is hidden until user clicks on the create new group link
function spa_forums_create_group_form() {
?>
<script>
   	spj.loadAjaxForm('sfgroupnew', 'sfreloadfb');
</script>
<?php
	spa_paint_options_init();

    $ajaxURL = wp_nonce_url(SPAJAXURL.'forums-loader&amp;saveform=creategroup', 'forums-loader');
?>
	<form action="<?php echo $ajaxURL; ?>" method="post" id="sfgroupnew" name="sfgroupnew">
<?php
		echo sp_create_nonce('forum-adminform_groupnew');
		
		spa_paint_open_tab(SP()->primitives->admin_text('Create New Group'));

			spa_paint_open_panel();
				spa_paint_open_fieldset(SP()->primitives->admin_text('Group Details'), 'true', 'create-new-forum-group');

                    spa_paint_input(SP()->primitives->admin_text('Group name'), 'group_name', '', false, false,'sf-half');

					$custom_icons =  spa_get_custom_icons();

					spa_select_iconset_icon_picker('group_icon', SP()->primitives->admin_text('Group Icon'), array( 'Custom Icons' => $custom_icons ), '',true, 'sf-half' );

					spa_paint_input(SP()->primitives->admin_text('Description'), 'group_desc', '', false, true);

					spa_paint_wide_textarea('Message', 'group_message', '','','4', 'Special group message to be displayed above forums');

					do_action('sph_forums_create_group_panel');

				spa_paint_close_fieldset();

			spa_paint_close_panel();

		spa_paint_tab_right_cell();

			spa_paint_open_panel();
				spa_paint_open_fieldset(SP()->primitives->admin_text('Group Permissions'), true, 'create-new-forum-permissions');

					echo '<div class="sf-alert-block sf-info">';
					echo SP()->primitives->admin_text('This will not add or modify any current permissions.');
					echo '</div>';

					# Permissions
					$usergroups = spa_get_usergroups_all();
					$roles = sp_get_all_roles();

					foreach ($usergroups as $usergroup) {
						echo '<input type="hidden" name="usergroup_id[]" value="'.$usergroup->usergroup_id.'" />';
						spa_paint_select_start(SP()->displayFilters->title($usergroup->usergroup_name), 'role[]', '');
						echo '<option value="-1">'.SP()->primitives->admin_text('Select permission set').'</option>';
						foreach ($roles as $role) {
							echo '<option value="'.$role->role_id.'">'.SP()->displayFilters->title($role->role_name).'</option>'."\n";
						}
						spa_paint_select_end();
					}

				spa_paint_close_fieldset();
			spa_paint_close_panel();
		spa_paint_close_container();
?>
		<div class="sf-form-submit-bar">
		<input type="submit" class="sf-button-primary" id="saveit" name="saveit" value="<?php SP()->primitives->admin_etext('Create Group'); ?>" />
		</div>

	<?php spa_paint_close_tab(); ?>
	</form>
	<div class="sfform-panel-spacer"></div>
<?php
}
