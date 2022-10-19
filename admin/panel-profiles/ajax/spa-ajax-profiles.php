<?php
/*
Simple:Press
profiles Specials
$LastChangedDate: 2018-10-17 15:14:27 -0500 (Wed, 17 Oct 2018) $
$Rev: 15755 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

spa_admin_ajax_support();

if (!sp_nonce('profiles')) die();

# Check Whether User Can Manage Profiles
if (!SP()->auths->current_user_can('SPF Manage Profiles')) die();

$action = SP()->filters->str($_GET['targetaction']);

if ($action == 'delavatar') {
	$file = SP()->filters->filename($_GET['file']);
	$path = SP_STORE_DIR.'/'.SP()->plugin->storage['avatar-pool'].'/'.$file;
	@unlink($path);
	echo '1';
}

if ($action == 'deldefault') {
	$file = SP()->filters->filename($_GET['file']);
	$path = SP_STORE_DIR.'/'.SP()->plugin->storage['avatars'].'/defaults/'.$file;
	@unlink($path);
	echo '1';
}

if ($action == 'delete-tab') {
	$slug = SP()->filters->str($_GET['slug']);
	SP()->profile->delete_tab_by_slug($slug);
}

die();
