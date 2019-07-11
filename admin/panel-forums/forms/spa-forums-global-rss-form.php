<?php
/*
Simple:Press
Admin Forums Global RSS Settings Form
$LastChangedDate: 2017-12-28 11:37:41 -0600 (Thu, 28 Dec 2017) $
$Rev: 15601 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# function to display the add global permission set form. It is hidden until user clicks the add global permission set link
function spa_forums_global_rss_form() {

?>
<script>
   	spj.loadAjaxForm('sfnewglobalrss', 'sfreloadfd');
</script>
<?php
	spa_paint_options_init();

	$ajaxURL = wp_nonce_url(SPAJAXURL.'forums-loader&amp;saveform=globalrss', 'forums-loader');
?>
	<form action="<?php echo $ajaxURL; ?>" method="post" id="sfnewglobalrss" name="sfnewglobalrss">
<?php
		echo sp_create_nonce('forum-adminform_globalrss');
		spa_paint_open_tab(/*SP()->primitives->admin_text('Forums').' - '.*/SP()->primitives->admin_text('Global RSS Settings'), true);
			spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('Globally Enable/Disable RSS Feeds'), true, 'global-rss');

				spa_paint_input(SP()->primitives->admin_text('Replacement external RSS URL for all RSS').'<br />'.SP()->primitives->admin_text('Default').': <strong>'.SP()->spPermalinks->build_url('', '', 0, 0, 0, 1).'</strong>', 'sfallrssurl', SP()->options->get('sfallRSSurl'));

				$base = wp_nonce_url(SPAJAXURL.'forums-loader', 'forums-loader');
				$target = 'sfallrss';
				$image = SPADMINIMAGES;

				echo '<div class="sf-alert-block sf-info">';
				$rss_count = SP()->DB->count(SPFORUMS, 'forum_rss_private=0');
				echo SP()->primitives->admin_text('Enabled Forum RSS feeds').': '.$rss_count.'&nbsp;&nbsp;&nbsp;&nbsp;';
				$rss_count = SP()->DB->count(SPFORUMS, 'forum_rss_private=1');
				echo SP()->primitives->admin_text('Disabled Forum RSS feeds').': '.$rss_count;
				echo '</div>';
?>
				<input type="button" class="sf-button-secondary spLoadForm" value="<?php echo SP()->primitives->admin_text('Disable All RSS Feeds'); ?>" data-form="globalrssset" data-url="<?php echo $base; ?>" data-target="<?php echo $target; ?>" data-img="<?php echo $image; ?>" data-id="1" data-open="open" />
				<input type="button" class="sf-button-secondary spLoadForm" value="<?php echo SP()->primitives->admin_text('Enable All RSS Feeds'); ?>" data-form="globalrssset" data-url="<?php echo $base; ?>" data-target="<?php echo $target; ?>" data-img="<?php echo $image; ?>" data-id="0" data-open="open" />

				<div class="sfinline-form">  <!-- This row will hold ajax forms for the all rss -->
				    <div id="sfallrss"></div>
				</div>
<?php
			spa_paint_close_fieldset();
			spa_paint_close_panel();
			do_action('sph_forums_global_rss_panel');
		spa_paint_close_container();
?>
		<div class="sf-form-submit-bar">
		<input type="submit" class="sf-button-primary" id="saveit" name="saveit" value="<?php SP()->primitives->admin_etext('Update Global RSS Settings'); ?>" />
		</div>
	<?php spa_paint_close_tab(); ?>
	</form>
	<div class="sfform-panel-spacer"></div>
<?php
}
