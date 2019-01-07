<?php
/*
Simple:Press
Admin General Ajax file
$LastChangedDate: 2017-02-11 15:35:37 -0600 (Sat, 11 Feb 2017) $
$Rev: 15187 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

spa_admin_ajax_support();

if (isset($_GET['targetaction']) && $_GET['targetaction'] == 'news') {
	if (!sp_nonce('remove-news')) die();
	$news = SP()->meta->get('news', 'news');
	if (!empty($news)) {
		$news[0]['meta_value']['show'] = 0;
		SP()->meta->update('news', 'news', $news[0]['meta_value'], $news[0]['meta_id']);
	}
}

die();
