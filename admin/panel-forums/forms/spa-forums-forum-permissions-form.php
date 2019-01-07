<?php
/*
Simple:Press
Admin Forums Forum Permission Form
$LastChangedDate: 2016-10-21 20:37:22 -0500 (Fri, 21 Oct 2016) $
$Rev: 14651 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# function to display the current forum permission set.  It is hidden until the permission set link is clicked.
# additional forms to add, edit or delete these permission set are further hidden belwo the permission set information
function spa_forums_view_forums_permission_form($forum_id)
{
	$forum = spdb_table(SFFORUMS, "forum_id=$forum_id", 'row');

	spa_paint_options_init();
	spa_paint_open_tab(spa_text('Forums').' - '.spa_text('Manage Groups and Forums'), true);
		spa_paint_open_panel();
			spa_paint_open_fieldset(spa_text('View Forum Permissions'), false);
				$perms = sp_get_forum_permissions($forum->forum_id);
				if ($perms) {
?>
					<table class="sfmaintable" style="padding:5px;border-spacing:3px;border-collapse:separate;">
						<tr>
							<td style="text-align:center" colspan="3"><strong><?php echo spa_text('Current permission set for forum').' '.sp_filter_title_display($forum->forum_name); ?></strong></td>
						</tr>
<?php
					foreach ($perms as $perm) {
						$usergroup = spa_get_usergroups_row($perm->usergroup_id);
						$role = spa_get_role_row($perm->permission_role);
?>
						<tr>
							<td class="sflabel"><?php echo sp_filter_title_display($usergroup->usergroup_name); ?> => <?php echo sp_filter_title_display($role->role_name); ?></td>
							<td style="text-align:center">
<?php
                                $base = wp_nonce_url(SPAJAXURL.'forums-loader', 'forums-loader');
								$target = "curperm-$perm->permission_id";
								$image = SFADMINIMAGES;
?>
								<input type="button" class="button-secondary spStackBtnLong spLoadForm" value="<?php echo spa_text('Edit Permission Set'); ?>" data-form="editperm" data-url="<?php echo $base; ?>" data-target="<?php echo $target; ?>" data-img="<?php echo $image; ?>" data-id="<?php echo $perm->permission_id; ?>" data-open="" />
								<input type="button" class="button-secondary spStackBtnLong spLoadForm" value="<?php echo spa_text('Delete Permission Set'); ?>" data-form="delperm" data-url="<?php echo $base; ?>" data-target="<?php echo $target; ?>" data-img="<?php echo $image; ?>" data-id="<?php echo $perm->permission_id; ?>" data-open="" />
							</td>
			   			</tr>
						<tr class="sfinline-form"> <!-- This row will hold hidden forms for the current forum permission set -->
						  	<td colspan="3">
								<div id="curperm-<?php echo $perm->permission_id; ?>">
							</td>
						</tr>
					<?php } ?>
				<?php } else { ?>
					<table class="sfmaintable" style="padding:5px;border-spacing:3px;border-collapse:separate;">
						<tr>
							<td>
								<?php spa_etext('No permission sets for any usergroup'); ?>
							</td>
						</tr>
				<?php } ?>
			   			<tr>
			   				<td colspan="3" style="text-align:center">
<?php
                                $base = wp_nonce_url(SPAJAXURL.'forums-loader', 'forums-loader');
								$target = "newperm-$forum->forum_id";
								$image = SFADMINIMAGES;
?>
								<input type="button" class="button-secondary spStackBtn spLoadForm" value="<?php echo spa_text('Add Permission'); ?>" data-form="addperm" data-url="<?php echo $base; ?>" data-target="<?php echo $target; ?>" data-img="<?php echo $image; ?>" data-id="<?php echo $forum->forum_id; ?>" data-open="open" />
			   				</td>
						</tr>
						<tr class="sfinline-form"> <!-- This row will hold ajax forms for adding a new forum permission set -->
						  	<td colspan="3">
								<div id="newperm-<?php echo $forum->forum_id; ?>">
								</div>
							</td>
						</tr>
					</table>
<?php
			spa_paint_close_fieldset();
		spa_paint_close_panel();
		spa_paint_close_container();
?>
	<form>
		<div class="sfform-submit-bar">
            <input type="button" class="button-primary spCancelForm" data-target="#forum-<?php echo $forum->forum_id; ?>" name="forumcancel<?php echo $forum->forum_id; ?>" value="<?php spa_etext('Cancel'); ?>" />
		</div>
	</form>
	<?php spa_paint_close_tab(); ?>
	<div class="sfform-panel-spacer"></div>
<?php
}
?>