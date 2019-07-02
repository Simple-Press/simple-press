<?php
/*
Simple:Press
Admin Forums Add Permission Form
$LastChangedDate: 2017-12-28 11:37:41 -0600 (Thu, 28 Dec 2017) $
$Rev: 15601 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# function to display the add new forum permission set form.  It is hidden until the add permission set link is clicked
function spa_forums_add_permission_form($forum_id) {
?>
<script>
   	spj.loadAjaxForm('sfpermissionnew<?php echo $forum_id; ?>', 'sfreloadfb');
</script>
<?php
	$forum = SP()->DB->table(SPFORUMS, "forum_id=$forum_id", 'row');

	echo '<div class="sfform-panel-spacer"></div>';

	spa_paint_options_init();

    $ajaxURL = wp_nonce_url(SPAJAXURL.'forums-loader&amp;saveform=addperm', 'forums-loader');
?>
	<form action="<?php echo $ajaxURL; ?>" method="post" id="sfpermissionnew<?php echo $forum->forum_id; ?>" name="sfpermissionnew<?php echo $forum->forum_id; ?>">
<?php
		echo sp_create_nonce('forum-adminform_permissionnew');
		spa_paint_open_tab(SP()->primitives->admin_text('Forums').' - '.SP()->primitives->admin_text('Manage Groups and Forums'), true);
			spa_paint_open_panel();
				spa_paint_open_fieldset(SP()->primitives->admin_text('Add Permission Set'), 'true', 'add-user-group-permission-set');
?>
					<table class="form-table">
						<tr>
							<td class="sflabel"><?php spa_display_usergroup_select(true, $forum->forum_id); ?></td>
						</tr><tr>
							<td class="sflabel"><?php spa_display_permission_select(); ?></td>
						</tr>
					</table>
					<input type="hidden" name="forum_id" value="<?php echo $forum->forum_id; ?>" />
<?php
				spa_paint_close_fieldset();
			spa_paint_close_panel();

			do_action('sph_forums_add_perm_panel');
		spa_paint_close_container();
?>
		<div class="sf-form-submit-bar">
		<input type="submit" class="sf-button-primary" id="permnew<?php echo $forum->forum_id; ?>" name="permnew<?php echo $forum->forum_id; ?>" value="<?php SP()->primitives->admin_etext('Add Permission Set'); ?>" />
		<input type="button" class="sf-button-primary spCancelForm" data-target="#newperm-<?php echo $forum->forum_id; ?>" name="addpermcancel<?php echo $forum->forum_id; ?>" value="<?php SP()->primitives->admin_etext('Cancel'	); ?>" />
		</div>
<?php spa_paint_close_tab(); ?>
	</form>
	<div class="sfform-panel-spacer"></div>
<?php
}
