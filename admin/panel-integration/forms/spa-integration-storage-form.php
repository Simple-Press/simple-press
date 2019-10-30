<?php
/*
Simple:Press
Admin Integration Storage Locations Form
$LastChangedDate: 2018-11-13 20:41:56 -0600 (Tue, 13 Nov 2018) $
$Rev: 15817 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_integration_storage_form() {
	?>
    <script>
        spj.loadAjaxForm('sfstorageform', 'sfreloadsl');
    </script>
<?php
	$sfdata = spa_get_storage_data();
	$sfoptions = spa_get_storage_options();

	spa_paint_options_init();

	$ajaxURL = wp_nonce_url(SPAJAXURL.'integration-loader&amp;saveform=storage', 'integration-loader');
?>
    <div id="sfhousekeepingformblock">
    <form action="<?php echo $ajaxURL; ?>" method="post" id="sfstorageform" name="sfstorage">

		<?php echo sp_create_nonce('forum-adminform_storage');

	    $info = '<div class="sf-alert-block sf-info">' .
	            sprintf(SP()->primitives->front_text('BEWARE: Please read the help before making any changes to these locations. Incorrect changes may cause Simple:Press to stop functioning'), '<b>', '</b>') .
	            '</div>';

	    spa_paint_open_tab(SP()->primitives->admin_text('Storage Locations'), true, $info);

	    spa_paint_open_panel();
		    spa_paint_open_fieldset(SP()->primitives->admin_text('Set Storage Locations'), 'true', 'storage-locations', 'true');
		echo '<div class="collapsible-closed">';
                echo '<table><tr>';
                echo '<td><span class="sf-icon sf-check" title="'.SP()->primitives->admin_text('Location found').'"></span>'
                     .SP()->primitives->admin_text('Location found').'</td>';
                echo '<td><span class="sf-icon sf-no-check" title="'.SP()->primitives->admin_text('Location not found').'"></span>'
                     .SP()->primitives->admin_text('Location not found').'</td></tr><tr>';
                echo '<td><span class="sf-icon sf-requires-enable" title="'.SP()->primitives->admin_text('Write - OK').'"></span>'
                     .SP()->primitives->admin_text('Write - OK').'</td>';
                echo '<td><span class="sf-icon sf-warning" title="'.SP()->primitives->admin_text('Write - denied').'"></span>'
                     .SP()->primitives->admin_text('Write - denied').'</td></tr><tr>';
                echo '</tr></table>';
            echo '</div>';
		echo '<br>';
	        spa_paint_close_fieldset();
	    spa_paint_close_panel();

	    echo '<br>';
	    $ok = true;

	    foreach ($sfoptions as $option){
		    spa_paint_open_panel();
		            $r = spa_paint_storage_input(SP()->primitives->admin_text($option['title']), $option['name'], $sfdata[$option['name']]);
		    spa_paint_close_panel();
		    if (!$r) $ok = false;
	    }
		do_action('sph_integration_storage_panel_location');
	    if (!$ok) {
		    echo '<tr><td colspan="3"><br /><div class="sf-alert-block sf-info"><h4>';
		    SP()->primitives->admin_etext('For Simple:Press to function correctly it is imperative that the above location errors are resolved');
		    echo '</h4></div></td></tr>';
	    }
		do_action('sph_integration_storage_panel');
	    spa_paint_close_container();
	    ?>

        <div class="sf-form-submit-bar">
            <input type="submit" class="sf-button-primary" id="saveit" name="saveit" value="<?php SP()->primitives->admin_etext('Update Storage Locations'); ?>" />
        </div>
		<?php spa_paint_close_tab(); ?>
    </form>
    </div>
    <script>
        var collapsiblebtn = "<a class=\"sf-icon-button sfToggleBtn\"><span class=\"sf-icon sf-expanded\"></span></a>";
        var questBtn = jQuery('#sfhousekeepingformblock fieldset .sf-panel-body-top-right').html();
        jQuery('#sfhousekeepingformblock fieldset .sf-panel-body-top-right').append(collapsiblebtn);
        jQuery('#sfhousekeepingformblock fieldset .sf-panel-body-top-right').children('.sfhelplink').toggleClass('hide');
        jQuery('#sfhousekeepingformblock .sf-panel-body-top').on('click', function(){
            jQuery(this).parent().parent().find('.sfToggleBtn').children().toggleClass("sf-collapsed").toggleClass("sf-expanded");
            jQuery(this).parent().children('div[class^=\"collapsible-\"]').toggleClass('collapsible-closed').toggleClass('collapsible-open');
            jQuery(this).parent().children('div:nth-child(1)').toggleClass('bg-gray');
            jQuery(this).parent().parent().find('.sfhelplink').toggleClass('hide');
        });
    </script>
	<?php
	spa_check_upgrade_error();
}

/**
 * Input label
 * @param string $label
 * Input name
 * @param string $name
 * Input value
 * @param string $value
 *
 * @param bool $na
 *
 * @return bool
 */
function spa_paint_storage_input($label, $name, $value, $na = false) {
	global $tab;
	$adminhelpfile = 'admin-integration-storage-tips';

	$path = SP_STORE_DIR . '/' . $value;

	$found = false;
	$ok = false;
	if (file_exists($path)) {
		$found = true;
		$ok = true;
	}

	if ($found)
	{
		$icon1 = '<span class="sf-icon sf-check" title="'.SP()->primitives->admin_text('Location found').'"></span>';
	} else {
		$icon1 = '<span class="sf-icon sf-no-check" title="'.SP()->primitives->admin_text('Location not found').'"></span>';
		$icon2 = '<span class="sf-icon sf-warning" title="'.SP()->primitives->admin_text('Write - denied').'"></span>';
	}

	if ($found) {
		if (is_writable($path)) {
			$icon2 = '<span class="sf-icon sf-requires-enable" title="'.SP()->primitives->admin_text('Write - OK').'"></span>';
		} else {
			$icon2 = '<span class="sf-icon sf-warning" title="'.SP()->primitives->admin_text('Write - denied').'"></span>';
			$ok = false;
		}
	}

	if ($na) {
		$icon2 = '<img src="'.SPADMINIMAGES.'sp_NA.gif" title="" alt="" class="sf-vert-align-middle" />&nbsp;&nbsp;';
		$ok = $found;
	}

	echo "<span class='sf-float-l sf-mt-15 sf-ml-10'>$icon1 $icon2 </span>";
	spa_paint_open_fieldset(SP()->primitives->admin_text($label), true, $name, true,'', $adminhelpfile);

	echo '<div class="collapsible-closed">';
        echo SP_STORE_RELATIVE_BASE;

        echo '<input type="text" class="wp-core-ui sf-width-90-per " tabindex="'.$tab.'" name="'.$name.'" value="'.esc_attr($value).'" ';
	echo '</div>';
	spa_paint_close_fieldset();
	$tab++;
	return $ok;
}

function spa_check_upgrade_error() {
	# REPORTS ERRORS IF COPY OR UNZIP FAILED ---------------

	$r = SP()->DB->table(SPOPTIONS, "option_name='spStorageInstall2'", 'row');
	($r ? $sCreate = $r->option_value : $sCreate = true);
	$r = SP()->DB->table(SPOPTIONS, "option_name='spOwnersInstall2'", 'row');
	($r ? $sOwner = $r->option_value : $sOwner = true);
	$r = SP()->DB->table(SPOPTIONS, "option_name='spCopyZip2'", 'row');
	($r ? $sCopy = $r->option_value : $sCopy = true);
	$r = SP()->DB->table(SPOPTIONS, "option_name='spUnZip2'", 'row');
	($r ? $sUnzip = $r->option_value : $sUnzip = true);

	if ($sCreate && $sCopy && $sUnzip) {
		return;
	} else {
		$image = "<img src='".SP_PLUGIN_URL."/sp-startup/install/resources/images/important.png' alt='' class='sf-float-l sf-integration-storage' />";

		echo '<h3><br />';
		SP()->primitives->admin_etext('YOU WILL NEED TO PERFORM THE FOLLOWING TASKS TO ALLOW SIMPLE:PRESS TO WORK CORRECTLY');
		echo '</h3>';

		if ($sCreate == false) {
			echo $image.'<h4>[';
			SP()->primitives->admin_etext('Storage location creation failed on upgrade');
			echo '] - ';
			SP()->primitives->admin_etext("You will need to manually create a required sub-folder in your wp-content folder named 'sp-resources'");
			echo '</h4>';
		} else if ($sOwner == false) {
			echo $image.'<h5>[';
			SP()->primitives->admin_etext('Storage location part 1 ownership failed');
			echo '] - ';
			SP()->primitives->admin_etext("We were unable to create your folders with the correct server ownership and these will need to be manually changed");
			echo '</h5>';
		}
		if ($sCopy == false) {
			echo $image.'<h4>[';
			SP()->primitives->admin_etext('Resources file failed to copy on upgrade');
			echo '] - ';
			SP()->primitives->admin_etext("You will need to manually copy the file ".SP_FOLDER_NAME."/sp-startup/install/sp-resources-install-part2.zip' to the new 'wp-content/sp-resources' folder");
			echo '</h4>';
		}
		if ($sUnzip == false) {
			echo $image.'<h4>[';
			SP()->primitives->admin_etext('Resources file failed to unzip on upgrade');
			echo '] - ';
			SP()->primitives->admin_etext("You will need to manually unzip the file 'sp-resources-install-part2.zip in the new 'wp-content/sp-resources' folder");
			echo '</h4>';
		}
	}
	SP()->options->delete('spStorageInstall2');
	SP()->options->delete('spCopyZip2');
	SP()->options->delete('spUnZip2');
}
