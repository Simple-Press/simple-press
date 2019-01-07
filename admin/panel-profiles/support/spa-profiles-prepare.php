<?php
/*
Simple:Press
Admin Profiles Support Functions
$LastChangedDate: 2017-02-11 15:35:37 -0600 (Sat, 11 Feb 2017) $
$Rev: 15187 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_get_options_data() {
	$sfprofile = SP()->options->get('sfprofile');
	$sfsigimagesize = SP()->options->get('sfsigimagesize');
	$sfprofile['sfsigwidth'] = $sfsigimagesize['sfsigwidth'];
	$sfprofile['sfsigheight'] = $sfsigimagesize['sfsigheight'];
	return $sfprofile;
}

function spa_get_tabsmenus_data() {
	$tabs = SP()->profile->get_tabs_menus();
	return $tabs;
}

function spa_get_avatars_data() {
	$sfavatars = SP()->options->get('sfavatars');
	if(empty($sfavatars['sfavatarpriority'])) {
		$sfavatars['sfavatarpriority'] = array(0, 2, 3, 1, 4, 5);
	}
	return $sfavatars;
}

function spa_paint_avatar_pool() {
	$out = '';

	# Open avatar pool folder and get cntents for matching
	$path = SP_STORE_DIR.'/'.SP()->plugin->storage['avatar-pool'].'/';
	$dlist = @opendir($path);
	if (!$dlist) {
		echo '<table><tr><td class="sflabel"><strong>'.SP()->primitives->admin_text('The avatar pool folder does not exist').'</strong></td></tr></table>';
		return;
	}

	# start the table display
	$out.= '<table class="wp-list-table widefat"><tr>';
	$out.= '<th style="width:30%;text-align:center">'.SP()->primitives->admin_text('Avatar').'</th>';
	$out.= '<th style="width:50%;text-align:center">'.SP()->primitives->admin_text('Filename').'</th>';
	$out.= '<th style="text-align:center">'.SP()->primitives->admin_text('Remove').'</th>';
	$out.= '</tr>';

	$out.= '<tr><td colspan="3">';
	$out.= '<div id="sf-avatar-pool">';
	while (false !== ($file = readdir($dlist))) {
		if ($file != "." && $file != "..") {
			$out.= '<table style="width:100%">';
			$out.= '<tr>';
			$out.= '<td style="text-align:center;width:30%" class="spWFBorder"><img class="sfavatarpool" src="'.esc_url(SPAVATARDIR.$file).'" alt="" /></td>';
			$out.= '<td style="text-align:center;width:50%" class="spWFBorder sflabel">';
			$out.= $file;
			$out.= '</td>';
			$out.= '<td style="text-align:center" class="spWFBorder">';
			$site = esc_url(wp_nonce_url(SPAJAXURL."profiles&amp;targetaction=delavatar&amp;file=$file", 'profiles'));
			$out.= '<img src="'.SPCOMMONIMAGES.'delete.png" title="'.SP()->primitives->admin_text('Delete Avatar').'" alt="" class="spDeleteRowReload" data-url="'.$site.'" data-reload="sfreloadpool" />';
			$out.= '</td>';
			$out.= '</tr>';
			$out.= '</table>';
		}
	}
	$out.= '</div>';
	$out.= '</td></tr></table>';
	closedir($dlist);

	echo $out;
}

function spa_paint_avatar_defaults() {
	$out = '';

	# Open avatar defaults folder and get cntents for matching
	$path = SP_STORE_DIR.'/'.SP()->plugin->storage['avatars'].'/defaults/';
	$dlist = @opendir($path);
	if (!$dlist) {
		echo '<table><tr><td class="sflabel"><strong>'.SP()->primitives->admin_text('The avatar defaults folder does not exist').'</strong></td></tr></table>';
		return;
	}

	$def = SP()->options->get('spDefAvatars');

	# start the table display
	$out.= '<div id="av-browser">';

	while (false !== ($file = readdir($dlist))) {
		if ($file != "." && $file != "..") {
			$found = false;
			$border = (in_array($file, $def) ? '2px solid red' : '2px solid lightgray');

			$out.= '<div class="av-file" style="text-align:left;border:'.$border.';margin:5px;padding:6px;float:left;">';

				$out.= '<img src="'.esc_url(SPAVATARURL.'defaults/'.$file).'" alt="" />';

				$site = esc_url(wp_nonce_url(SPAJAXURL."profiles&amp;targetaction=deldefault&amp;file=$file", 'profiles'));
				$out.= '<img src="'.SPCOMMONIMAGES.'delete.png" alt="" class="spDeleteRowReload" data-url="'.$site.'" data-reload="sfreloadav" style="cursor:pointer;" />';
				$out.= '<div class="clearboth"></div>';

				$fileid = str_replace('.', 'z1z2z3', $file);
				$checked = ($def['admin']==$file) ? ' checked="checked" ' : '';
				if ($def['admin']==$file) $found=true;
				$out.= '<input type="radio" value="admin" class="spCheckAvatarDefaults" id="adm-'.$fileid.'" name="'.$fileid.'"'.$checked.'>';
				$out.= '<label for="adm-'.$fileid.'">'.SP()->primitives->admin_text('Admin').'</label><br>';

				$checked = ($def['mod']==$file) ? ' checked="checked" ' : '';
				if ($def['mod']==$file) $found=true;
				$out.= '<input type="radio" value="mod" class="spCheckAvatarDefaults" id="mod-'.$fileid.'" name="'.$fileid.'"'.$checked.'>';
				$out.= '<label for="mod-'.$fileid.'">'.SP()->primitives->admin_text('Moderator').'</label><br>';

				$checked = ($def['member']==$file) ? ' checked="checked" ' : '';
				if ($def['member']==$file) $found=true;
				$out.= '<input type="radio" value="member" class="spCheckAvatarDefaults" id="mem-'.$fileid.'" name="'.$fileid.'"'.$checked.'>';
				$out.= '<label for="mem-'.$fileid.'">'.SP()->primitives->admin_text('Member').'</label><br>';

				$checked = ($def['guest']==$file) ? ' checked="checked" ' : '';
				if ($def['guest']==$file) $found=true;
				$out.= '<input type="radio" value="guest" class="spCheckAvatarDefaults" id="gue-'.$fileid.'" name="'.$fileid.'"'.$checked.'>';
				$out.= '<label for="gue-'.$fileid.'">'.SP()->primitives->admin_text('Guest').'</label><br>';

				$checked = (!$found) ? ' checked="checked" ' : '';
				$out.= '<input type="radio" value="none" class="spCheckAvatarDefaults" id="non-'.$fileid.'" name="'.$fileid.'"'.$checked.'>';
				$out.= '<label for="non-'.$fileid.'">'.SP()->primitives->admin_text('None').'</label>';

				$out.= '<div class="clearboth"></div>';

			$out.= '</div>';
		}
	}

	$out.= '<div class="clearboth"></div>';
	$out.= '</div>';
	closedir($dlist);

	echo $out;
}
