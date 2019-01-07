<?php
/*
Simple:Press
Admin themes custom css
$LastChangedDate: 2015-04-30 03:41:40 +0100 (Thu, 30 Apr 2015) $
$Rev: 12814 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_themes_css_form() {
	$css = '';
	$id = 0;
	# get current theme
	$curTheme = sp_get_option('sp_current_theme');
	$rec = sp_get_sfmeta('css', $curTheme['theme']);
	if($rec) {
		$css = $rec[0]['meta_value'];
		$id = $rec[0]['meta_id'];
	}
?>
<script type="text/javascript">
jQuery(document).ready(function() {
	spjAjaxForm('speditcss', '');
});
</script>
<?php
    $ajaxURL = wp_nonce_url(SPAJAXURL.'themes-loader&amp;saveform=css', 'themes-loader');
?>
	<form action="<?php echo $ajaxURL; ?>" method="post" id="speditcss" name="speditcss">
	<?php echo sp_create_nonce('forum-adminform_css-editor'); ?>
<?php

	spa_paint_options_init();
	spa_paint_open_tab(spa_text('CSS Editor').' - '.spa_text('Custom Simple:Press Theme CSS'), true);
	spa_paint_open_panel();
	spa_paint_open_fieldset(spa_text('CSS Editor'), true, 'css-editor');

	echo '<div>';
	echo '<textarea rows="25" name="spnewcontent" id="spnewcontent" tabindex="1">'.$css.'</textarea>';
	echo '<input type="hidden" name="metaId" value="'.$id.'" />';
	echo '</div>';

	spa_paint_close_fieldset();
	spa_paint_close_panel();
	spa_paint_close_container();
?>
    	<div class="sfform-submit-bar">
    	   <input type="submit" class="button-primary" id="saveit" name="saveit" value="<?php spa_etext('Update CSS'); ?>" />
    	</div>
<?php
	spa_paint_close_tab();

	echo '</form>';
}

?>