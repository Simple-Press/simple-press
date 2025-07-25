<?php
/*
Simple:Press
Admin integration Page and Permalink Form
*/

if ( ! defined( 'ABSPATH' ) ) {
	die('Access denied - you cannot directly call this file');
}

function spa_integration_page_form() {
?>
<script>
   	spj.loadAjaxForm('wppageform', 'sfreloadpp');
</script>
<?php
	$sfoptions = spa_get_integration_page_data();

    $ajaxURL = wp_nonce_url(SPAJAXURL.'integration-loader&amp;saveform=page', 'integration-loader');
?>

	<form action="<?php echo esc_url($ajaxURL); ?>" method="post" id="wppageform" name="wppage">
	<?php sp_echo_create_nonce('forum-adminform_integration'); ?>
<?php
	spa_paint_open_tab( esc_html(SP()->primitives->admin_text('Page and Permalink')), true);
		spa_paint_open_panel();
			spa_paint_open_fieldset( esc_html(SP()->primitives->admin_text('WP Forum Page Details')), true, 'forum-page-details');
				if ($sfoptions['sfpage'] == 0) {
                    echo '<div class="sf-alert-block sf-info">' . esc_html(SP()->primitives->admin_text(
                            'ERROR: The page slug is either missing or incorrect. The forum will not display until this is corrected'
                        )) . '</div>>';
                }
				spa_paint_select_start( SP()->primitives->admin_text('Select the WP Page to be used to display your forum'), 'slug', 'slug');
				echo wp_kses(
                    spa_create_page_select($sfoptions['sfpage']),
                    [
                        'option' => [
                            'selected' => [],
                            'value' => []
                        ],
                        'optgroup' => [
                            'label' => []
                        ]
                    ]
                );
				spa_paint_select_end();
			spa_paint_close_fieldset();

			if ($sfoptions['sfpage'] != 0) {
				$title = SP()->DB->table(SPWPPOSTS, 'ID='.$sfoptions['sfpage'], 'post_title');
				$template = SP()->DB->table(SPWPPOSTMETA, "meta_key='_wp_page_template' AND post_id=".$sfoptions['sfpage'], 'meta_value');
				spa_paint_open_fieldset( esc_html(SP()->primitives->admin_text('Current WP Forum Page')), false);
					echo '<table class="table widefat  sf-plugin-hide">';
					echo '<thead><tr>';
					echo '<th>'.esc_html(SP()->primitives->admin_text('Forum page ID')).'</th>';
					echo '<th>'.esc_html(SP()->primitives->admin_text('Page title')).'</th>';
					echo '<th>'.esc_html(SP()->primitives->admin_text('Page template')).'</th>';
					echo '</tr></thead>';
					echo '<tbody><tr>';
					echo '<td>'.esc_html($sfoptions['sfpage']).'</td>';
					echo '<td>'.esc_html($title).'</td>';
					echo '<td>'.esc_html($template).'</td>';
					echo '</tr></tbody></table>';
				spa_paint_close_fieldset();

				echo '<div>';
                    echo '<table class="sf-plugin-list-mob sf-showm">';
                    echo '<tbody><tr>';
                    echo '<td><span class="sf-title-uppercase-blue">'
                         .esc_html(SP()->primitives->admin_text('Forum page ID')).
                         '</span></td><td>'
                         .esc_html($sfoptions['sfpage']).
                         '</td></tr>';
                    echo '<tr><td><span class="sf-title-uppercase-blue">'
                         .esc_html(SP()->primitives->admin_text('Page title')).
                         '</span></td><td>'
                         .esc_html($title).
                         '</td></tr>';
                    echo '<tr><td><span class="sf-title-uppercase-blue">'
                         .esc_html(SP()->primitives->admin_text('Page template')).
                         '</span></td><td>'
                         .esc_html($template).
                         '</td>';
                    echo '</tr></tbody></table>';
				echo '</div>';

				spa_paint_open_fieldset( esc_html(SP()->primitives->admin_text('Update Forum Permalink')), true, 'forum-permalink');
					echo '<p class="sf-sublabel sf-sublabel-small">'.esc_html(SP()->primitives->admin_text('Current permalink')).':<br /></p><div class="sf-subhead" id="adminupresult"><p>'.esc_html($sfoptions['sfpermalink']).'</p></div><br />';
					spa_paint_update_permalink();
				spa_paint_close_fieldset();
			}

		spa_paint_close_panel();
    spa_paint_close_tab();

        spa_paint_open_nohead_tab(false);
                    spa_paint_open_panel();
			spa_paint_open_fieldset( esc_html(SP()->primitives->admin_text('Integration Options')), true, 'integration-options');
				spa_paint_checkbox( esc_html(SP()->primitives->admin_text('Filter WP list pages')), 'sfwplistpages', $sfoptions['sfwplistpages'] ?? false);
				spa_paint_checkbox( esc_html(SP()->primitives->admin_text('Load javascript in footer')), 'sfscriptfoot', $sfoptions['sfscriptfoot'] ?? false);
				spa_paint_checkbox( esc_html(SP()->primitives->admin_text('Force the strict use of the WP API')), 'sfuseob', $sfoptions['sfuseob'] ?? false);
				spa_paint_checkbox( esc_html(SP()->primitives->admin_text('Run the wptexturize formatting on post content')), 'spwptexturize', $sfoptions['spwptexturize'] ?? false);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_tab_right_cell();

		spa_paint_open_panel();
			spa_paint_open_fieldset( esc_html(SP()->primitives->admin_text('Theme Display Options')), true, 'theme-options');
				spa_paint_checkbox( esc_html(SP()->primitives->admin_text('Limit forum display to within WP loop')), 'sfinloop', $sfoptions['sfinloop'] ?? false);
				spa_paint_checkbox( esc_html(SP()->primitives->admin_text('Allow multiple loading of forum content')), 'sfmultiplecontent', $sfoptions['sfmultiplecontent'] ?? false);
				spa_paint_input( esc_html(SP()->primitives->admin_text('Compensate (in pixels) for fixed WP theme header')), 'spheaderspace', $sfoptions['spheaderspace'] ?? 0, false, false);
				spa_paint_checkbox( esc_html(SP()->primitives->admin_text('Bypass wp_head action complete requirement')), 'sfwpheadbypass', $sfoptions['sfwpheadbypass'] ?? false);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		do_action('sph_integration_panel');
		spa_paint_close_container();
?>
	<div class="sf-form-submit-bar">
	<input type="submit" class="sf-button-primary" id="saveit" name="saveit" value="<?php esc_attr(SP()->primitives->admin_etext('Update WP Integration')); ?>" />
	</div>
<?php
	spa_paint_close_tab();
?>
	</form>
<?php
}

function spa_create_page_select($currentpageid) {
	$pages = SP()->DB->table(SPWPPOSTS, "post_type='page' && post_status!='trash'", '', 'menu_order');
	$out = '';
	if ($pages) {
		$default = '';
		//$out = '';
		$spacer = '&nbsp;&nbsp;&nbsp;&nbsp;';
		$out.= '<optgroup label="'.SP()->primitives->admin_text('Select the WP page').':">'."\n";
		foreach ($pages as $page) {
			$sublevel = 0;
			if ($page->post_parent) {
				$parent = $page->post_parent;
				$pageslug = $page->post_name;
				while ($parent) {
					$thispage = SP()->DB->table(SPWPPOSTS, "ID=$parent", 'row');
					$pageslug = $thispage->post_name.'/'.$pageslug;
					$parent = $thispage->post_parent;
					$sublevel++;
				}
			} else {
				$pageslug = $page->post_name;
			}

			if ($currentpageid == $page->ID) {
				$default = 'selected="selected" ';
			} else {
				$default = null;
			}
			$out.= '<option '.$default.'value="'.esc_attr($page->ID).'">'.$spacer.esc_html(str_repeat('&rarr;&nbsp;', $sublevel)).esc_html($pageslug).'</option>'."\n";
			$default = '';
		}
		$out.= '</optgroup>';
	} else {
		$out.='<option value="0">'.SP()->primitives->admin_text('No WP pages found - please create one').'</option>'."\n";
	}
	return $out;
}

function spa_paint_update_permalink() {
    $site = wp_nonce_url(SPAJAXURL.'integration-perm&amp;item=upperm', 'integration-perm');
	$target = 'adminupresult';
	$gif = SPCOMMONIMAGES.'working.gif';

	echo '<input type="button" class="sf-button sf-button-highlighted spAdminTool" value="' . esc_attr(SP()->primitives->admin_text('Update Forum Permalink')) . '" data-url="' . esc_attr($site) . '" data-target="' . esc_attr($target) . '" data-img="' . esc_attr($gif) . '" />';
}
