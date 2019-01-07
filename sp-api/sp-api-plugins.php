<?php
/*
Simple:Press
Plugin API Routines
$LastChangedDate: 2017-06-10 11:52:27 -0500 (Sat, 10 Jun 2017) $
$Rev: 15417 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# ==========================================================================================
#
#	SITE - This file loads at core level - all page loads
#	SP Plugin Handling
#
# ==========================================================================================

/**
* This function returns an array of plugin files to be included in global scope
*/
# Version: 5.0
function sp_get_active_and_valid_plugins() {
	$plugins = array();
	$active_plugins = (array) sp_get_option('sp_active_plugins', array());

	if (empty($active_plugins)) return $plugins;
	foreach ($active_plugins as $plugin) {
		if (!validate_file($plugin)	 # $plugin must validate as file
			&& '.php' == substr($plugin, -4)   # $plugin must end with '.php'
			&& file_exists(SFPLUGINDIR.$plugin )  # $plugin must exist
			)
		$plugins[] = SFPLUGINDIR.$plugin;
	}

	return $plugins;
}

/**
* Check the plugins directory and retrieve all plugin files with plugin data.
*
* Simple:Press only supports plugin files in the base plugins directory
* and in one directory above the plugins directory. The file it looks for
* has the plugin data and must be found in those two locations. It is
* recommended that do keep your plugin files in directories.
*
* The file with the plugin data is the file that will be included and therefore
* needs to have the main execution for the plugin. This does not mean
* everything must be contained in the file and it is recommended that the file
* be split for maintainability. Keep everything in one file for extreme
* optimization purposes.
*/
# Version: 5.0
function sp_get_plugins($plugin_folder='') {
	$sf_plugins = array();
	$plugin_root = untrailingslashit(SFPLUGINDIR);
	if (!empty($plugin_folder)) $plugin_root.= $plugin_folder;

	# Files in root plugins directory
	$plugins_dir = @opendir($plugin_root);
	$plugins_subdir = '';
	$plugin_files = array();
	if ($plugins_dir) {
		while (($file = readdir($plugins_dir)) !== false) {
			if (substr($file, 0, 1) == '.') continue;

			if (is_dir($plugin_root.'/'.$file)) {
				$plugins_subdir = @opendir($plugin_root.'/'.$file);
				if ($plugins_subdir) {
					while (($subfile = readdir($plugins_subdir)) !== false) {
						if (substr($subfile, 0, 1) == '.') continue;
						if (substr($subfile, -4) == '.php') $plugin_files[] = "$file/$subfile";
					}
				}
			} else {
				if (substr($file, -4) == '.php') $plugin_files[] = $file;
			}
		}
	} else {
		return $sf_plugins;
	}

	if ($plugins_dir) @closedir($plugins_dir);
	if ($plugins_subdir) @closedir($plugins_subdir);

	if (empty($plugin_files)) return $sf_plugins;
	foreach ($plugin_files as $plugin_file) {
		if (!is_readable("$plugin_root/$plugin_file")) continue;
		$plugin_data = sp_get_plugin_data("$plugin_root/$plugin_file", false, false); # Do not apply markup/translate as it'll be cached.
		if (empty($plugin_data['Name'])) continue;
		$sf_plugins[plugin_basename($plugin_file)] = $plugin_data;
	}
	uasort($sf_plugins, 'sp_sort_plugins');
	return $sf_plugins;
}

function sp_sort_plugins($x, $y) {
	return strnatcasecmp($x['Name'], $y['Name']);
}

/**
* Parse the simple:press plugin contents to retrieve plugin's metadata.
*
* The metadata of the plugin's data searches for the following in the plugin's
* header. All plugin data must be on its own line. For plugin description, it
* must not have any newlines or only parts of the description will be displayed
* and the same goes for the plugin data. The below is formatted for printing.
*
* Plugin Name: Name of Plugin
* Plugin URI: Link to plugin information
* Description: Plugin Description
* Author: Plugin author's name
* Author URI: Link to the author's web site
* Version: Must be set in the plugin for WordPress 2.3+
* Text Domain: Optional. Unique identifier, should be same as the one used in
*		plugin_text_domain()
*
* Plugin data returned array contains the following:
*		'Name' - Name of the plugin, must be unique.
*		'PluginURI' - Plugin web site address.
*		'Version' - The plugin version number.
*		'Description' - Description of what the plugin does and/or notes
*		from the author.
*		'Author' - The author's name
*		'AuthorURI' - The authors web site address.
*		'TextDomain' - Plugin's text domain for localization.
*
* The first 8kB of the file will be pulled in and if the plugin data is not
* within that first 8kB, then the plugin author should correct their plugin
* and move the plugin data headers to the top.
*
* The plugin file is assumed to have permissions to allow for scripts to read
* the file. This is not checked however and the file is only opened for
* reading.
*/
# Version: 5.0
function sp_get_plugin_data($plugin_file, $markup=true, $translate=true) {
	$default_headers = array(
		'Name' => 'Simple:Press Plugin Title',
		'PluginURI' => 'Plugin URI',
		'Version' => 'Version',
		'Description' => 'Description',
		'Author' => 'Author',
		'AuthorURI' => 'Author URI',
	);
	$plugin_data = get_file_data($plugin_file, $default_headers, 'sp-plugin');

	$allowedtags = array(
		'a'		  => array('href' => array(), 'title' => array()),
		'abbr'	  => array('title' => array()),
		'acronym' => array('title' => array()),
		'code'	  => array(),
		'em'	  => array(),
		'strong'  => array(),
		'b'		  => array(),
		'u'		  => array(),
		'br'	  => array()
	);
	$plugin_data['Name']		= wp_kses($plugin_data['Name'],		   $allowedtags);
	$plugin_data['Version']		= wp_kses($plugin_data['Version'],	   $allowedtags);
	$plugin_data['Description'] = wp_kses($plugin_data['Description'], $allowedtags);
	$plugin_data['Author']		= wp_kses($plugin_data['Author'],	   $allowedtags);

	return $plugin_data;
}

/**
* Attempts activation of plugin in a "sandbox" and redirects on success.
*
* A plugin that is already activated will not attempt to be activated again.
*/
# Version: 5.0
function sp_activate_sp_plugin($plugin) {
	$mess = '';
	$plugin	 = sp_plugin_basename(trim($plugin));
	$current = sp_get_option('sp_active_plugins', array());
	$valid = sp_validate_plugin($plugin);
	if (is_wp_error($valid)) return sp_text('An error occurred activating the plugin');

	if (!in_array($plugin, $current)) {
		include SFPLUGINDIR.$plugin;
		do_action('sph_activate_sp_plugin', trim($plugin));
		$current[] = $plugin;
		sort($current);
		sp_update_option('sp_active_plugins', $current);
		do_action('sph_activate_'.trim($plugin));
		do_action('sph_activated_sp_plugin', trim($plugin));

		$mess = sp_text('Plugin successfully activated');
	} else {
		$mess = sp_text('Plugin is already active');
	}
	return $mess;
}

/**
 * Deactivate a single plugin or multiple plugins.
 *
 * The deactivation hook is disabled by the plugin upgrader by using the $silent
 * parameter.
*/
# Version: 5.0
function sp_deactivate_sp_plugin($plugins, $silent = false) {
	$current = sp_get_option('sp_active_plugins', array());
	$do_blog = false;

	foreach ((array) $plugins as $plugin) {
		$plugin = sp_plugin_basename($plugin);
		if (!sp_is_plugin_active($plugin)) continue;
		if (!$silent) do_action('sph_deactivate_sp_plugin', trim($plugin));

		# Deactivate for this blog only
		$key = array_search($plugin, (array) $current);
		if (false !== $key) {
			$do_blog = true;
			array_splice($current, $key, 1);
		}

		# Used by Plugin updater to internally deactivate plugin, however, not to notify plugins of the fact to prevent plugin output.
		if (!$silent) {
			do_action('sph_deactivate_'.trim($plugin));
			do_action('sph_deactivated_sp_plugin', trim($plugin));
		}
	}
	if ($do_blog) sp_update_option('sp_active_plugins', $current);
}

# Version: 5.2
function sp_delete_sp_plugin($plugin) {
	$mess = '';
	if (!sp_is_plugin_active($plugin)) {
		$parts = explode('/', $plugin);
		sp_remove_dir(SFPLUGINDIR.$parts[0]);
		do_action('sph_delete_'.trim($plugin));
		do_action('sph_deleted_sp_plugin', trim($plugin));

		$mess = sp_text('Plugin successfully deleted');
	} else {
		$mess = sp_text('Plugin is active and cannot be deleted');
	}
	return $mess;
}

/**
*  This function gets the basename of a plugin.
*  This method extracts the name of a plugin from its filename.
*/
# Version: 5.0
function sp_plugin_basename($file) {
	$file = str_replace('\\', '/', $file); # sanitize for Win32 installs
	$file = preg_replace('|/+|', '/', $file); # remove any duplicate slash
	$plugin_dir = str_replace('\\', '/', SFPLUGINDIR); # sanitize for Win32 installs
	$plugin_dir = preg_replace('|/+|', '/', $plugin_dir); # remove any duplicate slash
	$file = preg_replace('#^'.preg_quote($plugin_dir, '#').'/#', '', $file); # get relative path from plugins dir
	$file = trim($file, '/');
	return $file;
}

/**
* Checks whether the plugin is active by checking the active plugins list.
*/
# Version: 5.0
function sp_is_plugin_active($plugin) {
	return in_array($plugin, (array) sp_get_option('sp_active_plugins', array()));
}

/**
* Validate active plugins
*
* Validate all active plugins, deactivates invalid plugins and
* returns an array of deactivated ones.
*/
# Version: 5.0
function sp_validate_active_plugins() {
	$plugins = sp_get_option('sp_active_plugins', array());

	# validate vartype: array
	if (!is_array($plugins)) {
		sp_update_option('sp_active_plugins', array());
		$plugins = array();
	}

	if (empty($plugins)) return;
	$invalid = array();

	# invalid plugins get deactivated
	foreach ($plugins as $plugin) {
		$result = sp_validate_plugin($plugin);
		if (is_wp_error($result)) {
			$invalid[$plugin] = $result;
			sp_deactivate_sp_plugin($plugin, true);
		}
	}
	return $invalid;
}

/**
* Validate the plugin path.
*
* Checks that the file exists and that it is a valid file.
*/
# Version: 5.0
function sp_validate_plugin($plugin) {
	if (validate_file($plugin)) return new WP_Error('plugin_invalid', sp_text('Invalid plugin path'));
	if (!file_exists(SFPLUGINDIR.$plugin)) return new WP_Error('plugin_not_found', sp_text('Plugin file does not exist'));
	$installed_plugins = sp_get_plugins();
	if (!isset($installed_plugins[$plugin])) return new WP_Error('no_plugin_header', sp_text('The plugin does not have a valid header'));
	return 0;
}

/**
 * Function to add a new forum admin panel
 *
 * admin panel array elements
 * 0 - panel name
 * 1 - spf capability to view
 * 2 - tool tip
 * 3 - icon
 * 4 - subpanels
*/
# Version: 5.0
function sp_add_admin_panel($name, $capability, $tooltop, $icon, $subpanels, $position='') {
	global $sfadminpanels, $sfactivepanels;

	# make sure the current user has capability to see this panel
	if (!sp_current_user_can($capability)) return false;

	# make sure the panel doesnt already exist
	if (array_key_exists($name, $sfadminpanels)) return false;

	# fix up the subpanels formids from user names
	$forms = array();
	foreach ($subpanels as $index => $subpanel) {
		$forms[$index] = array('plugin' => $subpanel['id'], 'admin' => $subpanel['admin'], 'save' => $subpanel['save'], 'form' => $subpanel['form']);
	}

	$num_panels = count($sfactivepanels);
	if (empty($position) || ($position < 0 || $position > $num_panels)) $position = $num_panels;

	# okay, lets add the new panel
	$panel_data = array($name, $capability, 'simple-press/admin/panel-plugins/spa-plugins.php', $tooltop, $icon, wp_nonce_url(SPAJAXURL.'plugins-loader', 'plugins-loader'), $forms, false);
	array_splice($sfadminpanels, $position, 0, array($panel_data));

	# and update the active panels list
	$new = array_keys($sfactivepanels);
	array_splice($new, $position, 0, $name);
	$sfactivepanels = array_flip($new);

	return true;
}

/**
 * Function to add a new forum admin subpanels
*/
# Version: 5.0
function sp_add_admin_subpanel($panel, $subpanels) {
	global $sfadminpanels, $sfactivepanels;

	# make sure the panel exists
	if (!array_key_exists($panel, $sfactivepanels)) return false;

	# fix up the subpanels formids from user names
	$forms = $sfadminpanels[$sfactivepanels[$panel]][6];
	foreach ($subpanels as $index => $subpanel) {
		$forms[$index] = array('plugin' => $subpanel['id'], 'admin' => $subpanel['admin'], 'save' => $subpanel['save'], 'form' => $subpanel['form']);
	}

	# okay, lets add the new subpanel
	$sfadminpanels[$sfactivepanels[$panel]][6] = $forms;
	return true;
}

# ----------------------------------------------
# sp_find_css()
# Version: 5.0
# Checks in theme for css file - returns path
# ----------------------------------------------
function sp_find_css($path, $file, $spCSSFile='') {
	# bail if we dont have a file to search for
	if (empty($file)) return '';

	global $spGlobals, $spDevice;

	# find css file checking theme, parent and finally path
	$curTheme = $spGlobals['theme'];

	$altFolder = ($spDevice == 'desktop') ? 'desktop-css/' : 'mobile-css/';

	# first check for spCSS file
	if (!empty($spCSSFile)) {
		if (file_exists(SPTHEMEDIR.$spCSSFile) || file_exists(SPTHEMEDIR.$altFolder.$spCSSFile)) return '';
	}
	# and if a child theme quickly check parent for sps file
	if (!empty($spCSSFile) && !empty($curTheme['parent'])) {
		if (file_exists(SPTHEMEBASEDIR.$curTheme['parent'].'/styles/'.$spCSSFile) || file_exists(SPTHEMEBASEDIR.$curTheme['parent'].'/styles/'.$altFolder.$spCSSFile)) return '';
	}

	# now for standard CSS files
	if (file_exists(SPTHEMEDIR.$file)) {
		return SPTHEMEURL.$file;
	} else if (!empty($curTheme['parent']) && file_exists(SPTHEMEBASEDIR.$curTheme['parent'].'/styles/'.$file)) {
		return SPTHEMEBASEURL.$curTheme['parent'].'/styles/'.$file;
	} else {
		return $path.$file;
	}
}

# ----------------------------------------------
# sp_paint_icon()
# Version: 5.5.7
#	$Class - Class to apply to img tag
#	$url - default url of icon file
#	$file - name of icon file
#	$toolTip - for title is needed
#	$srcOnly - Just return glyph name or path
# ----------------------------------------------
function sp_paint_icon($class, $url, $file, $toolTip='', $srcOnly=false) {
	# bail if we dont have a file to search for
	if (empty($file)) return '';
	global $spImages;

	$src = '';

	if ($spImages) {
		foreach ($spImages as $spIcon) {
			if (file_exists($spIcon['dir'].$file)) {
				$src = $spIcon['url'].$file;
				break;
			}
		}
	}

	# Only check the base (this will usually be plugins) if the
	# current theme does not use glyphs. Custom icons would have
	# been discovered by this time
	if (current_theme_supports('sp-theme-glyphs') == false && empty($src)) {
		$path = SF_STORE_DIR . substr($url, (strpos($url, 'wp-content') + 10));
		if(file_exists($path.$file)) {
			$src = $url.$file;
		}
	}

	$title = (empty($toolTip)) ? '' : "title='$toolTip'";

	if(!empty($src)) {
		if(empty($toolTip)) $toolTip=substr($file, 0, -4);
		if($srcOnly) {
			return $src;
		} else {
			return "<img class='$class' src='$src' alt='$toolTip' $title />\n";
		}
	} else {
		$file = substr($file, 0, -4);
		if($srcOnly) {
			return $file;
		} else {
			return "<span class='$class $file' $title></span>\n";
		}
	}
}

# ----------------------------------------------
# sp_paint_custom_icon()
# Version: 5.5.7
#	$Class - class to apply to img tag
#	$url - default url of icon file
# ----------------------------------------------
function sp_paint_custom_icon($class, $url) {
	# bail if we dont have a url to search for
	if (empty($url)) return '';
	$file = substr(basename($url), 0, -4);
	return "<img class='$class' src='$url' alt='$file'>\n";
}

# ----------------------------------------------
# sp_paint_icon_id()
# Version: 5.5.7
#	$icon - full img tag of icon
#	$id - the id to add to tag
# ----------------------------------------------
function sp_paint_icon_id($icon, $id) {
	return str_replace('<img ', "<img id='$id' ", $icon);
}

# ----------------------------------------------
# sp_paint_file_icon()
# replaces sp_find_icon()
# Version: 5.5.7
# Checks in theme for icon file - returns path
# ----------------------------------------------
function sp_paint_file_icon($path, $file) {
	# bail if we dont have a file to search for
	if (empty($file)) return '';

	global $spImages;

	if ($spImages) {
		foreach ($spImages as $spIcon) {
			if (file_exists($spIcon['dir'].$file)) {
				return $spIcon['url'].$file;
			}
		}
	}
	return $path.$file;
}

# ----------------------------------------------
# sp_find_template()
# Version: 5.0
# Checks in theme templates for plugin template
# returns path
# ----------------------------------------------
function sp_find_template($path, $file) {
	# bail if we dont have a file to search for
	if (empty($file)) return '';

	global $spGlobals;

	# find template file checking theme, parent and finally path
	$curTheme = $spGlobals['theme'];

	if (file_exists(SPTEMPLATES.$file)) {
		return SPTEMPLATES.$file;
	} else if (!empty($curTheme['parent']) && file_exists(SPTHEMEBASEDIR.$curTheme['parent'].'/templates/'.$file)) {
		return SPTHEMEBASEDIR.$curTheme['parent'].'/templates/'.$file;
	} else {
		return $path.$file;
	}
}

# ----------------------------------------------
# sp_plugin_enqueue_style()
# Version: 5.2
# Enqueue an SP plugin CSS style file.
#
# Registers the plugin style if src provided (does NOT overwrite) and enqueues.
# ----------------------------------------------
function sp_plugin_enqueue_style($handle, $src, $deps=array(), $ver=false) {
	global $sp_plugin_styles;

	if (empty($src)) return;

	if (!is_a($sp_plugin_styles, 'WP_Styles')) $sp_plugin_styles = new WP_Styles();

	if ($src) {
		$_handle = explode('?', $handle);

		global $spDevice;
        $media = 'all';
		if ($spDevice == 'mobile') $media = 'mobile';
		if ($spDevice == 'tablet') $media = 'tablet';
		$sp_plugin_styles->add($_handle[0], $src, $deps, $ver, $media);
	}
	$sp_plugin_styles->enqueue($handle);
}

# ----------------------------------------------
# sp_combine_plugin_css_files()
# Version: 5.2
# combines any registered SP plugin CSS into a single CSS file.
# ----------------------------------------------
function sp_combine_plugin_css_files() {
	global $sp_plugin_styles, $spDevice;

	if (!is_a($sp_plugin_styles, 'WP_Styles'))	$sp_plugin_styles = new WP_Styles();

	# save copy of styles in case of failure writing
	$saved_styles = clone $sp_plugin_styles;

	# check for standard theme or mobile
	if ($spDevice == 'mobile') {
		$option = 'sp_css_concat_mobile';
	} else if ($spDevice == 'tablet') {
		$option = 'sp_css_concat_tablet';
	} else {
		$option = 'sp_css_concat';
	}
	$css_concat = sp_get_option($option);

	if (!is_array($css_concat)) $css_concat = array();

	$css_files_modify = array();
	$css_files = array();
	if (is_array($sp_plugin_styles->queue)) { # is there anything in the queue?
		$sp_plugin_styles->all_deps($sp_plugin_styles->queue); # preparing the queue taking dependencies into account
		foreach ($css_concat as $css => $value) { # going through all the already found css files, checking that they are still required
			if ((!in_array(substr($css, 4), $sp_plugin_styles->to_do)) && substr($css, 0, 4) == 'css-') {  # if the css is not queued, rewrite the file
				$css_media = $value['type'];
				$css_files_modify[$css_media] = true;
				unset($css_concat[$css]);
			}
		}

		foreach ($sp_plugin_styles->to_do as $css) {
			$css_src = $sp_plugin_styles->registered[$css]->src;
			$css_media = $sp_plugin_styles->registered[$css]->args;
			# is the css is hosted localy AND is a css file?
			if ((!(strpos($css_src, get_bloginfo('url')) === false) || substr($css_src, 0, 1) === '/' || substr($css_src, 0, 1) === '.') &&
				(substr($css_src, strrpos($css_src, '.'), 4) == '.css' || substr($css_src, strrpos($css_src, '.'), 4) == '.php')) {
				if (!is_array($css_files) || !array_key_exists($css_media, $css_files)) $css_files[$css_media] = array();
				if (strpos($css_src, get_bloginfo('url')) === false) {
					$css_relative_url = substr($css_src, 1);
				} else {
					$css_relative_url = substr($css_src, strlen(get_bloginfo('url')) + 1);
				}
				if (strpos($css_relative_url, '?')) $css_relative_url = substr($css_relative_url, 0, strpos($css_relative_url, '?')); # removing parameters
				$css_m_time = null;
				$css_m_time = @filemtime($css_relative_url); # getting the mofified time of the css file. extracting the file's dir
				if ($css_m_time) { # only add the file if it's accessible
					# check for php theme file indicating main theme file and save whole url vs just relative
					if (substr($css_src, strrpos($css_src, '.'), 4) == '.php') {
						array_push($css_files[$css_media], $css_src);
					} else {
						array_push($css_files[$css_media], $css_relative_url);
					}
					if ((!file_exists(SP_COMBINED_CACHE_DIR.SP_COMBINED_CSS_BASE_NAME.$css_media.'.css')) || # combined css doesn't exist
						(isset($css_concat['css-'.$css]) && (($css_m_time <> $css_concat['css-'.$css]['modified']) || $css_concat['css-'.$css]['type'] <> $css_media )) || # css file has changed or the media type changed
						(!isset($css_concat['css-'.$css]))) {  # css file is first identified
						$css_files_modify[$css_media] = true;  # the combined file containing this media type css should be changed
						if (isset($css_concat['css-'.$css]) && $css_concat['css-'.$css]['type'] <> $css_media) { # if the media type changed - rewrite both css files
							$tmp = $css_concat['css-'.$css]['type'];
							$css_files_modify[$tmp] = true;
						}
						if (!isset($css_concat['css-'.$css])) $css_concat['css-'.$css] = array();
						$css_concat['css-'.$css]['modified'] = $css_m_time; # write the new modified date
						$css_concat['css-'.$css]['type'] = $css_media;
					}
					$sp_plugin_styles->remove($css);  # removes the css file from the queue
				}
			}
		}
	}

	foreach ($css_files_modify as $key => $value) {
		$combined_file = fopen(SP_COMBINED_CACHE_DIR.SP_COMBINED_CSS_BASE_NAME.$key.'.css', 'w');
		if ($combined_file) {
			$css_content = '';
			if (is_array($css_files[$key])) {
				foreach ($css_files[$key] as $css_src) {
					$css_content.= "\n".sp_get_css_content($css_src)."\n";
				}
			}
			if (!isset($css_concat['ver'][$key])) $css_concat['ver'][$key] = 0;
			$css_concat['ver'][$key]++;

			# compress the css before writing it out
			require ('sp-api-class-css-compressor.php');
			$css_content = Minify_CSS_Compressor::process($css_content);

			fwrite($combined_file, $css_content);
			fclose($combined_file);
		} else { # couldnt open file for writing so revert back to enqueueing all the styles
			if (!empty($saved_styles)) {
				foreach ($saved_styles->queue as $handle) {
					wp_enqueue_style($handle, $saved_styles->registered[$handle]->src);
				}
			}
			return; # enqueued through wp now so bail
		}
	}

	foreach ($css_files as $key => $value) { # enqueue the combined css files
		wp_enqueue_style(SP_COMBINED_CSS_BASE_NAME.$key, SP_COMBINED_CACHE_URL.SP_COMBINED_CSS_BASE_NAME.$key.'.css', array(), $css_concat['ver'][$key]);
	}
	sp_update_option($option, $css_concat);
}

# ----------------------------------------------
# sp_get_css_content()
# Version: 5.2
# returns the content of the css file after modifying relative urls
# ----------------------------------------------
function sp_get_css_content($css_file) {
	# have to handle theme php files differently than css files
	if (substr($css_file, strrpos($css_file, '.'), 4) == '.php') {
		$options = array('timeout' => 5);
		$response = wp_remote_get($css_file, $options); # parse the php styles into css
		if (is_wp_error($response) || wp_remote_retrieve_response_code($response) != 200) return '';
		$content = wp_remote_retrieve_body($response);
		if (empty($content)) return '';
		$css_file = substr($css_file, strlen(get_bloginfo('url')) + 1); # change to relative path
		if (strpos($css_file, '?')) $css_file = substr($css_file, 0, strpos($css_file, '?')); # removing parameters
	} else {
		$source_file = fopen($css_file, 'r');
		if ($source_file) {
			$content = fread($source_file, filesize($css_file));
			fclose($source_file);
		} else {
			return '';
		}
	}

	# get relative css path
	if (strrpos($css_file, '/')) {
		$css_path = home_url().'/'.substr($css_file, 0, strrpos($css_file, '/')).'/';
	} else {
		$css_path = home_url().'/';
	}

	# change relative path to absolute for urls in css file
	if (preg_match_all("/\burl\b\s*?\((\s*?[\"'])?(?!\/)(?!http)(.*?)([\"']?\s*)?\)/", $content, $matches)) {
		foreach ($matches[0] as $index => $match) {
			if (!preg_match("/\burl\s?\(\s?\"?'?http:\/\//", $match)) {
				$content = str_replace($match, "url('$css_path{$matches[2][$index]}')", $content);
			}
		}
	}
	return $content;
}

# ----------------------------------------------
# sp_clear_combined_css()
# Version: 5.2
# removes the combined css file for the specified media type
# ----------------------------------------------
function sp_clear_combined_css($media='all') {
	if (file_exists(SP_COMBINED_CACHE_DIR.SP_COMBINED_CSS_BASE_NAME.$media.'.css')) @unlink(SP_COMBINED_CACHE_DIR.SP_COMBINED_CSS_BASE_NAME.$media.'.css');
}

# ----------------------------------------------
# sp_plugin_enqueue_script()
# Version: 5.2
# Enqueue an SP plugin javascript file.
# ----------------------------------------------
function sp_plugin_enqueue_script($handle, $src=false, $deps=array(), $ver=false, $in_footer=false) {
	global $sp_plugin_scripts;

	if (!is_a( $sp_plugin_scripts, 'WP_Scripts')) $sp_plugin_scripts = new WP_Scripts();

	if ($src) {
		$_handle = explode('?', $handle);

		global $spDevice;
        $media = 'desktop';
		if ($spDevice == 'mobile') $media = 'mobile';
		if ($spDevice == 'tablet') $media = 'tablet';

		$sp_plugin_scripts->add($_handle[0], $src, $deps, $ver, $media);
		if ($in_footer) $sp_plugin_scripts->add_data($_handle[0], 'group', 1);
	}
	$sp_plugin_scripts->enqueue($handle);
}

# ----------------------------------------------
# sp_plugin_register_script()
# Version: 5.2
# Register an SP plugin javascript file.
# ----------------------------------------------
function sp_plugin_register_script($handle, $src, $deps=array(), $ver=false, $in_footer=false) {
	global $sp_plugin_scripts;
	if (!is_a($sp_plugin_scripts, 'WP_Scripts')) $sp_plugin_scripts = new WP_Scripts();

	$sp_plugin_scripts->add($handle, $src, $deps, $ver);
	if ($in_footer) $sp_plugin_scripts->add_data($handle, 'group', 1);
}

# ----------------------------------------------
# sp_plugin_localize_script()
# Version: 5.2
# localizes any register plugin script variables
# ----------------------------------------------
function sp_plugin_localize_script($handle, $object_name, $l10n) {
	global $sp_plugin_scripts;
	if (!is_a($sp_plugin_scripts, 'WP_Scripts')) return false;
	return $sp_plugin_scripts->localize($handle, $object_name, $l10n);
}

# ----------------------------------------------
# sp_combine_plugin_script_files()
# Version: 5.2
# combines any registered SP plugin scripts into a single script file.
# ----------------------------------------------
function sp_combine_plugin_script_files() {
	global $sp_plugin_scripts, $skip_combine_js, $spDevice;

	if (isset($skip_combine_js)) return null; # Don't run twice
	$skip_combine_js = true;

	# check for standard theme or mobile
	if ($spDevice == 'mobile') {
		$option = 'sp_js_concat_mobile';
	} else if ($spDevice == 'tablet') {
		$option = 'sp_js_concat_tablet';
	} else {
		$option = 'sp_js_concat';
	}
	$js_concat = sp_get_option($option);
	if (!is_array($js_concat)) $js_concat = array();

	# save copy of styles in case of failure writing
	$saved_scripts = clone $sp_plugin_scripts;

	$js_files_modify = array();
	$js_files = array();
	$js_extra = array();
	if (is_array($sp_plugin_scripts->queue)) {	# is there anything in the queue?
		$sp_plugin_scripts->all_deps($sp_plugin_scripts->queue); # preparing the queue taking dependencies into account
		foreach ($js_concat as $js => $value) { # going through all the already found js files, checking that they are still required
			if ((!in_array(substr($js, 3), $sp_plugin_scripts->to_do)) && substr($js, 0, 3) == 'js-') {	 # if the js is not queued, rewrite the file
				$js_place = $value['type'];
				$js_files_modify[$js_place] = true;
				unset($js_concat[$js]);
			}
		}

		$dep = array();
		foreach ($sp_plugin_scripts->to_do as $js) {
			$js_src = $sp_plugin_scripts->registered[$js]->src;
			$js_place = $sp_plugin_scripts->registered[$js]->extra;
			if (is_array($js_place) && isset($js_place['group'] )) {
				$js_place = $spDevice.'-footer';
			} else {
				$js_place = $spDevice.'-header';
			}

			# grab any wp js files as dependencies and then ignore for enqueueing with our plugin scripts
			if (strpos($js_src, 'wp-includes') !== false || strpos($js_src, 'wp-admin') !== false) {
				$dep[] = $js;
				continue;
			}

			if ((!(strpos($js_src, get_bloginfo('url')) === false) || substr($js_src, 0, 1) === '/' || substr($js_src, 0, 1) === '.') && (substr($js_src, strrpos($js_src, '.'), 3) == '.js') ) { #the js is hosted localy AND a .js file
				if (!is_array($js_files) || !array_key_exists($js_place, $js_files)) $js_files[$js_place] = array();
				if (strpos($js_src, get_bloginfo('url')) === false) {
					$js_relative_url = substr($js_src,1);
				} else {
					$js_relative_url = substr($js_src, strlen(get_bloginfo('url')) + 1);
				}
				if (strpos($js_relative_url, '?')) $js_relative_url = substr($js_relative_url, 0, strpos($js_relative_url, '?')); #removing parameters
				$js_m_time = null;
				$js_m_time = @filemtime($js_relative_url); # getting the mofified time of the js file. extracting the file's dir
				if ($js_m_time) { # only add the file if it's accessible
					array_push($js_files[$js_place], $js_relative_url);
					if ((!file_exists(SP_COMBINED_CACHE_DIR.SP_COMBINED_SCRIPTS_BASE_NAME.$js_place.'.js')) || # combined js doesn't exist
						(isset($js_concat['js-'.$js]) && (($js_m_time <> $js_concat['js-'.$js]['modified']) || $js_concat['js-'.$js]['type'] <> $js_place)) || # js file has changed or the target place changed
						(!isset($js_concat['js-'.$js]))) {	# js file is first identified
						$js_files_modify[$js_place] = true;	 # the combined file containing this place js should be changed
						if (isset($js_concat['js-'.$js]) && $js_concat['js-'.$js]['type'] <> $js_place) { # if the place type changed - rewrite both js files
							$tmp = $js_concat['js-'.$js]['type'];
							$js_files_modify[$tmp] = true;
						}
						if (!isset($js_concat['js-'.$js])) $js_concat['js-'.$js] = array();
						$js_concat['js-'.$js]['modified'] = $js_m_time; # write the new modified date
						$js_concat['js-'.$js]['type'] = $js_place;
					}

					if (is_array($sp_plugin_scripts->registered[$js]->extra) && isset($sp_plugin_scripts->registered[$js]->extra['data'])) {
						$js_extra[$js_relative_url] = $sp_plugin_scripts->registered[$js]->extra['data'];
					}

					$sp_plugin_scripts->remove($js);  # removes the js file from the queue
					array_shift($sp_plugin_scripts->to_do);
				}
			}
		}
	}

	foreach ($js_files_modify As $key => $value) {
		$combined_file = fopen(SP_COMBINED_CACHE_DIR.SP_COMBINED_SCRIPTS_BASE_NAME.$key.'.js', 'w');
		if ($combined_file) {
			$js_content = '';
			if (is_array($js_files[$key])) {
				foreach ($js_files[$key] as $js_src) {
					$source_file = fopen($js_src, 'r');
					if ($source_file === false) return;

					# do we need to localize the script?
					if (isset($js_extra[$js_src])) $js_content.= "\n".$js_extra[$js_src]."\n";

					$js_content.= "\n".fread($source_file, filesize($js_src))."\n";
					fclose($source_file);
				}
			}
			if (!isset($js_concat['ver'][$key.'-js'])) $js_concat['ver'][$key.'-js'] = 0;
			$js_concat['ver'][$key.'-js']++;

			fwrite($combined_file, $js_content);
			fclose($combined_file);
		} else { # couldnt open file for writing so revert back to enqueueing all the scripts
			if (!empty($saved_scripts)) {
				foreach ($saved_scripts->queue as $handle) {
					$plugin_footer = (is_array($saved_scripts->registered[$handle]->extra) && $saved_scripts->registered[$handle]->extra['group'] == 1) ? true : false;
					wp_enqueue_script($handle, $saved_scripts->registered[$handle]->src, $saved_scripts->registered[$handle]->deps, false, $plugin_footer);
				}
			}
			return; # enqueued through wp now so bail
		}
	}

	# enqueue the combined js files with dependencies
	foreach ($js_files as $key => $value) { # enqueue the combined js files
		wp_enqueue_script(SP_COMBINED_SCRIPTS_BASE_NAME.$key, SP_COMBINED_CACHE_URL.SP_COMBINED_SCRIPTS_BASE_NAME.$key.'.js', $dep, $js_concat['ver'][$key.'-js'], strpos($key, 'footer') !== false);
	}

	sp_update_option($option, $js_concat);
}

# ----------------------------------------------
# sp_clear_combined_scripts()
# Version: 5.2
# removes the combined js files
# ----------------------------------------------
function sp_clear_combined_scripts($media='desktop') {
	if (file_exists(SP_COMBINED_CACHE_DIR.SP_COMBINED_SCRIPTS_BASE_NAME.$media.'-header.js')) @unlink(SP_COMBINED_CACHE_DIR.SP_COMBINED_SCRIPTS_BASE_NAME.$media.'-header.js');
	if (file_exists(SP_COMBINED_CACHE_DIR.SP_COMBINED_SCRIPTS_BASE_NAME.$media.'-footer.js')) @unlink(SP_COMBINED_CACHE_DIR.SP_COMBINED_SCRIPTS_BASE_NAME.$media.'-footer.js');
}

?>