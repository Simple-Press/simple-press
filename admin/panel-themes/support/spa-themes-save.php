<?php
/*
Simple:Press
Admin plugins Update Support Functions
$LastChangedDate: 2018-11-02 12:29:50 -0500 (Fri, 02 Nov 2018) $
$Rev: 15792 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# Saves the selected theme as the current active theme
function spa_save_theme_data() {
	check_admin_referer('forum-adminform_themes', 'forum-adminform_themes');

	require_once SPBOOT.'site/sp-site-support-functions.php';

	$theme = SP()->filters->str($_POST['theme']);
	$style = SP()->filters->str($_POST['style']);
	$color = (isset($_POST['color-'.$theme])) ? SP()->filters->str($_POST['color-'.$theme]) : '';
	$parent = (isset($_POST['parent'])) ? SP()->filters->str($_POST['parent']) : '';

	if (isset($_POST['activate']) || isset($_POST['update'])) {
		if (empty($theme) || empty($style)) return SP()->primitives->admin_text('An error occurred activating the Theme!');
		if (empty($color)) $color = SP()->filters->str($_POST['default-color']);

		# activate the theme
		$current = array();
		$current['theme'] = $theme;
		$current['style'] = $style;
		$current['color'] = $color;
		$current['parent'] = $parent;

		$icons = '';
		if (!empty($color)) {
            if (!empty($parent) && !file_exists(SPTHEMEBASEDIR.$theme.'/styles/overlays/'.$color.'.php')) {
                $f = SPTHEMEBASEDIR.$parent.'/styles/overlays/'.$color.'.php';
            } else {
                $f = SPTHEMEBASEDIR.$theme.'/styles/overlays/'.$color.'.php';
            }
			//$icons = SP()->filters->str(SP()->theme->get_overlay_icons($f));
			$icons = SP()->theme->get_overlay_icons($f);
		}
		$current['icons'] = $icons;

		SP()->options->update('sp_current_theme', $current);

		# load theme functions file in case it wants to hook into activation
		if (file_exists(SPTHEMEBASEDIR.$theme.'/templates/spFunctions.php')) {
			require_once SPTHEMEBASEDIR.$theme.'/templates/spFunctions.php';
		}

		# clean out the combined css file
		SP()->plugin->clear_css_cache('all');
		SP()->plugin->clear_css_cache('mobile');
		SP()->plugin->clear_css_cache('tablet');

		# theme activation action
		do_action('sph_activate_theme', $current);
		do_action('sph_activate_theme_'.$theme, $current);

		return SP()->primitives->admin_text('Theme updated');
	} else if (isset($_POST['delete']) == 'delete' && (!is_multisite() || is_super_admin())) {
		$mess = SP()->theme->delete($theme);
		return $mess;
	}
	return '';
}

function spa_save_theme_mobile_data() {
	check_admin_referer('forum-adminform_themes', 'forum-adminform_themes');

   	$mobileTheme = SP()->options->get('sp_mobile_theme');
   	$curTheme = SP()->options->get('sp_current_theme');

   	$mobile = array();
   	$active = isset($_POST['active']);

    if (isset($_POST['saveit'])) {
		$pagetemplate = (isset($_POST['pagetemplate'])) ? SP()->filters->str($_POST['pagetemplate']) : $mobileTheme['pagetemplate'];

		if (isset($_POST['pagetemplate'])) {
			$usetemplate = isset($_POST['usetemplate']);
			$notitle = isset($_POST['notitle']);
		} else {
			$usetemplate = $mobileTheme['usetemplate'];
			$notitle = $mobileTheme['notitle'];
		}

		$mobile['active'] = $active;
		$mobile['theme'] = $mobileTheme['theme'];
		$mobile['style'] = $mobileTheme['style'];
		$mobile['color'] = $mobileTheme['color'];
		$mobile['parent'] = $mobileTheme['parent'];
		$mobile['usetemplate'] = $usetemplate;
		$mobile['pagetemplate'] = $pagetemplate;
		$mobile['notitle'] = $notitle;
    } else {
    	if ($active && $mobileTheme['active']) {
    		$theme = (isset($_POST['theme'])) ? SP()->filters->str($_POST['theme']) : $mobileTheme['theme'];
    		$style = (isset($_POST['style'])) ? SP()->filters->str($_POST['style']) : $mobileTheme['style'];
    		$color = (isset($_POST['color-'.$theme])) ? SP()->filters->str($_POST['color-'.$theme]) : '';
    		$parent = isset($_POST['parent']) ? SP()->filters->str($_POST['parent']) : $mobileTheme['parent'];

    		if (empty($theme) || empty($style)) return SP()->primitives->admin_text('No data changed');
    		if (empty($color)) $color = SP()->filters->str($_POST['default-color']);

    		$mobile['active'] = true;
    		$mobile['theme'] = $theme;
    		$mobile['style'] = $style;
    		$mobile['color'] = $color;
    		$mobile['parent'] = $parent;
    		$mobile['usetemplate'] = $mobileTheme['usetemplate'];
    		$mobile['pagetemplate'] = $mobileTheme['pagetemplate'];
    		$mobile['notitle'] = $mobileTheme['notitle'];
    	} else {
    		$mobile['active'] = $active;
    		$mobile['theme'] = $curTheme['theme'];
    		$mobile['style'] = $curTheme['style'];
    		$mobile['color'] = $curTheme['color'];
    		$mobile['parent'] = $curTheme['parent'];
    		$mobile['usetemplate'] = false;
    		$mobile['pagetemplate'] = SP()->DB->table(SPWPPOSTMETA, "meta_key='_wp_page_template' AND post_id=".SP()->options->get('sfpage'), 'meta_value');
    		$mobile['notitle'] = true;
    	}
    }

	$icons = '';
	if (!empty($mobile['color'])) {
        if (!empty($mobile['parent']) && !file_exists(SPTHEMEBASEDIR.$mobile['theme'].'/styles/overlays/'.$mobile['color'].'.php')) {
            $f = SPTHEMEBASEDIR.$mobile['parent'].'/styles/overlays/'.$mobile['color'].'.php';
        } else {
            $f = SPTHEMEBASEDIR.$mobile['theme'].'/styles/overlays/'.$mobile['color'].'.php';
        }
		$icons = SP()->filters->str(SP()->theme->get_overlay_icons($f));
	}
	$mobile['icons'] = $icons;

   	SP()->options->update('sp_mobile_theme', $mobile);

	# clean out the combined css file
	SP()->plugin->clear_css_cache('mobile');
	SP()->plugin->clear_css_cache('tablet');

	# theme activation action
	do_action('sph_activate_mobile_theme', $mobile);
	do_action('sph_activate_mobile_theme_'.$mobile['theme'], $mobile);

	return SP()->primitives->admin_text('Phone theme updated');
}

function spa_save_theme_tablet_data() {
	check_admin_referer('forum-adminform_themes', 'forum-adminform_themes');

	$tabletTheme = SP()->options->get('sp_tablet_theme');
	$curTheme = SP()->options->get('sp_current_theme');

	$tablet = array();
	$active = isset($_POST['active']);

    if (isset($_POST['saveit'])) {
		$pagetemplate = (isset($_POST['pagetemplate'])) ? SP()->filters->str($_POST['pagetemplate']) : $tabletTheme['pagetemplate'];

		if (isset($_POST['pagetemplate'])) {
			$usetemplate = isset($_POST['usetemplate']);
			$notitle = isset($_POST['notitle']);
		} else {
			$usetemplate = $tabletTheme['usetemplate'];
			$notitle = $tabletTheme['notitle'];
		}

		$tablet['active'] = $active;
		$tablet['theme'] = $tabletTheme['theme'];
		$tablet['style'] = $tabletTheme['style'];
		$tablet['color'] = $tabletTheme['color'];
		$tablet['parent'] = $tabletTheme['parent'];
		$tablet['usetemplate'] = $usetemplate;
		$tablet['pagetemplate'] = $pagetemplate;
		$tablet['notitle'] = $notitle;
    } else {
    	if ($active && $tabletTheme['active']) {
    		$theme = (isset($_POST['theme'])) ? SP()->filters->str($_POST['theme']) : $tabletTheme['theme'];
    		$style = (isset($_POST['style'])) ? SP()->filters->str($_POST['style']) : $tabletTheme['style'];
    		$color = (isset($_POST['color-'.$theme])) ? SP()->filters->str($_POST['color-'.$theme]) : '';
    		$parent = isset($_POST['parent']) ? SP()->filters->str($_POST['parent']) : $tabletTheme['parent'];

    		if (empty($theme) || empty($style)) return SP()->primitives->admin_text('No data changed');
    		if (empty($color)) $color = SP()->filters->str($_POST['default-color']);

    		$tablet['active'] = true;
    		$tablet['theme'] = $theme;
    		$tablet['style'] = $style;
    		$tablet['color'] = $color;
    		$tablet['parent'] = $parent;
    		$tablet['usetemplate'] = $tabletTheme['usetemplate'];
    		$tablet['pagetemplate'] = $tabletTheme['pagetemplate'];
    		$tablet['notitle'] = $tabletTheme['notitle'];
    	} else {
    		$tablet['active'] = $active;
    		$tablet['theme'] = $curTheme['theme'];
    		$tablet['style'] = $curTheme['style'];
    		$tablet['color'] = $curTheme['color'];
    		$tablet['parent'] = $curTheme['parent'];
    		$tablet['usetemplate'] = false;
    		$tablet['pagetemplate'] = SP()->DB->table(SPWPPOSTMETA, "meta_key='_wp_page_template' AND post_id=".SP()->options->get('sfpage'), 'meta_value');
    		$tablet['notitle'] = true;
    	}
    }

	$icons = '';
	if (!empty($tablet['color'])) {
        if (!empty($tablet['parent']) && !file_exists(SPTHEMEBASEDIR.$tablet['theme'].'/styles/overlays/'.$tablet['color'].'.php')) {
            $f = SPTHEMEBASEDIR.$tablet['parent'].'/styles/overlays/'.$tablet['color'].'.php';
        } else {
            $f = SPTHEMEBASEDIR.$tablet['theme'].'/styles/overlays/'.$tablet['color'].'.php';
        }
		$icons = SP()->filters->str(SP()->theme->get_overlay_icons($f));
	}
	$tablet['icons'] = $icons;

	SP()->options->update('sp_tablet_theme', $tablet);

	# clean out the combined css file
	SP()->plugin->clear_css_cache('mobile');
	SP()->plugin->clear_css_cache('tablet');

	# theme activation action
	do_action('sph_activate_tablet_theme', $tablet);
	do_action('sph_activate_tablet_theme_'.$tablet['theme'], $tablet);

	return SP()->primitives->admin_text('Tablet theme updated');
}

function spa_save_editor_data() {
	
	# This function should only be called if a wp-config.php constant is defined.
	# if it's not defined, bail immediately with an error.
	if ( ( ! defined('SP_ALLOW_THEME_EDITOR') ) || (defined('SP_ALLOW_THEME_EDITOR') && ! SP_ALLOW_THEME_EDITOR)) {
		$msg = SP()->primitives->admin_text('Security warning - you do not have permission to edit themes.');
		return $msg;
	}
	
	check_admin_referer('forum-adminform_theme-editor', 'forum-adminform_theme-editor');

	$file = SP()->filters->filename($_POST['file']);
	$newcontent = stripslashes($_POST['spnewcontent']);
	if (is_writeable($file)) {
		$f = fopen($file, 'w+');
		if ($f !== false) {
			fwrite($f, $newcontent);
			fclose($f);
			$msg = SP()->primitives->admin_text('Theme file updated!');
		} else {
			$msg = SP()->primitives->admin_text('Unable to save theme file');
		}
	} else {
		$msg = SP()->primitives->admin_text('Theme file is not writable!');
	}

	return $msg;
}

function spa_save_css_data() {
	$css = '';
	$curTheme = SP()->options->get('sp_current_theme');
	$css = esc_textarea($_POST['spnewcontent']);
	$css = SP()->saveFilters->nohtml($css);
	if ($_POST['metaId'] == 0)	{
		SP()->meta->add('css', $curTheme['theme'], $css);
	} else {
		SP()->meta->update('css', $curTheme['theme'], $css, (int) $_POST['metaId']);
	}

	$msg = SP()->primitives->admin_text('Custom theme CSS updated');
	return $msg;
}
