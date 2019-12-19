<?php
/*
Simple:Press
Admin themes mobile
$LastChangedDate: 2017-12-28 11:37:41 -0600 (Thu, 28 Dec 2017) $
$Rev: 15601 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_themes_mobile_form() {
?>
	<script>
		spj.loadAjaxForm('sfmobiletheme', 'sfreloadmlist');
	</script>
<?php
	# get current theme
	$mobileTheme = SP()->options->get('sp_mobile_theme');
	if (!isset($mobileTheme['active'])) $mobileTheme['active'] = false;

	$ajaxURL = wp_nonce_url(SPAJAXURL.'themes-loader&amp;saveform=mobile', 'themes-loader');
?>
	<form action="<?php echo $ajaxURL; ?>" method="post" id="sfmobiletheme" name="sfmobiletheme">
	<?php echo sp_create_nonce('forum-adminform_themes'); ?>
<?php
	spa_paint_options_init();

	spa_paint_open_tab(SP()->primitives->admin_text('Mobile Theme Support').' - '.SP()->primitives->admin_text('Mobile Theme'));
	spa_paint_open_panel();

	spa_paint_spacer();
	echo '<div class="sf-alert-block sf-info">';
	echo SP()->primitives->admin_text('Themes Folder').': <b>'.realpath(SP_STORE_DIR.'/'.SP()->plugin->storage['themes']).'</b>';
	echo '</div>';

	spa_paint_open_fieldset(SP()->primitives->admin_text('Mobile Support'), true, 'mobile-support');
	spa_paint_checkbox(SP()->primitives->admin_text('Enable mobile theme support'), 'active', $mobileTheme['active']);
	spa_paint_close_fieldset();

	spa_paint_close_panel();
	spa_paint_tab_right_cell();
	spa_paint_open_panel();
	if ($mobileTheme['active']) {
		require_once ABSPATH . 'wp-admin/includes/template.php' ;
		require_once ABSPATH.'wp-admin/includes/theme.php';
		spa_paint_open_fieldset(SP()->primitives->admin_text('Mobile Display Options'), true, 'mobile-display');
		spa_paint_checkbox(SP()->primitives->admin_text('Use alternate WordPress template'), 'usetemplate', $mobileTheme['usetemplate']);
		spa_paint_select_start(SP()->primitives->admin_text('Alternate page template'), 'pagetemplate', 'pagetemplate');
		echo '<option value="page.php">'.SP()->primitives->admin_text('Default Template').'</option>';
		page_template_dropdown($mobileTheme['pagetemplate']);
		spa_paint_select_end();
		spa_paint_checkbox(SP()->primitives->admin_text('Remove Page Title Completely'), 'notitle', $mobileTheme['notitle']);
		spa_paint_close_fieldset();
	}
	spa_paint_close_panel();
	do_action('sph_themes_mobile_option_panel');
	spa_paint_close_container();

?>
	<div class="sf-form-submit-bar">
	<input type="submit" class="sf-button-primary" id="saveit" name="saveit" value="<?php SP()->primitives->admin_etext('Update Mobile Component'); ?>" />
	</div>
	<?php spa_paint_close_tab(); ?>
	</form>
	<div class="sfform-panel-spacer"></div>
<?php

	if ($mobileTheme['active']) {
		# get themes
		$themes = SP()->theme->get_list();

		# get update version info
		$xml = sp_load_version_xml();

		spa_paint_open_tab(SP()->primitives->admin_text('Available Themes').' - '.SP()->primitives->admin_text('Select Simple:Press Mobile Theme'), true, '', false);
		spa_paint_open_panel();
		spa_paint_open_fieldset(SP()->primitives->admin_text('Mobile Theme Management'), true, 'themes');
?>
		<h3><?php echo SP()->primitives->admin_text('Current Mobile Theme'); ?></h3>
		<div class="theme-browser rendered">
		<div class="spThemeContainer">
		<div id="current-theme" class="spTheme spThemeMobile">
		<div class="spThemeInner">
		<h3 class="theme-name"><?php echo $themes[$mobileTheme['theme']]['Name']; ?></h3>
		<div><img src="<?php echo SPTHEMEBASEURL.$mobileTheme['theme'].'/'.$themes[$mobileTheme['theme']]['Screenshot']; ?>" alt="" /></div>
		<h4>
		<?php echo $themes[$mobileTheme['theme']]['Name'].' '.$themes[$mobileTheme['theme']]['Version'].' '.SP()->primitives->admin_text('by').' <a href="'.$themes[$mobileTheme['theme']]['AuthorURI'].'" title="'.SP()->primitives->admin_text('Visit author homepage').'">'.$themes[$mobileTheme['theme']]['Author'].'</a>'; ?>
		</h4>
<?php
		if (!empty($mobileTheme['parent'])) {
			if (file_exists(SPTHEMEBASEDIR.$mobileTheme['parent'])) {
				echo '<p class="theme-parent">';
				echo SP()->primitives->admin_text('This theme is a child theme of ').'<b>'.$mobileTheme['parent'].'</b>';
				echo '</p>';
			} else {
				echo '<p class="theme-parent">';
				echo '<b>'.SP()->primitives->admin_text('The specified parent theme')." '".$mobileTheme['parent']."' ".SP()->primitives->admin_text('does not exist').'</b> ';
				echo '</p>';
			}
		}
?>
		<p class="sf-description">
		<?php echo $themes[$mobileTheme['theme']]['Description']; ?>
		</p>
<?php
		$overlays = SP()->theme->get_overlays(SPTHEMEBASEDIR.$mobileTheme['theme'].'/styles/overlays');

		# pull in parent overlays if child theme
		if (!empty($mobileTheme['parent'])) {
			$parent_overlays = SP()->theme->get_overlays(SPTHEMEBASEDIR.$mobileTheme['parent'].'/styles/overlays');
			$overlays = array_merge($overlays, $parent_overlays);
		}

		if (!empty($overlays)) {
?>
			<script>
				(function(spj, $, undefined) {
					$(document).ready(function() {
						$('#sftheme-<?php echo esc_js($mobileTheme['theme']); ?>').ajaxForm({
							target: '#sfmsgspot',
							success: function() {
								$('#sfreloadmlist').click();
								$('#sfmsgspot').fadeIn();
								$('#sfmsgspot').fadeOut(6000);
							}
						});
					});
				}(window.spj = window.spj || {}, jQuery));
			</script>
			<br>
<?php
			$ajaxURL = wp_nonce_url(SPAJAXURL.'themes-loader&amp;saveform=mobile', 'themes-loader');
			echo '<form action="'.$ajaxURL.'" method="post" id="sftheme-'.esc_attr($mobileTheme['theme']).'" name="sftheme-'.esc_attr($mobileTheme['theme']).'">';
			echo sp_create_nonce('forum-adminform_themes');
			echo '<input type="hidden" name="active" value="'.$mobileTheme['active'].'" />';
			echo '<input type="hidden" name="theme" value="'.esc_attr($mobileTheme['theme']).'" />';
			echo '<input type="hidden" name="style" value="'.esc_attr($themes[$mobileTheme['theme']]['Stylesheet']).'" />';
			echo '<input type="hidden" name="parent" value="'.esc_attr($mobileTheme['parent']).'" />';
			echo '<input type="hidden" name="default-color" value="'.esc_attr($overlays[0]).'" />';

			# if only one overlay hide select controls
			$style = (count($overlays) > 1) ? 'style="display:block"' : 'style="display:none"';
			echo '<div '.$style.'>';
			echo '<label>'.SP()->primitives->admin_text('Select Overlay').': '.'</label>';
			echo '<select name="color-'.esc_attr($mobileTheme['theme']).'">';
			foreach ($overlays as $overlay) {
				$overlay = trim($overlay);
				$selected = ($mobileTheme['color'] == $overlay) ? ' selected="selected" ' : '';
				echo '<option'.$selected.' value="'.esc_attr($overlay).'">'.esc_html($overlay).'</option>';
			}
			echo '</select> ';
			echo ' <input type="submit" class="sf-button-secondary action" id="saveit-cur" name="saveit-cur" value="'.SP()->primitives->admin_text('Update Overlay').'" />';
			echo '</form>';
			echo '</div>';

			if(current_theme_supports('sp-theme-customiser')) {
				echo '<div><b>'.SP()->primitives->admin_text('Use the Customiser option in the Simple:Press Themes menu to customise your colours').'</b></div>';
			}
		}

		# any upgrade for this theme?  in multisite only main site can update
		if (is_main_site() && $xml) {
			foreach ($xml->themes->theme as $latest) {
				if ($themes[$mobileTheme['theme']]['Name'] == $latest->name) {
					if ((version_compare($latest->version, $themes[$mobileTheme['theme']]['Version'], '>') == 1)) {
						echo '<br />';
						echo '<p>';
						echo '<strong>'.SP()->primitives->admin_text('There is an update for the').' '.$themes[$mobileTheme['theme']]['Name'].' '.SP()->primitives->admin_text('theme').'.</strong> ';
						echo SP()->primitives->admin_text('Version').' '.$latest->version.' '.SP()->primitives->admin_text('is available').'. ';
						echo SP()->primitives->admin_text('For details and to download please visit').' '.SPPLUGHOME.' '.SP()->primitives->admin_text('or').' '.SP()->primitives->admin_text('go to the').' ';
						echo '<a href="'.self_admin_url('update-core.php').'" title="" target="_parent">'.SP()->primitives->admin_text('WordPress updates page').'</a>';
						echo '</p>';
					}
					break;
				}
			}
		}
?>
		</div></div></div></div>

		<br class="clear" />

		<h3><?php echo SP()->primitives->admin_text('Available Themes'); ?></h3>
<?php
		$numThemes = count($themes);
		if ($numThemes > 1) {
?>
			<div class="theme-browser rendered">
			<div class="spThemeContainer">
<?php
			foreach ((array) $themes as $theme_file => $theme_data) {
				# skip cur theme
				if ($theme_file == $mobileTheme['theme']) continue;

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
				<div class="spTheme spThemeMobile">
				<div class="spThemeInner">
				<h3 class="theme-name"><?php echo $theme_name; ?></h3>
				<div><img alt="" src="<?php echo $theme_image; ?>" /></div>
				<h4>
				<?php echo $theme_name.' '.$theme_version.' '.SP()->primitives->admin_text('by').' <a href="'.$theme_uri.'" title="'.SP()->primitives->admin_text('Visit author homepage').'">'.$theme_author.'</a>'; ?>
				</h4>
<?php
				if (!empty($theme_data['Parent'])) {
					if (file_exists(SPTHEMEBASEDIR.$theme_data['Parent'])) {
						echo '<p class="theme-parent">';
						echo SP()->primitives->admin_text('This theme is a child theme of ').'<b>'.$theme_data['Parent'].'</b>';
						echo '</p>';
					} else {
						echo '<p class="theme-parent">';
						echo '<b>'.SP()->primitives->admin_text('The specified parent theme')." '".$theme_data['Parent']."' ".SP()->primitives->admin_text('does not exist').'</b> ';
						echo '</p>';
					}
				}
?>
				<p class="sf-description">
				<?php echo $theme_desc; ?>
				</p>
				<br>
				<div class="action-links">
				<script>
					spj.loadAjaxForm('sftheme-<?php echo esc_js($theme_file); ?>', 'sfreloadmlist');
				</script>
				<?php $ajaxURL = wp_nonce_url(SPAJAXURL.'themes-loader&amp;saveform=mobile', 'themes-loader'); ?>
				<form action="<?php echo $ajaxURL; ?>" method="post" id="sftheme-<?php echo esc_attr($theme_file); ?>" name="sftheme-<?php echo esc_attr($theme_file);	?>">
				<?php echo sp_create_nonce('forum-adminform_themes'); ?>
				<input type="hidden" name="active" value="<?php echo $mobileTheme['active']; ?>" />
				<input type="hidden" name="theme" value="<?php echo esc_attr($theme_file); ?>" />
				<input type="hidden" name="style" value="<?php echo esc_attr($theme_style); ?>" />
				<input type="hidden" name="parent" value="<?php echo esc_attr($theme_data['Parent']); ?>" />
				<?php $defOverlay = (!empty($theme_overlays)) ? esc_attr($theme_overlays[0]) : 0; ?>
				<input type="hidden" name="default-color" value="<?php echo esc_attr($defOverlay); ?>" />
<?php
				if ($theme_overlays) {
					# only show if more than one overlay
					if(count($theme_overlays) > 1) {
						echo '<label>'.SP()->primitives->admin_text('Select Overlay').': '.'</label>';
						echo ' <select name="color-'.esc_attr($theme_file).'" style="margin-bottom:5px;">';
						foreach ($theme_overlays as $theme_overlay) {
							$theme_overlay = trim($theme_overlay);
							$selected = ($theme_overlays[0] == $theme_overlay) ? ' selected="selected" ' : '';
							echo '<option'.$selected.' value="'.esc_attr($theme_overlay).'">'.esc_html($theme_overlay).'</option>';
						}
						echo '</select> ';
						echo '<div class="clearboth"></div>';
					}
				}
?>
				<input type="submit" class="sf-button-secondary action" id="saveit-<?php echo esc_attr($theme_file); ?>" name="saveit-<?php echo esc_attr($theme_file); ?>" value="<?php echo SP()->primitives->admin_etext('Activate Mobile Theme'); ?>" />
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
								echo '<strong>'.SP()->primitives->admin_text('There is an update for the').' '.$theme_data['Name'].' '.SP()->primitives->admin_text('theme').'.</strong> ';
								echo SP()->primitives->admin_text('Version').' '.$latest->version.' '.SP()->primitives->admin_text('is available').'. ';
								echo SP()->primitives->admin_text('For details and to download please visit').' '.SPPLUGHOME.' '.SP()->primitives->admin_text('or').' '.SP()->primitives->admin_text('go to the').' ';
								echo '<a href="'.self_admin_url('update-core.php').'" title="" target="_parent">'.SP()->primitives->admin_text('WordPress updates page').'</a>';
								echo '</div></div>';
							}
							break;
						}
					}
				}
				echo '</div>';
				echo '</div>';
			}
			echo '</div>';
			echo '</div>';
		} else {
			echo SP()->primitives->admin_text('No other available themes found');
		}
		do_action('sph_themes_mobile_list_panel');

		spa_paint_close_fieldset();
		spa_paint_close_panel();
		spa_paint_close_container();
		spa_paint_close_tab();
	}
}
