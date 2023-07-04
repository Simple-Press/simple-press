<?php
/*
Simple:Press
User groups map users form
$LastChangedDate: 2017-12-28 11:37:41 -0600 (Thu, 28 Dec 2017) $
$Rev: 15601 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_usergroups_map_users() {
?>
<script>
	(function(spj, $, undefined) {
		$(document).ready(function() {
			$('#sfmapsettingsform').ajaxForm({
				target: '#sfmsgspot',
				success: function() {
					$('#sfreloadmu').click();
					$('#sfmsgspot').fadeIn();
					$('#sfmsgspot').fadeOut(6000);
				}
			});
			$('#sfmapusersform').ajaxForm({
				target: '#sfmsgspot',
			});
		});
	}(window.spj = window.spj || {}, jQuery));
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
	spa_paint_open_tab(SP()->primitives->admin_text('User Mapping Settings'));

		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('User Memberships'), true, 'user-memberships');
				echo '<div class="sf-alert-block sf-caution">';
					SP()->primitives->admin_etext('Warning: Use caution when setting the single usergroup membership option below. It should primarily be used in conjunction with a membership plugin (such as Wishlist) where strict usergroup membership is required.  Please note that auto usergroup membership by WP role or by forum rank may conflict or overwrite any manual usergroup memberships (such as moderator) you may set if you have single usergroup membership set');
    			echo '</div>';
				spa_paint_checkbox(SP()->primitives->admin_text('Users are limited to single usergroup membership'), 'sfsinglemembership', $sfoptions['sfsinglemembership']);
                echo '<div class="sf-form-row">';
                    echo '<h3>'.SP()->primitives->admin_text('Default usergroup membership').':</h3>';
                echo '</div>';
				spa_paint_select_start(SP()->primitives->admin_text('Default usergroup for guests'), 'sfguestsgroup', 'sfguestsgroup');
                    echo spa_create_usergroup_select($sfoptions['sfguestsgroup']);
				spa_paint_select_end();

				spa_paint_select_start(SP()->primitives->admin_text('Default usergroup for new members'), 'sfdefgroup', 'sfdefgroup');
                    echo spa_create_usergroup_select($sfoptions['sfdefgroup']);
				spa_paint_select_end();

				$roles = array_keys($wp_roles->role_names);
				if ($roles) {
                    echo '<div class="sf-form-row">';
                        echo '<h3>'.SP()->primitives->admin_text('Usergroup memberships based on WP role').':</h3>';
                    echo '</div>';
					$sfoptions['role'] = array();
					foreach ($roles as $index => $role) {
						$value = SP()->meta->get('default usergroup', $role);
                        $group = $value ? $value[0]['meta_value'] : $sfoptions['sfdefgroup'];
						echo '<input type="hidden" class="sfhiddeninput" name="sfoldrole['.$index.']" value="'.$group.'" />';
						spa_paint_select_start(SP()->primitives->admin_text('Default usergroup for').' '.$role, "sfrole[$index]", 'sfguestsgroup');
						echo spa_create_usergroup_select($group);
						spa_paint_select_end();
					}
				}
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		do_action('sph_usergroups_mapping_settings_panel');

		spa_paint_close_container();
?>
	<div class="sf-form-submit-bar">
	<input type="submit" class="sf-button-primary" id="saveit" name="saveit" value="<?php SP()->primitives->admin_etext('Update Mapping Settings'); ?>" />
	</div>
	<?php spa_paint_close_tab(); ?>
	</form>
	<div class="sfform-panel-spacer"></div>
<?php
    $ajaxURL = wp_nonce_url(SPAJAXURL.'usergroups-loader&amp;saveform=mapusers', 'usergroups-loader');

   	$uCount = SP()->DB->count(SPMEMBERS);
	$url = wp_nonce_url(SPAJAXURL.'usermapping', 'usermapping');
	$target = 'sfmsgspot';
	$smessage = esc_js(SP()->primitives->admin_text('Please Wait - Processing'));
	$emessage = $uCount.' '.esc_js(SP()->primitives->admin_text('Users mapped'));
?>
	<form action="<?php echo $ajaxURL; ?>" method="post" id="sfmapusersform" name="sfmapusersform" onsubmit="spj.batch('sfmapusersform', '<?php echo $url; ?>', '<?php echo $target; ?>', '<?php echo $smessage; ?>', '<?php echo $emessage; ?>', 0, 500, <?php echo $uCount; ?>);">
<?php
	echo sp_create_nonce('forum-adminform_mapusers');
	spa_paint_options_init();
        spa_paint_open_nohead_tab(true);
		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('Map Users'), true, 'map-users');
				echo '<div class="sf-alert-block sf-warning">';
                    SP()->primitives->admin_etext("Warning: Use caution when mapping users. This will adjust your user's memberships in User Groups. Choose the criteria and options carefully. The mapping cannot be undone except by remapping or manual process. Also, make sure you have saved your mapping settings above before mapping as they are two distinct actions.");
    			echo '</div>';
				$values = [
                    SP()->primitives->admin_text('Add user membership based on WP role to existing memberships'),
                    SP()->primitives->admin_text('Replace all user memberships with a single membership based on WP role')
                ];
				spa_paint_radiogroup(SP()->primitives->admin_text('Select mapping criteria'), 'mapoption', $values, 2, false, true);
				spa_paint_checkbox(SP()->primitives->admin_text('Ignore current SP Moderators when mapping'), 'ignoremods', true);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		do_action('sph_usergroups_map_users_panel');

?>
    	<div class="sf-form-submit-bar">
        	<span><input type="submit" class="sf-button-primary" id="saveit2" name="saveit2" value="<?php SP()->primitives->admin_etext('Map Users'); ?>" /> <span class="_sf-button sf-hidden-important" id='onFinish'></span></span>
        	<br />
        	<div class="pbar" id="progressbar"></div>
    	</div>
        <?php spa_paint_close_container(); ?>
    <?php spa_paint_close_tab(); ?>
	</form>
<?php
}

function spa_create_usergroup_select($sfdefgroup) {
    $out = '';

    $ugid = SP()->DB->table(SPUSERGROUPS, "usergroup_id=$sfdefgroup", 'usergroup_id');
	if (empty($ugid)) $out.= '<option selected="selected" value="-1">INVALID</option>';

	$usergroups = spa_get_usergroups_all();
	foreach ($usergroups as $usergroup) {
		if ($usergroup->usergroup_id == $sfdefgroup) {
			$default = 'selected="selected" ';
		} else {
			$default = null;
		}
		$out.= '<option '.$default.'value="'.$usergroup->usergroup_id.'">'.SP()->displayFilters->title($usergroup->usergroup_name).'</option>';
	}
	return $out;
}
