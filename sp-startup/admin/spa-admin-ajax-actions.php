<?php
/*
Simple:Press
Ajax Action handler - Admin Specific
$LastChangedDate: 2014-12-22 00:33:26 +0000 (Mon, 22 Dec 2014) $
$Rev: 12210 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# ==========================================================================================
#
# ADMIN - This file loads at admin level - all admin page loads
# handles all admin AJAX calls via WP Ajax
#
# ==========================================================================================

# Core Admin Form Loaders ------------------------------------------------------------------

function spa_ajax_forums_loader() {
	include SF_PLUGIN_DIR.'/admin/panel-forums/ajax/spa-ajax-forums-loader.php';
}
add_action('wp_ajax_forums-loader', 'spa_ajax_forums_loader');
add_action('wp_ajax_nopriv_forums-loader', 'spa_ajax_forums_loader');

function spa_ajax_options_loader() {
	include SF_PLUGIN_DIR.'/admin/panel-options/ajax/spa-ajax-options-loader.php';
}
add_action('wp_ajax_options-loader', 'spa_ajax_options_loader');
add_action('wp_ajax_nopriv_options-loader', 'spa_ajax_options_loader');

function spa_ajax_components_loader() {
	include SF_PLUGIN_DIR.'/admin/panel-components/ajax/spa-ajax-components-loader.php';
}
add_action('wp_ajax_components-loader', 'spa_ajax_components_loader');
add_action('wp_ajax_nopriv_components-loader', 'spa_ajax_components_loader');

function spa_ajax_usergroups_loader() {
	include SF_PLUGIN_DIR.'/admin/panel-usergroups/ajax/spa-ajax-usergroups-loader.php';
}
add_action('wp_ajax_usergroups-loader', 'spa_ajax_usergroups_loader');
add_action('wp_ajax_nopriv_usergroups-loader', 'spa_ajax_usergroups_loader');

function spa_ajax_permissions_loader() {
	include SF_PLUGIN_DIR.'/admin/panel-permissions/ajax/spa-ajax-permissions-loader.php';
}
add_action('wp_ajax_permissions-loader', 'spa_ajax_permissions_loader');
add_action('wp_ajax_nopriv_permissions-loader', 'spa_ajax_permissions_loader');

function spa_ajax_integration_loader() {
	include SF_PLUGIN_DIR.'/admin/panel-integration/ajax/spa-ajax-integration-loader.php';
}
add_action('wp_ajax_integration-loader', 'spa_ajax_integration_loader');
add_action('wp_ajax_nopriv_integration-loader', 'spa_ajax_integration_loader');

function spa_ajax_profiles_loader() {
	include SF_PLUGIN_DIR.'/admin/panel-profiles/ajax/spa-ajax-profiles-loader.php';
}
add_action('wp_ajax_profiles-loader', 'spa_ajax_profiles_loader');
add_action('wp_ajax_nopriv_profiles-loader', 'spa_ajax_profiles_loader');

function spa_ajax_admins_loader() {
	include SF_PLUGIN_DIR.'/admin/panel-admins/ajax/spa-ajax-admins-loader.php';
}
add_action('wp_ajax_admins-loader', 'spa_ajax_admins_loader');
add_action('wp_ajax_nopriv_admins-loader', 'spa_ajax_admins_loader');

function spa_ajax_users_loader() {
	include SF_PLUGIN_DIR.'/admin/panel-users/ajax/spa-ajax-users-loader.php';
}
add_action('wp_ajax_users-loader', 'spa_ajax_users_loader');
add_action('wp_ajax_nopriv_users-loader', 'spa_ajax_users_loader');

function spa_ajax_plugins_loader() {
	include SF_PLUGIN_DIR.'/admin/panel-plugins/ajax/spa-ajax-plugins-loader.php';
}
add_action('wp_ajax_plugins-loader', 'spa_ajax_plugins_loader');
add_action('wp_ajax_nopriv_plugins-loader', 'spa_ajax_plugins_loader');

function spa_ajax_themes_loader() {
	include SF_PLUGIN_DIR.'/admin/panel-themes/ajax/spa-ajax-themes-loader.php';
}
add_action('wp_ajax_themes-loader', 'spa_ajax_themes_loader');
add_action('wp_ajax_nopriv_themes-loader', 'spa_ajax_themes_loader');

function spa_ajax_toolbox_loader() {
	include SF_PLUGIN_DIR.'/admin/panel-toolbox/ajax/spa-ajax-toolbox-loader.php';
}
add_action('wp_ajax_toolbox-loader', 'spa_ajax_toolbox_loader');
add_action('wp_ajax_nopriv_toolbox-loader', 'spa_ajax_toolbox_loader');

# Core Admin Form Processing ---------------------------------------------------------------

function spa_ajax_forums() {
	include SF_PLUGIN_DIR.'/admin/panel-forums/ajax/spa-ajax-forums.php';
}
add_action('wp_ajax_forums', 'spa_ajax_forums');
add_action('wp_ajax_nopriv_forums', 'spa_ajax_forums');

function spa_ajax_components() {
	include SF_PLUGIN_DIR.'/admin/panel-components/ajax/spa-ajax-components.php';
}
add_action('wp_ajax_components', 'spa_ajax_components');
add_action('wp_ajax_nopriv_components', 'spa_ajax_components');

function spa_ajax_usergroups() {
	include SF_PLUGIN_DIR.'/admin/panel-usergroups/ajax/spa-ajax-usergroups.php';
}
add_action('wp_ajax_usergroups', 'spa_ajax_usergroups');
add_action('wp_ajax_nopriv_usergroups', 'spa_ajax_usergroups');

function spa_ajax_usermapping() {
	include SF_PLUGIN_DIR.'/admin/panel-usergroups/ajax/spa-ajax-map-users.php';
}
add_action('wp_ajax_usermapping', 'spa_ajax_usermapping');
add_action('wp_ajax_nopriv_usermapping', 'spa_ajax_usermapping');

function spa_ajax_memberships() {
	include SF_PLUGIN_DIR.'/admin/panel-usergroups/ajax/spa-ajax-memberships.php';
}
add_action('wp_ajax_memberships', 'spa_ajax_memberships');
add_action('wp_ajax_nopriv_memberships', 'spa_ajax_memberships');

function spa_ajax_integration_perm() {
	include SF_PLUGIN_DIR.'/admin/panel-integration/ajax/spa-ajax-integration-perm.php';
}
add_action('wp_ajax_integration-perm', 'spa_ajax_integration_perm');
add_action('wp_ajax_nopriv_integration-perm', 'spa_ajax_integration_perm');

function spa_ajax_integration_langs() {
	include SF_PLUGIN_DIR.'/admin/panel-integration/ajax/spa-ajax-integration-langs.php';
}
add_action('wp_ajax_integration-langs', 'spa_ajax_integration_langs');
add_action('wp_ajax_nopriv_integration-langs', 'spa_ajax_integration_langs');

function spa_ajax_profiles() {
	include SF_PLUGIN_DIR.'/admin/panel-profiles/ajax/spa-ajax-profiles.php';
}
add_action('wp_ajax_profiles', 'spa_ajax_profiles');
add_action('wp_ajax_nopriv_profiles', 'spa_ajax_profiles');

# Admin Form Support -----------------------------------------------------------------------

function spa_ajax_multiselect() {
	include SF_PLUGIN_DIR.'/admin/library/ajax/spa-ajax-multiselect.php';
}
add_action('wp_ajax_multiselect', 'spa_ajax_multiselect');
add_action('wp_ajax_nopriv_multiselect', 'spa_ajax_multiselect');

function spa_ajax_uploader() {
	include SF_PLUGIN_DIR.'/admin/resources/jscript/ajaxupload/sf-uploader.php';
}
add_action('wp_ajax_uploader', 'spa_ajax_uploader');
add_action('wp_ajax_nopriv_uploader', 'spa_ajax_uploader');

function spa_ajax_help() {
	include SF_PLUGIN_DIR.'/admin/library/ajax/spa-ajax-help.php';
}
add_action('wp_ajax_help', 'spa_ajax_help');
add_action('wp_ajax_nopriv_help', 'spa_ajax_help');

function spa_ajax_troubleshooting() {
	include SF_PLUGIN_DIR.'/admin/help/troubleshooting/spa-ajax-troubleshooting.php';
}
add_action('wp_ajax_troubleshooting', 'spa_ajax_troubleshooting');
add_action('wp_ajax_nopriv_troubleshooting', 'spa_ajax_troubleshooting');

function spa_ajax_adminsearch() {
	include SF_PLUGIN_DIR.'/admin/help/search/spa-ajax-search.php';
}
add_action('wp_ajax_adminsearch', 'spa_ajax_adminsearch');
add_action('wp_ajax_nopriv_adminsearch', 'spa_ajax_adminsearch');

function spa_ajax_adminkeywords() {
	include SF_PLUGIN_DIR.'/admin/help/search/spa-ajax-keywords.php';
}
add_action('wp_ajax_adminkeywords', 'spa_ajax_adminkeywords');
add_action('wp_ajax_nopriv_adminkeywords', 'spa_ajax_adminkeywords');

function spa_ajax_removenews() {
	include SF_PLUGIN_DIR.'/admin/library/ajax/spa-ajax-general.php';
}
add_action('wp_ajax_remove-news', 'spa_ajax_removenews');
add_action('wp_ajax_nopriv_remove-news', 'spa_ajax_removenews');

function spa_ajax_installlog() {
	include include SF_PLUGIN_DIR.'/admin/panel-toolbox/ajax/spa-ajax-install-log.php';
}
add_action('wp_ajax_install-log', 'spa_ajax_installlog');
add_action('wp_ajax_nopriv_install-log', 'spa_ajax_installlog');

function spa_ajax_plugintip() {
	include SF_PLUGIN_DIR.'/admin/panel-plugins/ajax/spa-ajax-plugins-help.php';
}
add_action('wp_ajax_plugin-tip', 'spa_ajax_plugintip');
add_action('wp_ajax_nopriv_plugin-tip', 'spa_ajax_plugintip');

function spa_ajax_usergrouptip() {
	include SF_PLUGIN_DIR.'/admin/panel-usergroups/ajax/spa-ajax-usergroups-help.php';
}
add_action('wp_ajax_usergroup-tip', 'spa_ajax_usergrouptip');
add_action('wp_ajax_nopriv_usergroup-tip', 'spa_ajax_usergrouptip');

function spa_ajax_permissiontip() {
	include SF_PLUGIN_DIR.'/admin/panel-permissions/ajax/spa-ajax-permissions-help.php';
}
add_action('wp_ajax_permission-tip', 'spa_ajax_permissiontip');
add_action('wp_ajax_nopriv_permission-tip', 'spa_ajax_permissiontip');

# Install and Upgrade ----------------------------------------------------------------------

function spa_ajax_upgrade() {
	include SPBOOT.'install/sp-upgrade.php';
}
add_action('wp_ajax_upgrade', 'spa_ajax_upgrade');
add_action('wp_ajax_nopriv_upgrade', 'spa_ajax_upgrade');

function spa_ajax_install() {
	include SPBOOT.'install/sp-install.php';
}
add_action('wp_ajax_install', 'spa_ajax_install');
add_action('wp_ajax_nopriv_install', 'spa_ajax_install');

?>