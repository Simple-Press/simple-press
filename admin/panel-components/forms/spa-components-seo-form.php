<?php
/*
Simple:Press
Admin Components SEO Form
$LastChangedDate: 2016-06-25 05:55:17 -0500 (Sat, 25 Jun 2016) $
$Rev: 14322 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_components_seo_form() {
?>
<script type="text/javascript">
    jQuery(document).ready(function() {
    	spjAjaxForm('sfseoform', 'sfreloadse');
    });
</script>
<?php
	$sfcomps = spa_get_seo_data();

    $ajaxURL = wp_nonce_url(SPAJAXURL.'components-loader&amp;saveform=seo', 'components-loader');
?>
	<form action="<?php echo $ajaxURL; ?>" method="post" id="sfseoform" name="sfseo">
	<?php echo sp_create_nonce('forum-adminform_seo'); ?>
<?php
	spa_paint_options_init();

    #== EXTENSIONS Tab ============================================================

	spa_paint_open_tab(spa_text('Components').' - '.spa_text('SEO'));
		spa_paint_open_panel();
			spa_paint_open_fieldset(spa_text('Page/Browser Title (SEO)'), true, 'seo-plugin-integration');
				spa_paint_checkbox(spa_text('Overwrite page/browser title with ours'), 'sfseo_overwrite', $sfcomps['sfseo_overwrite']);
				spa_paint_checkbox(spa_text('Include blog name in page/browser title'), 'sfseo_blogname', $sfcomps['sfseo_blogname']);
				spa_paint_checkbox(spa_text('Include page name in page/browser title'), 'sfseo_pagename', $sfcomps['sfseo_pagename']);
				spa_paint_checkbox(spa_text('Display page name on forum home page (Group View) only'), 'sfseo_homepage', $sfcomps['sfseo_homepage']);
				spa_paint_checkbox(spa_text('Include forum name in page/browser title'), 'sfseo_forum', $sfcomps['sfseo_forum']);
				spa_paint_checkbox(spa_text('Include topic name in page/browser title'), 'sfseo_topic', $sfcomps['sfseo_topic']);
				spa_paint_checkbox(spa_text('Exclude forum name in page/browser title on topic views only'), 'sfseo_noforum', $sfcomps['sfseo_noforum']);
				spa_paint_checkbox(spa_text('Include non-forum page view names (ie profile, member list, etc) in page/browser title'), 'sfseo_page', $sfcomps['sfseo_page']);
				spa_paint_input(spa_text('Title separator'), 'sfseo_sep', $sfcomps['sfseo_sep']);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_open_panel();
			spa_paint_open_fieldset(spa_text('Meta Tags Data'), true, 'meta-tags');
				$submessage = spa_text('Text you enter here will entered as a custom meta desciption tag if enabled in the option above');
				spa_paint_wide_textarea(spa_text('Custom meta description'), 'sfdescr', $sfcomps['sfdescr'], $submessage, 3);
				$submessage = spa_text('Enter keywords separated by commas');
				spa_paint_wide_textarea(spa_text('Custom meta keywords'), 'sfkeywords', $sfcomps['sfkeywords'], $submessage);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		do_action('sph_components_seo_left_panel');

		spa_paint_tab_right_cell();

		spa_paint_open_panel();
			spa_paint_open_fieldset(spa_text('Meta Tags Setup'), true, 'meta-setup');
				$values = array(spa_text('Do not add meta description to any forum pages'),
                                spa_text('Use custom meta description on all forum pages'),
                                spa_text('Use custom meta description on main forum page only and use forum description on forum and topic pages'),
                                spa_text('Use custom meta description on main forum page only, use forum description on forum pages and use topic title on topic pages'),
                                spa_text('Use custom meta description on main forum page only, use forum description on forum pages and use first post excerpt (120 chars) on topic pages'));
				spa_paint_radiogroup(spa_text('Select meta description option'), 'sfdescruse', $values, $sfcomps['sfdescruse'], false, true);
				$values = array(spa_text('Do not add meta keywords to any forum pages'),
                                spa_text('Use custom meta keywords (entered in left panel) on all forum pages'),
                                spa_text('Use custom meta keywords for each forum on forum and topic view pages. Custom meta keywords (from left panel) used on other forum pages'));
				spa_paint_radiogroup(spa_text('Select meta keywords option'), 'sfusekeywords', $values, $sfcomps['sfusekeywords'], false, true);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_open_panel();
			spa_paint_open_fieldset(spa_text('Open Graph'), true, 'seo-open-graph');
				spa_paint_checkbox(spa_text('Output Open Graph meta tags on forum views'), 'sfseo_og', $sfcomps['sfseo_og']);
				spa_paint_checkbox(spa_text('Use Image Attachment on Topic View if available'), 'seo_og_attachment', $sfcomps['seo_og_attachment']);
				spa_paint_input(spa_text('Specify Open Graph type (default is website)'), 'seo_og_type', $sfcomps['seo_og_type']);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		do_action('sph_components_seo_right_panel');

		spa_paint_close_container();
?>
    	<div class="sfform-submit-bar">
        	<input type="submit" class="button-primary" id="saveit" name="saveit" value="<?php spa_etext('Update SEO Component'); ?>" />
    	</div>
<?php
	spa_paint_close_tab();
?>
	</form>
<?php
}
?>