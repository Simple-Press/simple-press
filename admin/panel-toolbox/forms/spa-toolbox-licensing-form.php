<?php

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) {
    die('Access denied - you cannot directly call this file');
}

function spa_toolbox_licensing_form() {
	
	spa_paint_options_init();
	spa_paint_open_tab(SP()->primitives->admin_text('Licensing'), false);
	
		/* Paint Instructions...*/
		
			spa_paint_open_panel();
				spa_paint_open_fieldset(SP()->primitives->admin_text('Licensing Instructions'), true, 'licensing-instructions');
                echo '<div class="sf-form-row">';
					spa_toolbox_licensing_form_paint_instructions();
                echo '</div>';
				spa_paint_close_fieldset();
			spa_paint_close_panel();
		
		/* End Paint Instructions */
	
		/* Plugins Licensing Section */	
		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('Premium Plugins Licensing'), true, 'plugins-licensing');
            echo '<div class="sf-form-row">';
				spa_toolbox_licensing_form_paint_plugin_licenses();
            echo '</div>';
			spa_paint_close_fieldset();
		spa_paint_close_panel();
		/* End Plugins Licensing Section */

		/* Theme Licensing Section */	
		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('Premium Themes Licensing'), true, 'themes-licensing');
                echo '<div class="sf-form-row">';
                    spa_toolbox_licensing_form_paint_theme_licenses();
                echo '</div>';
			spa_paint_close_fieldset();
		spa_paint_close_panel();
		/* End Theme Licensing Section */
		
		spa_paint_tab_right_cell();
	
		/****************************************************/
		/* Paint section for forcing update checks manually */
		/****************************************************/
		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('Force an Update Check'), true, 'force-update-check');
            echo '<div class="sf-form-row">';
			echo '<div class="sf-alert-block sf-info">';
			echo SP()->primitives->admin_text('Simple:Press checks for updates once every day.');
			echo '<br />';
			echo SP()->primitives->admin_text('However, you can click the button below to check for updates to premium plugins and themes now.');
			echo '</div>';
			echo '<div class="sf-form-submit-bar">';
			echo '<input type="button" class="sf-button-primary" id="force_update_check" name="force_update_check" value="'.SP()->primitives->admin_text('Check For Updates Now').'">';
			echo '</div>';
            echo '</div>';
			spa_paint_close_fieldset();
		spa_paint_close_panel();
		/* End Paint section for forcing update checks manually */

		/********************************************************/
		/* Paint field for getting alternate license server url */
		/*******************************************************/		
		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('Licensing Server'), true, 'licensing-server');
                echo '<div class="sf-form-row">';
				$sp_addon_store_url = SP()->options->get( 'sp_addon_store_url');
				echo '<div class="sf-alert-block sf-caution">';
				echo SP()->primitives->admin_text('This field is usually blank which defaults the licensing server to simple-press.com.');
				echo('<br />');
				echo SP()->primitives->admin_text('But upon instruction by Simple:Press support staff you can use it to enter an alternative licensing server.');
				echo '</div>';
				echo '<form class="url_global">';
				echo '<table class="widefat sf-table-small sf-table-mobile">';
				echo '<tr valign="top">';
				echo '<td scope="row" class="sp_td_left" valign="top">' . SP()->primitives->admin_text('Licensing Server: ') . '</td>';
				echo '<td>';
				spa_paint_single_input('sp_licensing_server_url', $sp_addon_store_url, false, 'regular-text sp_licensing_server_url');
				echo '</td>';
				echo '</tr>';
				echo '</table>';
				echo '<div class="sf-form-submit-bar">';
				echo '<input type="submit" class="sf-button-primary" id="saveit" name="save_store_url" value="'.SP()->primitives->admin_text('Update Licensing Server').'">';
				echo '</div>';
				echo '</form>';
                echo '</div>';
			spa_paint_close_fieldset();
		spa_paint_close_panel();
		/* End paint field for alternate license server url */		
	
	spa_paint_close_tab();
}

/*
 * Paint the instructions for using this license form.
 */
function spa_toolbox_licensing_form_paint_instructions() {
    echo '<div class="sf-alert-block sf-caution">'.SP()->primitives->admin_text('Note: If you do not activate your license(s) you will not receive security and other automatic updates for your premium plugins and themes!').'</div>';
    echo '<div class="sf-licensing-instructions-wrap">'.'<h2>'.SP()->primitives->admin_text('Instructions for using this licensing screen').'</h2>';
		echo '<ul >';
			echo '<li><strong>'.SP()->primitives->admin_text('Step 1: ').'</strong>'.SP()->primitives->admin_text('Look up your license key in your ACCOUNT area on our website. License keys should also be in your purchase confirmation emails.').'</li>';
			echo '<li><strong>'.SP()->primitives->admin_text('Step 2: ').'</strong>'.SP()->primitives->admin_text('Enter your license key into the &#39;License Key&#39; field next to your products').'</li>';
			echo '<li><strong>'.SP()->primitives->admin_text('Step 3: ').'</strong>'.SP()->primitives->admin_text('Click the &#39;Activate License&#39; button next to your products').'</li>';
		echo '</ul>';
		echo SP()->primitives->admin_text('If your license key has expired, please renew your license from the ACCOUNT page on our site.');
		echo '<br/>';
		//@todo:  The string below needs to be constructed using printf so that the url can be replaced in the appropriate %s section during translation.
		echo SP()->primitives->admin_text('A license to one of our ') . '<a href="https://simple-press.com/pricing"> '.SP()->primitives->admin_text('plugin and theme bundles').'</a> ' . SP()->primitives->admin_text('grants you up-to-date access to more than 70 premium Simple:Press plugins and themes!');	
		spa_paint_hidden_input('ajax_error_message', SP()->primitives->admin_text('Something Went Wrong Please Try Again!'));
	echo '</div>';
}

/*
 * calculating the expiration date
 */
 
function spa_toolbox_calculating_expiration_date($license_info){
	
	$total_days = -1;
	$get_expiredate =  date('Y-m-d', strtotime($license_info->expires));
	$warn_expiredate = date('Y-m-d', strtotime(' + 3 days'));
	if($warn_expiredate >= $get_expiredate){
		$expire_date = date('Y-m-d', strtotime($license_info->expires)); 
		$today_date = date('Y-m-d');
		$total_days =  round(($expire_date - $today_date)/(60 * 60 * 24));
		if($total_days < 0){
			$total_days = 0;		
		}
	}
	return $total_days;
}


/*
 * function for paint common addons key license form
 */
 
function spa_toolbox_licensing_key_common($type, $get_key, $addon_data, $total_days, $license_status, $license_info, $sp_addon_name){
	
	if($type == 'plugins'){
		$ajaxURL = wp_nonce_url(SPAJAXURL.'toolbox-loader&amp;saveform=licensing', 'toolbox-loader');
		$classname = 'plugins_check';
		$form_name = 'plugins';
		$sp_item = 'sp_check_plugin';
		$sp_item_name = $addon_data['Name'];
		$sp_item_id = $addon_data['ItemId'];
	}else{
		$ajaxURL = wp_nonce_url(SPAJAXURL.'license-check&amp;saveform=licence_them', 'license-check');
		$classname = 'themes_check';
		$form_name = 'themes';
		$sp_item = 'sp_check_theme';
		$sp_item_name = $addon_data['Name'];
		$sp_item_id = $addon_data['ItemId'];
	}
	
	$button_id 	= $sp_addon_name;
?>	
    <h3><?php echo $sp_item_name; ?></h3>

    <form method="post" action="<?php echo $ajaxURL; ?>" class="<?php echo $classname; ?>" name="<?php echo $form_name; ?>">

        <?php
        spa_paint_hidden_input('sp_item', $sp_item);
        spa_paint_hidden_input('sp_item_name', $sp_item_name);
        spa_paint_hidden_input('sp_item_id', $sp_item_id);
        echo sp_create_nonce('forum-adminform_licensing');
        ?>

        <div class="licensing-wrapper">
            <div class="licensing-wrapper-tools">
                <div>
                    <?php
                        $css_classes = "regular-text sp_addon_license_key";
                        spa_paint_single_input('sp_addon_license_key', ($get_key && $get_key != '') ? $get_key : '', false, $css_classes, $sp_item_name . ' ' .SP()->primitives->admin_text('License Key') );
                    ?>
                </div>
                <div>
                    <?php if( $license_status !== false && $license_status == 'valid' ) { ?>
                        <input type="submit" class="sf-button-secondary" id="<?php echo $button_id; ?>" name="SP_license_deactivate" value="<?php echo SP()->primitives->admin_text('Deactivate License'); ?>"/>
                    <?php } else { ?>
                        <input type="submit" class="sf-button-secondary" id="<?php echo $button_id; ?>" name="SP_license_activate" value="<?php echo SP()->primitives->admin_text('Activate License'); ?>"/>
                    <?php } ?>

                    <?php if($license_status != 'valid' && $get_key != '') {
                        echo '<input type="submit" class="sf-button-secondary '.$button_id.'" name="SP_license_remove" value="'.SP()->primitives->admin_text('Delete License Key').'"/>';
                    } ?>
                </div>
            </div>


            <?php if ( $get_key === false || $get_key === '' ) {
                # Don't show error messages if the user has not entered anything
            } else if ( $get_key && $license_status !== false && ($license_status == 'valid' || $license_status == 'expired') ) {
                if($license_status == 'expired'){
                    echo '<div class="license-info"><span class="sf-icon sf-no-check" title="' . SP()->primitives->admin_text('License expired') . '"></span> ' . SP()->primitives->admin_text('Your License is expired please renew your license now.') . '</div>' ;
                }else{
                    echo '<div class="license-info"><span class="sf-icon sf-check" title="' . SP()->primitives->admin_text('License active') . '"></span> ' . SP()->primitives->admin_text('License active') . '</div>';
                }
            } else {
                echo '<div  class="license-info"><span class="sf-icon sf-no-check" title="' . SP()->primitives->admin_text('License invalid') . '"></span> ' . SP()->primitives->admin_text('Your license key seems to be inactive or invalid. Please enter your license key above and click the Activate button below.') . '</div>';
            } ?>

            <?php if( $license_status !== false && $license_status == 'valid' && !empty($license_info) ) { ?>
                <div>
                    <h4><?php echo SP()->primitives->admin_text('License Information'); ?></h4>
                    <div class="sf-ml-10">
                        <?php
							echo SP()->primitives->admin_text('License Limit:') . ' ';
							echo (isset($license_info->license_limit) && $license_info->license_limit == 0) ? SP()->primitives->admin_text('Unlimited') : $license_info->license_limit.' '.SP()->primitives->admin_text('Site(s)');
							echo '<br/>';
							echo SP()->primitives->admin_text('Active Site(s): ');
							echo isset($license_info->site_count) ? $license_info->site_count : 'N/A';
							echo '<br/>';
							echo SP()->primitives->admin_text('Site Activations Remaining : ');
							echo isset($license_info->activations_left) ? ucfirst($license_info->activations_left) : 'N/A';
							echo '<br/>';
							echo SP()->primitives->admin_text('Valid Until: ');
							echo (isset($license_info->expires) && $license_info->expires == 'lifetime') ? SP()->primitives->admin_text('Lifetime') : date('d M, Y', strtotime($license_info->expires));
						?>
                    </div>
                </div>
            <?php } ?>





			</div>
		</form>

<?php	
}

/*
 * Paint the input boxes for plugin licenses
 */
function spa_toolbox_licensing_form_paint_plugin_licenses() {

	$ajaxURLPlugin = wp_nonce_url(SPAJAXURL.'toolbox-loader&amp;saveform=licensing', 'toolbox-loader');
	$plugins = SP()->plugin->get_list();
	$count_plugins = 0;

	foreach ($plugins as $plugin_file => $plugin_data) {
		
		$is_active = SP()->plugin->is_active($plugin_file);
		
		if ($is_active && isset($plugin_data['ItemId']) && $plugin_data['ItemId'] != '') {
			
			$sp_plugin_name = sanitize_title_with_dashes($plugin_data['Name']);
			
			if ($sp_plugin_name && $sp_plugin_name != '') {
				
				$count_plugins++;
				$get_key = SP()->options->get( 'spl_plugin_key_'.$plugin_data['ItemId']);
				$license_status = SP()->options->get('spl_plugin_stats_'.$plugin_data['ItemId']);
				$license_info 	= SP()->options->get('spl_plugin_info_'.$plugin_data['ItemId']);
				$license_info	= json_decode($license_info);
				$total_days = -1;
				if(isset($license_info) && $license_info != '' && isset($license_info->expires) && ('lifetime' <> $license_info->expires) ){
					$total_days = spa_toolbox_calculating_expiration_date($license_info);
				}
				
				spa_toolbox_licensing_key_common('plugins', $get_key, $plugin_data, $total_days, $license_status, $license_info, $sp_plugin_name);
			}
		}
	}

	/* Show message if there are no plugins */
	if($count_plugins < 1){
		echo '<table class="form-table">';
			echo '<tr valign="top">';
				echo '<div class="sf-alert-block sf-info sp_addons_not_found">';
					echo SP()->primitives->admin_text('There are no items activated that require a license key at this time');
				echo '</div>';
			echo '</tr>';
		echo '</table>';	
	}
}

/*
 * Paint the input boxes for theme licenses
 */
function spa_toolbox_licensing_form_paint_theme_licenses() {

	$ajaxURLTheme = wp_nonce_url(SPAJAXURL.'license-check&amp;saveform=licence_them', 'license-check');
	$themes = SP()->theme->get_list();
	$count_themes = 0;

	foreach ($themes as $theme_file => $theme_data) {
		
		$sp_theme_name = sanitize_title_with_dashes($theme_data['Name']);
		
		if ($sp_theme_name && $sp_theme_name != '' && isset($theme_data['ItemId']) && $theme_data['ItemId'] != '') {
			
			$count_themes++;
			$get_key = SP()->options->get( 'spl_theme_key_'.$theme_data['ItemId']);
			$license_status = SP()->options->get('spl_theme_stats_'.$theme_data['ItemId']);
			$license_info 	= SP()->options->get('spl_theme_info_'.$theme_data['ItemId']);
			$license_info	= json_decode($license_info);
			$total_days = -1;
			if(isset($license_info) && $license_info != '' && isset($license_info->expires)){
				$total_days = spa_toolbox_calculating_expiration_date($license_info);
			}
		
			spa_toolbox_licensing_key_common('themes', $get_key, $theme_data, $total_days, $license_status, $license_info, $sp_theme_name);
		}
	}
		
	if($count_themes < 1){
		echo '<table class="form-table">';
			echo '<tr valign="top">';
				echo '<div class="sf-alert-block sf-info sp_addons_not_found">';
					echo SP()->primitives->admin_text('There are no items activated that require a license key at this time');
				echo '</div>';	
			echo '</tr>';
		echo '</table>';	
	}	
}