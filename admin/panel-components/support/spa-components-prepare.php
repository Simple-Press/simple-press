<?php
/*
Simple:Press
Admin Components General Support Functions
$LastChangedDate: 2016-10-29 14:08:09 -0500 (Sat, 29 Oct 2016) $
$Rev: 14686 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_get_login_data() {
	$sfcomps = array();

	$sflogin = array();
	$sflogin = sp_get_option('sflogin');
	$sfcomps['sfregmath'] = $sflogin['sfregmath'];
	$sfcomps['sfloginurl'] = sp_filter_url_display($sflogin['sfloginurl']);
	$sfcomps['sfloginemailurl'] = sp_filter_url_display($sflogin['sfloginemailurl']);
	$sfcomps['sflogouturl'] = sp_filter_url_display($sflogin['sflogouturl']);
	$sfcomps['sfregisterurl'] = sp_filter_url_display($sflogin['sfregisterurl']);
	$sfcomps['sptimeout'] = sp_esc_int($sflogin['sptimeout']);
	$sfcomps['spshowlogin'] = $sflogin['spshowlogin'];
	$sfcomps['spshowregister'] = $sflogin['spshowregister'];
	$sfcomps['spaltloginurl'] = sp_filter_url_display($sflogin['spaltloginurl']);
	$sfcomps['spaltlogouturl'] = sp_filter_url_display($sflogin['spaltlogouturl']);
	$sfcomps['spaltregisterurl'] = sp_filter_url_display($sflogin['spaltregisterurl']);

	$sfrpx = sp_get_option('sfrpx');
	$sfcomps['sfrpxenable'] = $sfrpx['sfrpxenable'];
	$sfcomps['sfrpxkey'] = $sfrpx['sfrpxkey'];
	$sfcomps['sfrpxredirect'] = sp_filter_url_display($sfrpx['sfrpxredirect']);

	return $sfcomps;
}

function spa_get_seo_data() {
	$sfcomps = array();

	# browser title
	$sfseo = sp_get_option('sfseo');
	$sfcomps['sfseo_overwrite'] = $sfseo['sfseo_overwrite'];
	$sfcomps['sfseo_blogname'] = $sfseo['sfseo_blogname'];
	$sfcomps['sfseo_pagename'] = $sfseo['sfseo_pagename'];
	$sfcomps['sfseo_homepage'] = $sfseo['sfseo_homepage'];
	$sfcomps['sfseo_topic'] = $sfseo['sfseo_topic'];
	$sfcomps['sfseo_forum'] = $sfseo['sfseo_forum'];
	$sfcomps['sfseo_noforum'] = $sfseo['sfseo_noforum'];
	$sfcomps['sfseo_page'] = $sfseo['sfseo_page'];
	$sfcomps['sfseo_sep'] = $sfseo['sfseo_sep'];
	$sfcomps['sfseo_og'] = $sfseo['sfseo_og'];
	$sfcomps['seo_og_attachment'] = $sfseo['seo_og_attachment'];
	$sfcomps['seo_og_type'] = empty($sfseo['seo_og_type']) ? 'website' : $sfseo['seo_og_type'];

	# meta tags
	$sfmetatags = array();
	$sfmetatags = sp_get_option('sfmetatags');
	$sfcomps['sfdescr'] = sp_filter_title_display($sfmetatags['sfdescr']);
	$sfcomps['sfdescruse'] = $sfmetatags['sfdescruse'];
	$sfcomps['sfusekeywords'] = sp_filter_title_display($sfmetatags['sfusekeywords']);
	$sfcomps['sfkeywords'] = (isset($sfmetatags['sfkeywords'])) ? $sfmetatags['sfkeywords'] : 0;

	return $sfcomps;
}

function spa_get_forumranks_data() {
	$rankings = sp_get_sfmeta('forum_rank');

	return $rankings;
}

function spa_get_specialranks_data() {
	$special_rankings = sp_get_sfmeta('special_rank');

	return $special_rankings;
}

function spa_get_messages_data() {
	$sfcomps = array();

	# custom message for posts
	$sfpostmsg = array();
	$sfpostmsg = sp_get_option('sfpostmsg');
	$sflogin = array();
	$sflogin = sp_get_option('sflogin');

	$sfcomps['sfpostmsgtext'] = sp_filter_text_edit($sfpostmsg['sfpostmsgtext']);
	$sfcomps['sfpostmsgtopic'] = $sfpostmsg['sfpostmsgtopic'];
	$sfcomps['sfpostmsgpost'] = $sfpostmsg['sfpostmsgpost'];

	# custom editor message
	$sfcomps['sfeditormsg'] = sp_filter_text_edit(sp_get_option('sfeditormsg'));

	$sneakpeek = sp_get_sfmeta('sneakpeek', 'message');
	$adminview = sp_get_sfmeta('adminview', 'message');
	$userview = sp_get_sfmeta('userview', 'message');

	$sfcomps['sfsneakpeek'] = '';
	$sfcomps['sfadminview'] = '';
	$sfcomps['sfuserview'] = '';
	if (!empty($sneakpeek[0])) $sfcomps['sfsneakpeek'] = sp_filter_text_edit($sneakpeek[0]['meta_value']);
	if (!empty($adminview[0])) $sfcomps['sfadminview'] = sp_filter_text_edit($adminview[0]['meta_value']);
	if (!empty($userview[0])) $sfcomps['sfuserview'] = sp_filter_text_edit($userview[0]['meta_value']);
	$sfcomps['sfsneakredirect'] = sp_filter_url_display($sflogin['sfsneakredirect']);

	return $sfcomps;
}

function spa_paint_rank_images() {
	global $tab, $spPaths;

	# Open badges folder and get cntents for matching
	$path = SF_STORE_DIR.'/'.$spPaths['ranks'].'/';
	$dlist = @opendir($path);
	if (!$dlist) {
		echo '<table><tr><td class="sflabel"><strong>'.spa_text('The rank badges folder does not exist').'</strong></td></tr></table>';
		return;
	}

	$files = array();
	while (false !== ($file = readdir($dlist))) {
		if ($file != '.' && $file != '..') {
			$files[] = $file;
		}
	}
	closedir($dlist);

	if (empty($files)) return;
	sort($files);

	# start the table display
?>
	<table id="sf-rank-badges" class="widefat fixed striped spMobileTable800">
		<thead>
			<tr>
				<th style='text-align:center'><?php spa_etext('Badge'); ?></th>
				<th style='text-align:center'><?php spa_etext('Filename'); ?></th>
				<th style='text-align:center'><?php spa_etext('Remove'); ?></th>
			</tr>
		</thead>
		<tbody>
<?php
	$row = 0;
	foreach ($files as $file) {
		$path_info = pathinfo($path.$file);
		$ext = strtolower($path_info['extension']);
		if ($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png' || $ext == 'gif' || $ext == 'bmp') {
			$found = false;
?>
			<tr id='rankbadge<?php echo $row; ?>' class="spMobileTableData">
				<td data-label='<?php spa_etext('Badge'); ?>'>
					<img class="sfrankbadge" src="<?php echo(esc_url(SFRANKS.$file)); ?>" alt="" />
				</td>
				<td data-label='<?php spa_etext('Filename'); ?>'>
					<?php echo($file); ?>
				</td>
				<td data-label='<?php spa_etext('Remove'); ?>'>
<?php
					$site = esc_url(wp_nonce_url(SPAJAXURL."components&amp;targetaction=delbadge&amp;file=$file", 'components'));
					echo '<img class="spDeleteRow" src="'.SFCOMMONIMAGES.'delete.png" title="'.spa_text('Delete Rank Badge').'" alt="" data-url="'.$site.'" data-target="rankbadge'.$row.'" />';
?>
				</td>
			</tr>
<?php
			$row++;
		}
	}
	echo '</table>';
	echo '<input type="hidden" id="rank-count" name="rank-count" value="'.$row.'" />';
}

function spa_paint_custom_smileys() {
	global $spPaths, $tab;

	$scount = -1;

	# load smiles from sfmeta
	$filelist = array();

	$meta = sp_get_sfmeta('smileys', 'smileys');
	$smeta = $meta[0]['meta_value'];

	# Open forum-smileys folder and get cntents for matching
	$path = SF_STORE_DIR.'/'.$spPaths['smileys'].'/';
	$dlist = @opendir($path);

	echo '<div class="sfoptionerror">';
	if (!$dlist) {
	   echo '<table><tr><td class="sflabel"><strong>'.spa_text('The forum-smileys folder does not exist').'</strong></td></tr></table>';
	   return;
	} else {
    	echo '<p><b>'.spa_text('Re-order your Smileys by dragging and dropping the buttons below. To edit - click on the open control to the right').'</b></p>';
	}

	$yes = '<img src="'.SFADMINIMAGES.'sp_Yes.png" title="'.spa_text('In use').'" alt="" style="vertical-align: middle;" />&nbsp;&nbsp;';
	$no =  '<img src="'.SFADMINIMAGES.'sp_No.png" title="'.spa_text('Not in use').'" alt="" style="vertical-align: middle;" />&nbsp;&nbsp;';

	echo '<table><tr>';
	echo '<td>'.$yes.'&nbsp;'.spa_text('Smiley is in use').'&nbsp;&nbsp;&nbsp;</td>';
	echo '<td>'.$no.'&nbsp;'.spa_text('Smiley is not in use').'</td>';
	echo '</tr></table>';
	echo '</div>';

	# start the table display
	echo '<div>';
	echo '<ul id="spSmileyListSet" class="menu">';

	# gather the file data
	while (false !== ($file = readdir($dlist))) {
		$path_info = pathinfo($path.$file);
		$ext = strtolower($path_info['extension']);
		if (($file != '.' && $file != '..') && ($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png' || $ext == 'gif' || $ext == 'bmp')) {
			$filelist[] = $file;
		}
	}

	# now to sort them if required
	$newfiles = (count($filelist) + 1);
	$sortlist = array();

	if ($filelist) {
		foreach ($filelist as $file) {
			$found = false;
			if ($meta[0]['meta_value']) {
				foreach ($meta[0]['meta_value'] as $name => $info) {
					if ($info[0] == $file) {
						$found = true;
						break;
					}
				}
			}
			if ($found) {
				if (isset($info[3])) {
					$sortlist[$info[3]] = $file;
				} else {
					$sortlist[] = $file;
				}
			} else {
				$sortlist[$newfiles] = $file;
				$newfiles++;
			}
		}
		ksort($sortlist);
	}

	if ($sortlist) {
		foreach ($sortlist as $file) {
			$found = false;

			if ($meta[0]['meta_value']) {
				foreach ($meta[0]['meta_value'] as $name => $info) {
					if ($info[0] == $file) {
						$found = true;
						break;
					}
				}
			}
			if (!$found) {
				$sname = str_replace('.', '_', $file);
				$code = str_replace('.', '_', $file);
				$in_use = false;
				$break = false;
			} else {
				$code = stripslashes($info[1]);
				$sname = $name;
				$in_use = $info[2];
				$break = (isset($info[4])) ? $info[4] : false;
			}
			$scount++;

			# image and file name and input fields

			echo '<li id="smfile_'.$scount.'" class="menu-item-depth-0" style="margin-bottom:5px;">';
				echo "<div class='menu-item'>";
					echo '<img class="spSmiley" src="'.SFSMILEYS.$file.'" alt="" style="margin-top:8px;"/>';
					echo '&nbsp;&nbsp;&nbsp;<span class="item-name">';
						echo $sname;
					echo '</span>';
					echo '<input type="hidden" name="smfile[]" value="'.$file.'" />';
					echo '<span class="item-controls">';

						$site = esc_url(wp_nonce_url(SPAJAXURL."components&amp;targetaction=delsmiley&amp;file=$file", 'components'));
						echo '<img src="'.SFCOMMONIMAGES.'delete.png" title="'.spa_text('Delete Smiley').'" alt="" style="vertical-align: middle;cursor:pointer;" class="spDeleteRowReload" data-url="'.$site.'" data-reload="sfreloadsm" />&nbsp;&nbsp;';
						if($in_use) {
							echo $yes;
						} else {
							echo $no;
						}
						echo '<a class="item-edit spLayerToggle" data-target="item-edit-'.$scount.'">Edit Menu</a>';
						echo '<div class="inline_edit" size="70" id="spMenusOrder'.$scount.'" name="spMenusOrder'.$scount.'"></div>';
					echo '</span>';
				echo '</div>';

				echo '<div id="item-edit-'.$scount.'" class="menu-item-settings inline_edit">';

					echo '<p class="description">'.spa_text('Smiley Name').'<br />';
					echo '<input type="text" class="sfpostcontrol" id="smname-'.$scount.'" name="smname[]" value="'.sp_filter_title_display($sname).'" /></p>';

					echo '<p class="description">'.spa_text('Smiley Code').'<br />';
					echo '<input type="text" class="sfpostcontrol" id="smcode-'.$scount.'" name="smcode[]" value="'.sp_filter_title_display($code).'" /></p>';

					echo '<p class="description">';
					$checked = ($break) ? ' checked="checked" ' : '';
					echo '<input type="checkbox" class="sfpostcontrol" id="break-'.$scount.'" name="smbreak-'.$sname.'" '.$checked.'/>';
					echo '<label for="break-'.$scount.'">'.spa_text('Break Smileys Row in Editor Display').'</label></p>';

					echo '<p class="description">';
					$checked = ($in_use) ? ' checked="checked" ' : '';
					echo '<input type="checkbox" class="sfpostcontrol" id="in_use-'.$scount.'" name="sminuse-'.$sname.'" '.$checked.'/>';
					echo '<label for="in_use-'.$scount.'">'.spa_text('Allow Use of this Smiley').'</label></p>';

				echo '</div>';
			echo '</li>';
			}
		}
		echo '<input type="hidden" id="smiley-count" name="smiley-count" value="'.$scount.'" />';
	echo '</ul>';
	echo '</div>';

	closedir($dlist);
}

?>