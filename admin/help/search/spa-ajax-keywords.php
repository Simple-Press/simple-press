<?php
/*
Simple:Press
Help and Troubleshooting
$LastChangedDate: 2015-08-04 11:06:48 +0100 (Tue, 04 Aug 2015) $
$Rev: 13244 $
*/

if (isset($_GET['targetaction'])) $action = SP()->filters->str($_GET['targetaction']);

spa_admin_ajax_support();

if (!sp_nonce('adminkeywords')) die();

if($action == 'gettasks') sp_search_admin_tasks();

function sp_search_admin_tasks() {
	if(isset($_GET['keyword']) && !empty($_GET['keyword'])) {
		$keyword = SP()->filters->str($_GET['keyword']);
		$key = SP()->filters->integer($_GET['id']);
		$sql = 'SELECT * FROM '.SPADMINTASKS.' WHERE keyword_id='.$key;
		$tasks = SP()->DB->select($sql);

		if($tasks) {
			# get the base url
			$base = SPHOMEURL.'wp-admin/admin.php?page='.SP_FOLDER_NAME.'/admin';

			echo '<img class="spLeft" src="'.SPCOMMONIMAGES.'task.png" alt="" title="" />';
			echo '<div class="codex-head">'.$keyword.'</div>';
			echo '<div class="clearboth"></div>';
			SP()->primitives->admin_etext('The links below will load the admin panel where the selected item is located');
			echo '<p></p>';
			echo '<div class="clearboth"></div>';

			foreach($tasks as $task) {
				echo '<div class="task-link"></div>';
				echo '<div class="sf-help-search-ajax-keywords-div" ><p class="sf-help-search-ajax-keywords-p"><a href="'.$base.'/'.$task->url.'" >';
				echo $task->task;
				echo '</a></p></div>';
				echo '<div class="clearboth"></div>';
			}
		}
	}
}

die();
