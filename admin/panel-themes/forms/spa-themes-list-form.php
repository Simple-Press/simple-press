<?php

if ( ! defined( 'ABSPATH' ) ) {
	die('Access denied - you cannot directly call this file');
}

function spa_themes_list_form() {
	global $adminhelpfile;
	// get current theme
	$curTheme = SP()->options->get( 'sp_current_theme' );

	// get themes
	$themes         = SP()->theme->get_list();
	$numThemes      = count( $themes );
	$numThemesChild = 0;
	foreach ( (array) $themes as $theme_file => $theme_data ) {
		if ( ! empty( $theme_data['Parent'] ) ) {
			$numThemesChild++;
		}
	}
	// get update version info
	$xml = sp_load_version_xml();

	spa_paint_options_init();
	spa_paint_open_tab( esc_html( SP()->primitives->admin_text( 'Available Themes' ) ), true );
	spa_paint_open_panel();

	spa_paint_spacer();
	$strspace   = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
	$stroutname = esc_html( SP()->primitives->admin_text( 'All' ) ) . '(' . $numThemes . ')';
	$strout     = "<a href='" . esc_url( add_query_arg( 'themegroup', 'all', esc_url( SPADMINTHEMES ) ) ) . "'>$stroutname</a>" . $strspace;
	$stroutname = esc_html( SP()->primitives->admin_text( 'Core' ) ) . '(' . ( $numThemes - $numThemesChild ) . ')';
	$strout    .= "<a href='" . esc_url( add_query_arg( 'themegroup', 'core', esc_url( SPADMINTHEMES ) ) ) . "'>$stroutname</a>" . $strspace;
	$stroutname = esc_html( SP()->primitives->admin_text( 'Child' ) ) . '(' . $numThemesChild . ')';
	$strout    .= "<a href='" . esc_url( add_query_arg( 'themegroup', 'child', esc_url( SPADMINTHEMES ) ) ) . "'>$stroutname</a>" . $strspace;
	spa_paint_open_panel();
	?>

  <fieldset class="sf-fieldset">
      <form id="theme-filter" method="get" action="<?php echo esc_url( SPADMINTHEMES ); ?>">
          <input type="hidden" name="page" value="<?php echo esc_attr( SP_FOLDER_NAME . '/admin/panel-themes/spa-themes.php' ); ?>" />
          <div class="flex">
              <div class="maxWidth">
                  <?php echo wp_kses_post( $strout );	?>
              </div>
              <div>
                  <p class="search-box">
                      <label class="screen-reader-text" for="<?php echo esc_attr( 'search_id-search-input' ); ?>">Search Themes:</label>
                      <input type="search" id="<?php echo esc_attr( 'search_id-search-input' ); ?>" name="s" value="<?php _admin_search_query(); ?>" />
                  </p>
              </div>
              <div>
                  <?php
                  echo wp_kses_post( spa_paint_help( 'themes', $adminhelpfile ) );
                  ?>
              </div>
          </div>

      </form>
  </fieldset>
	<?php
	spa_paint_close_panel();

	$ajaxURThem = wp_nonce_url( SPAJAXURL . 'license-check', 'license-check' );
	?>

	<h3><?php echo esc_html( SP()->primitives->admin_text( 'Available Themes' ) ); ?></h3>
	<?php
	$numThemes = count( $themes );
	if ( $numThemes > 1 ) {
		?>
		<div class="theme-browser rendered">
			<div class="spThemeContainer">
			<div id="current-theme" class="spTheme">
				<div class="spThemeInner">
		<?php
		if ( file_exists( SPTHEMEBASEDIR . $curTheme['theme'] . '/styles/' . $curTheme['style'] ) ) {
			?>
			<h3 class="theme-name"><?php echo esc_html( $themes[ $curTheme['theme'] ]['Name'] ) . ' ' . esc_html( $themes[ $curTheme['theme'] ]['Version'] ); ?></h3>

			<div><img src="<?php echo esc_url( SPTHEMEBASEURL . $curTheme['theme'] . '/' . $themes[ $curTheme['theme'] ]['Screenshot'] ); ?>" alt="" /></div>

			<?php
			if ( ! empty( $curTheme['parent'] ) ) {
				if ( file_exists( SPTHEMEBASEDIR . $curTheme['parent'] ) ) {
                    echo '<div class="sf-alert-block sf-info">';
                        echo esc_html( SP()->primitives->admin_text( 'This theme is a child theme of ' ) ) . '<b>' . esc_html( $curTheme['parent'] ) . '</b>';
					echo '</div>';
				} else {
                    echo '<div class="sf-alert-block sf-caution">';
                        echo '<b>' . esc_html( SP()->primitives->admin_text( 'The specified parent theme' ) ) . " '" . esc_html( $curTheme['parent'] ) . "' " . esc_html( SP()->primitives->admin_text( 'does not exist' ) ) . '</b> ';
					echo '</div>';
				}
			}
			?>
			
			<p class="sf-description" style="">
				<?php echo wp_kses_post( $themes[ $curTheme['theme'] ]['Description'] ); ?>
			</p><br/>
			
			<?php
			$overlays = SP()->theme->get_overlays( SPTHEMEBASEDIR . $curTheme['theme'] . '/styles/overlays' );

			// pull in parent overlays if child theme
			if ( ! empty( $curTheme['parent'] ) ) {
				$parent_overlays = SP()->theme->get_overlays( SPTHEMEBASEDIR . $curTheme['parent'] . '/styles/overlays' );
				$overlays        = array_merge( $overlays, $parent_overlays );
				$overlays        = array_unique( $overlays );
			}
			?>
			
			<?php
			if ( ! empty( $overlays ) ) {
				echo '<div class="action-links">';
				?>
				<script>
						spj.loadAjaxForm('sftheme-<?php echo esc_js( $curTheme['theme'] ); ?>', 'sfreloadtlist');
				</script>
				
				<?php
				$ajaxURL = wp_nonce_url( SPAJAXURL . 'themes-loader&amp;saveform=theme', 'themes-loader' );
				echo '<form action="' . esc_url( $ajaxURL ) . '" method="post" id="sftheme-' . esc_attr( $curTheme['theme'] ) . '" name="sftheme-' . esc_attr( $curTheme['theme'] ) . '">';
				sp_echo_create_nonce( 'forum-adminform_themes' );
				echo '<input type="hidden" name="theme" value="' . esc_attr( $curTheme['theme'] ) . '" />';
				echo '<input type="hidden" name="style" value="' . esc_attr( $themes[ $curTheme['theme'] ]['Stylesheet'] ) . '" />';
				echo '<input type="hidden" name="parent" value="' . esc_attr( $curTheme['parent'] ) . '" />';

				echo '<input type="hidden" name="default-color" value="' . esc_attr( $overlays[0] ) . '" />';
				?>

				<div class="currentTheme">
					<span class="sf-icon sf-check"></span>
					<span>Current Theme</span>
				</div>

				<?php
				$style = ( count( $overlays ) > 1 ) ? 'style="display:block"' : 'style="display:none"';
				echo '<label>' . esc_html( SP()->primitives->admin_text( 'Select Overlay' ) ) . ': ' . '</label>';
				echo '<select name="color-' . esc_attr( $curTheme['theme'] ) . '">';
				foreach ( $overlays as $overlay ) {
					$overlay  = trim( $overlay );
					$selected = ( $curTheme['color'] == $overlay ) ? ' selected="selected" ' : '';
					echo '<option' . $selected . ' value="' . esc_attr( $overlay ) . '">' . esc_html( $overlay ) . '</option>';
				}
				echo '</select> ';
				echo ' <input type="submit" class="currentThemeUpdate sf-button-secondary action" id="update" name="update" value="' . esc_attr( SP()->primitives->admin_text( 'Update Overlay' ) ) . '" />';
				echo '</form>';
				echo '</div>';
				?>
				<?php
			}
		} else {
			echo '<h4>' . esc_html( SP()->primitives->admin_text( 'The current theme stylesheet' ) ) . ':<br /><br />' . esc_html( SPTHEMEBASEDIR . $curTheme['theme'] . '/styles/' . $curTheme['style'] ) . '<br /><br />' . esc_html( SP()->primitives->admin_text( 'cannot be found. Please correct or select a new theme for proper operation.' ) ) . '</h4>';
		}
		?>
		
		</div>
	</div>

		<?php
		foreach ( (array) $themes as $theme_file => $theme_data ) {
			if ( $theme_file == $curTheme['theme'] ) {
				continue;
			}

			$theme_desc    = $theme_data['Description'];
			$theme_name    = $theme_data['Name'];
			$theme_version = $theme_data['Version'];
			$theme_author  = $theme_data['Author'];
			$theme_uri     = $theme_data['AuthorURI'];
			$theme_style   = $theme_data['Stylesheet'];
			if ( isset( $_REQUEST['s'] ) && strlen( trim( $_REQUEST['s'] ) ) && strripos( $theme_name, $_REQUEST['s'] ) === false ) {
				continue;
			}
			$theme_image    = SPTHEMEBASEURL . $theme_file . '/' . $theme_data['Screenshot'];
			$theme_overlays = SP()->theme->get_overlays( SPTHEMEBASEDIR . $theme_file . '/styles/overlays' );

			if ( ! empty( $theme_data['Parent'] ) ) {
				if ( isset( $_REQUEST['themegroup'] ) && trim( $_REQUEST['themegroup'] ) === 'core' ) {
					continue;
				}
				$parent_overlays = SP()->theme->get_overlays( SPTHEMEBASEDIR . $theme_data['Parent'] . '/styles/overlays' );
				$theme_overlays  = array_merge( $theme_overlays, $parent_overlays );
			} else {
				if ( isset( $_REQUEST['themegroup'] ) && trim( $_REQUEST['themegroup'] ) === 'child' ) {
					continue;
				}
			}
			?>
		<div class="spTheme">
			<div class="spThemeInner">
			<h3 class="theme-name"><?php echo esc_html( $theme_name ) . ' ' . esc_html( $theme_version ); ?></h3>
			<div><img alt="" src="<?php echo esc_url( $theme_image ); ?>" /></div>

			<?php
			if ( ! empty( $theme_data['Parent'] ) ) {
				if ( file_exists( SPTHEMEBASEDIR . $theme_data['Parent'] ) ) {
                    echo '<div class="sf-alert-block sf-info">';
                        echo esc_html( SP()->primitives->admin_text( 'This theme is a child theme of ' ) ) . '<b>' . esc_html( $theme_data['Parent'] ) . '</b>';
                    echo '</div>';
				} else {
                    echo '<div class="sf-alert-block sf-caution">';
                        echo '<b>' . esc_html( SP()->primitives->admin_text( 'The specified parent theme' ) ) . " '" . esc_html( $theme_data['Parent'] ) . "' " . esc_html( SP()->primitives->admin_text( 'does not exist' ) ) . '</b> ';
					echo '</div>';
				}
			}
			?>
		<p class="sf-description" style="">
			<?php echo wp_kses_post( $theme_desc ); ?>
		</p>
		<br />
		<div class="action-links">
			<script>
				spj.loadAjaxForm('sftheme-<?php echo esc_js( $theme_file ); ?>', 'sfreloadtlist');
			</script>
			<?php $ajaxURL = wp_nonce_url( SPAJAXURL . 'themes-loader&amp;saveform=theme', 'themes-loader' ); ?>
			<?php $msg = SP()->primitives->admin_text( 'Are you sure you want to delete this Simple Press theme?' ); ?>
			<form action="<?php echo esc_url( $ajaxURL ); ?>" method="post" id="sftheme-<?php echo esc_attr( $theme_file ); ?>" name="sftheme-<?php echo esc_attr( $theme_file ); ?>" >
				<?php sp_echo_create_nonce( 'forum-adminform_themes' ); ?>
				<input type="hidden" name="theme" value="<?php echo esc_attr( $theme_file ); ?>" />
				<input type="hidden" name="style" value="<?php echo esc_attr( $theme_style ); ?>" />
				<input type="hidden" name="parent" value="<?php echo esc_attr( $theme_data['Parent'] ); ?>" />

				<?php
				$defOverlay = ( ! empty( $theme_overlays ) ) ? $theme_overlays[0] : 0;
				echo "<input type='hidden' name='default-color' value='" . esc_attr( $defOverlay ) . "' />";
				if ( $theme_overlays ) {
					if ( count( $theme_overlays ) > 1 ) {
						echo '<label>' . esc_html( SP()->primitives->admin_text( 'Select Overlay' ) ) . ': ' . '</label>';
						echo ' <select name="color-' . esc_attr( $theme_file ) . '">';
						foreach ( $theme_overlays as $theme_overlay ) {
							$theme_overlay = trim( $theme_overlay );
							$selected      = ( $theme_overlays[0] == $theme_overlay ) ? ' selected="selected" ' : '';
							echo '<option' . $selected . ' value="' . esc_attr( $theme_overlay ) . '">' . esc_html( $theme_overlay ) . '</option>';
						}
						echo '</select> ';
					}
				}
				?>
				
				<?php
				if ( ! is_multisite() || is_super_admin() ) { ?>
                    <input type="submit" class="sf-button-secondary action" id="activate-<?php echo esc_attr( $theme_file ); ?>" name="activate" value="<?php echo esc_attr( SP()->primitives->admin_etext( 'Activate' ) ); ?>" />
                    <input type="submit" class="sf-button-secondary action spThemeDeleteConfirm" id="delete-<?php echo esc_attr( $theme_file ); ?>" name="delete" value="<?php echo esc_attr( SP()->primitives->admin_etext( 'Delete' ) ); ?>" data-msg="<?php echo esc_attr( $msg ); ?>" />
                <?php } ?>
			</form>
		</div>

				<?php
				if ( isset( $theme_data['ItemId'] ) && $theme_data['ItemId'] != '' ) {
					$sp_theme_name = sanitize_title_with_dashes( $theme_data['Name'] );
					$check_for_addon_update = SP()->options->get( 'spl_theme_versioninfo_' . $theme_data['ItemId'] );
					$check_for_addon_update = json_decode( $check_for_addon_update );
					$check_addons_status    = SP()->options->get( 'spl_theme_info_' . $theme_data['ItemId'] );
					$check_addons_status    = json_decode( $check_addons_status );
					$update_condition       = $check_for_addon_update != '' && isset( $check_for_addon_update->new_version ) && $check_for_addon_update->new_version != false;
					$status_condition       = $check_addons_status != '' && isset( $check_addons_status->license );
					$version_compare        = isset( $check_for_addon_update->new_version ) && ( version_compare( $check_for_addon_update->new_version, $theme_data['Version'], '>' ) == 1 );

					if ( is_main_site() && $update_condition && $status_condition && $version_compare ) {

						$changelog_link = add_query_arg(
							array(
								'tab'       => 'plugin-information',
								'plugin'    => $sp_theme_name,
								'section'   => 'changelog',
								'TB_iframe' => true,
								'width'     => 722,
								'height'    => 949,
							),
							admin_url( 'plugin-install.php' )
						);

						echo '<br />';

						if ( $check_addons_status->license == 'valid' ) {

							echo '<p class="plugin-update-tr"><p class="update-message notice inline notice-warning notice-alt" style="padding: 10px 0px;">';
							echo esc_html( SP()->primitives->admin_text( 'There is an update for the ' ) ) . ' ' . esc_html( $theme_data['Name'] ) . ' ' . esc_html( SP()->primitives->admin_text( 'theme' ) ) . '.<br />';
							echo esc_html( SP()->primitives->admin_text( 'Version' ) ) . ' ' . esc_html( $check_for_addon_update->new_version ) . ' ' . esc_html( SP()->primitives->admin_text( 'of the theme is available' ) ) . '.<br />';
							echo '<span title="' . esc_attr( SP()->primitives->admin_text( 'View version full details' ) ) . '" class="thickbox open-plugin-details-modal spPluginUpdate" data-width="1000" data-height="0" data-site="' . esc_url( $ajaxURThem ) . '" data-label="Simple:Press Plugin Update" data-href="' . esc_url( $changelog_link ) . '">' . esc_html( SP()->primitives->admin_text( 'View version ' ) ) . esc_html( $check_for_addon_update->new_version ) . esc_html( SP()->primitives->admin_text( ' details' ) ) . '</span> ' . esc_html( SP()->primitives->admin_text( 'or' ) ) . ' ';
							echo '<a href="' . esc_url( self_admin_url( 'update-core.php' ) ) . '" title="' . esc_attr( SP()->primitives->admin_text( 'update now' ) ) . '">' . esc_html( SP()->primitives->admin_text( 'update now' ) ) . '</a>.';
							echo '</p></p>';

						} else {

							echo '<p class="plugin-update-tr"><p class="update-message notice inline notice-warning notice-alt" style="padding: 10px 0px;">';
							echo esc_html( SP()->primitives->admin_text( 'There is an update for the ' ) ) . ' ' . esc_html( $theme_data['Name'] ) . ' ' . esc_html( SP()->primitives->admin_text( 'theme' ) ) . '.<br />';
							echo esc_html( SP()->primitives->admin_text( 'Version' ) ) . ' ' . esc_html( $check_for_addon_update->new_version ) . ' ' . esc_html( SP()->primitives->admin_text( 'of the theme is available' ) ) . '.<br />';
							echo '<span title="' . esc_attr( SP()->primitives->admin_text( 'View version full details' ) ) . '" class="thickbox open-plugin-details-modal spPluginUpdate" data-width="1000" data-height="0" data-site="' . esc_url( $ajaxURThem ) . '" data-label="Simple:Press Plugin Update" data-href="' . esc_url( $changelog_link ) . '">' . esc_html( SP()->primitives->admin_text( 'View version ' ) ) . esc_html( $check_for_addon_update->new_version ) . esc_html( SP()->primitives->admin_text( ' details' ) ) . '</span>';
							echo '<br />' . esc_html( SP()->primitives->admin_text( ' Automatic update is unavailable for this theme - most likely because the license key is not present.' ) );
							echo '</p></p>';
						}
					}
				} else {

					// any upgrade for this theme?
					if ( $xml ) {
						foreach ( $xml->themes->theme as $latest ) {
							if ( $theme_data['Name'] == $latest->name ) {
								if ( ( version_compare( $latest->version, $theme_data['Version'], '>' ) == 1 ) ) {
									echo '<br />';
									echo '<div class="plugin-update-tr"><div class="update-message" style="background-color:#fcf3ef;margin-left:10px;">';
									echo '<strong>' . esc_html( SP()->primitives->admin_text( 'There is an update for the' ) ) . ' ' . esc_html( $theme_data['Name'] ) . ' ' . esc_html( SP()->primitives->admin_text( 'theme' ) ) . '.</strong> ';
									echo esc_html( SP()->primitives->admin_text( 'Version' ) ) . ' ' . esc_html( $latest->version ) . ' ' . esc_html( SP()->primitives->admin_text( 'is available' ) ) . '. ';
									echo esc_html( SP()->primitives->admin_text( 'For details and to download please visit' ) ) . ' ' . esc_html( SPPLUGHOME ) . ' ' . esc_html( SP()->primitives->admin_text( 'or' ) ) . ' ' . esc_html( SP()->primitives->admin_text( 'go to the' ) ) . ' ';
									echo '<a href="' . esc_url( self_admin_url( 'update-core.php' ) ) . '" title="Simple:Press Plugin Update" target="_parent">' . esc_html( SP()->primitives->admin_text( 'WordPress updates page' ) ) . '</a>';
									echo '</div></div>';
								}
								break;
							}
						}
					}
				}

				echo '</div>';
				echo '</div>';
		}
		echo '</div>';
		echo '</div>';

	} else {
		echo esc_html( SP()->primitives->admin_text( 'No other available themes found' ) );
	}
	do_action( 'sph_themes_list_panel' );

	spa_paint_close_fieldset();
	spa_paint_close_panel();
	spa_paint_close_container();
	spa_paint_close_tab();
}
