<?php
/*
Simple:Press
Remove a user notice in demand
$LastChangedDate: 2016-06-25 08:14:16 -0500 (Sat, 25 Jun 2016) $
$Rev: 14331 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

sp_forum_ajax_support();

if (!sp_nonce('spUserNotice')) die();

if (isset($_GET['notice'])) {
	$id = (int) $_GET['notice'];
	if ($id) spdb_query('DELETE FROM '.SFNOTICES." WHERE notice_id=$id");
}

die();
?>