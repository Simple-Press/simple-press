<?php
/*
Simple:Press
Admin Components General Support Functions
$LastChangedDate: 2017-08-05 17:36:04 -0500 (Sat, 05 Aug 2017) $
$Rev: 15488 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_get_login_data() {
	$sfcomps = array();

	$sflogin = array();
	$sflogin = SP()->options->get('sflogin');
	$sfcomps['sfregmath'] = $sflogin['sfregmath'];
	$sfcomps['sfloginurl'] = SP()->displayFilters->url($sflogin['sfloginurl']);
	$sfcomps['sfloginemailurl'] = SP()->displayFilters->url($sflogin['sfloginemailurl']);
	$sfcomps['sflogouturl'] = SP()->displayFilters->url($sflogin['sflogouturl']);
	$sfcomps['sfregisterurl'] = SP()->displayFilters->url($sflogin['sfregisterurl']);
	$sfcomps['sptimeout'] = SP()->filters->integer($sflogin['sptimeout']);
	$sfcomps['spshowlogin'] = $sflogin['spshowlogin'];
	$sfcomps['spshowregister'] = $sflogin['spshowregister'];
	$sfcomps['spaltloginurl'] = SP()->displayFilters->url($sflogin['spaltloginurl']);
	$sfcomps['spaltlogouturl'] = SP()->displayFilters->url($sflogin['spaltlogouturl']);
	$sfcomps['spaltregisterurl'] = SP()->displayFilters->url($sflogin['spaltregisterurl']);

	$sfrpx = SP()->options->get('sfrpx');
	$sfcomps['sfrpxenable'] = $sfrpx['sfrpxenable'];
	$sfcomps['sfrpxkey'] = $sfrpx['sfrpxkey'];
	$sfcomps['sfrpxredirect'] = SP()->displayFilters->url($sfrpx['sfrpxredirect']);

	return $sfcomps;
}

function spa_get_seo_data() {
	$sfcomps = array();

	# browser title
	$sfseo = SP()->options->get('sfseo');
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
	$sfmetatags = SP()->options->get('sfmetatags');
	$sfcomps['sfdescr'] = SP()->displayFilters->title($sfmetatags['sfdescr']);
	$sfcomps['sfdescruse'] = $sfmetatags['sfdescruse'];
	$sfcomps['sfusekeywords'] = SP()->displayFilters->title($sfmetatags['sfusekeywords']);
	$sfcomps['sfkeywords'] = (isset($sfmetatags['sfkeywords'])) ? $sfmetatags['sfkeywords'] : 0;

	return $sfcomps;
}

function spa_get_forumranks_data() {
	$rankings = SP()->meta->get('forum_rank');

	return $rankings;
}

function spa_get_specialranks_data() {
	$special_rankings = SP()->meta->get('special_rank');

	return $special_rankings;
}

function spa_get_messages_data() {
	$sfcomps = array();

	# custom message for posts
	$sfpostmsg = array();
	$sfpostmsg = SP()->options->get('sfpostmsg');
	$sflogin = array();
	$sflogin = SP()->options->get('sflogin');

	$sfcomps['sfpostmsgtext'] = SP()->editFilters->text($sfpostmsg['sfpostmsgtext']);
	$sfcomps['sfpostmsgtopic'] = $sfpostmsg['sfpostmsgtopic'];
	$sfcomps['sfpostmsgpost'] = $sfpostmsg['sfpostmsgpost'];

	$sfcomps['sfpostmsgtext2'] = SP()->editFilters->text($sfpostmsg['sfpostmsgtext2']);
	$sfcomps['sfpostmsgtopic2'] = $sfpostmsg['sfpostmsgtopic2'];
	$sfcomps['sfpostmsgpost2'] = $sfpostmsg['sfpostmsgpost2'];	

	# custom editor message
	$sfcomps['sfeditormsg'] = SP()->editFilters->text(SP()->options->get('sfeditormsg'));

	$sneakpeek = SP()->meta->get('sneakpeek', 'message');
	$adminview = SP()->meta->get('adminview', 'message');
	$userview = SP()->meta->get('userview', 'message');

	$sfcomps['sfsneakpeek'] = '';
	$sfcomps['sfadminview'] = '';
	$sfcomps['sfuserview'] = '';
	if (!empty($sneakpeek[0])) $sfcomps['sfsneakpeek'] = SP()->editFilters->text($sneakpeek[0]['meta_value']);
	if (!empty($adminview[0])) $sfcomps['sfadminview'] = SP()->editFilters->text($adminview[0]['meta_value']);
	if (!empty($userview[0])) $sfcomps['sfuserview'] = SP()->editFilters->text($userview[0]['meta_value']);
	$sfcomps['sfsneakredirect'] = SP()->displayFilters->url($sflogin['sfsneakredirect']);

	return $sfcomps;
}

function spa_paint_rank_images() {
	# Open badges folder and get cntents for matching
	$path = SP_STORE_DIR.'/'.SP()->plugin->storage['ranks'].'/';
	$dlist = @opendir($path);
	if (!$dlist) {
		echo '<table><tr><td class="sflabel"><strong>'.SP()->primitives->admin_text('The rank badges folder does not exist').'</strong></td></tr></table>';
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
				<th style='text-align:center'><?php SP()->primitives->admin_etext('Badge'); ?></th>
				<th style='text-align:center'><?php SP()->primitives->admin_etext('Filename'); ?></th>
				<th style='text-align:center'><?php SP()->primitives->admin_etext('Remove'); ?></th>
			</tr>
		</thead>
		<tbody>
<?php
	$row = 0;
	foreach ($files as $file) {
		$path_info = pathinfo($path.$file);
		$ext = strtolower($path_info['extension']);
		if ($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png' || $ext == 'gif' || $ext == 'bmp') {
?>
			<tr id='rankbadge<?php echo $row; ?>' class="spMobileTableData">
				<td data-label='<?php SP()->primitives->admin_etext('Badge'); ?>'>
					<img class="sfrankbadge" src="<?php echo(esc_url(SPRANKS.$file)); ?>" alt="" />
				</td>
				<td data-label='<?php SP()->primitives->admin_etext('Filename'); ?>'>
					<?php echo($file); ?>
				</td>
				<td data-label='<?php SP()->primitives->admin_etext('Remove'); ?>'>
<?php
					$site = esc_url(wp_nonce_url(SPAJAXURL."components&amp;targetaction=delbadge&amp;file=$file", 'components'));
					echo '<img class="spDeleteRow" src="'.SPCOMMONIMAGES.'delete.png" title="'.SP()->primitives->admin_text('Delete Rank Badge').'" alt="" data-url="'.$site.'" data-target="rankbadge'.$row.'" />';
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
	$scount = -1;

	# load smiles from sfmeta
	$filelist = array();

	$meta = SP()->meta->get('smileys', 'smileys');

	# Open forum-smileys folder and get cntents for matching
	$path = SP_STORE_DIR.'/'.SP()->plugin->storage['smileys'].'/';
	$dlist = @opendir($path);

	echo '<div class="sf-alert-block sf-info">';
	if (!$dlist) {
	   echo '<table><tr><td class="sflabel"><strong>'.SP()->primitives->admin_text('The forum-smileys folder does not exist').'</strong></td></tr></table>';
	   return;
	} else {
    	echo '<p><b>'.SP()->primitives->admin_text('Re-order your Smileys by dragging and dropping the buttons below. To edit - click on the open control to the right').'</b></p>';
	}

	$yes = '<img src="'.SPADMINIMAGES.'sp_Yes.png" title="'.SP()->primitives->admin_text('In use').'" alt="" style="vertical-align: middle;" />&nbsp;&nbsp;';
	$no =  '<img src="'.SPADMINIMAGES.'sp_No.png" title="'.SP()->primitives->admin_text('Not in use').'" alt="" style="vertical-align: middle;" />&nbsp;&nbsp;';

	echo '<table><tr>';
	echo '<td>'.$yes.'&nbsp;'.SP()->primitives->admin_text('Smiley is in use').'&nbsp;&nbsp;&nbsp;</td>';
	echo '<td>'.$no.'&nbsp;'.SP()->primitives->admin_text('Smiley is not in use').'</td>';
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
					echo '<img class="spSmiley" src="'.SPSMILEYS.$file.'" alt="" style="margin-top:8px;"/>';
					echo '&nbsp;&nbsp;&nbsp;<span class="item-name">';
						echo $sname;
					echo '</span>';
					echo '<input type="hidden" name="smfile[]" value="'.$file.'" />';
					echo '<span class="item-controls">';

						$site = esc_url(wp_nonce_url(SPAJAXURL."components&amp;targetaction=delsmiley&amp;file=$file", 'components'));
						echo '<img src="'.SPCOMMONIMAGES.'delete.png" title="'.SP()->primitives->admin_text('Delete Smiley').'" alt="" style="vertical-align: middle;cursor:pointer;" class="spDeleteRowReload" data-url="'.$site.'" data-reload="sfreloadsm" />&nbsp;&nbsp;';
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

					echo '<p class="sf-description">'.SP()->primitives->admin_text('Smiley Name').'<br />';
					echo '<input type="text" class="sfpostcontrol" id="smname-'.$scount.'" name="smname[]" value="'.SP()->displayFilters->title($sname).'" /></p>';

					echo '<p class="sf-description">'.SP()->primitives->admin_text('Smiley Code').'<br />';
					echo '<input type="text" class="sfpostcontrol" id="smcode-'.$scount.'" name="smcode[]" value="'.SP()->displayFilters->title($code).'" /></p>';

					echo '<p class="sf-description">';
					$checked = ($break) ? ' checked="checked" ' : '';
					echo '<input type="checkbox" class="sfpostcontrol" id="break-'.$scount.'" name="smbreak-'.$sname.'" '.$checked.'/>';
					echo '<label for="break-'.$scount.'">'.SP()->primitives->admin_text('Break Smileys Row in Editor Display').'</label></p>';

					echo '<p class="sf-description">';
					$checked = ($in_use) ? ' checked="checked" ' : '';
					echo '<input type="checkbox" class="sfpostcontrol" id="in_use-'.$scount.'" name="sminuse-'.$sname.'" '.$checked.'/>';
					echo '<label for="in_use-'.$scount.'">'.SP()->primitives->admin_text('Allow Use of this Smiley').'</label></p>';

				echo '</div>';
			echo '</li>';
			}
		}
		echo '<input type="hidden" id="smiley-count" name="smiley-count" value="'.$scount.'" />';
	echo '</ul>';
	echo '</div>';

	closedir($dlist);
}
