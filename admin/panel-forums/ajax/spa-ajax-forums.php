<?php
/*
Simple:Press
Forum Specials
$LastChangedDate: 2018-10-17 15:14:27 -0500 (Wed, 17 Oct 2018) $
$Rev: 15755 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

spa_admin_ajax_support();

if (!sp_nonce('forums')) die();

include_once SP_PLUGIN_DIR.'/admin/panel-forums/support/spa-forums-prepare.php';

# Check Whether User Can Manage Components
if (!SP()->auths->current_user_can('SPF Manage Forums')) die();

if (isset($_GET['targetaction'])) $action = SP()->filters->str($_GET['targetaction']);
if (isset($_GET['type'])) $type = SP()->filters->str($_GET['type']);
if (isset($_GET['id'])) $id = SP()->filters->integer($_GET['id']);
if (isset($_GET['title'])) $title = SP()->filters->str($_GET['title']);
if (isset($_GET['slugaction'])) $slugaction = SP()->filters->str($_GET['slugaction']);

if ($action == 'slug') {
	$checkdupes = true;
	if ($slugaction == 'edit') $checkdupes = false;
	$newslug = sp_create_slug($title, $checkdupes, SPFORUMS, 'forum_slug');
	$newslug = sp_create_slug($newslug, $checkdupes, SPWPPOSTS, 'post_name'); # must also check WP posts table as WP can mistake forum slug for WP post
	echo $newslug;
}

if ($action == 'delicon') {
	$file = SP()->filters->filename($_GET['file']);
	$path = SP_STORE_DIR.'/'.SP()->plugin->storage['custom-icons'].'/'.$file;
	@unlink($path);
}

if ($action == 'delimage') {
	$file = SP()->filters->filename($_GET['file']);
	$path = SP_STORE_DIR.'/'.SP()->plugin->storage['forum-images'].'/'.$file;
	@unlink($path);
}

die();
