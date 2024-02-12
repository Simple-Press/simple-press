<?php
/*
Simple:Press
Admin Profile Options Form
$LastChangedDate: 2017-12-28 11:37:41 -0600 (Thu, 28 Dec 2017) $
$Rev: 15601 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_profiles_options_form() {
	require_once ABSPATH.'wp-admin/includes/plugin.php';
?>
<script>
	spj.loadAjaxForm('sfoptionsform', '');
</script>
<?php
	$sfoptions = spa_get_options_data();

    $ajaxURL = wp_nonce_url(SPAJAXURL.'profiles-loader&amp;saveform=options', 'profiles-loader');
?>
	<form action="<?php echo $ajaxURL; ?>" method="post" id="sfoptionsform" name="sfoptions">
	<?php echo sp_create_nonce('forum-adminform_options'); ?>
<?php
	spa_paint_options_init();

    #== PROFILE OPTIONS Tab ============================================================

	spa_paint_open_tab(/*SP()->primitives->admin_text('Profiles').' - '.*/SP()->primitives->admin_text('Profile Options'));
		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('Display Name Format'), true, 'display-name-format');
                echo '<div class="sf-alert-block sf-caution">';
                SP()->primitives->admin_etext('Warning: If you change the display name format, it may take some time on a large number of users to update them to the new format. Please be patient.');
                echo '</div>';
				spa_paint_checkbox(SP()->primitives->admin_text('Let member choose display name'), 'nameformat', $sfoptions['nameformat'] ?? false);
				spa_paint_select_start(SP()->primitives->admin_text('Display name format if member cannot choose').'<br />'.SP()->primitives->admin_text('(ignored if member allowed to choose)'), 'fixeddisplayformat', 'fixeddisplayformat');
				echo spa_display_name_format_options($sfoptions['fixeddisplayformat']);
				spa_paint_select_end();
			spa_paint_close_fieldset();
		spa_paint_close_panel();

        spa_paint_open_panel();
            spa_paint_open_fieldset(SP()->primitives->admin_text('Hide user info'), false);
                spa_paint_checkbox(SP()->primitives->admin_text('Hide personal information'), 'hideuserinfo', $sfoptions['hideuserinfo'] ?? false);
                echo '<span class="sf-sublabel sf-sublabel-small">This will hide firstname, lastname and e-mail from the userprofile.</span>';
            spa_paint_close_fieldset();
        spa_paint_close_panel();

        spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('Personal Photos'), true, 'personal-photos');
				spa_paint_input(SP()->primitives->admin_text('Maximum number of photos allowed'), 'photosmax', $sfoptions['photosmax'] ?? null, false, false);
				spa_paint_input(SP()->primitives->admin_text('Number of columns for photo display'), 'photoscols', $sfoptions['photoscols'] ?? null, false, false);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('Signature Image Size'), true, 'sig-images');
                echo '<div class="sf-form-row">';
                    echo SP()->primitives->admin_text('If you are allowing signature images (zero = not limited)');
                echo '</div>';
				spa_paint_input(SP()->primitives->admin_text('Maximum signature width (pixels)'), 'sfsigwidth', $sfoptions['sfsigwidth'] ?? 0);
				spa_paint_input(SP()->primitives->admin_text('Maximum signature height (pixels)'), 'sfsigheight', $sfoptions['sfsigheight'] ?? 0);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('First Forum Visit'), true, 'first-forum-visit');
				spa_paint_checkbox(SP()->primitives->admin_text('Display profile form on login'), 'firstvisit', $sfoptions['firstvisit'] ?? false);
            	$show_password_fields = apply_filters('show_password_fields', true);
        		if ($show_password_fields) spa_paint_checkbox(SP()->primitives->admin_text('Force password change'), 'forcepw', $sfoptions['forcepw'] ?? false);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		do_action('sph_profiles_options_left_panel');
		spa_paint_tab_right_cell();

		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('Display Profile Mode'), true, 'display-profile-mode');

    			echo '<div class="sf-alert-block sf-info">';
    			SP()->primitives->admin_etext('Note - if Popup Window is selected the option will be automatically switched to Forum Profile Page when viewed on a mobile phone device');
    			echo '</div>';

				$values = array(SP()->primitives->admin_text('Popup window'), SP()->primitives->admin_text('Forum profile page'), SP()->primitives->admin_text('BuddyPress profile'), SP()->primitives->admin_text('WordPress author page'), SP()->primitives->admin_text('Other page'), SP()->primitives->admin_text('Mingle profile'));
                if (!is_plugin_active('buddypress/bp-loader.php')) unset($values[2]); # dont show BP option if not active
                if (!is_plugin_active('mingle/mingle.php')) unset($values[5]); # dont show Mingle option if not active
				spa_paint_radiogroup(SP()->primitives->admin_text('Display profile information in'), 'displaymode', $values, $sfoptions['displaymode'], false, true);
				spa_paint_input(SP()->primitives->admin_text('URL for Other page'), 'displaypage', SP()->displayFilters->url($sfoptions['displaypage'] ?? ''), false, true);
				spa_paint_input(SP()->primitives->admin_text('Query String Variable Name'), 'displayquery', SP()->displayFilters->title($sfoptions['displayquery'] ?? ''), false, true);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('Profile Entry Form Mode'), true, 'profile-entry-form-mode');
				$values = array(SP()->primitives->admin_text('Forum profile form'), SP()->primitives->admin_text('WordPress profile form'), SP()->primitives->admin_text('BuddyPress profile'), SP()->primitives->admin_text('Other form'), SP()->primitives->admin_text('Mingle profile'));
                if (!is_plugin_active('buddypress/bp-loader.php')) unset($values[2]); # dont show BP option if not active
                if (!is_plugin_active('mingle/mingle.php')) unset($values[4]); # dont show Mingle option if not active
				spa_paint_radiogroup(SP()->primitives->admin_text('Enter profile information In'), 'formmode', $values, $sfoptions['formmode'], false, true);
				spa_paint_input(SP()->primitives->admin_text('URL for Other page'), 'formpage', SP()->displayFilters->url($sfoptions['formpage'] ?? ''), false, true);
				spa_paint_input(SP()->primitives->admin_text('Query string variable name'), 'formquery', SP()->displayFilters->title($sfoptions['formquery'] ?? ''), false, true);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('Profile Overview Message'), true, 'profile-message');
				$submessage = SP()->primitives->admin_text('Text you enter here will be displayed to the User on their profile overview page');
				spa_paint_wide_textarea(SP()->primitives->admin_text('Profile overview message'), 'sfprofiletext', SP()->editFilters->text($sfoptions['sfprofiletext'] ?? ''), $submessage);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		do_action('sph_profiles_options_right_panel');
		spa_paint_close_container();
?>
    	<div class="sf-form-submit-bar">
    	   <input type="submit" class="sf-button-primary" id="saveit" name="saveit" value="<?php SP()->primitives->admin_etext('Update Profile Options'); ?>" />
    	</div>
<?php
	spa_paint_close_tab();
?>
	</form>
<?php
}

# ------------------------------------------------------------------
# spa_display_name_format_options()
# Initializes the different options for the Display Name Format
# dropdown list.
# ------------------------------------------------------------------
function spa_display_name_format_options($option_id=0) {
	$options = array(0 => 'WP DisplayName',
					 1 => 'WP Login',
					 2 => 'FirstName',
					 3 => 'LastName',
					 4 => 'FirstName LastName',
					 5 => 'LastName, FirstName',
					 6 => 'FirstInitial LastName',
					 7 => 'FirstName LastInitial',
					 8 => 'First and Last Initials');

	$out = '';
	foreach ($options as $option_value => $option_name) {
		$selected_text = '';
		if (intval($option_value) == intval($option_id)) $selected_text = 'selected="selected" ';
		$out.= '<option '.$selected_text.'value="'.$option_value.'">'.$option_name.'</option>'."\n";
	}

	return $out;
}
