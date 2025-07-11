<?php
/*
Simple:Press
Admin themes tablet
*/

if ( ! defined( 'ABSPATH' ) ) {
	die('Access denied - you cannot directly call this file');
}

function spa_themes_tablet_form() {
?>
	<script>
		spj.loadAjaxForm('sftablettheme', 'sfreloadtablist');
	</script>
<?php
	# get current theme
	$tabletTheme = SP()->options->get('sp_tablet_theme');
	if (!isset($tabletTheme['active'])) $tabletTheme['active'] = false;

	$ajaxURL = wp_nonce_url(SPAJAXURL.'themes-loader&amp;saveform=tablet', 'themes-loader');
?>
	<form action="<?php echo esc_url($ajaxURL); ?>" method="post" id="sftablettheme" name="sftablettheme">
	<?php sp_echo_create_nonce('forum-adminform_themes'); ?>
<?php
	spa_paint_options_init();

	spa_paint_open_tab(esc_html(SP()->primitives->admin_text('Tablet Theme Support')).' - '.esc_html(SP()->primitives->admin_text('Tablet Theme')));
	spa_paint_open_panel();

	spa_paint_spacer();
	echo '<div class="sf-alert-block sf-info">';
	echo esc_html(SP()->primitives->admin_text('Themes Folder')).': <b>'.esc_html(realpath(SP_STORE_DIR.'/'.SP()->plugin->storage['themes'])).'</b>';
	echo '</div>';

	spa_paint_open_fieldset(esc_html(SP()->primitives->admin_text('Tablet Support')), true, 'tablet-support');
	spa_paint_checkbox(esc_html(SP()->primitives->admin_text('Enable tablet theme support')), 'active', $tabletTheme['active']);
	spa_paint_close_fieldset();

	spa_paint_close_panel();
	spa_paint_tab_right_cell();
	spa_paint_open_panel();
	if ($tabletTheme['active']) {
		require_once ABSPATH . 'wp-admin/includes/template.php';
		require_once ABSPATH.'wp-admin/includes/theme.php';
		spa_paint_open_fieldset(esc_html(SP()->primitives->admin_text('Tablet Display Options')), true, 'tablet-display');
		spa_paint_checkbox(esc_html(SP()->primitives->admin_text('Use alternate WordPress template')), 'usetemplate', $tabletTheme['usetemplate']);
		spa_paint_select_start(esc_html(SP()->primitives->admin_text('Alternate page template')), 'pagetemplate', 'pagetemplate');
		echo '<option value="page.php">'.esc_html(SP()->primitives->admin_text('Default Template')).'</option>';
		page_template_dropdown($tabletTheme['pagetemplate']);
		spa_paint_select_end();
		spa_paint_checkbox(esc_html(SP()->primitives->admin_text('Remove Page Title Completely')), 'notitle', $tabletTheme['notitle']);
		spa_paint_close_fieldset();
	}
	spa_paint_close_panel();

	do_action('sph_themes_tablet_option_panel');
	spa_paint_close_container();
?>
	<div class="sf-form-submit-bar">
	<input type="submit" class="sf-button-primary" id="saveit" name="saveit" value="<?php esc_attr(SP()->primitives->admin_etext('Update Tablet Component')); ?>" />
	</div>
	<?php spa_paint_close_tab(); ?>
	</form>
	<div class="sfform-panel-spacer"></div>
<?php
	if ($tabletTheme['active']) {
		# get themes
		$themes = SP()->theme->get_list();

		# get update version info
		$xml = sp_load_version_xml();

		spa_paint_open_tab(esc_html(SP()->primitives->admin_text('Available Themes')).' - '.esc_html(SP()->primitives->admin_text('Select Simple:Press Tablet Theme')), true, '', false );
		spa_paint_open_panel();
		spa_paint_open_fieldset(esc_html(SP()->primitives->admin_text('Tablet Theme Management')), true, 'themes');
?>
		<h3><?php esc_html(SP()->primitives->admin_etext('Current Tablet Theme')); ?></h3>
		<div class="theme-browser rendered">
		<div class="spThemeContainer">
		<div id="current-theme" class="spTheme spThemeTablet">
		<div class="spThemeInner">
		<h3 class="theme-name"><?php echo esc_html($themes[$tabletTheme['theme']]['Name']); ?></h3>
		<img src="<?php echo esc_url(SPTHEMEBASEURL.$tabletTheme['theme'].'/'.$themes[$tabletTheme['theme']]['Screenshot']); ?>" alt="" />
		<h4>
		<?php echo esc_html($themes[$tabletTheme['theme']]['Name']).' '.esc_html($themes[$tabletTheme['theme']]['Version']).' '.esc_html(SP()->primitives->admin_text('by')).' <a href="'.esc_url($themes[$tabletTheme['theme']]['AuthorURI']).'" title="'.esc_attr(SP()->primitives->admin_text('Visit author homepage')).'">'.esc_html($themes[$tabletTheme['theme']]['Author']).'</a>'; ?>
		</h4>
<?php
		if (!empty($tabletTheme['parent'])) {
			if (file_exists(SPTHEMEBASEDIR.$tabletTheme['parent'])) {
				echo '<p class="theme-parent">';
				echo esc_html(SP()->primitives->admin_text('This theme is a child theme of ')).'<b>'.esc_html($tabletTheme['parent']).'</b>';
				echo '</p>';
			} else {
				echo '<p class="theme-parent">';
				echo '<b>'.esc_html(SP()->primitives->admin_text('The specified parent theme'))." '".esc_html($tabletTheme['parent'])."' ".esc_html(SP()->primitives->admin_text('does not exist')).'</b> ';
				echo '</p>';
			}
		}
?>
		<p class="sf-description">
		<?php echo esc_html($themes[$tabletTheme['theme']]['Description']); ?>
		</p>
<?php
		$overlays = SP()->theme->get_overlays(SPTHEMEBASEDIR.$tabletTheme['theme'].'/styles/overlays');

		# pull in parent overlays if child theme
		if (!empty($tabletTheme['parent'])) {
			$parent_overlays = SP()->theme->get_overlays(SPTHEMEBASEDIR.$tabletTheme['parent'].'/styles/overlays');
			$overlays = array_merge($overlays, $parent_overlays);
		}

		if (!empty($overlays)) {
?>
			<script>
				(function(spj, $, undefined) {
					$(document).ready(function() {
						$('#sftheme-<?php echo esc_js($tabletTheme['theme']); ?>').ajaxForm({
							target: '#sfmsgspot',
							success: function() {
								$('#sfreloadtablist').click();
								$('#sfmsgspot').fadeIn();
								$('#sfmsgspot').fadeOut(6000);
							}
						});
					});
				}(window.spj = window.spj || {}, jQuery));
			</script>
			<br>
<?php
			$ajaxURL = wp_nonce_url(SPAJAXURL.'themes-loader&amp;saveform=tablet', 'themes-loader');
			echo '<form action="'.esc_url($ajaxURL).'" method="post" id="sftheme-'.esc_attr($tabletTheme['theme']).'" name="sftheme-'.esc_attr($tabletTheme['theme']).'">';
			sp_echo_create_nonce('forum-adminform_themes');
			echo '<input type="hidden" name="active" value="'.esc_attr($tabletTheme['active']).'" />';
			echo '<input type="hidden" name="theme" value="'.esc_attr($tabletTheme['theme']).'" />';
			echo '<input type="hidden" name="style" value="'.esc_attr($themes[$tabletTheme['theme']]['Stylesheet']).'" />';
			echo '<input type="hidden" name="parent" value="'.esc_attr($tabletTheme['parent']).'" />';
			echo '<input type="hidden" name="default-color" value="'.esc_attr($overlays[0]).'" />';

			# if only one overlay hide select controls
			$style = (count($overlays) > 1) ? 'style="display:block"' : 'style="display:none"';
			echo '<div ' . esc_attr($style) . '>';
			echo '<label>'.esc_html(SP()->primitives->admin_text('Select Overlay')).': '.'</label>';
			echo '<select name="color-'.esc_attr($tabletTheme['theme']).'">';
			foreach ($overlays as $overlay) {
				$overlay = trim($overlay);
				$selected = ($tabletTheme['color'] == $overlay) ? ' selected="selected" ' : '';
				echo '<option'.esc_attr($selected).' value="'.esc_attr($overlay).'">'.esc_html($overlay).'</option>';
			}
			echo '</select> ';
			echo ' <input type="submit" class="sf-button-secondary action" id="saveit-cur" name="saveit-cur" value="' . esc_attr(SP()->primitives->admin_etext('Update Overlay')) . '" />';
			echo '</form>';
			echo '</div>';

			if(current_theme_supports('sp-theme-customiser')) {
				echo '<div><b>'.esc_html(SP()->primitives->admin_text('Use the Customiser option in the Simple:Press Themes menu to customise your colours')).'</b></div>';
			}
		}

		# any upgrade for this theme?  in multisite only main site can update
		if (is_main_site() && $xml) {
			foreach ($xml->themes->theme as $latest) {
				if ($themes[$tabletTheme['theme']]['Name'] == $latest->name) {
					if ((version_compare($latest->version, $themes[$tabletTheme['theme']]['Version'], '>') == 1)) {
						echo '<br />';
						echo '<p>';
						echo '<strong>'.esc_html(SP()->primitives->admin_text('There is an update for the')).' '.esc_html($themes[$tabletTheme['theme']]['Name']).' '.esc_html(SP()->primitives->admin_text('theme')).'.</strong> ';
						echo esc_html(SP()->primitives->admin_text('Version')).' '.esc_html($latest->version).' '.esc_html(SP()->primitives->admin_text('is available')).'. ';
						echo esc_html(SP()->primitives->admin_text('For details and to download please visit')).' '.esc_url(SPPLUGHOME).' '.esc_html(SP()->primitives->admin_text('or')).' '.esc_html(SP()->primitives->admin_text('go to the')).' ';
						echo '<a href="'.esc_url(self_admin_url('update-core.php')).'" title="" target="_parent">'.esc_html(SP()->primitives->admin_text('WordPress updates page')).'</a>';
						echo '</p>';
					}
					break;
				}
			}
		}
?>
		</div></div></div></div>

		<br class="clear" />

		<h3><?php esc_html(SP()->primitives->admin_etext('Available Themes')); ?></h3>
<?php
		$numThemes = count($themes);
		if ($numThemes > 1) {
?>
			<div class="theme-browser rendered">
			<div class="spThemeContainer">
<?php
			foreach ((array) $themes as $theme_file => $theme_data) {
				# skip cur theme
				if ($theme_file == $tabletTheme['theme']) continue;

				$theme_desc = $theme_data['Description'];
				$theme_name = $theme_data['Name'];
				$theme_version = $theme_data['Version'];
				$theme_author = $theme_data['Author'];
				$theme_uri = $theme_data['AuthorURI'];
				$theme_style = $theme_data['Stylesheet'];
				$theme_image = SPTHEMEBASEURL.$theme_file.'/'.$theme_data['Screenshot'];
				$theme_overlays = SP()->theme->get_overlays(SPTHEMEBASEDIR.$theme_file.'/styles/overlays');

				# pull in parent overlays if child theme
				if (!empty($theme_data['Parent'])) {
					$parent_overlays = SP()->theme->get_overlays(SPTHEMEBASEDIR.$theme_data['Parent'].'/styles/overlays');
					$theme_overlays = array_merge($theme_overlays, $parent_overlays);
				}
?>
				<div class="spTheme spThemeTablet">
				<div class="spThemeInner">
				<h3 class="theme-name"><?php echo esc_html($theme_name); ?></h3>
				<img alt="" src="<?php echo esc_url($theme_image); ?>" />
				<h4>
				<?php echo esc_html($theme_name.' '.$theme_version.' '.SP()->primitives->admin_text('by').' <a href="'.esc_url($theme_uri).'" title="'.esc_attr(SP()->primitives->admin_text('Visit author homepage')).'">'.$theme_author.'</a>'); ?>
				</h4>
<?php
				if (!empty($theme_data['Parent'])) {
					if (file_exists(SPTHEMEBASEDIR.$theme_data['Parent'])) {
						echo '<p class="theme-parent">';
						echo esc_html(SP()->primitives->admin_text('This theme is a child theme of ')).'<b>'.esc_html($theme_data['Parent']).'</b>';
						echo '</p>';
					} else {
						echo '<p class="theme-parent">';
						echo '<b>'.esc_html(SP()->primitives->admin_text('The specified parent theme'))." '".esc_html($theme_data['Parent'])."' ".esc_html(SP()->primitives->admin_text('does not exist')).'</b> ';
						echo '</p>';
					}
				}
?>
				<p class="sf-description">
				<?php echo esc_html($theme_desc); ?>
				</p>
				<br>
				<div class="action-links">
				<script>
					spj.loadAjaxForm('sftheme-<?php echo esc_js($theme_file); ?>', 'sfreloadtablist');
				</script>
				<?php $ajaxURL = wp_nonce_url(SPAJAXURL.'themes-loader&amp;saveform=tablet', 'themes-loader'); ?>
				<form action="<?php echo esc_url($ajaxURL); ?>" method="post" id="sftheme-<?php echo esc_attr($theme_file); ?>" name="sftheme-<?php echo esc_attr($theme_file); ?>">
				<?php sp_echo_create_nonce('forum-adminform_themes'); ?>
				<input type="hidden" name="active" value="<?php echo esc_attr($tabletTheme['active']); ?>" />
				<input type="hidden" name="theme" value="<?php echo esc_attr($theme_file); ?>" />
				<input type="hidden" name="style" value="<?php echo esc_attr($theme_style); ?>" />
				<input type="hidden" name="parent" value="<?php echo esc_attr($theme_data['Parent']); ?>" />
				<?php $defOverlay = (!empty($theme_overlays)) ? esc_attr($theme_overlays[0]) : 0; ?>
				<input type="hidden" name="default-color" value="<?php echo esc_attr($defOverlay); ?>" />
				<?php
				if ($theme_overlays) {
					# only show if more than one overlay
					if(count($theme_overlays) > 1) {
						echo '<label>'.esc_html(SP()->primitives->admin_text('Select Overlay')).': '.'</label>';
						echo ' <select name="color-'.esc_attr($theme_file).'" style="margin-bottom:5px;">';
						foreach ($theme_overlays as $theme_overlay) {
							$theme_overlay = trim($theme_overlay);
							$selected = ($theme_overlays[0] == $theme_overlay) ? ' selected="selected" ' : '';
							echo '<option'.esc_attr($selected).' value="'.esc_attr($theme_overlay).'">'.esc_html($theme_overlay).'</option>';
						}
						echo '</select> ';
						echo '<div class="clearboth"></div>';
					}
				}
				?>
				<input type="submit" class="sf-button-secondary action" id="saveit-<?php echo esc_attr($theme_file); ?>" name="saveit-<?php echo esc_attr($theme_file); ?>" value="<?php esc_attr(SP()->primitives->admin_etext('Activate Tablet Theme')); ?>" />
				</form>
				</div>
				<?php
				# any upgrade for this theme?
				if ($xml) {
					foreach ($xml->themes->theme as $latest) {
						if ($theme_data['Name'] == $latest->name) {
							if ((version_compare($latest->version, $theme_data['Version'], '>') == 1)) {
								echo '<br />';
								echo '<div class="plugin-update-tr"><div class="update-message" style="background-color:#fcf3ef;margin-left:10px;">';
								echo '<strong>'.esc_html(SP()->primitives->admin_text('There is an update for the')).' '.esc_html($theme_data['Name']).' '.esc_html(SP()->primitives->admin_text('theme')).'.</strong> ';
								echo esc_html(SP()->primitives->admin_text('Version')).' '.esc_html($latest->version).' '.esc_html(SP()->primitives->admin_text('is available')).'. ';
								echo esc_html(SP()->primitives->admin_text('For details and to download please visit')).' '.esc_url(SPPLUGHOME).' '.esc_html(SP()->primitives->admin_text('or')).' '.esc_html(SP()->primitives->admin_text('go to the')).' ';
								echo '<a href="'.esc_url(self_admin_url('update-core.php')).'" title="" target="_parent">'.esc_html(SP()->primitives->admin_text('WordPress updates page')).'</a>';
								echo '</div></div>';
							}
							break;
						}
					}
				}
				echo '</div></div>';
			}
			echo '</div>';
			echo '</div>';
		} else {
			echo esc_html(SP()->primitives->admin_text('No other available themes found'));
		}
		do_action('sph_themes_tablet_list_panel');

		spa_paint_close_fieldset();
		spa_paint_close_panel();
		spa_paint_close_container();
		spa_paint_close_tab();
	}
}
