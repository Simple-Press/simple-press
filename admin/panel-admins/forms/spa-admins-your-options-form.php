<?php
/*
Simple:Press
Admin Admins Your Options Form
$LastChangedDate: 2017-12-28 11:37:41 -0600 (Thu, 28 Dec 2017) $
$Rev: 15601 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_admins_your_options_form() {
?>
<script>
   	spj.loadAjaxForm('sfmyadminoptionsform', 'sfreloadao');
</script>
<?php
	$sfadminsettings = spa_get_admins_your_options_data();

    $ajaxURL = wp_nonce_url(SPAJAXURL.'admins-loader&amp;saveform=youradmin', 'admins-loader');
?>
	<form action="<?php echo $ajaxURL; ?>" method="post" id="sfmyadminoptionsform" name="sfmyadminoptions">
	<?php echo sp_create_nonce('my-admin_options'); ?>
<?php
	spa_paint_options_init();
	spa_paint_open_tab(/*SP()->primitives->admin_text('Admins').' - '.*/SP()->primitives->admin_text('Your Admin Options'), false);

		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('Your Admin/Moderator Options'), true, 'your-admin-options');

				if (SP()->user->thisUser->admin) {
					echo '<div class="sf-alert-block sf-info"><strong>';
					SP()->primitives->admin_etext('The following options are personal - each admin and moderator needs to visit this panel to set their own options');
					echo '</strong><br />';
					SP()->primitives->admin_etext('Alternatively you can check the option below to apply to all moderators and when you update this panel they will inherit the same option settings');
					echo '</div>';
				}

				spa_paint_checkbox(SP()->primitives->admin_text('Receive email notification on new topic/post'), 'sfnotify', $sfadminsettings['sfnotify'] ?? false);
				spa_paint_checkbox(SP()->primitives->admin_text('Receive notification (within forum - not email) on topic/post edits'), 'notify-edited', $sfadminsettings['notify-edited'] ?? false);
				spa_paint_checkbox(SP()->primitives->admin_text('Bypass the Simple:Press logout redirect'), 'bypasslogout', $sfadminsettings['bypasslogout'] ?? false);
			spa_paint_close_fieldset();
		spa_paint_close_panel();		
		
		do_action('sph_admins_options_top_panel');

		if (SP()->user->thisUser->admin) {
			spa_paint_open_panel();
				spa_paint_open_fieldset(SP()->primitives->admin_text('Set Your Moderator Options'), 'true', 'set-moderator-options');
					spa_paint_checkbox(SP()->primitives->admin_text('Grant all moderators the same option settings as above'), 'setmods', $sfadminsettings['setmods'] ?? false);

					echo '<div class="sf-alert-block sf-info"><strong>';
					SP()->primitives->admin_etext('If you check this option so that all your moderators inherit the settings above - note that after you update this panel this checkbox will return to an unchecked state');
					echo '</strong><br />';
					SP()->primitives->admin_etext('Inheritance ONLY takes place when this box is checked and the panel updated. Any updated changes you make with the box unchecked ONLY apply to you');
					echo '</div>';

				spa_paint_close_fieldset();
			spa_paint_close_panel();
		}
		
		do_action('sph_admins_options_bottom_panel');
		
		spa_paint_tab_right_cell();
		
		do_action('sph_admins_options_right_panel');		
		
		spa_paint_close_container();
?>
	<div class="sf-form-submit-bar">
	<input type="submit" class="sf-button-primary" id="saveit" name="saveit" value="<?php SP()->primitives->admin_etext('Update Your Admin Options'); ?>" />
	</div>
<?php
	spa_paint_close_tab();
?>
	</form>
<?php
}
