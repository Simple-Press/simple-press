<?php
/*
Simple:Press
Admin Components Forum Ranks Form
$LastChangedDate: 2016-10-21 16:27:53 -0500 (Fri, 21 Oct 2016) $
$Rev: 14650 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

define('SFUPLOADER', wp_nonce_url(SPAJAXURL.'uploader', 'uploader'));

include_once SF_PLUGIN_DIR.'/admin/panel-components/forms/spa-components-special-ranks-form.php';

function spa_components_forumranks_form() {
	global $spPaths;
?>
<script type= "text/javascript">/*<![CDATA[*/
    jQuery(document).ready(function(){
    	spjAjaxForm('sfforumranksform', 'sfreloadfr');

    	var button = jQuery('#sf-upload-button'), interval;
    	new AjaxUpload(button,{
    		action: '<?php echo SFUPLOADER; ?>',
    		name: 'uploadfile',
    	    data: {
    		    saveloc : '<?php echo addslashes(SF_STORE_DIR.'/'.$spPaths['ranks'].'/'); ?>'
    	    },
    		onSubmit : function(file, ext){
    			/* check for valid extension */
    			if (! (ext && /^(jpg|png|jpeg|gif|JPG|PNG|JPEG|GIF)$/.test(ext))){
    				jQuery('#sf-upload-status').html('<p class="sf-upload-status-fail"><?php echo esc_js(spa_text('Only JPG, PNG or GIF files are allowed!')); ?></p>');
    				return false;
    			}
    			/* change button text, when user selects file */
    			utext = '<?php echo esc_js(spa_text('Uploading')); ?>';
    			button.text(utext);
    			/* If you want to allow uploading only 1 file at time, you can disable upload button */
    			this.disable();
    			/* Uploding -> Uploading. -> Uploading... */
    			interval = window.setInterval(function(){
    				var text = button.text();
    				if (text.length < 13){
    					button.text(text + '.');
    				} else {
    					button.text(utext);
    				}
    			}, 200);
    		},
    		onComplete: function(file, response){
    			jQuery('#sf-upload-status').html('');
    			button.text('<?php echo esc_js(spa_text('Browse')); ?>');
    			window.clearInterval(interval);
    			/* re-enable upload button */
    			this.enable();
    			/* add file to the list */
    			if (response==="success"){
                    var site = "<?php echo SPAJAXURL; ?>components&amp;_wpnonce=<?php echo wp_create_nonce('components'); ?>&amp;targetaction=delbadge&amp;file=" + file;
    				var count = document.getElementById('rank-count');
    				var rcount = parseInt(count.value) + 1;
    				jQuery('#sf-rank-badges').append('<tr id="rankbadge' + rcount + '" class="spMobileTableData"><td data-label="<?php spa_etext('Badge'); ?>"><img class="sfrankbadge" src="<?php echo SFRANKS; ?>/' + file + '" alt="" /></td><td  data-label="<?php spa_etext('Filename'); ?>">' + file + '</td><td data-label="<?php spa_etext('Remove'); ?>"><img class="spDeleteRow" src="<?php echo SFCOMMONIMAGES; ?>' + 'delete.png' + '" title="<?php echo esc_js(spa_text('Delete Rank Badge')); ?>" alt="" data-url="' + site + '" data-target="rankbadge' + rcount + '" /></td></tr>');
    				jQuery('#sf-upload-status').html('<p class="sf-upload-status-success"><?php echo esc_js(spa_text('Forum badge uploaded!')); ?></p>');
                	jQuery('.ui-tooltip').hide();
    			} else if (response==="invalid"){
    				jQuery('#sf-upload-status').html('<p class="sf-upload-status-fail"><?php echo esc_js(spa_text('Sorry, the file has an invalid format!')); ?></p>');
    			} else if (response==="exists") {
    				jQuery('#sf-upload-status').html('<p class="sf-upload-status-fail"><?php echo esc_js(spa_text('Sorry, the file already exists!')); ?></p>');
    			} else {
    				jQuery('#sf-upload-status').html('<p class="sf-upload-status-fail"><?php echo esc_js(spa_text('Error uploading file!')); ?></p>');
    			}
    		}
    	});
    });/*]]>*/
</script>
<?php
	$rankings = spa_get_forumranks_data();

    $ajaxURL = wp_nonce_url(SPAJAXURL.'components-loader&amp;saveform=forumranks', 'components-loader');
?>
	<form action="<?php echo $ajaxURL; ?>" method="post" id="sfforumranksform" name="sfforumranks">
	<?php echo sp_create_nonce('forum-adminform_forumranks'); ?>
<?php
	spa_paint_options_init();

#== FORUM RANKS Tab ============================================================

	spa_paint_open_tab(spa_text('Components').' - '.spa_text('Standard Forum Ranks'), true);
		spa_paint_open_panel();
			spa_paint_open_fieldset(spa_text('Forum Ranks'), true, 'forum-ranks');
				spa_paint_rankings_table($rankings);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_close_container();
?>
	<div class="sfform-submit-bar">
	<input type="submit" class="button-primary" id="saveit" name="saveit" value="<?php spa_etext('Update Forum Ranks Components'); ?>" />
	</div>
<?php
	spa_paint_close_tab();
?>
	</form>
	<div class="sfform-panel-spacer"></div>
<?php

	$special_rankings = spa_get_specialranks_data();
	spa_special_rankings_form($special_rankings);

	echo '<div class="sfform-panel-spacer"></div>';

	spa_paint_open_tab(spa_text('Components').' - '.spa_text('Forum Rank Badges'), true);
		spa_paint_open_panel();
			spa_paint_open_fieldset(spa_text('Custom rank badge upload'), true, 'badges-upload');
				$loc = SF_STORE_DIR.'/'.$spPaths['ranks'].'/';
				spa_paint_file(spa_text('Select rank badge to upload'), 'newrankfile', false, true, $loc);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_open_panel();
			spa_paint_open_fieldset(spa_text('Custom Rank Badges'), true, 'rank-badges');
                spa_paint_rank_images();
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_close_container();

		do_action('sph_components_ranks_panel');
	spa_paint_close_tab();
	echo '<div class="sfform-panel-spacer"></div>';
}

function spa_paint_rankings_table($rankings) {
	global $tab, $spPaths;

	$usergroups = spa_get_usergroups_all();

	# sort rankings from lowest to highest
	if ($rankings) {
		foreach ($rankings as $x => $info) {
			$ranks['id'][$x] = $info['meta_id'];
			$ranks['title'][$x] = $info['meta_key'];
			$ranks['posts'][$x] = $info['meta_value']['posts'];
			$ranks['usergroup'][$x] = $info['meta_value']['usergroup'];
			$ranks['badge'][$x] = (!empty($info['meta_value']['badge'])) ? $info['meta_value']['badge'] : '';
		}
		array_multisort($ranks['posts'], SORT_ASC, $ranks['title'], $ranks['usergroup'], $ranks['badge'], $ranks['id']);
	}
?>
	<table class="widefat fixed striped spMobileTable1280">
		<thead>
			<tr>
				<th style='text-align:center'><?php spa_etext('Forum Rank Name'); ?></th>
				<th style='text-align:center'><?php spa_etext('# Posts For Rank'); ?></th>
				<th style='text-align:center'><?php spa_etext('Automatic User Group Membership'); ?></th>
				<th style='text-align:center'><?php spa_etext('Badge'); ?></th>
				<th style='text-align:center'><?php spa_etext('Remove'); ?></th>
			</tr>
		</thead>
		<tbody>
<?php
	# display rankings info
	for ($x = 0; $x < count($rankings); $x++) {
?>
		<tr id="rank<?php echo($x); ?>" class="spMobileTableData">

		<td data-label='<?php spa_etext('Forum Rank Name'); ?>'>
			<input type='text' size="12" class='wp-core-ui' tabindex='<?php echo $tab; ?>' name='rankdesc[]' value='<?php echo esc_attr($ranks['title'][$x]); ?>' />
			<input type='hidden' name='rankid[]' value='<?php echo esc_attr($ranks['id'][$x]); ?>' />
		</td>
		<?php $tab++; ?>

		<td data-label='<?php spa_etext('# Posts For Rank'); ?>'>
			<input type='text' class='wp-core-ui' size='5' tabindex='<?php echo $tab; ?>' name='rankpost[]' value='<?php echo $ranks['posts'][$x]; ?>' />
			<?php echo ' '.spa_text('Posts'); ?>
		</td>
		<?php $tab++; ?>

		<td data-label='<?php spa_etext('Auto User Group'); ?>'>
			<select class="wp-core-ui" name="rankug[]" style="width:135px;">
<?php
			if ($ranks['usergroup'][$x] == 'none') {
				$out = '<option value="none" selected="selected">'.spa_text('None').'</option>';
			} else {
				$out = '<option value="none">'.spa_text('None').'</option>';
			}
			foreach ($usergroups as $usergroup) {
				if ($ranks['usergroup'][$x] == $usergroup->usergroup_id) {
					$selected = ' SELECTED';
				} else {
					$selected = '';
				}
				$out.= '<option value="'.$usergroup->usergroup_id.'"'.$selected.'>'.sp_filter_title_display($usergroup->usergroup_name).'</option>';
			}
			echo $out;
?>
			</select>
		</td>
		<?php $tab++; ?>

		<td data-label='<?php spa_etext('Badge'); ?>'>
			<?php spa_select_icon_dropdown('rankbadge[]', spa_text('Select Badge'), SF_STORE_DIR.'/'.$spPaths['ranks'].'/', $ranks['badge'][$x], true, 135); ?>
		</td>
		<?php $tab++; ?>

		<td data-label='<?php spa_etext('Remove'); ?>'>
<?php
	        $site = wp_nonce_url(SPAJAXURL.'components&amp;targetaction=del_rank&amp;key='.$ranks['id'][$x], 'components');
?>
			<img class="spDeleteRow" data-url="'.$site.'" data-target="rankbadge'.$x.'" src="<?php echo SFCOMMONIMAGES; ?>delete.png" title="<?php spa_etext('Delete Rank'); ?>" alt="" />
		</td>
		<?php $tab++; ?>

		</tr>
<?php
	}
?>
		<!--empty row for new rank-->
		<tr class="spMobileTableData">

		<td data-label='<?php spa_etext('Forum Rank Name'); ?>'>
			<input type='text' size="12"  class='wp-core-ui' tabindex='<?php echo $tab; ?>' name='rankdesc[]' value='' />
			<input type='hidden' name='rankid[]' value='-1' />
		</td>
		<?php $tab++; ?>

		<td data-label='<?php spa_etext('# Posts For Rank'); ?>'>
			<input type='text' class='wp-core-ui' size='5' tabindex='<?php echo $tab; ?>' name='rankpost[]' value='' />
			<?php echo ' '.spa_text('Posts'); ?>
		</td>
		<?php $tab++; ?>

		<td data-label='<?php spa_etext('Auto User Group'); ?>'>
			<select class="wp-core-ui" name="rankug[]" style="width:135px;">
<?php
			$out = '<option value="none">'.spa_text('None').'</option>';
			foreach ($usergroups as $usergroup) {
				$out.= '<option value="'.$usergroup->usergroup_id.'">'.sp_filter_title_display($usergroup->usergroup_name).'</option>';
			}
			echo $out;
?>
			</select>
		</td>
		<?php $tab++; ?>

		<td data-label='<?php spa_etext('Badge'); ?>'>
			<?php spa_select_icon_dropdown('rankbadge[]', spa_text('Select Badge'), SF_STORE_DIR.'/'.$spPaths['ranks'].'/', '', true, 135); ?>
		</td>
		<?php $tab++; ?>

		<td></td>
		</tr>
		</tbody>
	</table>
<?php
}
?>