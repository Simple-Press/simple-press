<?php
/*
Simple:Press
Admin Components Special Ranks Form
$LastChangedDate: 2016-10-21 16:27:53 -0500 (Fri, 21 Oct 2016) $
$Rev: 14650 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_special_rankings_form($rankings) {
	global $tab, $spPaths;
?>
<script type="text/javascript">
    jQuery(document).ready(function() {
    	spjAjaxForm('sfaddspecialrank', 'sfreloadfr');
    });
</script>
<?php
	spa_paint_options_init();
    $ajaxURL = wp_nonce_url(SPAJAXURL.'components-loader&amp;saveform=specialranks&amp;targetaction=newrank', 'components-loader');
?>
	<form action="<?php echo $ajaxURL; ?>" method="post" name="sfaddspecialrank" id="sfaddspecialrank">
<?php
	echo sp_create_nonce('special-rank-new');
	spa_paint_open_tab(spa_text('Components').' - '.spa_text('Special Forum Ranks'), true);
		spa_paint_open_panel();
			spa_paint_open_fieldset(spa_text('Special Forum Ranks'), true, 'special-ranks');

				spa_paint_input(spa_text('New Special Rank Name'), 'specialrank', '', false, true);
				echo '<input type="submit" class="button-primary" id="addspecialrank" name="addspecialrank" value="'.spa_text('Add Special Rank').'" />';

			spa_paint_close_fieldset();

			do_action('sph_components_add_rank_panel');
		spa_paint_close_panel();

		spa_paint_close_container();
		echo '<div class="sfform-panel-spacer"></div>';
	spa_paint_close_tab();
	echo '</form>';

	# display rankings info
	if ($rankings) {
		spa_paint_open_nohead_tab(true);
		spa_paint_open_panel();
?>
		<table class="widefat fixed striped">
    		<thead>
    			<tr>
    				<th><?php spa_etext('Special Rank Name') ?></strong></th>
    				<th><?php spa_etext('Special Rank Badge') ?></strong></th>
    				<th style="text-align: center;"><?php spa_etext('Special Rank Manage') ?></strong></th>
    			</tr>
    		</thead>
    		<tbody>
<?php
		foreach ($rankings as $rank) {
			$ajaxURL = wp_nonce_url(SPAJAXURL.'components-loader&amp;saveform=specialranks&amp;targetaction=updaterank&amp;id='.$rank['meta_id'], 'components-loader');
			$delsite = wp_nonce_url(SPAJAXURL.'components&amp;targetaction=del_specialrank&amp;key='.$rank['meta_id'], 'components');

    		$base = wp_nonce_url(SPAJAXURL.'components-loader', 'components-loader');
    		$target = 'members-'.$rank['meta_id'];
    		$image = SFADMINIMAGES;
?>
				<tr id="srank<?php echo $rank['meta_id']; ?>">
                    <td colspan="3">
            			<form action="<?php echo $ajaxURL; ?>" method="post" id="sfspecialrankupdate<?php echo $rank['meta_id']; ?>" name="sfspecialrankupdate<?php echo $rank['meta_id']; ?>">
<?php
                		echo sp_create_nonce('special-rank-update');
?>
                        <table class='wp-list-table widefat fixed'>
                            <tr>
            					<td>
            						<input type="hidden" name="<?php echo('currentname['.$rank['meta_id'].']'); ?>" value="<?php echo $rank['meta_key']; ?>" />
            						<input type="text" size="16" tabindex="<?php echo $tab; ?>" name="<?php echo('specialrankdesc['.$rank['meta_id'].']'); ?>" value="<?php echo $rank['meta_key']; ?>" />
            						<br />
<?php
            						$thisRank = $rank['meta_key'];
            						sp_display_item_stats(SFSPECIALRANKS, 'special_rank', "'$thisRank'", spa_text('Members in Rank'));
?>
            					</td>
            					<td>
            						<?php spa_select_icon_dropdown('specialrankbadge['.$rank['meta_id'].']', spa_text('Select Badge'), SF_STORE_DIR.'/'.$spPaths['ranks'].'/', $rank['meta_value']['badge'], true, 105); ?>
            					</td>
            					<td>
            						<div class="sp-half-row-left">
                						<img class="spDeleteRow" data-url="<?php echo $delsite; ?>" data-target="srank<?php echo $rank['meta_id']; ?>" src="<?php echo SFCOMMONIMAGES; ?>delete.png" title="<?php spa_etext('Delete Special Rank'); ?>" alt="" />
            						</div>
            						<div class="sp-half-row-right">
                                        <input type="submit" class="button-primary" id="updatespecialrank<?php echo $rank['meta_id']; ?>" name="updatespecialrank<?php echo $rank['meta_id']; ?>" value="<?php spa_etext('Update Rank'); ?>" />
            						</div>
            					</td>
                            </tr>
            				<tr>
            					<td colspan="3">
<?php
            			            $loc = '#sfrankshow-'.$rank['meta_id'];
            			            $site = wp_nonce_url(SPAJAXURL.'components&amp;targetaction=show&amp;key='.$rank['meta_id'], 'components');
            						$gif = SFCOMMONIMAGES.'working.gif';
            						$text = esc_js(spa_text('Show/Hide Members'));
?>
            						<input type="button" id="show<?php echo $rank['meta_id']; ?>" class="button-secondary spSpecialRankShow" value="<?php echo $text; ?>" data-loc="<?php echo $loc; ?>" data-site="<?php echo $site; ?>" data-img="<?php echo $gif; ?>" data-id="<?php echo $rank['meta_id']; ?>" />

            						<input type="button" id="remove<?php echo $rank['meta_id']; ?>" class="button-secondary spSpecialRankForm" value="<?php spa_etext('Remove Members'); ?>" data-loc="<?php echo $loc; ?>" data-form="delmembers" data-base="<?php echo $base; ?>" data-target="<?php echo $target; ?>" data-img="<?php echo $image; ?>" data-id="<?php echo $rank['meta_id']; ?>" />
            						<input type="button" id="add<?php echo $rank['meta_id']; ?>" class="button-secondary spSpecialRankForm" value="<?php spa_etext('Add Members'); ?>" data-loc="<?php echo $loc; ?>" data-form="addmembers" data-base="<?php echo $base; ?>" data-target="<?php echo $target; ?>" data-img="<?php echo $image; ?>" data-id="<?php echo $rank['meta_id']; ?>" />
            					</td>
            				</tr>

            				<tr id="sfrankshow-<?php echo $rank['meta_id']; ?>">
            					<td colspan="3">
            					   <div id="members-<?php echo $rank['meta_id']; ?>"></div>
            					</td>
            				</tr>
                        </table>
            			</form>
                    </td>
               </tr>
                <script type="text/javascript">
                    jQuery(document).ready(function() {
                    	spjAjaxForm('sfspecialrankupdate<?php echo $rank['meta_id']; ?>', '');
                    });
                </script>
<?php
		}
?>
            </tbody>
        </table>
<?php
		spa_paint_close_panel();
		spa_paint_close_container();
		echo '<div class="sfform-panel-spacer"></div>';
	spa_paint_close_tab();
	}
}
?>