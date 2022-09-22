<?php
/*
Simple:Press
Admin Components SEO Form
$LastChangedDate: 2017-12-28 11:37:41 -0600 (Thu, 28 Dec 2017) $
$Rev: 15601 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_components_seo_form() {
?>
<script>
  	spj.loadAjaxForm('sfseoform', 'sfreloadse');
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

	spa_paint_open_tab(/*SP()->primitives->admin_text('Components').' - '.*/SP()->primitives->admin_text('SEO'));
		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('Page/Browser Title (SEO)'), true, 'seo-plugin-integration');
				spa_paint_checkbox(SP()->primitives->admin_text('Overwrite page/browser title with ours'), 'sfseo_overwrite', isset($sfcomps['sfseo_overwrite']) ? $sfcomps['sfseo_overwrite'] : false);
				spa_paint_checkbox(SP()->primitives->admin_text('Include blog name in page/browser title'), 'sfseo_blogname', isset($sfcomps['sfseo_blogname']) ? $sfcomps['sfseo_blogname'] : false);
				spa_paint_checkbox(SP()->primitives->admin_text('Include page name in page/browser title'), 'sfseo_pagename', isset($sfcomps['sfseo_pagename']) ? $sfcomps['sfseo_pagename'] : false);
				spa_paint_checkbox(SP()->primitives->admin_text('Display page name on forum home page (Group View) only'), 'sfseo_homepage',isset($sfcomps['sfseo_homepage']) ? $sfcomps['sfseo_homepage'] : false);
				spa_paint_checkbox(SP()->primitives->admin_text('Include forum name in page/browser title'), 'sfseo_forum', isset($sfcomps['sfseo_forum']) ? $sfcomps['sfseo_forum'] : false);
				spa_paint_checkbox(SP()->primitives->admin_text('Include topic name in page/browser title'), 'sfseo_topic', isset($sfcomps['sfseo_topic']) ? $sfcomps['sfseo_topic'] : false);
				spa_paint_checkbox(SP()->primitives->admin_text('Exclude forum name in page/browser title on topic views only'), 'sfseo_noforum',  isset($sfcomps['sfseo_noforum']) ? $sfcomps['sfseo_noforum'] : false);
				spa_paint_checkbox(SP()->primitives->admin_text('Include non-forum page view names (ie profile, member list, etc) in page/browser title'), 'sfseo_page', isset($sfcomps['sfseo_page']) ? $sfcomps['sfseo_page'] : false);
				spa_paint_input(SP()->primitives->admin_text('Title separator'), 'sfseo_sep', isset($sfcomps['sfseo_sep']) ? $sfcomps['sfseo_sep'] : "");
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('Meta Tags Data'), true, 'meta-tags');
				$submessage = SP()->primitives->admin_text('Text you enter here will entered as a custom meta desciption tag if enabled in the option above');
				spa_paint_wide_textarea(SP()->primitives->admin_text('Custom meta description'), 'sfdescr', $sfcomps['sfdescr'], $submessage, 3);
				$submessage = SP()->primitives->admin_text('Enter keywords separated by commas');
				spa_paint_wide_textarea(SP()->primitives->admin_text('Custom meta keywords'), 'sfkeywords', $sfcomps['sfkeywords'], $submessage);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		do_action('sph_components_seo_left_panel');

		spa_paint_tab_right_cell();

		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('Meta Tags Setup'), true, 'meta-setup');
				$values = array(SP()->primitives->admin_text('Do not add meta description to any forum pages'),
                                SP()->primitives->admin_text('Use custom meta description on all forum pages'),
                                SP()->primitives->admin_text('Use custom meta description on main forum page only and use forum description on forum and topic pages'),
                                SP()->primitives->admin_text('Use custom meta description on main forum page only, use forum description on forum pages and use topic title on topic pages'),
                                SP()->primitives->admin_text('Use custom meta description on main forum page only, use forum description on forum pages and use first post excerpt (120 chars) on topic pages'));
				spa_paint_radiogroup(SP()->primitives->admin_text('Select meta description option'), 'sfdescruse', $values, $sfcomps['sfdescruse'], false, true);
				$values = array(SP()->primitives->admin_text('Do not add meta keywords to any forum pages'),
                                SP()->primitives->admin_text('Use custom meta keywords (entered in left panel) on all forum pages'),
                                SP()->primitives->admin_text('Use custom meta keywords for each forum on forum and topic view pages. Custom meta keywords (from left panel) used on other forum pages'));
				spa_paint_radiogroup(SP()->primitives->admin_text('Select meta keywords option'), 'sfusekeywords', $values, $sfcomps['sfusekeywords'], false, true);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('Open Graph'), true, 'seo-open-graph');
				spa_paint_checkbox(SP()->primitives->admin_text('Output Open Graph meta tags on forum views'), 'sfseo_og', isset($sfcomps['sfseo_og']) ? $sfcomps['sfseo_og'] : false);
				spa_paint_checkbox(SP()->primitives->admin_text('Use Image Attachment on Topic View if available'), 'seo_og_attachment', isset($sfcomps['seo_og_attachment']) ? $sfcomps['seo_og_attachment'] : false);
				spa_paint_input(SP()->primitives->admin_text('Specify Open Graph type (default is website)'), 'seo_og_type', isset($sfcomps['seo_og_type']) ? $sfcomps['seo_og_type'] : "");
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		do_action('sph_components_seo_right_panel');

		spa_paint_close_container();
?>
    	<div class="sf-form-submit-bar">
        	<input type="submit" class="sf-button-primary" id="saveit" name="saveit" value="<?php SP()->primitives->admin_etext('Update SEO Component'); ?>" />
    	</div>
<?php
	spa_paint_close_tab();
?>
	</form>
<?php
}
