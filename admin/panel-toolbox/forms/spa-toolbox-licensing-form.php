<?php
/*
Simple:Press
Admin Toolbox Licensing Form
$LastChangedDate: 2019-01-30 16:40:00 -0600 (Wed, 30 Jan 2019) $
$Rev: 15601 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_toolbox_licensing_form() {
	
	spa_paint_options_init();
	spa_paint_open_tab(SP()->primitives->admin_text('Toolbox').' - '.SP()->primitives->admin_text('Licensing'), true);
	
	/* Paint Instructions...*/
	echo "<div class='sp-licensing-instructions-tab'>";
		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('Licensing Instructions'), true, 'licensing-instructions');			
				spa_toolbox_licensing_form_paint_instructions();
			spa_paint_close_fieldset();
		spa_paint_close_panel();
	echo"</div>";
	/* End Paint Instructions */
	
	/* Plugins Licensing Section */	
	spa_paint_open_panel();
		spa_paint_open_fieldset(SP()->primitives->admin_text('Plugins Licensing'), true, 'plugins-licensing');
			spa_toolbox_licensing_form_paint_plugin_licenses();
		spa_paint_close_fieldset();
	spa_paint_close_panel();
	/* End Plugins Licensing Section */

	/* Theme Licensing Section */	
	spa_paint_open_panel();
		spa_paint_open_fieldset(SP()->primitives->admin_text('Themes Licensing'), true, 'themes-licensing');
			spa_toolbox_licensing_form_paint_theme_licenses();	
		spa_paint_close_fieldset();
	spa_paint_close_panel();
	/* End Theme Licensing Section */
	
	/****************************************************/
	/* Paint section for forcing update checks manually */
	/****************************************************/
	spa_paint_open_panel();
		spa_paint_open_fieldset(SP()->primitives->admin_text('Force an Update Check'), true, 'force-update-check');

		echo SP()->primitives->admin_text('Simple:Press checks for updates once every day.');
		echo '<br />';
		echo SP()->primitives->admin_text('However, you can click the button below to check for updates to premium plugins and themes now.');

		echo '<div class="sfform-submit-bar" style="margin-bottom: 6px;">';
		echo '<input type="button" class="button-primary" id="force_update_check" name="force_update_check" value="'.SP()->primitives->admin_text('Check For Updates Now').'">';
		echo '</div>';
	
		spa_paint_close_fieldset();
	spa_paint_close_panel();
	/* End Paint section for forcing update checks manually */

	/********************************************************/
	/* Paint field for getting alternate license server url */
	/*******************************************************/		
	spa_paint_open_panel();
		spa_paint_open_fieldset(SP()->primitives->admin_text('Licensing Server'), true, 'licensing-server');
			$sp_addon_store_url = SP()->options->get( 'sp_addon_store_url');
			
			echo SP()->primitives->admin_text('This field is usually blank which defaults the licensing server to simple-press.com.');
			echo('<br />');
			echo SP()->primitives->admin_text('But upon instruction by Simple:Press support staff you can use it to enter an alternative licensing server.');
			echo '<form class="url_global" style="margin:0px 0px 30px 0px;">';
			echo '<table class="form-table">';
			echo '<tr valign="top">';
			echo '<th scope="row" style="width:20%;border-bottom: 1px solid #ddd;" valign="top">' . SP()->primitives->admin_text('Licensing Server: ') . '</th>';
			echo '<td><input name="sp_sample_store_url" type="text" class="regular-text sp_sample_store_url" value="'.$sp_addon_store_url.'"></td>';
			echo '</tr>';
			echo '</table>';
			echo '<div class="sfform-submit-bar">
			<input type="submit" class="button-primary" id="saveit" name="save_store_url" value="Update Licensing Server">
			</div>';
			echo '</form>';
		spa_paint_close_fieldset();
	spa_paint_close_panel();
	/* End paint field for alternate license server url */		
	
}

/*
 * Paint the instructions for using this license form.
 */
function spa_toolbox_licensing_form_paint_instructions() {
	echo '<div class="sf-licensing-instructions-wrap">'.'<h2>'.SP()->primitives->admin_text('Instructions for using this licensing screen').'</h2>';
		echo '<ul class="licensing_note_list" style="margin:10px;">';
			echo '<li><strong>'.SP()->primitives->admin_text('Step 1: ').'</strong>'.SP()->primitives->admin_text('Look up your license key in your ACCOUNT area on our website. License keys should also be in your purchase confirmation emails.').'</li>';		
			echo '<li><strong>'.SP()->primitives->admin_text('Step 2: ').'</strong>'.SP()->primitives->admin_text('Enter your license key into the &#39;License Key&#39; field next to your products').'</li>';
			echo '<li><strong>'.SP()->primitives->admin_text('Step 3: ').'</strong>'.SP()->primitives->admin_text('Click the &#39;Activate License&#39; button next to your products').'</li>';			
		echo '</ul>';
		echo SP()->primitives->admin_text('Click the HELP icon in the upper right of this panel for more information about licenses.');
		echo '<br/>';
		echo '<br/>';
		echo SP()->primitives->admin_text('If your license key has expired, please renew your license from the ACCOUNT page on our site.');	
		echo '<br/>';
		//@todo:  The string below needs to be constructed using printf so that the url can be replaced in the appropriate %s section during translation.
		echo SP()->primitives->admin_text('A license to one of our ') . '<a href="https://simple-press.com/pricing">plugin and theme bundles</a> ' . SP()->primitives->admin_text('grants you up-to-date access to more than 70 premium Simple:Press plugins and themes!');	
		echo '<br/>';
	echo '</div>';
	spa_paint_spacer();
	echo '<div class="sfoptionerror">'.SP()->primitives->admin_text('Note: If you do not activate your license(s) you will not receive security and other automatic updates for your premium plugins and themes!').'</div>';	
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
				$get_key = SP()->options->get( 'plugin_'.$sp_plugin_name);
				$license_status = SP()->options->get('spl_plugin_stats_'.$sp_plugin_name);
				$license_info 	= SP()->options->get('spl_plugin_info_'.$sp_plugin_name);
				$license_info	= json_decode($license_info);

				$button_id 	= $sp_plugin_name;
				$total_days = -1;
				
				if(isset($license_info) && $license_info != '' && isset($license_info->expires)){
					
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
				}
			?>
			<div class="sffieldset">
				<div class="plugin_title" style="font-size: 17px;color: #0073aa;font-weight: 600;margin-bottom: 10px;"><?php echo $plugin_data['Name']; ?></div>
				
				<form method="post" action="<?php echo $ajaxURLPlugin; ?>" class="plugins_check" name="plugins">
					<input name="sp_itemn" type="hidden" class="regular-text sp_sample_license_key" value="sp_check_pugin" />
					<input name="sp_item_name" type="hidden" class="regular-text sp_item_name" value="<?php echo $plugin_data['Name']; ?>" />
					<input name="sp_item_id" type="hidden" class="regular-text sp_item_id" value="<?php echo $plugin_data['ItemId']; ?>" />
					
					<?php settings_fields('sp_sample_license'); ?>
					<?php echo sp_create_nonce('forum-adminform_housekeeping'); ?>
					
					<table class="form-table">
						<tbody>
							<tr valign="top">
								<th scope="row" valign="top" style="width:20%;">
									<?php _e('License Key'); ?>
								</th>
								<td>
									<input name="sp_sample_license_key" type="text" class="regular-text sp_sample_license_key" value="<?php if($get_key && $get_key != ''){ echo $get_key;} ?>" />
									<?php if( $license_status !== false && $license_status == 'valid' ) {
									
										if($total_days >= 0){
											echo '<span style="color:green;">';
											echo SP()->primitives->admin_text('License key is active');
											echo '</span>';

											echo '<span style="color:red;">';
											echo SP()->primitives->admin_text(' Your License is expire in '.$total_days.' day(s) please renew your license now. ');
											echo '</span>';
										}elseif($total_days == 'over'){
											echo '<span style="color:red;">';
											echo SP()->primitives->admin_text(' Your License is expired please renew your license now. ');
											echo '</span>';
										}else{
											echo '<span style="color:green;">';
											echo SP()->primitives->admin_text('License key is active');
											echo '</span>';
										}
									
									}else {
										echo '<label class="description" for="sp_sample_license_key">';
										echo SP()->primitives->admin_text('Enter your license key');
										echo '</label>';
									} ?>
								</td>
							</tr>
							<?php if( $license_status !== false && $license_status == 'valid' && !empty($license_info) ) { ?>
							<tr>
								<th valign="top"><?php echo SP()->primitives->admin_text('License Information'); ?></th>
								<td style="font-weight: 600; line-height: 18px;font-size: 12px;">
								<?php echo SP()->primitives->admin_text('License Limit: '); ?><?php echo (isset($license_info->license_limit) && $license_info->license_limit == 0) ? 'Unlimited' : $license_info->license_limit.' Sites'; ?> <br/>
								<?php echo SP()->primitives->admin_text('Total Active Site(s): '); ?><?php echo isset($license_info->site_count) ? $license_info->site_count : 'N/A'; ?> <br/>
								<?php echo SP()->primitives->admin_text('Activations or Sites Remaining: '); ?><?php echo isset($license_info->activations_left) ? ucfirst($license_info->activations_left) : 'N/A'; ?> <br/>
								<?php echo SP()->primitives->admin_text('License Valid Until : '); ?><?php echo (isset($license_info->expires) && $license_info->expires == 'lifetime') ? 'Lifetime' : date('d M, Y', strtotime($license_info->expires)); ?>
								</td>
							</tr>
							<?php } ?>
							<tr valign="top">
								<th scope="row" valign="top" style="border-bottom: 1px solid #ddd;">
									<?php if( $license_status !== false && $license_status == 'valid' ) { ?>
										<?php echo SP()->primitives->admin_text('Deactivate License'); ?>
									<?php } else { ?>
										<?php echo SP()->primitives->admin_text('Activate License'); ?>
									<?php } ?>
								</th>
								<td>
									<?php if( $license_status !== false && $license_status == 'valid' ) { ?>
										<span style="color:green;"><?php echo SP()->primitives->admin_text('Active'); ?></span>
										<input type="submit" class="button-secondary SP_license_deactivate" id="<?php echo $button_id; ?>" name="SP_license_deactivate" value="<?php echo SP()->primitives->admin_text('Deactivate License'); ?>"/>
									<?php } else { ?>
										<input type="submit" class="button-secondary SP_license_activate" id="<?php echo $button_id; ?>" name="SP_license_activate" value="<?php echo SP()->primitives->admin_text('Activate License'); ?>"/>
									<?php } ?>
								</td>
							</tr>
						</tbody>
					</table>
				</form>
			</div>
			<?php
			}
		}
	}

	/* Show message if there are no plugins */
	if($count_plugins < 1){
		echo '<table class="form-table">';
			echo '<tr valign="top">';
				echo '<div class="sfoptionerror" style="margin-left: 0px;">';
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
			
			$get_key = SP()->options->get( 'theme_'.$sp_theme_name);
			$license_status = SP()->options->get('spl_theme_stats_'.$sp_theme_name);
			$license_info 	= SP()->options->get('spl_theme_info_'.$sp_theme_name);
			$license_info	= json_decode($license_info);

			$button_id 	= $sp_theme_name;
			$total_days = -1;
			$count_themes++;
			
			if(isset($license_info) && $license_info != '' && isset($license_info->expires)){
				
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
			}
		?>
		<div class="sffieldset">		
			<div class="plugin_title" style="font-size: 17px;color: #0073aa;font-weight: 600;margin-bottom: 10px;"><?php echo $theme_data['Name']; ?></div>
			
			<form method="post" action="<?php echo $ajaxURLTheme; ?>" class="themes_check" name="themes">
				<input name="sp_itemn" type="hidden" class="regular-text sp_check_theme" value="sp_check_theme" />
				<input name="sp_item_name" type="hidden" class="regular-text sp_item_name" value="<?php echo $theme_data['Name']; ?>" />
				<input name="sp_item_id" type="hidden" class="regular-text sp_item_id" value="<?php echo $theme_data['ItemId']; ?>" />
				
				<?php settings_fields('sp_sample_license'); ?>
				
				<table class="form-table">
					<tbody>
						<tr valign="top">
							<th scope="row" valign="top" style="width:20%;">
								<?php _e('License Key'); ?>
							</th>
							<td>
								<input name="sp_sample_license_key" type="text" class="regular-text sp_sample_license_key" value="<?php if($get_key && $get_key != ''){ echo $get_key;} ?>" />
								<?php if( $license_status !== false && $license_status == 'valid' ) {
									
									if($total_days >= 0){
										
										echo '<span style="color:green;">';
										echo SP()->primitives->admin_text('License key is active');
										echo '</span>';

										echo '<span style="color:red;">';
										echo SP()->primitives->admin_text('Your License is expire in '.$total_days.' days please renew your license now');
										echo '</span>';

									}elseif($total_days == 'over'){
										
										echo ' <span style="color:red;">'. SP()->primitives->admin_text('Your License is expired please renew your license now'). '</span>';
										
									}else{
										
										echo '<span style="color:green;">'. SP()->primitives->admin_text('License key is active') .'</span>';
									}
								
								}else {
									echo '<label class="description" for="sp_sample_license_key">'. SP()->primitives->admin_text('Enter your license key').'</label>';
								} ?>
							</td>
						</tr>
						<?php if( $license_status !== false && $license_status == 'valid' && !empty($license_info) ) { ?>
						<tr>
							<th valign="top">License Information</th>
							<td style="font-weight: 600; line-height: 18px;font-size: 12px;">
							<?php echo SP()->primitives->admin_text('License Limit :'); ?><?php echo (isset($license_info->license_limit) && $license_info->license_limit == 0) ? 'Unlimited' : $license_info->license_limit.' Sites'; ?> <br/>
							<?php echo SP()->primitives->admin_text('Active Site(s) : '); ?><?php echo isset($license_info->site_count) ? $license_info->site_count : 'N/A'; ?> <br/>
							<?php echo SP()->primitives->admin_text('Activations Left Site(s) : '); ?><?php echo isset($license_info->activations_left) ? ucfirst($license_info->activations_left) : 'N/A'; ?> <br/>
							<?php echo SP()->primitives->admin_text('Valid Upto : '); ?><?php echo (isset($license_info->expires) && $license_info->expires == 'lifetime') ? 'Lifetime' : date('d M, Y', strtotime($license_info->expires)); ?>
							</td>
						</tr>
						<?php } ?>
						<tr valign="top">
							<th scope="row" valign="top" style="border-bottom: 1px solid #ddd;">
								<?php if( $license_status !== false && $license_status == 'valid' ) { ?>
									<?php echo SP()->primitives->admin_text('Deactivate License'); ?>
								<?php } else { ?>
									<?php echo SP()->primitives->admin_text('Activate License'); ?>
								<?php } ?>
							</th>
							<td>
								<?php if( $license_status !== false && $license_status == 'valid' ) { ?>
									<span style="color:green;"><?php echo SP()->primitives->admin_text('Active'); ?></span>
									<input type="submit" class="button-secondary" id="<?php echo $button_id; ?>" name="SP_license_deactivate" value="<?php echo SP()->primitives->admin_text('Deactivate License'); ?>"/>
								<?php } else {
									?>
									<input type="submit" class="button-secondary" id="<?php echo $button_id; ?>" name="SP_license_activate" value="<?php echo SP()->primitives->admin_text('Activate License'); ?>"/>
								<?php } ?>
							</td>
						</tr>
					</tbody>
				</table>
			</form>
		</div>
		<?php
	
		}
	}
		
	if($count_themes < 1){
		echo '<table class="form-table">';
			echo '<tr valign="top">';
				echo '<div class="sfoptionerror" style="margin-left: 0px;">';
					echo SP()->primitives->admin_text('There are no items activated that require a license key at this time');
				echo '</div>';	
			echo '</tr>';
		echo '</table>';	
	}	
}

