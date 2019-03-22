<?php
/**
 * Global Core Constants
 * This file loads at core level - all page loads for admin and front
 *
 * $LastChangedDate: 2019-01-30 16:40:00 -0600 (Wed, 30 Jan 2019) $
 * $Rev: 15817 $
 */

# Charset
if (!defined('SPCHARSET')) define('SPCHARSET', get_bloginfo('charset'));

# Storage Locations
if (is_multisite() && !get_site_option('ms_files_rewriting')) {
	$sp_install_version = SP()->DB->select('SELECT version FROM '.SPLOG.' LIMIT 1', 'var');
	if (empty($sp_install_version) || $sp_install_version < 5.6) {
		if (!defined('SP_STORE_DIR')) define('SP_STORE_DIR', WP_CONTENT_DIR);
		if (!defined('SP_STORE_URL')) define('SP_STORE_URL', content_url());
	} else {
		$uploads = wp_get_upload_dir();
		if (!defined('SP_STORE_DIR')) define('SP_STORE_DIR', $uploads['basedir']);
		if (!defined('SP_STORE_URL')) define('SP_STORE_URL', $uploads['baseurl']);
	}
} else {
	if (!defined('SP_STORE_DIR')) define('SP_STORE_DIR', WP_CONTENT_DIR);
	if (!defined('SP_STORE_URL')) define('SP_STORE_URL', content_url());
}
if (!defined('SP_STORE_RELATIVE_BASE')) define('SP_STORE_RELATIVE_BASE', str_replace(ABSPATH, '', SP_STORE_DIR).'/');

# Location of uploaded Avatars, Smileys and Ranks
if (!defined('SPAVATARURL')) define('SPAVATARURL', SP_STORE_URL.'/'.SP()->plugin->storage['avatars'].'/');
if (!defined('SPAVATARDIR')) define('SPAVATARDIR', SP_STORE_DIR.'/'.SP()->plugin->storage['avatars'].'/');
if (!defined('SPAVATARDIR')) define('SPAVATARDIR', SP_STORE_URL.'/'.SP()->plugin->storage['avatar-pool'].'/');
if (!defined('SPAVATARPOOLDIR')) define('SPAVATARPOOLDIR', SP_STORE_DIR.'/'.SP()->plugin->storage['avatar-pool'].'/');
if (!defined('SPAVATARPOOLURL')) define('SPAVATARPOOLURL', SP_STORE_URL.'/'.SP()->plugin->storage['avatar-pool'].'/');
if (!defined('SPSMILEYS')) define('SPSMILEYS', SP_STORE_URL.'/'.SP()->plugin->storage['smileys'].'/');
if (!defined('SPRANKS')) define('SPRANKS', SP_STORE_URL.'/'.SP()->plugin->storage['ranks'].'/');

# Location of plugins
if (!defined('SPPLUGINURL')) define('SPPLUGINURL', SP_STORE_URL.'/'.SP()->plugin->storage['plugins'].'/');
if (!defined('SPPLUGINDIR')) define('SPPLUGINDIR', SP_STORE_DIR.'/'.SP()->plugin->storage['plugins'].'/');

# Location of custom icons and featured images
if (!defined('SPCUSTOMDIR')) define('SPCUSTOMDIR', SP_STORE_DIR.'/'.SP()->plugin->storage['custom-icons'].'/');
if (!defined('SPCUSTOMURL')) define('SPCUSTOMURL', SP_STORE_URL.'/'.SP()->plugin->storage['custom-icons'].'/');
if (!defined('SPOGIMAGEDIR')) define('SPOGIMAGEDIR', SP_STORE_DIR.'/'.SP()->plugin->storage['forum-images'].'/');
if (!defined('SPOGIMAGEURL')) define('SPOGIMAGEURL', SP_STORE_URL.'/'.SP()->plugin->storage['forum-images'].'/');

# Location of scripts
if (!defined('SPJSCRIPT')) define('SPJSCRIPT', SP_PLUGIN_URL.'/forum/resources/jscript/');
if (!defined('SPIJSCRIPT')) define('SPIJSCRIPT', SP_PLUGIN_URL.'/install/resources/jscript/');
if (!defined('SPCJSCRIPT')) define('SPCJSCRIPT', SP_PLUGIN_URL.'/resources/jscript/');
if (!defined('SPAJSCRIPT')) define('SPAJSCRIPT', SP_PLUGIN_URL.'/admin/resources/jscript/');
if (!defined('SPVJSCRIPT')) define('SPVJSCRIPT', SP_PLUGIN_URL.'/forum/content/resources/jscript/');

# these are constants no longer used in 5.0+ except for upgrade support and uninstall
# for users who upgraded to 5.0 put didnt use the equivalent plugins
if (!defined('SPPOSTRATINGS')) define('SPPOSTRATINGS', SP_PREFIX.'sfpostratings');
if (!defined('SPMESSAGES')) define('SPMESSAGES', SP_PREFIX.'sfmessages');

# WP tables needed
global $wpdb;
if (!defined('SPWPPOSTS')) define('SPWPPOSTS', $wpdb->posts);
if (!defined('SPWPPOSTMETA')) define('SPWPPOSTMETA', $wpdb->postmeta);
if (!defined('SPWPCOMMENTS')) define('SPWPCOMMENTS', $wpdb->comments);

if (defined('CUSTOM_USER_TABLE')) {
	if (!defined('SPUSERS')) define('SPUSERS', CUSTOM_USER_TABLE);
} else {
	if (!defined('SPUSERS')) define('SPUSERS', $wpdb->users);
}
if (defined('CUSTOM_USER_META_TABLE')) {
	if (!defined('SPUSERMETA')) define('SPUSERMETA', CUSTOM_USER_META_TABLE);
} else {
	if (!defined('SPUSERMETA')) define('SPUSERMETA', $wpdb->usermeta);
}

if (!defined('SPLOADINSTALL')) define('SPLOADINSTALL', SPBOOT.'sp-load-install.php');

# date stuff
if (!defined('SPDATES')) define('SPDATES', SP()->options->get('sfdates'));
if (!defined('SPTIMES')) define('SPTIMES', SP()->options->get('sftimes'));

# WP Ajax url
$urlfrag = (defined('SP_DEVFLAG') && SP_DEVFLAG) ? '?XDEBUG_SESSION_START=netbeans-xdebug&action=' : '?action=';
if (!defined('SPAJAXURL')) define('SPAJAXURL', admin_url("admin-ajax.php$urlfrag"));

# Location of themes
if (!defined('SPTHEMEBASEURL')) define('SPTHEMEBASEURL', SP_STORE_URL.'/'.SP()->plugin->storage['themes'].'/');
if (!defined('SPTHEMEBASEDIR')) define('SPTHEMEBASEDIR', SP_STORE_DIR.'/'.SP()->plugin->storage['themes'].'/');

$curTheme = SP()->theme->get_current();

if (!defined('SPTHEMEICONSURL')) {
	$i = (!isset($curTheme['icons']) || empty($curTheme['icons'])) ? '/images/' : '/images/'.$curTheme['icons'].'/';
	$p = (SP()->core->device == 'mobile' && file_exists(SPTHEMEBASEDIR.$curTheme['theme'].$i.'mobile/')) ? $curTheme['theme'].$i.'mobile/' : $curTheme['theme'].$i;
	define('SPTHEMEICONSURL', SPTHEMEBASEURL.$p);
	define('SPTHEMEICONSDIR', SPTHEMEBASEDIR.$p);
}

# Dir of templates, Dir of images and url of CSS file
if (!defined('SPTEMPLATES')) define('SPTEMPLATES', SPTHEMEBASEDIR.$curTheme['theme'].'/templates/');
if (!defined('SPTHEMEURL')) define('SPTHEMEURL', SPTHEMEBASEURL.$curTheme['theme'].'/styles/');
if (!defined('SPTHEMEDIR')) define('SPTHEMEDIR', SPTHEMEBASEDIR.$curTheme['theme'].'/styles/');
if (!defined('SPTHEMECSS')) define('SPTHEMECSS', SPTHEMEBASEURL.$curTheme['theme'].'/styles/'.$curTheme['style']);
if (!defined('SPTHEMECSSEXTRA')) define('SPTHEMECSSEXTRA', SPTHEMEBASEURL.$curTheme['theme'].'/styles/');

/**
 * Editor Constants
 *    RICHTEXT    - 1
 *    HTML        - 2
 *    BBCODE        - 3
 *    PLAINTEXT    - 4
 */
define('PLAINTEXT', 4);
define('PLAINTEXTNAME', 'Plain Text');

if (!defined('SPADMINURL')) define('SPADMINURL', SP_PLUGIN_URL.'/admin/');
if (!defined('SPADMINIMAGES')) define('SPADMINIMAGES', SP_PLUGIN_URL.'/admin/resources/images/');
if (!defined('SPADMINCSS')) define('SPADMINCSS', SP_PLUGIN_URL.'/admin/resources/css/');
if (!defined('SPCOMMONCSS')) define('SPCOMMONCSS', SP_PLUGIN_URL.'/resources/css/');
if (!defined('SPCOMMONIMAGES')) define('SPCOMMONIMAGES', SP_PLUGIN_URL.'/resources/images/');

if (!defined('SPADMINUPGRADE')) define('SPADMINUPGRADE', admin_url('admin.php?page='.SP_FOLDER_NAME.'/sp-startup/sp-load-install.php'));

# Combined CSS/JS cache file
if (!defined('SP_COMBINED_CACHE_URL')) define('SP_COMBINED_CACHE_URL', SP_STORE_URL.'/'.SP()->plugin->storage['cache'].'/');
if (!defined('SP_COMBINED_CACHE_DIR')) define('SP_COMBINED_CACHE_DIR', SP_STORE_DIR.'/'.SP()->plugin->storage['cache'].'/');
if (!defined('SP_COMBINED_CSS_BASE_NAME')) define('SP_COMBINED_CSS_BASE_NAME', 'sp-plugin-styles-');
if (!defined('SP_COMBINED_SCRIPTS_BASE_NAME')) define('SP_COMBINED_SCRIPTS_BASE_NAME', 'sp-plugin-scripts-');

# Base admin panels
if (!defined('SPADMINFORUM')) define('SPADMINFORUM', admin_url('admin.php?page='.SP_FOLDER_NAME.'/admin/panel-forums/spa-forums.php'));
if (!defined('SPADMINOPTION')) define('SPADMINOPTION', admin_url('admin.php?page='.SP_FOLDER_NAME.'/admin/panel-options/spa-options.php'));
if (!defined('SPADMINCOMPONENTS')) define('SPADMINCOMPONENTS', admin_url('admin.php?page='.SP_FOLDER_NAME.'/admin/panel-components/spa-components.php'));
if (!defined('SPADMINUSERGROUP')) define('SPADMINUSERGROUP', admin_url('admin.php?page='.SP_FOLDER_NAME.'/admin/panel-usergroups/spa-usergroups.php'));
if (!defined('SPADMINPERMISSION')) define('SPADMINPERMISSION', admin_url('admin.php?page='.SP_FOLDER_NAME.'/admin/panel-permissions/spa-permissions.php'));
if (!defined('SPADMINUSER')) define('SPADMINUSER', admin_url('admin.php?page='.SP_FOLDER_NAME.'/admin/panel-users/spa-users.php'));
if (!defined('SPADMINPROFILE')) define('SPADMINPROFILE', admin_url('admin.php?page='.SP_FOLDER_NAME.'/admin/panel-profiles/spa-profiles.php'));
if (!defined('SPADMINADMIN')) define('SPADMINADMIN', admin_url('admin.php?page='.SP_FOLDER_NAME.'/admin/panel-admins/spa-admins.php'));
if (!defined('SPADMINTAGS')) define('SPADMINTAGS', admin_url('admin.php?page='.SP_FOLDER_NAME.'/admin/panel-tags/spa-tags.php'));
if (!defined('SPADMINTOOLBOX')) define('SPADMINTOOLBOX', admin_url('admin.php?page='.SP_FOLDER_NAME.'/admin/panel-toolbox/spa-toolbox.php'));
if (!defined('SPADMINPLUGINS')) define('SPADMINPLUGINS', admin_url('admin.php?page='.SP_FOLDER_NAME.'/admin/panel-plugins/spa-plugins.php'));
if (!defined('SPADMINTHEMES')) define('SPADMINTHEMES', admin_url('admin.php?page='.SP_FOLDER_NAME.'/admin/panel-themes/spa-themes.php'));
if (!defined('SPADMININTEGRATION')) define('SPADMININTEGRATION', admin_url('admin.php?page='.SP_FOLDER_NAME.'/admin/panel-integration/spa-integration.php'));

# get value of store url for get license of plugins from

$sp_addon_store_url = SP()->options->get( 'sp_addon_store_url');

if($sp_addon_store_url == ''){
	
	$sp_addon_store_url = 'https://simple-press.com/';
}

if (!defined('SP_Addon_STORE_URL')) define('SP_Addon_STORE_URL', $sp_addon_store_url);