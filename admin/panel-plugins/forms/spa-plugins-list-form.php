<?php
/*
Simple:Press
Admin plugins list
$LastChangedDate: 2016-10-22 14:40:59 -0500 (Sat, 22 Oct 2016) $
$Rev: 14658 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_plugins_list_form() {
	global $spPaths;
?>
<script type="text/javascript">
    jQuery(document).ready(function() {
    	spjAjaxForm('sppluginsform', 'sfreloadpl');
        /* wp check all logic */
    	jQuery('thead, tfoot').find('.check-column :checkbox').click( function(e) {

    		var c = jQuery(this).prop('checked'),
    			kbtoggle = 'undefined' == typeof toggleWithKeyboard ? false : toggleWithKeyboard,
    			toggle = e.shiftKey || kbtoggle;

    		jQuery(this).closest( 'table' ).children( 'tbody' ).filter(':visible')
    		.children().children('.check-column').find(':checkbox')
    		.prop('checked', function() {
    			if ( jQuery(this).is(':hidden') )
    				return false;
    			if ( toggle )
    				return jQuery(this).prop( 'checked' );
    			else if (c)
    				return true;
    			return false;
    		});

    		jQuery(this).closest('table').children('thead,  tfoot').filter(':visible')
    		.children().children('.check-column').find(':checkbox')
    		.prop('checked', function() {
    			if ( toggle )
    				return false;
    			else if (c)
    				return true;
    			return false;
    		});
    	});
    });
</script>
<?php
    # get plugins
	$plugins = spa_get_plugins_list_data();

	# get update version info
	$xml = sp_load_version_xml();

    # get versions of our plugins
    $versions = array();
    $required = array();
    $folders = array();
    if ($xml) {
        foreach ($xml->plugins->plugin as $plugin) {
        	$versions[(string) $plugin->name] = (string) $plugin->version;
        	$required[(string) $plugin->name] = (string) $plugin->requires;
        	$folders[(string) $plugin->name] = (string) $plugin->folder;
        }
    }

    # check active plugins
    $invalid = sp_validate_active_plugins();
    if (!empty($invalid)) {
        foreach ($invalid as $plugin_file => $error) {
    		echo '<div id="message" class="error"><p>'.sprintf(spa_text('The plugin %1$s has been deactivated due to error: %2$s'), esc_html($plugin_file), $error->get_error_message()).'</p></div>';
        }
    }

    $ajaxURL = wp_nonce_url(SPAJAXURL.'plugins-loader&amp;saveform=list', 'plugins-loader');
	$msg = esc_js(spa_text('Are you sure you want to delete the selected Simple Press plugins?'));
?>
	<form action="<?php echo $ajaxURL; ?>" method="post" id="sppluginsform" name="sppluginsform" onsubmit="javascript: if (ActionType.options[ActionType.selectedIndex].value == 'delete-selected' || ActionType2.options[ActionType2.selectedIndex].value == 'delete-selected') {if (confirm('<?php echo $msg; ?>')) {return true;} else {return false;}} else {return true;}">
	<?php echo sp_create_nonce('forum-adminform_plugins'); ?>
<?php
	spa_paint_options_init();
	spa_paint_open_tab(spa_text('Available Plugins').' - '.spa_text('Install Simple:Press Plugins'), true);
	spa_paint_open_panel();

	spa_paint_spacer();
	echo '<div class="sfoptionerror">';
	echo spa_text('Plugins Folder').': <b>'.realpath(SF_STORE_DIR.'/'.$spPaths['plugins']).'</b>';
	echo '</div>';

	spa_paint_open_fieldset(spa_text('Plugin Management'), true, 'plugins');
?>
	<div class="tablenav top">
		<div class="alignleft actions">
			<select id="ActionType" name="action1">
				<option selected="selected" value="-1"><?php echo spa_text('Bulk Actions'); ?></option>
				<option value="activate-selected"><?php echo spa_text('Activate'); ?></option>
				<option value="deactivate-selected"><?php echo spa_text('Deactivate'); ?></option>
				<?php if (!is_multisite() || is_super_admin()) { ?><option value="delete-selected"><?php echo spa_text('Delete'); ?></option><?php }?>
			</select>
			<input id="doaction1" class="button-secondary action" type="submit" value="<?php echo spa_text('Apply'); ?>" />
		</div>
		<div class="tablenav-pages one-page">
			<span class="displaying-num"><?php echo count($plugins).' '.spa_text('plugins');?></span>
		</div>
	</div>

	<table class="wp-list-table widefat plugins">
        <thead>
		<tr>
			<td id='cb' class='manage-column column-cb check-column'>
				&nbsp;&nbsp;&nbsp;<input type="checkbox" name="cbhead" id="cbhead" />
				<label class="wp-core-ui" for='cbhead'>&nbsp;</label>
			</td>
            <th class='manage-column check-column'></th>
			<th class='manage-column column-name column-primary'>
				<?php spa_etext('Plugin'); ?>
			</th>
			<th class='manage-column column-description'>
				<?php spa_etext('Description'); ?>
			</th>
		</tr>
        </thead>

        <tbody class="the-list">
<?php
        if (empty($plugins)) echo '<tr><td colspan="4">'.spa_text('No plugins found.').'</td></tr>';

		$disabled = '';

    	foreach ((array) $plugins as $plugin_file => $plugin_data) {
            $update = (!empty($versions[$plugin_data['Name']])) ? ((version_compare($versions[$plugin_data['Name']], $plugin_data['Version'], '>') == 1)) : 0;

			# check for valid folder name
			$path = explode('/', $plugin_file);
			$bad = is_numeric(substr($path[0], -1));

    		$is_active = sp_is_plugin_active($plugin_file);

            if ($is_active) {
                $url = SFADMINPLUGINS.'&amp;action=deactivate&amp;plugin='.esc_attr($plugin_file).'&amp;title='.urlencode(esc_attr($plugin_data['Name'])).'&amp;sfnonce='.wp_create_nonce('forum-adminform_plugins');
                $actionlink = "<a href='$url' title='".spa_text('Deactivate this Plugin')."'>".spa_text('Deactivate').'</a>';
				$actionlink = apply_filters('sph_plugins_active_buttons', $actionlink, $plugin_file);
				$actionlink.= sp_paint_plugin_tip($plugin_data['Name'], $plugin_file);
				$rowClass = 'active';
                if ($update) $rowClass.= ' update';
                $icon = '<img src="'.SFADMINIMAGES.'sp_Yes.png" title="'.spa_text('Plugin activated').'" alt="" style="vertical-align:middle;" />';
            } else {
				if ($bad) {
					$rowClass = 'inactive spWarningBG';
					$actionlink = '';
	                $icon = '<img src="'.SFADMINIMAGES.'sp_NoWrite.png" title="'.spa_text('Warning').'" alt="" style="vertical-align:middle;" />';
	                $disabled = ' disabled="disabled" ';
				} else {
					$url = SFADMINPLUGINS.'&amp;action=activate&amp;plugin='.esc_attr($plugin_file).'&amp;title='.urlencode(esc_attr($plugin_data['Name'])).'&amp;sfnonce='.wp_create_nonce('forum-adminform_plugins');
					$actionlink = "<a href='$url' title='".spa_text('Activate this Plugin')."'>".spa_text('Activate')."</a>";
					$url = SFADMINPLUGINS.'&amp;action=delete&amp;plugin='.esc_attr($plugin_file).'&amp;title='.urlencode(esc_attr($plugin_data['Name'])).'&amp;sfnonce='.wp_create_nonce('forum-adminform_plugins');
					$msg = esc_js(spa_text('Are you sure you want to delete this Simple Press plugin?'));
					if (!is_multisite() || is_super_admin()) {
						$actionlink.= ' | <a href="javascript: if (confirm(\''.$msg.'\')) {window.location=\''.$url.'\';}" title="'.spa_text('Delete this Plugin').'">'.spa_text('Delete').'</a>';
					}
					$actionlink = apply_filters('sph_plugins_inactive_buttons', $actionlink, $plugin_file);
					$rowClass = 'inactive';
                    if ($update) $rowClass.= ' update';
					$icon = '<img src="'.SFADMINIMAGES.'sp_No.png" title="'.spa_text('Plugin not activated').'" alt="" style="vertical-align: middle;" />';
					$disabled = '';
				}
            }

    		$description = $plugin_data['Description'];
    		$plugin_name = $plugin_data['Name'];
?>
        	<tr class='<?php echo $rowClass; ?>'>
        		<th class='manage-column column-cb check-column' scope='row'>
					<?php
						$thisId = 'checkbox_'.rand();
					?>
					&nbsp;&nbsp;&nbsp;<input id="<?php echo($thisId); ?>" type="checkbox" value="<?php echo $plugin_file; ?>" name="checked[]" <?php echo $disabled; ?>/>
					<label for="<?php echo($thisId); ?>">&nbsp;</label>
				</th>
				<td class='manage-column check-column'>
                	<?php echo $icon; ?>
				</td>
        		<td class='manage-column column-name column-primary'>
					<strong><?php echo esc_html($plugin_name); ?></strong>
	        		<div class="row-actions-visible">
    	   				<span><?php echo str_replace('&nbsp;&nbsp;', '  |  ', $actionlink); ?></span>
        			</div>
				</td>
        		<td class='manage-column column-description'>
        			<div class='manage-column column-description'>
						<?php echo $description; ?>
					</div>
					<div class='<?php echo $rowClass; ?> second plugin-version-author-uri'>
<?php
		        		$plugin_meta = array();
		        		if (!empty($plugin_data['Version'])) $plugin_meta[] = sprintf(spa_text('Version %s'), $plugin_data['Version']);
		        		if (!empty($plugin_data['Author'])) {
		        			$author = $plugin_data['Author'];
		        			if (!empty($plugin_data['AuthorURI'])) $author = '<a href="'.esc_url($plugin_data['AuthorURI']).'" title="'.spa_text('Visit author homepage').'">'.esc_html($plugin_data['Author']).'</a>';
		        			$plugin_meta[] = sprintf(spa_text('By %s'), $author);
		        		}
		        		if (!empty($plugin_data['PluginURI'])) $plugin_meta[] = '<a href="'.esc_url($plugin_data['PluginURI']).'" title="'.spa_text('Visit plugin site').'">'.esc_html(spa_text('Visit plugin site')).'</a>';

		        		echo implode(' | ', $plugin_meta);
?>
					</div>
				</td>
        	</tr>
<?php
			# is it bad?
			if ($bad) {
                preg_match('/-?\s*\d+$/', $path[0], $fix);
                $suggest = str_replace($fix[0], '', $path[0]);
?>
				<tr class='<?php echo $rowClass; ?>'>
					<td colspan="4">
						<div class="sfoptionerror">
							<?php echo sprintf(spa_text('The folder name of this plugin has become corrupted - probably due to multiple downloads. Please remove the %s at the end of the folder name.  The proper folder name should be %s'), "<strong>$fix[0]</strong>", "<strong>$suggest</strong>"); ?>
						</div>
					</td>
				</tr>
<?php
			}

        	# any upgrade for this plugin?  in multisite only main site can update
			if (is_main_site() && $versions && $update) {
            $active = ($is_active) ? ' active' : '';
?>
				<tr class="plugin-update-tr<?php echo $active; ?>">
					<td class="plugin-update colspanchange" colspan="4">
						<div class="update-message notice inline notice-warning notice-alt">
							<?php echo spa_text('There is an update for the').' '.$plugin_data['Name'].' '.spa_text('plugin').'.<br />'; ?>
							<?php echo spa_text('Version').' '.$versions[$plugin_data['Name']].' '.spa_text('of the plugin is available').'.<br />'; ?>
							<?php echo spa_text('This newer version requires at least Simple:Press version').' '.$required[$plugin_data['Name']].'.<br />'; ?>
							<?php echo spa_text('For details, please visit').' '.SFPLUGHOME.' '.spa_text('or').' ' ?>
							<?php echo '<a href="'.self_admin_url('update-core.php').'" title="">'.spa_text('update now').'</a>.'; ?>
						</div>
					</td>
				</tr>
<?php
			}
        }
		do_action('sph_plugins_list_panel');
?>
        </tbody>

        <tfoot>
		<tr>
			<td class='manage-column column-cb check-column'>
				&nbsp;&nbsp;&nbsp;<input type="checkbox" name="cbfoot" id="cbfoot" />
				<label class="wp-core-ui" for='cbfoot'>&nbsp;</label>
			</td>
            <th class='manage-column check-column'></th>
			<th class='manage-column column-name column-primary'>
				<?php spa_etext('Plugin'); ?>
			</th>
			<th class='manage-column column-description'>
				<?php spa_etext('Description'); ?>
			</th>
		</tr>
        </tfoot>
    </table>

	<div class="tablenav bottom">
		<div class="alignleft actions">
			<select id="ActionType2" name="action2">
				<option selected="selected" value="-1"><?php echo spa_text('Bulk Actions'); ?></option>
				<option value="activate-selected"><?php echo spa_text('Activate'); ?></option>
				<option value="deactivate-selected"><?php echo spa_text('Deactivate'); ?></option>
				<?php if (!is_multisite() || is_super_admin()) { ?><option value="delete-selected"><?php echo spa_text('Delete'); ?></option><?php }?>
			</select>
			<input id="doaction2" class="button-secondary action" type="submit" value="<?php echo spa_text('Apply'); ?>" name="" />
		</div>
		<div class="tablenav-pages one-page">
			<span class="displaying-num"><?php echo count($plugins).' '.spa_text('plugins');?></span>
		</div>
	</div>
<?php
	spa_paint_close_fieldset();
	spa_paint_close_panel();
	spa_paint_close_container();
?>
	<div class="sfform-panel-spacer"></div>
<?php
	spa_paint_close_tab();
?>
	</form>
<?php
}

function sp_paint_plugin_tip($name, $file) {
    # make sure getting started help file name replacement works or bail to keep from returning the main plugin file
	if ($file == ($xfile = str_replace('-plugin.php', '-help.php', $file))) return '';
	$site = wp_nonce_url(SPAJAXURL."plugin-tip&amp;file=$xfile", 'plugin-tip');
	$atitle = spa_text('Getting Started');
	$htitle = $atitle.' - '.esc_js($name);
	$out = '<br />';
	$out.= '<a class="spLinkHighlight spOpenDialog" data-site="'.$site.'" data-label="'.$htitle.'" data-width="400" data-height="0" data-align="center"><b>'.$atitle.'</b></a>';
	return $out;
}

?>