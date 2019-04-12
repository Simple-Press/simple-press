<?php

/**
 * Core class used for Simple Press themes.
 *
 * This class is used to access the theme api code within Simple:Press
 *
 * @since 6.0
 *
 * Public methods available:
 *------------------------
 * get_data($theme_file)
 * get_list()
 * get_overlays($dir)
 * get_overlay_icons($path)
 * get_current()
 * set_image_array($curTheme)
 * delete($theme)
 * find_css($path, $file, $spCSSFile)
 * find_template($path, $file)
 * paint_icon($class, $url, $file, $toolTip, $srcOnly)
 * paint_custom_icon($class, $url)
 * paint_file_icon($path, $file)
 * paint_icon_id($icon, $id)
 *
 * $LastChangedDate: 2019-01-30 16:40:00 -0600 (Wed, 30 Jan 2019) $
 * $Rev: 15704 $
 *
 */
class spcTheme {
	/**
	 *
	 * @var array    paths to potential location of SP theme images
	 *
	 * @since 6.0
	 */
	public $images = array();

	/**
	 * This method retrieves the theme header info for the specified theme
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param string $theme_file theme for which to grab header info
	 *
	 * @returns    array   theme header info
	 */
	public function get_data($theme_file) {
		$default_headers = array('Name'       => 'Simple:Press Theme Title',
								 'ItemId'      => 'Item Id',
		                         'ThemeURI'    => 'Theme URI',
		                         'Version'     => 'Version',
		                         'Description' => 'Description',
		                         'Author'      => 'Author',
		                         'AuthorURI'   => 'Author URI',
		                         'Stylesheet'  => 'Stylesheet',
		                         'Screenshot'  => 'Screenshot',
		                         'Parent'      => 'Parent',);
		$theme_data      = get_file_data($theme_file, $default_headers, 'sp-theme');

		$allowedtags = array('a'       => array('href'  => array(),
		                                        'title' => array()),
		                     'abbr'    => array('title' => array()),
		                     'acronym' => array('title' => array()),
		                     'code'    => array(),
		                     'em'      => array(),
		                     'strong'  => array(),);

		$theme_data['Name']        = wp_kses($theme_data['Name'], $allowedtags);
		$theme_data['Version']     = wp_kses($theme_data['Version'], $allowedtags);
		$theme_data['Description'] = wp_kses($theme_data['Description'], $allowedtags);
		$theme_data['Author']      = wp_kses($theme_data['Author'], $allowedtags);
		
		if($theme_data['ItemId'] && $theme_data['ItemId'] != ''){
			
			$theme_data['ItemId']        = wp_kses($theme_data['ItemId'], $allowedtags);
		}
		
		return $theme_data;
	}

	/**
	 * This method check the themes directory and retrieve all theme files with theme data.
	 *
	 * Simple:Press only supports theme files in a subdirectory below the base themes directory.
	 * The file it looks for has the theme data and must be found in this location.
	 * It is required that do keep your theme files in directories.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param void
	 *
	 * @returns    array    list of themes
	 */
	public function get_list() {
		$theme_root = untrailingslashit(SPTHEMEBASEDIR);

		$themes_dir  = @opendir($theme_root);
		$theme_files = array();
		if ($themes_dir) {
			while (($file = readdir($themes_dir)) !== false) {
				# themes must be in subdir
				if (is_dir($theme_root.'/'.$file)) {
					$themes_subdir = @opendir($theme_root.'/'.$file);
					if ($themes_subdir) {
						while (($subfile = readdir($themes_subdir)) !== false) {
							if ($subfile == 'spTheme.txt') $theme_files[] = "$file";
						}
					}
					@closedir($themes_subdir);
				}
			}
		} else {
			return array();
		}

		@closedir($themes_dir);

		if (empty($theme_files)) return array();

		$themes = array();
		foreach ($theme_files as $theme_file) {
			if (!is_readable("$theme_root/$theme_file/spTheme.txt")) continue;
			$theme_data = SP()->theme->get_data("$theme_root/$theme_file/spTheme.txt");
			if (empty($theme_data['Name'])) continue;
			$themes[$this->basename($theme_file)] = $theme_data;
		}

		uasort($themes, array($this, 'sort_themes'));

		return $themes;
	}

	/**
	 * This method gets a list of overlays available for the specified theme directory.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param string $dir theme directory
	 *
	 * @returns    array    list of overlays for theme
	 */
	public function get_overlays($dir) {
		$overlays = array();
		if (file_exists($dir)) {
			$overlays_dir = @opendir($dir);
			if ($overlays_dir) {
				while (($subfile = readdir($overlays_dir)) !== false) {
					if (substr($subfile, 0, 1) == '.') continue;
					if (substr($subfile, -4) == '.php' || substr($subfile, -4) == '.css') {
						$name       = explode('.', $subfile);
						$overlays[] = $name[0];
					}
				}
			}
			@closedir($overlays_dir);
		}

		return $overlays;
	}

	/**
	 * This function retrieves current WP theme icon pack to be used with an overlay.
	 *
	 * @since 6.0
	 *
	 * @param string $path path to current SP Theme overlay
	 *
	 * @return array    icon pack for overlay if one exists
	 */
	public function get_overlay_icons($path) {
		if (empty($path)) return array();

		$defaults = array('Icons' => 'Icons');

		$data = get_file_data($path, $defaults);

		if (!empty($data['Icons'])) {
			return $data['Icons'];
		} else {
			return array();
		}
	}

	/**
	 * This function returns the current theme array accounting for device type.
	 *
	 * @since 6.0
	 *
	 * @param void
	 *
	 * @return array    current theme to be displayed
	 */
	public function get_current() {
		if (SP()->core->device == 'mobile') {
			$theme = SP()->options->get('sp_mobile_theme');
			if (!empty($theme) && $theme['active']) return $theme;
		}

		if (SP()->core->device == 'tablet') {
			$theme = SP()->options->get('sp_tablet_theme');
			if (!empty($theme) && $theme['active']) return $theme;
		}

		return SP()->options->get('sp_current_theme');
	}

	/**
	 * This function sets up the icon array for current SP theme for SP()->theme->paint_icon() function to use.
	 *
	 * @since 6.0
	 *
	 * @param array $curTheme current SP theme elements
	 *
	 * @return void
	 */
	public function set_image_array($curTheme) {
		$idx = 0;

		# Current theme special icons folder (overlay specified)
		if (!empty($curTheme['icons'])) {
			$p                         = (SP()->core->device == 'mobile') ? $curTheme['theme'].'/images/'.$curTheme['icons'].'/mobile/' : $curTheme['theme'].'/images/'.$curTheme['icons'].'/';
			$this->images[$idx]['dir'] = SPTHEMEBASEDIR.$p;
			$this->images[$idx]['url'] = SPTHEMEBASEURL.$p;
			$idx++;
		}
		# Current theme default images folder
		$p                         = (SP()->core->device == 'mobile') ? $curTheme['theme'].'/images/mobile/' : $curTheme['theme'].'/images/';
		$this->images[$idx]['dir'] = SPTHEMEBASEDIR.$p;
		$this->images[$idx]['url'] = SPTHEMEBASEURL.$p;
		$idx++;

		# if Child theme add porent locations
		if (!empty($curTheme['parent'])) {
			# Parent theme special icons folder (overlay specified)
			if (!empty($curTheme['icons'])) {
				$p                         = (SP()->core->device == 'mobile') ? $curTheme['parent'].'/images/'.$curTheme['icons'].'/mobile/' : $curTheme['parent'].'/images/'.$curTheme['icons'].'/';
				$this->images[$idx]['dir'] = SPTHEMEBASEDIR.$p;
				$this->images[$idx]['url'] = SPTHEMEBASEURL.$p;
				$idx++;
			}

			# Parent theme default images folder
			$p                         = (SP()->core->device == 'mobile') ? $curTheme['parent'].'/images/mobile/' : $curTheme['parent'].'/images/';
			$this->images[$idx]['dir'] = SPTHEMEBASEDIR.$p;
			$this->images[$idx]['url'] = SPTHEMEBASEURL.$p;
		}
	}

	/**
	 * This method deletes the specified theme from the server.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param string $theme theme to be deleted
	 *
	 * @returns    string    success/fail for theme deletion
	 */
	public function delete($theme) {
		$mobileTheme = SP()->options->get('sp_mobile_theme');
		$tabletTheme = SP()->options->get('sp_tablet_theme');
		$curTheme    = SP()->options->get('sp_current_theme');

		if ($curTheme['theme'] == $theme) {
			$mess = SP()->primitives->admin_text('Sorry, cannot delete the active theme');
		} else if ($mobileTheme['theme'] == $theme) {
			$mess = SP()->primitives->admin_text('Sorry, cannot delete the active mobile theme');
		} else if ($tabletTheme['theme'] == $theme) {
			$mess = SP()->primitives->admin_text('Sorry, cannot delete the active tablet theme');
		} else {
			SP()->primitives->remove_dir(SPTHEMEBASEDIR.$theme);

			do_action('sph_delete_'.trim($theme));
			do_action('sph_deleted_sp_theme', trim($theme));

			$mess = SP()->primitives->admin_text('Theme deleted');
		}

		return $mess;
	}

	/**
	 * This method looks in the theme for the css file to use.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 */
	public function find_css($path, $file, $spCSSFile = '') {
		# bail if we dont have a file to search for
		if (empty($file)) return '';

		# bail if theme built with LESS
		if(current_theme_supports('sp-theme-less')) return '';

		# find css file checking theme, parent and finally path
		$curTheme = SP()->core->forumData['theme'];

		$altFolder = (SP()->core->device == 'desktop') ? 'desktop-css/' : 'mobile-css/';

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

	/**
	 * This method checks in theme templates for plugin template.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 */
	public function find_template($path, $file) {
		# bail if we dont have a file to search for
		if (empty($file)) return '';

		# find template file checking theme, parent and finally path
		$curTheme = SP()->core->forumData['theme'];

		if (file_exists(SPTEMPLATES.$file)) {
			return SPTEMPLATES.$file;
		} else if (!empty($curTheme['parent']) && file_exists(SPTHEMEBASEDIR.$curTheme['parent'].'/templates/'.$file)) {
			return SPTHEMEBASEDIR.$curTheme['parent'].'/templates/'.$file;
		} else {
			return $path.$file;
		}
	}

	/**
	 * This method finds the right theme icon to display.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 */
	public function paint_icon($class, $url, $file, $toolTip = '', $srcOnly = false) {
		# bail if we dont have a file to search for
		if (empty($file)) return '';

		$src = '';

		if ($this->images) {
			foreach ($this->images as $spIcon) {
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
			$path = SP_STORE_DIR.substr($url, (strpos($url, 'wp-content') + 10));
			if (file_exists($path.$file)) {
				$src = $url.$file;
			}
		}

		$title = (empty($toolTip)) ? '' : "title='$toolTip'";

		if (!empty($src)) {
			if (empty($toolTip)) $toolTip = substr($file, 0, -4);
			if ($srcOnly) {
				return $src;
			} else {
				return "<img class='$class' src='$src' alt='$toolTip' $title />\n";
			}
		} else {
			$file = substr($file, 0, -4);
			if ($srcOnly) {
				return $file;
			} else {
				return "<span class='$class $file' $title></span>\n";
			}
		}
	}

	/**
	 * This method finds the right theme icon to display.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 */
	public function paint_custom_icon($class, $url) {
		# bail if we dont have a url to search for
		if (empty($url)) return '';
		$file = substr(basename($url), 0, -4);

		return "<img class='$class' src='$url' alt='$file'>\n";
	}

	/**
	 * This method finds the right theme icon to display.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 */
	public function paint_file_icon($path, $file) {
		# bail if we dont have a file to search for
		if (empty($file)) return '';

		if ($this->images) {
			foreach ($this->images as $spIcon) {
				if (file_exists($spIcon['dir'].$file)) {
					return $spIcon['url'].$file;
				}
			}
		}

		return $path.$file;
	}

	/**
	 * This method finds the right theme icon to display.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 */
	public function paint_icon_id($icon, $id) {
		return str_replace('<img ', "<img id='$id' ", $icon);
	}

	/**
	 * This method gets the basename of a theme.
	 * This method extracts the name of a theme from its filename - works for unix or windows style.
	 *
	 * @access private
	 *
	 * @since 6.0
	 *
	 * @param string $file contains the filename to get basename for
	 *
	 * @returns    string    basename
	 */
	private function basename($file) {
		$file      = str_replace('\\', '/', $file); # sanitize for Win32 installs
		$file      = preg_replace('|/+|', '/', $file); # remove any duplicate slash
		$theme_dir = str_replace('\\', '/', SPTHEMEBASEDIR); # sanitize for Win32 installs
		$theme_dir = preg_replace('|/+|', '/', $theme_dir); # remove any duplicate slash
		$file      = preg_replace('#^'.preg_quote($theme_dir, '#').'/#', '', $file); # get relative path from plugins dir
		$file      = trim($file, '/');

		return $file;
	}

	/**
	 * This method sorts the theme list.
	 *
	 * @access private
	 *
	 * @since 6.0
	 *
	 * @returns    string|object    empty array on success or WP_ERROR object
	 */
	private function sort_themes($x, $y) {
		return strnatcasecmp($x['Name'], $y['Name']);
	}
}