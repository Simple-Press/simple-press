<?php
/*
Simple:Press
Admin General Ajax file
$LastChangedDate: 2018-11-02 13:02:17 -0500 (Fri, 02 Nov 2018) $
$Rev: 15795 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

spa_admin_ajax_support();

if (isset($_GET['targetaction']) && sanitize_text_field($_GET['targetaction']) == 'news') {
	if (!sp_nonce('remove-news')) die();
	$news = SP()->meta->get('news', 'news');
	if (!empty($news)) {
		$news[0]['meta_value']['show'] = 0;
		SP()->meta->update('news', 'news', $news[0]['meta_value'], $news[0]['meta_id']);
	}
}

die();
