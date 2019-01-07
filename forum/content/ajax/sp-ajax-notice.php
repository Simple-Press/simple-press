<?php
/*
Simple:Press
Remove a user notice in demand
$LastChangedDate: 2017-02-11 15:35:37 -0600 (Sat, 11 Feb 2017) $
$Rev: 15187 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

sp_forum_ajax_support();

if (!sp_nonce('spUserNotice')) die();

if (isset($_GET['notice'])) {
	$id = (int) $_GET['notice'];
	if ($id) SP()->DB->execute('DELETE FROM '.SPNOTICES." WHERE notice_id=$id");
}

die();
