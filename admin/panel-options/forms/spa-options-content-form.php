<?php

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) {
    die('Access denied - you cannot directly call this file');
}

function spa_options_content_form() {
?>
<script>
   	spj.loadAjaxForm('sfcontentform', '');
</script>
<?php
	$sfoptions = spa_get_content_data();

    $ajaxURL = wp_nonce_url(SPAJAXURL.'options-loader&amp;saveform=content', 'options-loader');
?>
<form action="<?php echo $ajaxURL; ?>" method="post" id="sfcontentform" name="sfcontent">
	<?php echo sp_create_nonce('forum-adminform_content'); ?>
<?php

    #== POSTS Tab ============================================================

	spa_paint_open_tab(SP()->primitives->admin_text('Content Settings'));
		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('Date/Time Formatting'), true, 'date-time-formatting');
				spa_paint_input(SP()->primitives->admin_text('Date display format'), 'sfdates', $sfoptions['sfdates']);
				spa_paint_input(SP()->primitives->admin_text('Time display format'), 'sftimes', $sfoptions['sftimes']);
                echo '<div class="sf-form-row">';
                    echo '<span class="sf-sublabel sf-sublabel-small">';
                        spa_paint_link('http://codex.wordpress.org/Formatting_Date_and_Time', SP()->primitives->admin_text('Date/Time help'));
                        $tz = get_option('timezone_string');
                        if (empty($tz)) {
                            $tz = SP()->primitives->admin_text('Unknown');
                        }
                        echo '&nbsp;'.SP()->primitives->admin_text('Server timezone set to').': '.$tz;
                    echo '</span>';
                echo '</div>';

			spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('Image Enlargement'), true, 'image-enlarging');
				spa_paint_checkbox(SP()->primitives->admin_text('Use popup image enlargement'), 'sfimgenlarge', $sfoptions['sfimgenlarge']);
				spa_paint_checkbox(SP()->primitives->admin_text('Constrain popup enlargement to current window size'), 'constrain', $sfoptions['constrain']);
				spa_paint_checkbox(SP()->primitives->admin_text('Always use image thumbnails'), 'process', $sfoptions['process']);
				spa_paint_input(SP()->primitives->admin_text('Thumbnail width of images in posts (Minimum 100px)'), 'sfthumbsize', $sfoptions['sfthumbsize']);
				spa_paint_select_start(SP()->primitives->admin_text('Default image style'), 'style', 'style');
				echo spa_create_imagestyle_select($sfoptions['style']);
				spa_paint_select_end();
				spa_paint_checkbox(SP()->primitives->admin_text('Force paragraph after an image to start new line'), 'forceclear', $sfoptions['forceclear']);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('Smileys'), true, 'smileys');
				spa_paint_input(SP()->primitives->admin_text('Maximum smileys allowed in post (0 = unlimited)'), 'sfmaxsmileys', $sfoptions['sfmaxsmileys']);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('Spam Posts'), true, 'spam-post');
				spa_paint_checkbox(SP()->primitives->admin_text('Refuse duplicate post made by member'), 'sfdupemember', $sfoptions['sfdupemember']);
				spa_paint_checkbox(SP()->primitives->admin_text('Refuse duplicate post made by guest'), 'sfdupeguest', $sfoptions['sfdupeguest']);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		do_action('sph_options_content_left_panel');

	spa_paint_tab_right_cell();

		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('Additional Filtering'), true, 'additional-filters');
				spa_paint_checkbox(SP()->primitives->admin_text('Filter out HTML pre tags'), 'sffilterpre', $sfoptions['sffilterpre']);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('Post Links Filtering'), true, 'post-links-filtering');
				spa_paint_input(SP()->primitives->admin_text('Maximum links allowed in post (0 = unlimited)'), 'sfmaxlinks', $sfoptions['sfmaxlinks']);
				spa_paint_checkbox(SP()->primitives->admin_text('Add nofollow to links'), 'sfnofollow', $sfoptions['sfnofollow']);
				spa_paint_checkbox(SP()->primitives->admin_text('Open links in new tab/window'), 'sftarget', $sfoptions['sftarget']);
				spa_paint_input(SP()->primitives->admin_text('URL shortening limit (0 = not shortened)'), 'sfurlchars', $sfoptions['sfurlchars']);
				$submessage = SP()->primitives->admin_text("If post viewer doesn't have view links permission, this custom message will be displayed instead");
				spa_paint_textarea(SP()->primitives->admin_text('Hidden links custom message'), 'sfnolinksmsg', $sfoptions['sfnolinksmsg'], $submessage, 3);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('Shortcodes Filtering'), true, 'shortcode-filters');
				spa_paint_checkbox(SP()->primitives->admin_text('Filter WP shortcodes (if disabled ALL WP shortcodes will be passed)'), 'sffiltershortcodes', $sfoptions['sffiltershortcodes']);
				$submessage = SP()->primitives->admin_text('Enter allowed WP shortcodes (if filtering enabled above) - one shortcode per line.');
				spa_paint_textarea(SP()->primitives->admin_text('Allowed WP shortcodes in posts'), 'sfshortcodes', $sfoptions['sfshortcodes'], $submessage, 3);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		do_action('sph_options_content_right_panel');

		spa_paint_close_container();
?>
	<div class="sf-form-submit-bar">
	<input type="submit" class="sf-button-primary" id="saveit" name="saveit" value="<?php SP()->primitives->admin_etext('Update Content Options'); ?>" />
	</div>
<?php
	spa_paint_close_tab();
?>
	</form>
<?php
}

function spa_create_imagestyle_select($defstyle) {
    $out = '';
	$styles = array('left', 'right', 'baseline', 'top', 'middle', 'bottom', 'text-top', 'text-bottom');
	$default = '';
	foreach ($styles as $style) {
		if ($style == $defstyle) {
			$default = 'selected="selected" ';
		} else {
			$default = null;
		}
		$out.= '<option '.$default.'value="'.$style.'">'.$style.'</option>';
		$default = '';
	}
	return $out;
}
