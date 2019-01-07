<?php
/*
Simple:Press
User groups map users form
$LastChangedDate: 2016-06-25 05:55:17 -0500 (Sat, 25 Jun 2016) $
$Rev: 14322 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_usergroups_map_users() {
?>
<script type="text/javascript">
    jQuery(document).ready(function() {
    	jQuery('#sfmapsettingsform').ajaxForm({
    		target: '#sfmsgspot',
    		success: function() {
    			jQuery('#sfreloadmu').click();
    			jQuery('#sfmsgspot').fadeIn();
    			jQuery('#sfmsgspot').fadeOut(6000);
    		}
    	});
    	jQuery('#sfmapusersform').ajaxForm({
    		target: '#sfmsgspot',
    	});
    });
</script>
<?php
	global $wp_roles;

    $sfoptions = spa_get_mapping_data();

    $ajaxURL = wp_nonce_url(SPAJAXURL.'usergroups-loader&amp;saveform=mapsettings', 'usergroups-loader');
?>
	<form action="<?php echo $ajaxURL; ?>" method="post" id="sfmapsettingsform" name="sfmapsettingsform">
	<?php echo sp_create_nonce('forum-adminform_mapusers'); ?>
<?php
	spa_paint_options_init();
	spa_paint_open_tab(spa_text('User Groups').' - '.spa_text('User Mapping Settings'), true);

		spa_paint_open_panel();
			spa_paint_open_fieldset(spa_text('User Memberships'), true, 'user-memberships');
    			echo '<br /><div class="sfoptionerror">';
    			spa_etext('Warning: Use caution when setting the single usergroup membership option below. It should primarily be used in conjunction with a membership plugin (such as Wishlist) where strict usergroup membership is required.  Please note that auto usergroup membership by WP role or by forum rank may conflict or overwrite any manual usergroup memberships (such as moderator) you may set if you have single usergroup membership set');
    			echo '</div><br />';
				spa_paint_checkbox(spa_text('Users are limited to single usergroup membership'), 'sfsinglemembership', $sfoptions['sfsinglemembership']);
				echo '<p class="subhead">'.spa_text('Default usergroup membership').':</p>';
				spa_paint_select_start(spa_text('Default usergroup for guests'), 'sfguestsgroup', 'sfguestsgroup');
				echo spa_create_usergroup_select($sfoptions['sfguestsgroup']);
				spa_paint_select_end();

				spa_paint_select_start(spa_text('Default usergroup for new members'), 'sfdefgroup', 'sfdefgroup');
				echo spa_create_usergroup_select($sfoptions['sfdefgroup']);
				spa_paint_select_end();

				$roles = array_keys($wp_roles->role_names);
				if ($roles) {
					echo '<p class="subhead">'.spa_text('Usergroup memberships based on WP role').':</p>';
					$sfoptions['role'] = array();
					foreach ($roles as $index => $role) {
						$value = sp_get_sfmeta('default usergroup', $role);
						if ($value) {
							$group = $value[0]['meta_value'];
						} else {
							$group = $sfoptions['sfdefgroup'];
						}
						echo '<input type="hidden" class="sfhiddeninput" name="sfoldrole['.$index.']" value="'.$group.'" />';
						spa_paint_select_start(spa_text('Default usergroup for').' '.$role, "sfrole[$index]", 'sfguestsgroup');
						echo spa_create_usergroup_select($group);
						spa_paint_select_end();
					}
				}
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		do_action('sph_usergroups_mapping_settings_panel');

		spa_paint_close_container();
?>
	<div class="sfform-submit-bar">
	<input type="submit" class="button-primary" id="saveit" name="saveit" value="<?php spa_etext('Update Mapping Settings'); ?>" />
	</div>
	<?php spa_paint_close_tab(); ?>
	</form>
	<div class="sfform-panel-spacer"></div>
<?php
    $ajaxURL = wp_nonce_url(SPAJAXURL.'usergroups-loader&amp;saveform=mapusers', 'usergroups-loader');

   	$uCount = spdb_count(SFMEMBERS);
	$url = wp_nonce_url(SPAJAXURL.'usermapping', 'usermapping');
	$target = 'sfmsgspot';
	$smessage = esc_js(spa_text('Please Wait - Processing'));
	$emessage = $uCount.' '.esc_js(spa_text('Users mapped'));
?>
	<form action="<?php echo $ajaxURL; ?>" method="post" id="sfmapusersform" name="sfmapusersform" onsubmit="spjBatch('sfmapusersform', '<?php echo $url; ?>', '<?php echo $target; ?>', '<?php echo $smessage; ?>', '<?php echo $emessage; ?>', 0, 500, <?php echo $uCount; ?>);">
<?php
	echo sp_create_nonce('forum-adminform_mapusers');
	spa_paint_options_init();
	spa_paint_open_tab(spa_text('User Groups').' - '.spa_text('Map Users'), true);
		spa_paint_open_panel();
			spa_paint_open_fieldset(spa_text('Map Users'), true, 'map-users');
    			echo '<br /><div class="sfoptionerror">';
    			spa_etext("Warning: Use caution when mapping users. This will adjust your user's memberships in User Groups. Choose the criteria and options carefully. The mapping cannot be undone except by remapping or manual process. Also, make sure you have saved your mapping settings above before mapping as they are two distinct actions.");
    			echo '</div><br />';
				$values = array(spa_text('Add user membership based on WP role to existing memberships'),
                                spa_text('Replace all user memberships with a single membership based on WP role'));
				spa_paint_radiogroup(spa_text('Select mapping criteria'), 'mapoption', $values, 2, false, true);
				spa_paint_checkbox(spa_text('Ignore current SP Moderators when mapping'), 'ignoremods', true);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		do_action('sph_usergroups_map_users_panel');

		spa_paint_close_container();
?>
    	<div class="sfform-submit-bar">
        	<span><input type="submit" class="button-primary" id="saveit2" name="saveit2" value="<?php spa_etext('Map Users'); ?>" /> <span class="button sfhidden" id='onFinish'></span></span>
        	<br />
        	<div class="pbar" id="progressbar"></div>
    	</div>
<?php
	spa_paint_close_tab();
?>
	</form>
<?php
}

function spa_create_usergroup_select($sfdefgroup) {
    $out = '';

    $ugid = spdb_table(SFUSERGROUPS, "usergroup_id=$sfdefgroup", 'usergroup_id');
	if (empty($ugid)) $out.= '<option selected="selected" value="-1">INVALID</option>';

	$usergroups = spa_get_usergroups_all();
	$default = '';
	foreach ($usergroups as $usergroup) {
		if ($usergroup->usergroup_id == $sfdefgroup) {
			$default = 'selected="selected" ';
		} else {
			$default = null;
		}
		$out.= '<option '.$default.'value="'.$usergroup->usergroup_id.'">'.sp_filter_title_display($usergroup->usergroup_name).'</option>';
		$default = '';
	}
	return $out;
}

?>