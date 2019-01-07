<?php
/*
Simple:Press
Theme API Routines
$LastChangedDate: 2017-06-10 11:52:27 -0500 (Sat, 10 Jun 2017) $
$Rev: 15417 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# ==========================================================================================
#
# 	SITE - This file loads at core level - all page loads
#	SP Theme Handling
#
# ==========================================================================================

/**
* Parse the simple:press theme contents to retrieve theme's metadata.
*
* The metadata of the theme's data searches for the following in the theme's
* text definition file. This text file MUST be named spTheme.txt and be
* located in the theme root directory.  All theme data must be on its own line.
* For theme description, it must not have any newlines or only parts of the
* description will be displayed and the same goes for the theme data. The below
* is formatted for printing.
*
* Simple:Press Theme Title: Name of Theme
* Theme URI: Link to theme information
* Description: Theme Description
* Author: Theme author's name
* Author URI: Link to the author's web site
* Version: Version of the plugin
* Stylesheet: Contains the main theme stylesheet name
* Screenshot: Name of optional image of the theme
* Colors: optional list of color variants supported
*
* The main stylesheet must reside the templates subdirectory. The screenshot
* must be located in the theme root directory.
*
* The first 8kB of the file will be pulled in and if the theme data is not
* within that first 8kB, then the theme author should correct their theme
* and move the theme data headers to the top.
*
* The theme file is assumed to have permissions to allow for scripts to read
* the file. This is not checked however and the file is only opened for
* reading.
*/
# Version: 5.0
function sp_get_theme_data($theme_file) {
	$default_headers = array(
		'Name' => 'Simple:Press Theme Title',
		'ThemeURI' => 'Theme URI',
		'Version' => 'Version',
		'Description' => 'Description',
		'Author' => 'Author',
		'AuthorURI' => 'Author URI',
		'Stylesheet' => 'Stylesheet',
		'Screenshot' => 'Screenshot',
		'Parent' => 'Parent',
	);
	$theme_data = get_file_data($theme_file, $default_headers, 'sp-theme');

	$allowedtags = array(
		'a'       => array( 'href' => array(), 'title' => array() ),
		'abbr'    => array( 'title' => array() ),
		'acronym' => array( 'title' => array() ),
		'code'    => array(),
		'em'      => array(),
		'strong'  => array(),
	);

	$theme_data['Name']        = wp_kses($theme_data['Name'],        $allowedtags);
	$theme_data['Version']     = wp_kses($theme_data['Version'],     $allowedtags);
	$theme_data['Description'] = wp_kses($theme_data['Description'], $allowedtags);
	$theme_data['Author']      = wp_kses($theme_data['Author'],      $allowedtags);

	return $theme_data;
}

/**
*  This function gets the basename of a theme.
*  This method extracts the name of a theme from its filename.
*/
# Version: 5.0
function sp_theme_basename($file) {
	$file = str_replace('\\', '/', $file); # sanitize for Win32 installs
	$file = preg_replace('|/+|', '/', $file); # remove any duplicate slash
	$theme_dir = str_replace('\\', '/', SPTHEMEBASEDIR); # sanitize for Win32 installs
	$theme_dir = preg_replace('|/+|', '/', $theme_dir); # remove any duplicate slash
	$file = preg_replace('#^'.preg_quote($theme_dir, '#').'/#', '', $file); # get relative path from plugins dir
	$file = trim($file, '/');
	return $file;
}

/**
* Check the themes directory and retrieve all plugin files with plugin data.
*
* Simple:Press only supports theme files in a subdirectory below the base
* themes directory. The file it looks for has the theme data and must be found
* in this location. It is required that do keep your theme files in directories.
*
* The file with the theme data is the file that will be included and therefore
* needs to have some required theme information.
*/
# Version: 5.0
function sp_get_themes() {
	$sf_themes = array();

	$theme_root = untrailingslashit(SPTHEMEBASEDIR);

	$themes_dir = @opendir($theme_root);
	$theme_files = array();
	if ($themes_dir) {
		while (($file = readdir($themes_dir)) !== false) {
            # themes must be in subdir
			if (is_dir($theme_root.'/'.$file)) {
				$themes_subdir = @opendir($theme_root.'/'.$file);
				if ($themes_subdir) {
					while (($subfile = readdir($themes_subdir)) !== false){
						if ($subfile == 'spTheme.txt') $theme_files[] = "$file";
					}
				}
			}
		}
	} else {
		return $sf_themes;
	}

	@closedir($themes_dir);
	@closedir($themes_subdir);

	if (empty($theme_files)) return $sf_themes;
	foreach ($theme_files as $theme_file) {
		if (!is_readable("$theme_root/$theme_file/spTheme.txt")) continue;
		$theme_data = sp_get_theme_data("$theme_root/$theme_file/spTheme.txt");
		if (empty($theme_data['Name'])) continue;
		$sf_themes[sp_theme_basename($theme_file)] = $theme_data;
	}
	uasort($sf_themes, 'sp_sort_themes');
	return $sf_themes;
}

function sp_sort_themes($x, $y) {
	return strnatcasecmp($x['Name'], $y['Name']);
}

/**
* Returns a list of themes in the theme directory that are available for a user to use.
*/

# Version: 5.0
function sp_get_themes_list_data() {
    $themes = sp_get_themes();
    return $themes;
}

# Version: 5.0
function sp_get_overlays($dir) {
    $overlays = array();
    if (file_exists($dir)) {
       	$overlays_dir = @opendir($dir);
        if ($overlays_dir) {
        	while (($subfile = readdir($overlays_dir)) !== false) {
        		if (substr($subfile, 0, 1) == '.') continue;
        		if (substr($subfile, -4) == '.php' || substr($subfile, -4) == '.css') {
                    $name = explode('.', $subfile);
                    $overlays[] = $name[0];
                }
        	}
        }
        @closedir($overlays_dir);
    }
    return $overlays;
}

# Version: 5.2
function sp_delete_sp_theme($theme) {
    $mobileTheme = sp_get_option('sp_mobile_theme');
    $tabletTheme = sp_get_option('sp_tablet_theme');
    $curTheme = sp_get_option('sp_current_theme');
    if ($curTheme['theme'] == $theme) {
        $mess = spa_text('Sorry, cannot delete the active theme');
    } else if ($mobileTheme['theme'] == $theme) {
        $mess = spa_text('Sorry, cannot delete the active mobile theme');
    } else if ($tabletTheme['theme'] == $theme) {
        $mess = spa_text('Sorry, cannot delete the active tablet theme');
    } else {
        sp_remove_dir(SPTHEMEBASEDIR.$theme);
    	do_action('sph_delete_'.trim($theme));
    	do_action('sph_deleted_sp_theme', trim($theme));
        $mess = spa_text('Theme deleted');
    }
    return $mess;
}

?>