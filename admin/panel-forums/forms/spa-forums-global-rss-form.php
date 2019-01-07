<?php
/*
Simple:Press
Admin Forums Global RSS Settings Form
$LastChangedDate: 2016-10-21 20:37:22 -0500 (Fri, 21 Oct 2016) $
$Rev: 14651 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# function to display the add global permission set form. It is hidden until user clicks the add global permission set link
function spa_forums_global_rss_form() {

?>
<script type="text/javascript">
    jQuery(document).ready(function() {
    	spjAjaxForm('sfnewglobalrss', 'sfreloadfd');
    });
</script>
<?php
	spa_paint_options_init();

	$ajaxURL = wp_nonce_url(SPAJAXURL.'forums-loader&amp;saveform=globalrss', 'forums-loader');
?>
	<form action="<?php echo $ajaxURL; ?>" method="post" id="sfnewglobalrss" name="sfnewglobalrss">
<?php
		echo sp_create_nonce('forum-adminform_globalrss');
		spa_paint_open_tab(spa_text('Forums').' - '.spa_text('Global RSS Settings'), true);
			spa_paint_open_panel();
			spa_paint_open_fieldset(spa_text('Globally Enable/Disable RSS Feeds'), true, 'global-rss');

				spa_paint_input(spa_text('Replacement external RSS URL for all RSS').'<br />'.spa_text('Default').': <strong>'.sp_build_url('', '', 0, 0, 0, 1).'</strong>', 'sfallrssurl', sp_get_option('sfallRSSurl'));

				$base = wp_nonce_url(SPAJAXURL.'forums-loader', 'forums-loader');
				$target = 'sfallrss';
				$image = SFADMINIMAGES;

				$rss_count = spdb_count(SFFORUMS, 'forum_rss_private=0');
				echo spa_text('Enabled Forum RSS feeds').': '.$rss_count.'&nbsp;&nbsp;&nbsp;&nbsp;';
				$rss_count = spdb_count(SFFORUMS, 'forum_rss_private=1');
				echo spa_text('Disabled Forum RSS feeds').': '.$rss_count.'<hr />';
?>
				<input type="button" class="button-secondary spLoadForm" value="<?php echo spa_text('Disable All RSS Feeds'); ?>" data-form="globalrssset" data-url="<?php echo $base; ?>" data-target="<?php echo $target; ?>" data-img="<?php echo $image; ?>" data-id="1" data-open="open" />
				<input type="button" class="button-secondary spLoadForm" value="<?php echo spa_text('Enable All RSS Feeds'); ?>" data-form="globalrssset" data-url="<?php echo $base; ?>" data-target="<?php echo $target; ?>" data-img="<?php echo $image; ?>" data-id="0" data-open="open" />

				<div class="sfinline-form">  <!-- This row will hold ajax forms for the all rss -->
				    <div id="sfallrss"></div>
				</div>
<?php
			spa_paint_close_fieldset();
			spa_paint_close_panel();
			do_action('sph_forums_global_rss_panel');
		spa_paint_close_container();
?>
		<div class="sfform-submit-bar">
		<input type="submit" class="button-primary" id="saveit" name="saveit" value="<?php spa_etext('Update Global RSS Settings'); ?>" />
		</div>
	<?php spa_paint_close_tab(); ?>
	</form>
	<div class="sfform-panel-spacer"></div>
<?php
}
?>