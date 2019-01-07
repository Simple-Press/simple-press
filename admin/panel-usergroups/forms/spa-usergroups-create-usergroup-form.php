<?php
/*
Simple:Press
Admin User Groups Add User Group Form
$LastChangedDate: 2016-06-25 05:55:17 -0500 (Sat, 25 Jun 2016) $
$Rev: 14322 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# function to display the create user group form.  It is hidden until the create user group link is clicked
function spa_usergroups_create_usergroup_form() {
    global $spPaths;
?>
<script type="text/javascript">
    jQuery(document).ready(function() {
    	spjAjaxForm('sfusergroupnew', 'sfreloadub');
    });
</script>
<?php
	spa_paint_options_init();

    $ajaxURL = wp_nonce_url(SPAJAXURL.'usergroups-loader&amp;saveform=newusergroup', 'usergroups-loader');
?>
	<form action="<?php echo $ajaxURL; ?>" method="post" id="sfusergroupnew" name="sfusergroupnew">
<?php
		echo sp_create_nonce('forum-adminform_usergroupnew');
		spa_paint_open_tab(spa_text('User Groups').' - '.spa_text('Create New User Group'), true);
			spa_paint_open_panel();
				spa_paint_open_fieldset(spa_text('Create New User Group'), 'true', 'edit-user-group');
					spa_paint_input(spa_text('User Group Name'), 'usergroup_name', '', false, true);
					spa_paint_input(spa_text('User Group Description'), 'usergroup_desc', '', false, true);
					spa_paint_select_start(spa_text('Select Badge'), 'usergroup_badge', 'usergroup_badge');
					spa_select_icon_dropdown('usergroup_badge', spa_text('Select Badge'), SF_STORE_DIR.'/'.$spPaths['ranks'].'/', '', false);
					spa_paint_select_end('<small>('.spa_text('Upload badges on the Components - Forum Ranks admin panel').')</small>');
					spa_paint_checkbox(spa_text('Allow members to join usergroup'), 'usergroup_join', false, false, false, false, '<small>'.spa_text('(Indicates that members are allowed to choose to join this usergroup on their profile page)').'</small>');
					spa_paint_checkbox(spa_text('Hide members from user statistics'), 'hide_stats', false, false, false, false, '<small>'.spa_text('(This applies to the basic statistics optionally displayed on forum pages)').'</small>');
					spa_paint_checkbox(spa_text('Is moderator'), 'usergroup_is_moderator', false, false, false, false, '<small>'.spa_text('(Indicates that members of this usergroup are considered Moderators)').'</small>');
				spa_paint_close_fieldset();
			spa_paint_close_panel();
			do_action('sph_usergroup_create_panel');
		spa_paint_close_container();
?>
		<div class="sfform-submit-bar">
		<input type="submit" class="button-primary" id="saveit" name="saveit" value="<?php spa_etext('Create New User Group'); ?>" />
		</div>
	<?php spa_paint_close_tab(); ?>
	</form>
	<div class="sfform-panel-spacer"></div>
<?php
}
?>