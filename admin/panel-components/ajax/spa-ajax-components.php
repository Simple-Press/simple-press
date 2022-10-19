<?php
/*
Simple:Press
Component Specials
$LastChangedDate: 2018-10-17 15:14:27 -0500 (Wed, 17 Oct 2018) $
$Rev: 15755 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

spa_admin_ajax_support();

if (!sp_nonce('components')) die();

# Check Whether User Can Manage Components
if (!SP()->auths->current_user_can('SPF Manage Components')) die();

$action = SP()->filters->str($_GET['targetaction']);

if ($action == 'del_rank') {
	$key = SP()->filters->integer($_GET['key']);

	# remove the forum rank
	SP()->meta->delete($key);
}

if ($action == 'del_specialrank') {
	$key = SP()->filters->integer($_GET['key']);
	$specialRank = SP()->meta->get('special_rank', false, $key);

    # remove members rank first
	SP()->DB->execute('DELETE FROM '.SPSPECIALRANKS.' WHERE special_rank="'.$specialRank[0]['meta_key'].'"');

	# remove the forum rank
	SP()->meta->delete($key);
}

if ($action == 'show') {
    $key = SP()->filters->integer($_GET['key']);
    $specialRank = SP()->meta->get('special_rank', false, $key);

	$users = SP()->DB->select('SELECT display_name
						  FROM '.SPSPECIALRANKS.'
						  JOIN '.SPMEMBERS.' ON '.SPSPECIALRANKS.'.user_id = '.SPMEMBERS.'.user_id
						  WHERE special_rank = "'.$specialRank[0]['meta_key'].'"
						  ORDER BY display_name', 'col');

    echo '<fieldset class="sfsubfieldset">';
    echo '<legend>'.SP()->primitives->admin_text('Special Rank Members').'</legend>';
    if ($users) {
    	echo '<ul class="memberlist">';
    	for ($x = 0; $x < count($users); $x++) {
    		echo '<li>'.SP()->displayFilters->name($users[$x]).'</li>';
    	}
    	echo '</ul>';
    } else {
    	SP()->primitives->admin_etext('No users with this special rank');
    }

    echo '</fieldset>';
}

if ($action == 'delsmiley') {
	$file = SP()->filters->filename($_GET['file']);
	$path = SP_STORE_DIR.'/'.SP()->plugin->storage['smileys'].'/'.$file;
	@unlink($path);

	# load smiles from sfmeta
	$meta = SP()->meta->get('smileys', 'smileys');

	# now cycle through to remove this entry and resave
	if (!empty($meta[0]['meta_value'])) {
		$newsmileys = array();
		foreach ($meta[0]['meta_value'] as $name => $info) {
			if ($info[0] != $file) {
				$newsmileys[$name][0] = SP()->saveFilters->title($info[0]);
				$newsmileys[$name][1] = SP()->saveFilters->name($info[1]);
				$newsmileys[$name][2] = SP()->saveFilters->name($info[2]);
				$newsmileys[$name][3] = $info[3];
				$newsmileys[$name][4] = $info[4];
			}
		}
		SP()->meta->update('smileys', 'smileys', $newsmileys, $meta[0]['meta_id']);
	}

	echo '1';
}

if ($action == 'delbadge') {
	$file = SP()->filters->filename($_GET['file']);
	$path = SP_STORE_DIR.'/'.SP()->plugin->storage['ranks'].'/'.$file;
	@unlink($path);
	echo '1';
}

die();
