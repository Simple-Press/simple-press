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
	if(!empty($sfrpx)){
		$sfcomps['sfrpxenable'] = isset($sfrpx['sfrpxenable']) ? $sfrpx['sfrpxenable'] : false;
		$sfcomps['sfrpxkey'] = isset($sfrpx['sfrpxkey']) ? $sfrpx['sfrpxkey'] : "";
		$sfcomps['sfrpxredirect'] = SP()->displayFilters->url(isset($sfrpx['sfrpxredirect']) ? $sfrpx['sfrpxredirect'] : "");
	}

	return $sfcomps;
}

function spa_get_seo_data() {
	$sfcomps = array();

	# browser title
	$sfseo = SP()->options->get('sfseo');
    if(!empty($sfseo)){
		$sfcomps['sfseo_overwrite'] = isset($sfseo['sfseo_overwrite']) ? $sfseo['sfseo_overwrite']: false;
		$sfcomps['sfseo_blogname'] = isset($sfseo['sfseo_blogname']) ? $sfseo['sfseo_blogname']: false;
		$sfcomps['sfseo_pagename'] = isset($sfseo['sfseo_pagename']) ? $sfseo['sfseo_pagename']: false;
		$sfcomps['sfseo_homepage'] = isset($sfseo['sfseo_homepage']) ? $sfseo['sfseo_homepage']: false;
		$sfcomps['sfseo_topic'] = isset($sfseo['sfseo_topic']) ? $sfseo['sfseo_topic']: false;
		$sfcomps['sfseo_forum'] = isset($sfseo['sfseo_forum']) ? $sfseo['sfseo_forum']: false;
		$sfcomps['sfseo_noforum'] = isset($sfseo['sfseo_noforum']) ? $sfseo['sfseo_noforum']: false;
		$sfcomps['sfseo_page'] = isset($sfseo['sfseo_page']) ? $sfseo['sfseo_page']: false;
		$sfcomps['sfseo_sep'] = isset($sfseo['sfseo_sep']) ? $sfseo['sfseo_sep']: "";
		$sfcomps['sfseo_og'] = isset($sfseo['sfseo_og']) ? $sfseo['sfseo_og']: false;
		$sfcomps['seo_og_attachment'] = isset($sfseo['seo_og_attachment']) ? $sfseo['seo_og_attachment']: false;
		$sfcomps['seo_og_type'] = isset($sfseo['seo_og_type']) ? $sfseo['seo_og_type'] : "website";
	}
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
	$sflogin = array();
	$sflogin = SP()->options->get('sflogin');
	$sfpostmsg = SP()->options->get('sfpostmsg');

	if(!empty($sfpostmsg)){
		$sfcomps['sfpostmsgtext'] = SP()->editFilters->text( isset($sfpostmsg['sfpostmsgtext']) ? $sfpostmsg['sfpostmsgtext'] : '' );
		$sfcomps['sfpostmsgtopic'] = isset($sfpostmsg['sfpostmsgtopic']) ? $sfpostmsg['sfpostmsgtopic'] : false;
		$sfcomps['sfpostmsgpost'] =  isset($sfpostmsg['sfpostmsgpost']) ? $sfpostmsg['sfpostmsgpost'] : false;

		$sfcomps['sfpostmsgtext2'] = SP()->editFilters->text(isset($sfpostmsg['sfpostmsgtext2']) ? $sfpostmsg['sfpostmsgtext2'] : '' );
		$sfcomps['sfpostmsgtopic2'] = isset($sfpostmsg['sfpostmsgtopic2']) ? $sfpostmsg['sfpostmsgtopic2'] : false;
		$sfcomps['sfpostmsgpost2'] = isset($sfpostmsg['sfpostmsgpost2']) ? $sfpostmsg['sfpostmsgpost2'] : false;
	}

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
	$sfcomps['sfsneakredirect'] = SP()->displayFilters->url( isset($sflogin['sfsneakredirect']) ? $sflogin['sfsneakredirect'] : '');

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

	//if (empty($files)) return;
	sort($files);

	# start the table display
?>
	<table id="sf-rank-badges" class="widefat sf-table-small sf-table-mobile">
		<thead>
			<tr>
				<th><?php SP()->primitives->admin_etext('Filename'); ?></th>
				<th><?php SP()->primitives->admin_etext('Badge'); ?></th>
				<th><?php //SP()->primitives->admin_etext('Remove'); ?></th>
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
			<tr id='rankbadge<?php echo $row; ?>' class="spMobileTableData sf-display-table-row">
				<td data-label='<?php SP()->primitives->admin_etext('Filename'); ?>' class="sf-Leftm">
					<?php echo($file); ?>
				</td>
				<td data-label='<?php SP()->primitives->admin_etext('Badge'); ?>' class="sf-Leftm">
					<img class="sfrankbadge" src="<?php echo(esc_url(SPRANKS.$file)); ?>" alt="" />
				</td>
				<td data-label='<?php SP()->primitives->admin_etext('Remove'); ?>' class="sf-Leftm">
<?php
					$site = esc_url(wp_nonce_url(SPAJAXURL."components&amp;targetaction=delbadge&amp;file=$file", 'components'));
					echo '<span class="sf-item-controls"><span class="sf-icon-button sf-small sf-little spDeleteRow" title="'.SP()->primitives->admin_text('Delete Rank Badge').'" data-url="'.$site.'" data-target="rankbadge'.$row.'"><span class="sf-icon sf-delete"></></span></span>';
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

	
	if (!$dlist) {
           echo '<div class="sf-alert-block sf-info">';
	   echo '<table><tr><td class="sflabel"><strong>'.SP()->primitives->admin_text('The forum-smileys folder does not exist').'</strong></td></tr></table>';
	   echo '</div>';
           return;
	} else {
    	//echo '<p><b>'.SP()->primitives->admin_text('Re-order your Smileys by dragging and dropping the buttons below. To edit - click on the open control to the right').'</b></p>';
	}

	$yes = '<span class="sf-icon sf-check" title="'.SP()->primitives->admin_text('In use').'"></span>';
	$no =  '<span class="sf-icon sf-no-check" title="'.SP()->primitives->admin_text('Not in use').'"></span>';

        echo '<div class="sf-alert-block sf-info">';
	echo $yes.SP()->primitives->admin_text('Smiley is in use');
	echo '</div>';
        echo '<div class="sf-alert-block sf-info">';
	echo $no.SP()->primitives->admin_text('Smiley is not in use');
	echo '</div>';

	# start the table display
	echo '<div>';
	echo '<ul id="spSmileyListSet" class="sf-list">';

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

			echo '<li id="smfile_'.$scount.'" class="sf-list-item-depth-0">';
                            echo "<div class='sf-list-item'>";
				echo '<img class="spSmiley" src="'.SPSMILEYS.$file.'" alt=""/>';
                                echo '<span class="sf-item-name">';
                                    echo $sname;
                                echo '</span>';
                                echo '<input type="hidden" name="smfile[]" value="'.$file.'" />';
                                echo '<span class="sf-item-controls">';
                                    echo $in_use ? $yes : $no;
                                    echo '<a class="sf-item-edit spLayerToggle" data-target="item-edit-'.$scount.'">Edit Menu</a>';
                                echo '</span>';
                            echo '</div>';
                            echo '<div id="item-edit-'.$scount.'" class="sf-list-item-settings sf-inline-edit">';
                                echo '<div class="sf-form-row">';
                                    echo '<label>'.SP()->primitives->admin_text('Smiley Name');
                                    echo '<input type="text" class="sfpostcontrol" id="smname-'.$scount.'" name="smname[]" value="'.SP()->displayFilters->title($sname).'" /></label>';
                                echo '</div>';
                                echo '<div class="sf-form-row">';
                                    echo '<label>'.SP()->primitives->admin_text('Smiley Code');
                                    echo '<input type="text" class="sfpostcontrol" id="smcode-'.$scount.'" name="smcode[]" value="'.SP()->displayFilters->title($code).'" /></label>';
                                echo '</div>';
                                $checked = ($break) ? ' checked="checked" ' : '';
                                echo '<div class="sf-form-row">';
                                    echo '<input type="checkbox" class="sfpostcontrol" id="break-'.$scount.'" name="smbreak-'.$sname.'" '.$checked.'/>';
                                    echo '<label for="break-'.$scount.'">'.SP()->primitives->admin_text('Break Smileys Row in Editor Display').'</label>';
                                echo '</div>';
                                $checked = ($in_use) ? ' checked="checked" ' : '';
                                echo '<div class="sf-form-row">';
                                    echo '<input type="checkbox" class="sfpostcontrol" id="in_use-'.$scount.'" name="sminuse-'.$sname.'" '.$checked.'/>';
                                    echo '<label for="in_use-'.$scount.'">'.SP()->primitives->admin_text('Allow Use of this Smiley').'</label>';
                                echo '</div>';
                                $site = esc_url(wp_nonce_url(SPAJAXURL."components&amp;targetaction=delsmiley&amp;file=$file", 'components'));
                                echo '<div class="sf-form-submit-bar">';
                                    echo '<span title="'.SP()->primitives->admin_text('Delete Smiley').'" class="sf-link spDeleteRowReload" data-url="'.$site.'" data-reload="sfreloadsm"><span class="sf-icon sf-delete"></span>'.SP()->primitives->admin_text('Delete ').'</span>';	
                                    echo '<div class="sf-pull-right">';
                                        echo '<a class="sf-button-secondary sf-btn-small spLayerToggle">Cancel</a>';
                                        echo '<input type="submit" class="sf-button-primary sf-btn-small" name="saveit" value="'.SP()->primitives->admin_text('Save').'" />';
                                    echo '</div>';
                                echo '</div>';
                            echo '</div>';
			echo '</li>';
			}
		}
		
	echo '</ul>';
        echo '<input type="hidden" id="smiley-count" name="smiley-count" value="'.$scount.'" />';
	echo '</div>';

	closedir($dlist);
}
