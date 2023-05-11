<?php
/*
Simple:Press
Admin Forums Global RSS Set Form
$LastChangedDate: 2017-12-28 11:37:41 -0600 (Thu, 28 Dec 2017) $
$Rev: 15601 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# function to display the add global permission set form. It is hidden until user clicks the add global permission set link
function spa_forums_global_rssset_form($id) {
?>
<script>
  	spj.loadAjaxForm('sfglobalrssset', 'sfreloadfd');
</script>
<?php
	spa_paint_options_init();

    $ajaxURL = wp_nonce_url(SPAJAXURL.'forums-loader&amp;saveform=globalrssset', 'forums-loader');
?>
	<form action="<?php echo $ajaxURL; ?>" method="post" id="sfglobalrssset" name="sfglobalrssset">
<?php
		echo sp_create_nonce('forum-adminform_globalrssset');
		spa_paint_open_tab(SP()->primitives->admin_text('Forums').' - '.SP()->primitives->admin_text('Global RSS Settings'), true);
			spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('Globally Enable/Disable RSS Feeds'), false);
				echo '<tr><td colspan="2"><br />';
				echo '<div class="sf-alert-block sf-caution">';
				SP()->primitives->admin_etext('Warning: Enabling or disabling RSS feeds from this form will apply that setting to ALL forums and overwrite any existing RSS feed settings. If you wish to individually enable/disable RSS feeds for a single forum, please visit the manage forums admin panel - edit the forum and set the RSS feed status there');
				echo '<br /><br />';
				if ($id == 1) SP()->primitives->admin_etext('Please press the confirm button below to disable RSS feeds for all forums');
				if ($id == 0) SP()->primitives->admin_etext('Please press the confirm button below to enable RSS feeds for all forums');
				echo '</div><br />';
				echo '</td></tr>';
				echo '<input type="hidden" name="sfglobalrssset" value="'.$id.'" />';
			spa_paint_close_fieldset();
			spa_paint_close_panel();
			do_action('sph_forums_rss_panel');
		spa_paint_close_container();
?>
		<div class="sf-form-submit-bar">
			<input type="submit" class="sf-button-primary" id="saveit" name="saveit" value="<?php SP()->primitives->admin_etext('Confirm RSS Feed Status'); ?>" />
			<input type="button" class="sf-button-primary spCancelForm" data-target="#sfallrss" id="sfallrsscancel" name="sfallrsscancel" value="<?php SP()->primitives->admin_etext('Cancel'); ?>" />
		</div>
	<?php spa_paint_close_tab(); ?>
	</form>
	<div class="sfform-panel-spacer"></div>
<?php
}
