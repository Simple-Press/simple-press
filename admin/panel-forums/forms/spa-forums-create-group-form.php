<?php
/*
Simple:Press
Admin Forums Create Group Form
$LastChangedDate: 2016-06-25 05:55:17 -0500 (Sat, 25 Jun 2016) $
$Rev: 14322 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# function to display the create new group form. It is hidden until user clicks on the create new group link
function spa_forums_create_group_form() {
?>
<script type="text/javascript">
    jQuery(document).ready(function() {
    	spjAjaxForm('sfgroupnew', 'sfreloadfb');
    });
</script>
<?php
	global $spPaths;

	spa_paint_options_init();

    $ajaxURL = wp_nonce_url(SPAJAXURL.'forums-loader&amp;saveform=creategroup', 'forums-loader');
?>
	<form action="<?php echo $ajaxURL; ?>" method="post" id="sfgroupnew" name="sfgroupnew">
<?php
		echo sp_create_nonce('forum-adminform_groupnew');
		spa_paint_open_tab(spa_text('Forums').' - '.spa_text('Create New Group'), false);
			spa_paint_open_panel();
				spa_paint_open_fieldset(spa_text('Create New Group'), 'true', 'create-new-forum-group');

					spa_paint_input(spa_text('Group Name'), 'group_name', '', false, true);
					spa_paint_input(spa_text('Description'), 'group_desc', '', false, true);

					spa_paint_select_start(spa_text('Select Custom Icon'), 'group_icon', '');
					spa_select_icon_dropdown('group_icon', spa_text('Select Custom Icon'), SF_STORE_DIR.'/'.$spPaths['custom-icons'].'/', '', false);
					spa_paint_select_end();

					spa_paint_wide_textarea('Special group message to be displayed above forums', 'group_message', '');

					do_action('sph_forums_create_group_panel');

				spa_paint_close_fieldset();

			echo '<div class="sfoptionerror spaceabove">';
			echo sprintf(sp_text('To re-order your Groups, Forums and SubForums use the %s Order Groups and Forums %s option from the Forums Menu'), '<b>', '</b>');
			echo '</div>';

			spa_paint_close_panel();

		spa_paint_tab_right_cell();

			spa_paint_open_panel();
				spa_paint_open_fieldset(spa_text('Default User Group Permissions'), false);

					echo '<strong>'.spa_text('Set default usergroup permission sets for this group').'</strong><br />';
					echo spa_text('Note - This will not add or modify any current permissions. It is only a default setting for future forums created in this group.');

					# Permissions
					$usergroups = spa_get_usergroups_all();
					$roles = sp_get_all_roles();

					foreach ($usergroups as $usergroup) {
						echo '<input type="hidden" name="usergroup_id[]" value="'.$usergroup->usergroup_id.'" />';
						spa_paint_select_start(sp_filter_title_display($usergroup->usergroup_name), 'role[]', '');
						echo '<option value="-1">'.spa_text('Select permission set').'</option>';
						foreach ($roles as $role) {
							echo '<option value="'.$role->role_id.'">'.sp_filter_title_display($role->role_name).'</option>'."\n";
						}
						spa_paint_select_end();
					}

				spa_paint_close_fieldset();
			spa_paint_close_panel();
		spa_paint_close_container();
?>
		<div class="sfform-submit-bar">
		<input type="submit" class="button-primary" id="saveit" name="saveit" value="<?php spa_etext('Create New Group'); ?>" />
		</div>

	<?php spa_paint_close_tab(); ?>
	</form>
	<div class="sfform-panel-spacer"></div>
<?php
}

?>