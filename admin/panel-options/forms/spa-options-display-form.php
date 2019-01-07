<?php
/*
Simple:Press
Admin Options Global Display Form
$LastChangedDate: 2016-12-28 04:25:23 -0600 (Wed, 28 Dec 2016) $
$Rev: 14924 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_options_display_form() {
?>
<script type="text/javascript">
    jQuery(document).ready(function() {
    	spjAjaxForm('sfdisplayform', '');
    });
</script>
<?php
	$sfoptions = spa_get_display_data();
    $ajaxURL = wp_nonce_url(SPAJAXURL.'options-loader&amp;saveform=display', 'options-loader');
?>
	<form action="<?php echo $ajaxURL; ?>" method="post" id="sfdisplayform" name="sfdisplay">
	<?php echo sp_create_nonce('forum-adminform_display'); ?>
<?php
	spa_paint_options_init();

    #== GLOBAL Tab ============================================================

	spa_paint_open_tab(spa_text('Options').' - '.spa_text('General Display Settings'));
		spa_paint_open_panel();
			spa_paint_open_fieldset(spa_text('Forum Page Title'), true, 'forum-page-title');
				spa_paint_checkbox(spa_text('Remove page title completely'), 'sfnotitle', $sfoptions['sfnotitle']);
				spa_paint_input(spa_text('Graphic replacement URL'), 'sfbanner', $sfoptions['sfbanner'], false, true);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_open_panel();
			spa_paint_open_fieldset(spa_text('Forum View Formatting'), true, 'topic-view-formatting');
				spa_paint_input(spa_text('Topics to display per page'), 'sfpagedtopics', $sfoptions['sfpagedtopics']);
				spa_paint_checkbox(spa_text('Sort topics by most recent postings (newest first)'), 'sftopicsort', $sfoptions['sftopicsort']);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_open_panel();
			spa_paint_open_fieldset(spa_text('Topic View Formatting'), true, 'post-view-formatting');
				spa_paint_input(spa_text('Posts to display per page'), 'sfpagedposts', $sfoptions['sfpagedposts']);
				spa_paint_checkbox(spa_text('Sort posts newest to oldest'), 'sfsortdesc', $sfoptions['sfsortdesc']);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		do_action('sph_options_display_left_panel');
	spa_paint_tab_right_cell();

		spa_paint_open_panel();
			spa_paint_open_fieldset(spa_text('Integrated Editor Toolbar'), true, 'editor-options-toolbar');
				spa_paint_checkbox(spa_text('Use the integrated editor options toolbar'), 'sftoolbar', $sfoptions['sftoolbar']);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_open_panel();
			spa_paint_open_fieldset(spa_text('Single Forum Sites'), true, 'single-forum-sites');
				spa_paint_checkbox(spa_text('Skip group view on single forum sites'), 'sfsingleforum', $sfoptions['sfsingleforum']);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_open_panel();
			spa_paint_open_fieldset(spa_text('Display Forum Stats'), true, 'display-forum-statistics');
				spa_paint_input(spa_text('Update interval for stats (in hours)'), 'statsinterval', $sfoptions['statsinterval'], false, false);
				spa_paint_input(spa_text('Display how many top posters'), 'showtopcount', $sfoptions['showtopcount'], false, false);
				spa_paint_input(spa_text('Display how many new users'), 'shownewcount', $sfoptions['shownewcount'], false, false);
				spa_paint_checkbox(spa_text('For members count exclude users in hidden user groups and in no user group'), 'hidemembers', $sfoptions['hidemembers']);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		do_action('sph_options_display_right_panel');

		spa_paint_close_container();
?>
	<div class="sfform-submit-bar">
	<input type="submit" class="button-primary" id="saveit" name="saveit" value="<?php spa_etext('Update Display Options'); ?>" />
	</div>
<?php
	spa_paint_close_tab();
?>
	</form>
<?php
}
?>