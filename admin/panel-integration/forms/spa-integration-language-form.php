<?php
/*
Simple:Press
Admin Integration Storage Locations Form
*/

if ( ! defined( 'ABSPATH' ) ) {
	die('Access denied - you cannot directly call this file');
}

function spa_integration_language_form() {
	require_once SP_PLUGIN_DIR.'/admin/library/sp-languages.php';
	global $siteLang, $locale;

	$spCode = '';

	# Get language setting and see if on glotpress
	$locale = get_locale();
	if (!empty($locale)) {
		foreach($langSets as $lKey => $wpData) {
			if ($locale == $wpData['wpCode']) {
				$siteLang = $wpData['wpCode'];
				$spCode = $lKey;
				break;
			}
		}
	}

	if ($siteLang=='en_US' || $siteLang=='en-US') {
		echo '<div class="sf-alert-block sf-info">';
		SP()->primitives->admin_etext('Your site language setting is English/USA and therefore no translation files are required for Simple:Press');
		echo '</div>';
		return;
	}
	# check we can download
	if (ini_get('allow_url_fopen') == false) {
		echo '<br /><div class="sf-alert-block sf-info">';
		SP()->primitives->admin_etext('Your server will not allow us to download the language files from Simple:Press');
		echo '</div>';
		return;
	}

	$userLang = array();
	$userLang['spLang'] = $siteLang;
	$userLang['spCode'] = $spCode;

	$formLang = '';
	$displayLang = '';

	spa_paint_open_tab(esc_html(SP()->primitives->admin_text('Integration')).' - '.esc_html(SP()->primitives->admin_text('Language Settings')));
		spa_paint_open_panel();
			spa_paint_open_fieldset(esc_html(SP()->primitives->admin_text('Language')), true, 'language-select');

				if (!empty($userLang) && array_key_exists($userLang['spCode'], $langSets)) {
					$formLang = $userLang['spLang'];
				} elseif (array_key_exists($siteLang, $langSets)) {
					$formLang = $siteLang;
				}

				if (!empty($formLang)) {
					foreach($langSets as $lKey => $wpData) {
						if($formLang == $wpData['wpCode']) $displayLang = $wpData['langName'];
					}
				}
				if (empty($formLang)) {
					$link = '<a href="https://simple-press.com/documentation/installation/installation-information/localization/" target="blank">' . esc_html(SP()->primitives->admin_text('This Page')) . '</a>';
					echo '<div class="sf-alert-block sf-info">';
					SP()->primitives->admin_etext('Your WordPress site language setting has not been recognised by Simple:Press');
					echo '<br /><br />'.wp_kses_post(sprintf(esc_html(SP()->primitives->admin_text('Please see %s for manual install instructions')), $link));
					echo '</div>';
				} else {
					echo '<div class="sf-alert-block sf-info">';
					echo esc_html(SP()->primitives->admin_text('Your site language is set to')).' <b>'.esc_html($formLang).' - '.esc_html($displayLang).'</b>';
					echo '</div>';
				}

			spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_close_container();
		echo '<div class="sfform-panel-spacer"></div>';

	spa_paint_close_tab();

	echo '<div class="sfform-panel-spacer"></div>';

	# Now stop if we still do not know the language
	if (empty($userLang['spLang'])) return;

	# load up the XML file
	$c = wp_remote_get('https://simple-press.com/downloads/simple-press/simple-press_6.0.xml');
	if (is_wp_error($c) || wp_remote_retrieve_response_code($c) != 200) {
		echo '<p>'.esc_html(SP()->primitives->admin_text('Unable to communicate with Simple Press server')).'</p>';
		return;
	}
	$l = new SimpleXMLElement($c['body']);
	if (empty($l)) {
		echo '<p>'.esc_html(SP()->primitives->admin_text('Unable to communicate with Simple Press server')).'</p>';
		return;
	}

	# Core, theme and plugin lists and downloads
	$gif = SPCOMMONIMAGES.'working.gif';
	$site = wp_nonce_url(SPAJAXURL.'integration-langs', 'integration-langs');
	$x = 0;

	spa_paint_open_tab(esc_html(SP()->primitives->admin_text('Integration')).' - '.esc_html(SP()->primitives->admin_text('Language Translations')), true, '', false );
		spa_paint_open_panel();
			echo '<span class="sf-icon sf-check" title="'.esc_attr(SP()->primitives->admin_text('Translation file installed')).'"></span>' . esc_html(SP()->primitives->admin_text('Translation file installed'));
			echo '&nbsp;&nbsp;&nbsp;&nbsp;<span class="sf-icon sf-no-check" title="'.esc_attr(SP()->primitives->admin_text('Translation install failed')).'"></span>' . esc_html(SP()->primitives->admin_text('Install failed - or there is no available translation'));
			echo '<br /><br /><br />';
# Core - front and admin --------------------------------------

			spa_paint_open_fieldset(esc_html(SP()->primitives->admin_text('Core Simple:Press')), false);

				$item = $l->core;
				$version = $item->version;

				echo '<table class="wp-list-table widefat striped">';
					echo '<tr><td class="sf-width-50-per"><b>Core: Simple:Press ' . esc_html($version) . '</b></td>';
					$thisItem = $site.'&amp;item=corefront&amp;version='.sp_format_version($version).'&amp;langcode='.$userLang['spCode'].'&amp;textdom=sp';
					$target = 'spItem'.$x;
					$x++;
					echo '<td><span id="'.esc_attr($target).'">';
					if (sp_check_for_mo('language-sp', 'sp', $thisItem, $target)) {
						$btext = SP()->primitives->admin_text('Get Latest');
					} else {
						$btext = SP()->primitives->admin_text('Install');
					}
					echo '&nbsp;&nbsp;';
					echo '<input type="button" class="logDetail button spLoadAjax" value="'.esc_attr($btext).'" data-url="'.esc_attr($thisItem).'" data-target="'.esc_attr($target).'" data-img="'.esc_attr($gif).'" /></span></td></tr>';

					echo '<tr><td class="sf-width-50-per"><b>Core: Administration ' . esc_html($version) . '</b></td>';
					$thisItem = $site.'&amp;item=coreadmin&amp;version='.sp_format_version($version).'&amp;langcode='.$userLang['spCode'].'&amp;textdom=spa';
					$target = 'spItem'.$x;
					$x++;
					echo '<td><span id="'.esc_attr($target).'">';
					if (sp_check_for_mo('language-sp', 'spa', $thisItem, $target)) {
						$btext = SP()->primitives->admin_text('Get Latest');
					} else {
						$btext = SP()->primitives->admin_text('Install');
					}
					echo '&nbsp;&nbsp;';
					echo '<input type="button" class="logDetail button spLoadAjax" value="'.esc_attr($btext).'" data-url="'.esc_attr($thisItem).'" data-target="'.esc_attr($target).'" data-img="'.esc_attr($gif).'" /></span></td></tr>';
				echo '</table>';

			spa_paint_close_fieldset();

# Themes in use --------------------------------------

			spa_paint_open_fieldset(esc_html(SP()->primitives->admin_text('Active Simple:Press Themes')), false);

				$list = $l->themes;
				$done = array();
				$child = false;

				echo '<table class="wp-list-table widefat striped">';

# Core Theme
					$t = SP()->options->get('sp_current_theme');
					if (!empty($t['parent'])) {
						# Is a child theme
						$child = true;
						$theme = $t['parent'];
						$done[] = $theme;
						$done[] = $t['theme'];
					} else {
						$theme = $t['theme'];
						$done[] = $theme;
					}

					$data = sp_get_xml_theme_entry($list, $theme);
					$data = (object) $data;
					$name = (isset($data->name)) ? $data->name : $theme;
					echo '<tr><td class="sf-width-50-per"><b>'.esc_html($name).'</b>';
					if ($child) echo '&nbsp;('.esc_html(SP()->primitives->admin_text('Child Theme Parent')).')';
					echo '</td>';
					if (isset($data->name)) {
						$thisItem = $site.'&amp;item=theme&amp;langcode='.$userLang['spCode'].'&amp;textdom='.$data->lang.'&amp;name='.$theme;
						$target = 'spItem'.$x;
						$x++;
						echo '<td><span id="'.esc_attr($target).'">';
						if (sp_check_for_mo('language-sp-themes', $data->lang, $thisItem, $target)) {
							$btext = SP()->primitives->admin_text('Get Latest');
						} else {
							$btext = SP()->primitives->admin_text('Install');
						}
						echo '&nbsp;&nbsp;';
						echo '<input type="button" class="logDetail button spLoadAjax" value="'.esc_attr($btext).'" data-url="'.esc_attr($thisItem).'" data-target="'.esc_attr($target).'" data-img="'.esc_attr($gif).'" /></span></td></tr>';
					} else {
						echo '<td>'.esc_html(SP()->primitives->admin_text('No Translation Project Exists')).'</td></tr>';
					}

# Tablet Theme
					$t = SP()->options->get('sp_tablet_theme');
					if ($t['active']) {
						$child = false;
						if (!empty($t['parent'])) {
							# Is a child theme
							$child = true;
							$theme = $t['parent'];
						} else {
							$theme = $t['theme'];
						}

						if (!in_array($theme, $done)) {
							if ($child) {
								$done[] = $theme;
								$done[] = $t['theme'];
							} else {
								$done[] = $theme;
							}

							$data = sp_get_xml_theme_entry($list, $theme);
							$data = (object) $data;
							$name = (isset($data->name)) ? $data->name : $theme;
							echo '<tr><td class="sf-width-50-per"><b>'.esc_html($name).'</b></td>';
							echo '<tr><td class="sf-width-50-per"><b>'.esc_html($name).'</b>';
							if($child) echo '&nbsp;('.esc_html(SP()->primitives->admin_text('Child Theme Parent')).')';
							echo '</td>';
							if (isset($data->name)) {
								$thisItem = $site.'&amp;item=theme&amp;langcode='.$userLang['spCode'].'&amp;textdom='.$data->lang.'&amp;name='.$theme;
								$target = 'spItem'.$x;
								$x++;
								echo '<td><span id="'.esc_attr($target).'">';
								if (sp_check_for_mo('language-sp-themes', $data->lang, $thisItem, $target)) {
									$btext = SP()->primitives->admin_text('Get Latest');
								} else {
									$btext = SP()->primitives->admin_text('Install');
								}
								echo '&nbsp;&nbsp;';
								echo '<input type="button" class="logDetail button spLoadAjax" value="'.esc_attr($btext).'" data-url="'.esc_attr($thisItem).'" data-target="'.esc_attr($target).'" data-img="'.esc_attr($gif).'" /></span></td></tr>';
							} else {
								echo '<td>'.esc_html(SP()->primitives->admin_text('No Translation Project Exists')).'</td></tr>';
							}
						}
					}

# Mobile theme
					$t = SP()->options->get('sp_mobile_theme');
					if ($t['active']) {
						$child = false;
						if (!empty($t['parent'])) {
							# Is a child theme
							$child = true;
							$theme = $t['parent'];
						} else {
							$theme = $t['theme'];
						}

						if (!in_array($theme, $done)) {
							if ($child) {
								$done[] = $theme;
								$done[] = $t['theme'];
							} else {
								$done[] = $theme;
							}

							$data = sp_get_xml_theme_entry($list, $theme);
							$data = (object) $data;
							$name = (isset($data->name)) ? $data->name : $theme;
							echo '<tr><td class="sf-width-50-per"><b>'.esc_html($name).'</b>';
							if ($child) echo '&nbsp;('.esc_html(SP()->primitives->admin_text('Child Theme Parent')).')';
							echo '</td>';
							if (isset($data->name)) {
								$thisItem = $site.'&amp;item=theme&amp;langcode='.$userLang['spCode'].'&amp;textdom='.$data->lang.'&amp;name='.$theme;
								$target = 'spItem'.$x;
								$x++;
								echo '<td><span id="'.esc_attr($target).'">';
								if (sp_check_for_mo('language-sp-themes', $data->lang, $thisItem, $target)) {
									$btext = SP()->primitives->admin_text('Get Latest');
								} else {
									$btext = SP()->primitives->admin_text('Install');
								}
								echo '&nbsp;&nbsp;';
								echo '<input type="button" class="logDetail button spLoadAjax" value="'.esc_attr($btext).'" data-url="'.esc_attr($thisItem).'" data-target="'.esc_attr($target).'" data-img="'.esc_attr($gif).'" /></span></td></tr>';
							} else {
								echo '<td>'.esc_html(SP()->primitives->admin_text('No Translation Project Exists')).'</td></tr>';
							}
						}
					}
				echo '</table>';

			spa_paint_close_fieldset();

# Plugins if any --------------------------------------

			$plugins = SP()->options->get('sp_active_plugins');
			if ($plugins) {

				spa_paint_open_fieldset(esc_html(SP()->primitives->admin_text('Active Simple:Press Plugins')), false);

					echo '<table class="wp-list-table widefat striped">';

						$list = $l->plugins;

						foreach($plugins as $plugin) {
							$name = explode('/', $plugin);
							$data = sp_get_xml_plugin_entry($list, $name[0]);
							$data = (object) $data;
							$plugname = (isset($data->name)) ? $data->name : $name[0];
							echo '<tr><td class="sf-width-50-per"><b>'.esc_html($plugname).'</b></td>';
							if (isset($data->name)) {
								$thisItem = $site.'&amp;item=plugin&amp;langcode='.$userLang['spCode'].'&amp;textdom='.$data->lang.'&amp;name='.$name[0];
								$target = 'spItem'.$x;
								$x++;
								echo '<td><span id="'.esc_attr($target).'">';
								if(sp_check_for_mo('language-sp-plugins', $data->lang, $thisItem, $target)) {
									$btext = SP()->primitives->admin_text('Get Latest');
								} else {
									$btext = SP()->primitives->admin_text('Install');
								}
								echo '&nbsp;&nbsp;';
								echo '<input type="button" class="logDetail button spLoadAjax" value="'.esc_attr($btext).'" data-url="'.esc_attr($thisItem).'" data-target="'.esc_attr($target).'" data-img="'.esc_attr($gif).'" /></span></td></tr>';
							} else {
								echo '<td>'.esc_html(SP()->primitives->admin_text('No Translation Project Exists')).'</td></tr>';
							}
						}
					echo '</table>';

				spa_paint_close_fieldset();
			}

		spa_paint_close_panel();
		echo '<div class="sfform-panel-spacer"></div>';

		spa_paint_close_container();
	spa_paint_close_tab();
}

# --- Support Functions ---

function sp_format_version($ver) {
	$v = explode('.', $ver);
	$ver = $v[0].$v[1];
	return $ver;
}

function sp_get_xml_theme_entry($list, $cTheme) {
	$data = '';
	foreach ($list->theme as $theme) {
		if (strcasecmp($theme->name, $cTheme) == 0) {
			$data = $theme;
			break;
		}
	}
	return $data;
}

function sp_get_xml_plugin_entry($list, $cPlugin) {
	$data = '';
	foreach ($list->plugin as $plugin) {
		if (strcasecmp($plugin->folder, $cPlugin) == 0) {
			$data = $plugin;
			break;
		}
	}
	return $data;
}

function sp_check_for_mo($folder, $tDom, $thisItem, $target) {
	global $siteLang;
	$moFile = SP_STORE_DIR.'/'.SP()->plugin->storage[$folder].'/'.$tDom.'-'.$siteLang.'.mo';
	if (file_exists($moFile)) {
		$gif = SPCOMMONIMAGES.'working.gif';
		echo '<span class="sf-icon sf-check" title="'.esc_attr(SP()->primitives->admin_text('Translation file found')).'"></span>';
		$thisItem.= '&amp;remove=1';
		echo '<input type="button" class="logDetail button spLoadAjax" value="'.esc_attr(SP()->primitives->admin_text('Remove')).'" data-url="'.esc_attr($thisItem).'" data-target="'.esc_attr($target).'" data-img="'.esc_attr($gif).'" />';
		return true;
	} else {
		echo '<span class="sf-icon sf-no-check" title="'.esc_attr(SP()->primitives->admin_text('No translation file found')).'"></span>';
		return false;
	}
}
