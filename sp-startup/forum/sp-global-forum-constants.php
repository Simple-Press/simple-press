<?php
/*
Simple:Press
DESC:
$LastChangedDate: 2015-09-12 16:02:06 -0500 (Sat, 12 Sep 2015) $
$Rev: 13445 $
*/

# ==========================================================================================
#
# 	FORUM PAGES = BOTH ADMIN AND SITE FORUM LOADS
#	This file loads for forum pages only on both admin and front end.
#
# ==========================================================================================
#

global $spPaths;

if (!defined('SF_STORE_RELATIVE_BASE')) define('SF_STORE_RELATIVE_BASE', str_replace(ABSPATH, '', SF_STORE_DIR).'/');

if (!defined('SFADMINURL'))     define('SFADMINURL',     SF_PLUGIN_URL.'/admin/');
if (!defined('SFADMINIMAGES'))  define('SFADMINIMAGES',  SF_PLUGIN_URL.'/admin/resources/images/');
if (!defined('SFADMINCSS'))     define('SFADMINCSS',	 SF_PLUGIN_URL.'/admin/resources/css/');
if (!defined('SFCOMMONCSS'))    define('SFCOMMONCSS',    SF_PLUGIN_URL.'/resources/css/');
if (!defined('SFCOMMONIMAGES')) define('SFCOMMONIMAGES', SF_PLUGIN_URL.'/resources/images/');

# Base admin panels
if (!defined('SFADMINFORUM'))      	define('SFADMINFORUM',	   		admin_url('admin.php?page=simple-press/admin/panel-forums/spa-forums.php'));
if (!defined('SFADMINOPTION'))     	define('SFADMINOPTION',	   		admin_url('admin.php?page=simple-press/admin/panel-options/spa-options.php'));
if (!defined('SFADMINCOMPONENTS'))  define('SFADMINCOMPONENTS', 	admin_url('admin.php?page=simple-press/admin/panel-components/spa-components.php'));
if (!defined('SFADMINUSERGROUP'))  	define('SFADMINUSERGROUP',  	admin_url('admin.php?page=simple-press/admin/panel-usergroups/spa-usergroups.php'));
if (!defined('SFADMINPERMISSION')) 	define('SFADMINPERMISSION', 	admin_url('admin.php?page=simple-press/admin/panel-permissions/spa-permissions.php'));
if (!defined('SFADMINUSER'))       	define('SFADMINUSER',	   		admin_url('admin.php?page=simple-press/admin/panel-users/spa-users.php'));
if (!defined('SFADMINPROFILE'))    	define('SFADMINPROFILE',	   	admin_url('admin.php?page=simple-press/admin/panel-profiles/spa-profiles.php'));
if (!defined('SFADMINADMIN'))      	define('SFADMINADMIN',	   		admin_url('admin.php?page=simple-press/admin/panel-admins/spa-admins.php'));
if (!defined('SFADMINTAGS'))       	define('SFADMINTAGS',	   		admin_url('admin.php?page=simple-press/admin/panel-tags/spa-tags.php'));
if (!defined('SFADMINTOOLBOX'))    	define('SFADMINTOOLBOX',	   	admin_url('admin.php?page=simple-press/admin/panel-toolbox/spa-toolbox.php'));
if (!defined('SFADMINPLUGINS'))    	define('SFADMINPLUGINS',	   	admin_url('admin.php?page=simple-press/admin/panel-plugins/spa-plugins.php'));
if (!defined('SFADMINTHEMES'))     	define('SFADMINTHEMES',	   		admin_url('admin.php?page=simple-press/admin/panel-themes/spa-themes.php'));
if (!defined('SFADMININTEGRATION'))	define('SFADMININTEGRATION',	admin_url('admin.php?page=simple-press/admin/panel-integration/spa-integration.php'));

if (!defined('SFADMINUPGRADE'))      define('SFADMINUPGRADE',		admin_url('admin.php?page=simple-press/sp-startup/sp-load-install.php'));

do_action('sph_global_forum_constants');

?>