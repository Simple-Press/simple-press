<?php
/*
Simple:Press
Admin Options Members Form
$LastChangedDate: 2018-12-03 11:05:54 -0600 (Mon, 03 Dec 2018) $
$Rev: 15840 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_options_members_form() {
?>
<script>
   	spj.loadAjaxForm('sfmembersform', 'sfreloadms');
</script>
<?php
	$sfoptions = spa_get_members_data();

    $ajaxURL = wp_nonce_url(SPAJAXURL.'options-loader&amp;saveform=members', 'options-loader');
?>
	<form action="<?php echo $ajaxURL; ?>" method="post" id="sfmembersform" name="sfmembers">
	<?php echo sp_create_nonce('forum-adminform_members'); ?>
<?php
	spa_paint_options_init();

    #== MEMBERS Tab ============================================================

	spa_paint_open_tab(/*SP()->primitives->admin_text('Options').' - '.*/SP()->primitives->admin_text('Member Settings'));
		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('Member Profiles'), true, 'member-profiles');
				spa_paint_checkbox(SP()->primitives->admin_text('Disallow members not logged in to post as guests'), 'sfcheckformember', $sfoptions['sfcheckformember']);
				spa_paint_checkbox(SP()->primitives->admin_text('Allow members to hide their online status'), 'sfhidestatus', $sfoptions['sfhidestatus']);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('Member Name Linking'), true, 'member-name-linking');
				$values = array(SP()->primitives->admin_text('Nothing'), SP()->primitives->admin_text("Member's profile"), SP()->primitives->admin_text("Member's website"));
				spa_paint_radiogroup(SP()->primitives->admin_text("Link a member's name when displayed to"), 'namelink', $values, $sfoptions['namelink'], false, true);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('Guest Settings'), true, 'guest-settings');
				spa_paint_checkbox(SP()->primitives->admin_text('Require guests to enter email address'), 'reqemail', $sfoptions['reqemail']);
				spa_paint_checkbox(SP()->primitives->admin_text('Store guest information in a cookie for subsequent visits'), 'storecookie', $sfoptions['storecookie']);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('Inactive Members Account Auto Removal'), true, 'user-removal');
				echo '<div class="sf-alert-block sf-info">';
				SP()->primitives->admin_etext('Remember - users are members of your WordPress site NOT members of Simple:Press. WordPress performs the actual user deletion which will include any components (like blog posts for example) that the user may have contributed. Use with care!');
				echo '</div>';
				spa_paint_checkbox(SP()->primitives->admin_text('Enable auto removal of member accounts'), 'sfuserremove', $sfoptions['sfuserremove'] ?? false);
				spa_paint_checkbox(SP()->primitives->admin_text('Remove inactive members (if auto removal enabled)'), 'sfuserinactive', $sfoptions['sfuserinactive'] ?? false );
				spa_paint_checkbox(SP()->primitives->admin_text('Remove members who have not posted  (if auto removal enabled)'), 'sfusernoposts', $sfoptions['sfusernoposts'] ?? false);
				spa_paint_input(SP()->primitives->admin_text('Number of days back to remove inactive members and/or members with no forum posts (if auto removal enabled)'), 'sfuserperiod', $sfoptions['sfuserperiod'] ?? null);
				if ($sfoptions['sched']) {
					$msg = SP()->primitives->admin_text('Users auto removal cron job is scheduled to run daily');
					echo '<tr><td class="message" colspan="2" class="sf-line-h-2-em">&nbsp;<u>'.$msg.'</u></td></tr>';
				}
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('Post Counts on Deletion'), true, 'delete-count');
				spa_paint_checkbox(SP()->primitives->admin_text('Adjust users post count when post deleted'), 'post_count_delete', $sfoptions['post_count_delete']);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		do_action('sph_options_members_left_panel');

		spa_paint_tab_right_cell();

		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('Blacklists'), true, 'member-blacklists');
    			$submessage = SP()->primitives->admin_text('Enter a comma separated list of account names to disallow when a user registers');
				spa_paint_wide_textarea(SP()->primitives->admin_text('Blocked account names'), 'account-name', $sfoptions['account-name'], $submessage);
    			$submessage = SP()->primitives->admin_text('Enter a comma separated list of display names to disallow for users');
				spa_paint_wide_textarea(SP()->primitives->admin_text('Blocked display names'), 'display-name', $sfoptions['display-name'], $submessage);
    			$submessage = SP()->primitives->admin_text('Enter a comma separated list of guest names to disallow when a guest posts');
				spa_paint_wide_textarea(SP()->primitives->admin_text('Blocked guest posting names'), 'guest-name', $sfoptions['guest-name'], $submessage);
			spa_paint_close_fieldset();
		spa_paint_close_panel();
		
		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('Deprecated Identities'), true, 'member-deprecated-identities');
				echo '<div class="sf-alert-block sf-info">'.SP()->primitives->admin_text('Certain identities such as AIM are part of services that no longer exists. We have removed these from the main user profile screen so that users can no longer enter new data for these identities. If you would still like your users to see these, turn this option on.').'</div>';
				spa_paint_checkbox(SP()->primitives->admin_text('Display Deprecated Identities'), 'sfdisplaydeprecatedidentities', $sfoptions['display_deprecated_identities']);
			spa_paint_close_fieldset();
		spa_paint_close_panel();		
		

		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('Privacy Data Export'), true, 'privacy-export');
				spa_paint_checkbox(SP()->primitives->admin_text('Include Forum Posts in Data Export'), 'posts', $sfoptions['posts']);
				do_action('sph_options_members_privacy_export');
				spa_paint_input(SP()->primitives->admin_text('Number of posts to batch process'), 'number', $sfoptions['number'], false, false, null, SP()->primitives->admin_text('Please note that users with a large number of posts may cause the exporter to run out of available resources') );
                ///function spa_paint_input($label, $name, $value, $disabled=false, $large=false, $css_classes='', $sublabel =null,  $sublabel_class='sf-sublabel sf-sublabel-small') {
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('Privacy Data Erasure'), true, 'privacy-erasure');
				$values = array(SP()->primitives->admin_text('Anonymize user in forum posts as guest'), SP()->primitives->admin_text("Erase forum posts completely"));
				spa_paint_radiogroup(SP()->primitives->admin_text("Select forum posts erasure method"), 'erase', $values, $sfoptions['erase'], false, true);
				spa_paint_wide_textarea(SP()->primitives->admin_text('Message to display in anonymized forum posts'), 'mess', $sfoptions['mess']);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		do_action('sph_options_members_right_panel');

		spa_paint_close_container();
?>
	<div class="sf-form-submit-bar">
	<input type="submit" class="sf-button-primary" id="saveit" name="saveit" value="<?php SP()->primitives->admin_etext('Update Members Options'); ?>" />
	</div>
<?php
	spa_paint_close_tab();
?>
	</form>
<?php
}

function spa_create_usergroup_select($sfdefgroup) {
    $out = '';

    $ugid = SP()->DB->table(SPUSERGROUPS, "usergroup_id=$sfdefgroup", 'usergroup_id');
	if (empty($ugid)) $out.= '<option selected="selected" value="-1">INVALID</option>';

	$usergroups = spa_get_usergroups_all();
	$default='';
	foreach ($usergroups as $usergroup) {
		if ($usergroup->usergroup_id == $sfdefgroup) {
			$default = 'selected="selected" ';
		} else {
			$default = null;
		}
		$out.= '<option '.$default.'value="'.$usergroup->usergroup_id.'">'.SP()->displayFilters->title($usergroup->usergroup_name).'</option>';
		$default = '';
	}
	return $out;
}
