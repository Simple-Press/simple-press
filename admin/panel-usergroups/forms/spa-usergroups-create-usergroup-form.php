<?php
/*
Simple:Press
Admin User Groups Add User Group Form
$LastChangedDate: 2017-12-28 11:37:41 -0600 (Thu, 28 Dec 2017) $
$Rev: 15601 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# function to display the create user group form.  It is hidden until the create user group link is clicked
function spa_usergroups_create_usergroup_form() {
?>
<script>
   	spj.loadAjaxForm('sfusergroupnew', 'sfreloadub');
</script>
<?php
	spa_paint_options_init();

    $ajaxURL = wp_nonce_url(SPAJAXURL.'usergroups-loader&amp;saveform=newusergroup', 'usergroups-loader');
?>
	<form action="<?php echo $ajaxURL; ?>" method="post" id="sfusergroupnew" name="sfusergroupnew">
<?php
		echo sp_create_nonce('forum-adminform_usergroupnew');
		spa_paint_open_tab(/*SP()->primitives->admin_text('User Groups').' - '.*/SP()->primitives->admin_text('Create New User Group'), true);
			spa_paint_open_panel();
				spa_paint_open_fieldset(SP()->primitives->admin_text('Create New User Group'), 'true', 'edit-user-group');
					spa_paint_input(SP()->primitives->admin_text('User Group Name'), 'usergroup_name', '', false, true);
					spa_paint_input(SP()->primitives->admin_text('User Group Description'), 'usergroup_desc', '', false, true);
					spa_paint_select_start(SP()->primitives->admin_text('Select Badge'), 'usergroup_badge', 'usergroup_badge');
					spa_select_icon_dropdown('usergroup_badge', SP()->primitives->admin_text('Select Badge'), SP_STORE_DIR.'/'.SP()->plugin->storage['ranks'].'/', '', false);
					spa_paint_select_end('<span class="sf-sublabel sf-sublabel-small">('.SP()->primitives->admin_text('Upload badges on the Components - Forum Ranks admin panel').')</span>');
					spa_paint_checkbox(SP()->primitives->admin_text('Allow members to join usergroup'), 'usergroup_join', false, false, false, false, '<span class="sf-sublabel sf-sublabel-small">'.SP()->primitives->admin_text('(Indicates that members are allowed to choose to join this usergroup on their profile page)').'</span>');
					spa_paint_checkbox(SP()->primitives->admin_text('Hide members from user statistics'), 'hide_stats', false, false, false, false, '<span class="sf-sublabel sf-sublabel-small">'.SP()->primitives->admin_text('(This applies to the basic statistics optionally displayed on forum pages)').'</span>');
					spa_paint_checkbox(SP()->primitives->admin_text('Is moderator'), 'usergroup_is_moderator', false, false, false, false, '<span class="sf-sublabel sf-sublabel-small">'.SP()->primitives->admin_text('(Indicates that members of this usergroup are considered Moderators)').'</span>');
				spa_paint_close_fieldset();
			spa_paint_close_panel();
			do_action('sph_usergroup_create_panel');
		spa_paint_close_container();
?>
		<div class="sf-form-submit-bar">
		<input type="submit" class="sf-button-primary" id="saveit" name="saveit" value="<?php SP()->primitives->admin_etext('Create New User Group'); ?>" />
		</div>
	<?php spa_paint_close_tab(); ?>
	</form>
	<div class="sfform-panel-spacer"></div>
<?php
}
