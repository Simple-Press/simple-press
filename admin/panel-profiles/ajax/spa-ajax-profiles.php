<?php
/*
Simple:Press
profiles Specials
$LastChangedDate: 2016-12-03 14:06:51 -0600 (Sat, 03 Dec 2016) $
$Rev: 14745 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

spa_admin_ajax_support();

if (!sp_nonce('profiles')) die();

# Check Whether User Can Manage Profiles
if (!sp_current_user_can('SPF Manage Profiles')) die();

global $spPaths;

$action = $_GET['targetaction'];

if ($action == 'delavatar') {
	$file = $_GET['file'];
	$path = SF_STORE_DIR.'/'.$spPaths['avatar-pool'].'/'.$file;
	@unlink($path);
	echo '1';
}

if ($action == 'deldefault') {
	$file = $_GET['file'];
	$path = SF_STORE_DIR.'/'.$spPaths['avatars'].'/defaults/'.$file;
	@unlink($path);
	echo '1';
}

if ($action == 'delete-tab') {
	$slug = sp_esc_str($_GET['slug']);
	sp_profile_delete_tab_by_slug($slug);
}

die();

?>