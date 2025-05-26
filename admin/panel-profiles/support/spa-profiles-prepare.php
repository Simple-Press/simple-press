<?php
/*
Simple:Press
Admin Profiles Support Functions
$LastChangedDate: 2018-11-03 11:12:02 -0500 (Sat, 03 Nov 2018) $
$Rev: 15799 $
*/

if ( ! defined( 'ABSPATH' ) ) {
	die('Access denied - you cannot directly call this file');
}

function spa_get_options_data() {
	$sfprofile = SP()->options->get('sfprofile');
	$sfsigimagesize = SP()->options->get('sfsigimagesize');
	if($sfsigimagesize){
		$sfprofile['sfsigwidth'] = isset($sfsigimagesize['sfsigwidth']) ? $sfsigimagesize['sfsigwidth'] : null;
		$sfprofile['sfsigheight'] = isset($sfsigimagesize['sfsigheight']) ? $sfsigimagesize['sfsigheight'] : null;
	}
	
	return $sfprofile;
}

function spa_get_tabsmenus_data() {
	$tabs = SP()->profile->get_tabs_menus();
	return $tabs;
}


function spa_get_avatars_data() {
	$sfavatars = SP()->options->get('sfavatars');
	if(empty($sfavatars['sfavatarpriority'])) {
		$sfavatars['sfavatarpriority'] = [0, 2, 3, 1, 4, 5];
	}
	return $sfavatars;
}

function spa_paint_avatar_pool() {
	$out = '';

	# Open avatar pool folder and get cntents for matching
	$path = SP_STORE_DIR.'/'.SP()->plugin->storage['avatar-pool'].'/';
	$dlist = @opendir($path);
	if (!$dlist) {
		echo '<table><tr><td class="sflabel"><strong>'.esc_html(SP()->primitives->admin_text('The avatar pool folder does not exist')).'</strong></td></tr></table>';
		return;
	}

	# start the table display
	echo '<table class="widefat sf-table-small sf-table-mobile"><thead><tr>';
	echo '<th>'.esc_html(SP()->primitives->admin_text('Avatar')).'</th>';
	echo '<th>'.esc_html(SP()->primitives->admin_text('Filename')).'</th>';
	echo '<th>'.esc_html(SP()->primitives->admin_text('Remove')).'</th>';
	echo '</tr></thead>';
	echo '<tbody>';
	while (false !== ($file = readdir($dlist))) {
		if ($file != "." && $file != "..") {
			echo '<tr>';
			echo '<td class="spWFBorder"><div class="sf-avatar"><img class="sfavatarpool" src="'.esc_url(SPAVATARPOOLURL.$file).'" alt="" /></div></td>';
			echo '<td class="spWFBorder sflabel">';
			echo esc_html($file);
			echo '</td>';
			echo '<td class="spWFBorder"><div class="sf-item-controls">';
			$site = wp_nonce_url(SPAJAXURL."profiles&amp;targetaction=delavatar&amp;file=$file", 'profiles');
			echo '<span title="'.esc_html(SP()->primitives->admin_text('Delete Avatar')).'" class="sf-icon sf-delete spDeleteRowReload" data-url="'.esc_url($site).'" data-reload="sfreloadpool"></span>';
			echo '</div></td>';
			echo '</tr>';
		}
	}
	echo '</tbody></table>';
	closedir($dlist);
}

function spa_paint_avatar_defaults() {
	# Open avatar defaults folder and get cntents for matching
	$path = SP_STORE_DIR.'/'.SP()->plugin->storage['avatars'].'/defaults/';
	$dlist = @opendir($path);
	if (!$dlist) {
		echo '<table><tr><td class="sflabel"><strong>'.esc_html(SP()->primitives->admin_text('The avatar defaults folder does not exist')).'</strong></td></tr></table>';
		return;
	}

	$def = SP()->options->get('spDefAvatars');

	# start the table display
	echo '<div id="av-browser">';

	while (false !== ($file = readdir($dlist))) {
		if ($file != "." && $file != "..") {
			$found = false;
			$border = (in_array($file, $def) ? '2px solid red' : '2px solid lightgray');

			echo '<div class="av-file" style="text-align:left;border:'.esc_html($border).';margin:5px;padding:6px;float:left;">';
				echo '<div class="sf-form-row">';
				echo '<div class="sf-avatar"><img src="'.esc_url(SPAVATARURL.'defaults/'.$file).'" alt="" /></div>';
				$site = wp_nonce_url(SPAJAXURL."profiles&amp;targetaction=deldefault&amp;file=$file", 'profiles');
				echo '<span class="sf-icon sf-delete spDeleteRowReload sf-pull-right" data-url="'.esc_url($site).'" data-reload="sfreloadav"></span>';
				echo '</div>';

				$fileid = str_replace('.', 'z1z2z3', $file);
				$checked = ($def['admin']==$file) ? ' checked="checked" ' : '';
				if ($def['admin']==$file) $found=true;

				echo '<input type="radio" value="admin" class="spCheckAvatarDefaults" id="adm-'.esc_html($fileid).'" name="'.esc_html($fileid).'"'.esc_html($checked).'>';
				echo '<label for="adm-'.esc_html($fileid).'">'.esc_html(SP()->primitives->admin_text('Admin')).'</label><br>';

				$checked = ($def['mod']==$file) ? ' checked="checked" ' : '';
				if ($def['mod']==$file) $found=true;
				echo '<input type="radio" value="mod" class="spCheckAvatarDefaults" id="mod-'.esc_html($fileid).'" name="'.esc_html($fileid).'"'.esc_html($checked).'>';
				echo '<label for="mod-'.esc_html($fileid).'">'.esc_html(SP()->primitives->admin_text('Moderator')).'</label><br>';

				$checked = ($def['member']==$file) ? ' checked="checked" ' : '';
				if ($def['member']==$file) $found=true;
				echo '<input type="radio" value="member" class="spCheckAvatarDefaults" id="mem-'.esc_html($fileid).'" name="'.esc_html($fileid).'"'.esc_html($checked).'>';
				echo '<label for="mem-'.esc_html($fileid).'">'.esc_html(SP()->primitives->admin_text('Member')).'</label><br>';

				$checked = ($def['guest']==$file) ? ' checked="checked" ' : '';
				if ($def['guest']==$file) $found=true;
				echo '<input type="radio" value="guest" class="spCheckAvatarDefaults" id="gue-'.esc_html($fileid).'" name="'.esc_html($fileid).'"'.esc_html($checked).'>';
				echo '<label for="gue-'.esc_html($fileid).'">'.esc_html(SP()->primitives->admin_text('Guest')).'</label><br>';

				$checked = (!$found) ? ' checked="checked" ' : '';
				echo '<input type="radio" value="none" class="spCheckAvatarDefaults" id="non-'.esc_html($fileid).'" name="'.esc_html($fileid).'"'.esc_html($checked).'>';
				echo '<label for="non-'.esc_html($fileid).'">'.esc_html(SP()->primitives->admin_text('None')).'</label>';

				echo '<div class="clearboth"></div>';

			echo '</div>';
		}
	}

	echo '<div class="clearboth"></div>';
	echo '</div>';
	closedir($dlist);
}
