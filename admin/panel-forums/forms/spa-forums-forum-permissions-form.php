<?php
/*
Simple:Press
Admin Forums Forum Permission Form
$LastChangedDate: 2017-08-05 17:36:04 -0500 (Sat, 05 Aug 2017) $
$Rev: 15488 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# function to display the current forum permission set.  It is hidden until the permission set link is clicked.
# additional forms to add, edit or delete these permission set are further hidden belwo the permission set information
function spa_forums_view_forums_permission_form($forum_id)
{
	$forum = SP()->DB->table(SPFORUMS, "forum_id=$forum_id", 'row');

	spa_paint_options_init();
	?><div class="sf-form"><?php
	spa_paint_open_tab(SP()->primitives->admin_text('Forums').' - '.SP()->primitives->admin_text('Manage Groups and Forums'), true);
		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('View Forum Permissions'), false);
				$perms = sp_get_forum_permissions($forum->forum_id);
				if ($perms) {
?>
					<table class="sfmaintable sf-forum-perms-table">
						<tr>
							<td class="sf-text-al-center" colspan="3"><strong><?php echo SP()->primitives->admin_text('Current permission set for forum').' '.SP()->displayFilters->title($forum->forum_name); ?></strong></td>
						</tr>
<?php
					foreach ($perms as $perm) {
						$usergroup = spa_get_usergroups_row($perm->usergroup_id);
						$role = spa_get_role_row($perm->permission_role);
?>
						<tr>
							<td class="sflabel"><?php echo SP()->displayFilters->title($usergroup->usergroup_name); ?> => <?php echo SP()->displayFilters->title($role->role_name); ?></td>
							<td class="sf-text-al-center" >
<?php
                                $base = wp_nonce_url(SPAJAXURL.'forums-loader', 'forums-loader');
								$target = "curperm-$perm->permission_id";
								$image = SPADMINIMAGES;
?>
								<input type="button" class="sf-button-secondary spStackBtnLong spLoadForm" value="<?php echo SP()->primitives->admin_text('Edit Permission Set'); ?>" data-form="editperm" data-url="<?php echo $base; ?>" data-target="<?php echo $target; ?>" data-img="<?php echo $image; ?>" data-id="<?php echo $perm->permission_id; ?>" data-open="" />
								<input type="button" class="sf-button-secondary spStackBtnLong spLoadForm" value="<?php echo SP()->primitives->admin_text('Delete Permission Set'); ?>" data-form="delperm" data-url="<?php echo $base; ?>" data-target="<?php echo $target; ?>" data-img="<?php echo $image; ?>" data-id="<?php echo $perm->permission_id; ?>" data-open="" />
							</td>
			   			</tr>
						<tr class="sfinline-form"> <!-- This row will hold hidden forms for the current forum permission set -->
						  	<td colspan="3">
								<div id="curperm-<?php echo $perm->permission_id; ?>">
							</td>
						</tr>
					<?php } ?>
				<?php } else { ?>
					<table class="sfmaintable sf-forum-perms-table">
						<tr>
							<td>
								<?php SP()->primitives->admin_etext('No permission sets for any usergroup'); ?>
							</td>
						</tr>
				<?php } ?>
			   			<tr>
			   				<td colspan="3" class="sf-text-al-center" >
<?php
                                $base = wp_nonce_url(SPAJAXURL.'forums-loader', 'forums-loader');
								$target = "newperm-$forum->forum_id";
								$image = SPADMINIMAGES;
?>
								<input type="button" class="sf-button-secondary spStackBtn spLoadForm" value="<?php echo SP()->primitives->admin_text('Add Permission'); ?>" data-form="addperm" data-url="<?php echo $base; ?>" data-target="<?php echo $target; ?>" data-img="<?php echo $image; ?>" data-id="<?php echo $forum->forum_id; ?>" data-open="open" />
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
		<div class="sf-form-submit-bar">
            <input type="button" class="sf-button-primary spCancelForm" data-target="#forum-<?php echo $forum->forum_id; ?>" name="forumcancel<?php echo $forum->forum_id; ?>" value="<?php SP()->primitives->admin_etext('Cancel'); ?>" />
		</div>
	</form>
	<?php spa_paint_close_tab(); ?>
	</div>
<?php
}
