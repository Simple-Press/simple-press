<?php
/*
Simple:Press
Admin Options Global Form
$LastChangedDate: 2018-11-05 07:39:53 -0600 (Mon, 05 Nov 2018) $
$Rev: 15808 $
*/

if ( preg_match( '#' . basename( __FILE__ ) . '#', $_SERVER['PHP_SELF'] ) ) {
	die( 'Access denied - you cannot directly call this file' );
}

function spa_options_global_form() {
	?>
    <script>
        spj.loadAjaxForm('sfglobalform', 'sfreloadog');
    </script>
	<?php
	global $wp_roles, $tab;

	$sfoptions = spa_get_global_data();
	$ajaxURL   = wp_nonce_url( SPAJAXURL . 'options-loader&amp;saveform=global', 'options-loader' );
	?>
    <form action="<?php echo $ajaxURL; ?>" method="post" id="sfglobalform" name="sfglobal">
		<?php echo sp_create_nonce( 'forum-adminform_global' ); ?>
		<?php
		#== GLOBAL Tab ============================================================

		spa_paint_open_tab(SP()->primitives->admin_text( 'Global Settings' ) );

		spa_paint_open_panel();
		spa_paint_open_fieldset( SP()->primitives->admin_text( 'Lock Down Forum' ), true, 'lock-down-forum' );
		spa_paint_checkbox( SP()->primitives->admin_text( 'Lock the entire forum (read only)' ), 'sflockdown', $sfoptions['sflockdown'] );
		spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_open_panel();
		spa_paint_open_fieldset( SP()->primitives->admin_text( 'WP Admin Pages Access' ), true, 'block-admin' );
		spa_paint_checkbox( SP()->primitives->admin_text( 'Block user access to WP admin pages' ), 'blockadmin', $sfoptions['blockadmin'] );
		if ( $sfoptions['blockadmin'] ) {
			$roles = array_keys( $wp_roles->role_names );
			if ( $roles ) {
				echo '<p class="sf-subhead">' . SP()->primitives->admin_text( 'Allow these WP roles access to the WP admin' ) . ':</p>';
				echo '<p><strong><small>(' . SP()->primitives->admin_text( 'Administrators will always have access' ) . ')</small></strong></p>';
				foreach ( $roles as $index => $role ) {
					if ( $role != 'administrator' ) {
						$checked = ( ! empty( $sfoptions['blockroles'][ $role ] ) ) ? 1 : 0;
						spa_paint_checkbox( $role, 'role-' . $index, $checked );
					}
				}
			}
			spa_paint_input( SP()->primitives->admin_text( 'URL to redirect to if blocking admin access' ), 'blockredirect', $sfoptions['blockredirect'], false, true );
			spa_paint_checkbox( SP()->primitives->admin_text( "Redirect to user's profile page (overrides URL above)" ), 'blockprofile', $sfoptions['blockprofile'] );
		}
		spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_open_panel();
		spa_paint_open_fieldset( SP()->primitives->admin_text( 'Auto Update' ), true, 'auto-update' );
		spa_paint_checkbox( SP()->primitives->admin_text( 'Use auto update' ), 'sfautoupdate', $sfoptions['sfautoupdate'] );
		spa_paint_input( SP()->primitives->admin_text( 'How many seconds before refresh' ), 'sfautotime', $sfoptions['sfautotime'] );
		spa_paint_close_fieldset();
		spa_paint_close_panel();

		do_action( 'sph_options_global_left_panel' );

		spa_paint_open_panel();
		spa_paint_open_fieldset( SP()->primitives->admin_text( 'Flood Control' ), true, 'flood-control' );
		spa_paint_input( SP()->primitives->admin_text( 'Flood control interval (seconds) required between multiple posts from single user (0 disables)' ), 'floodcontrol', $sfoptions['floodcontrol'] );
		spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_open_panel();
		spa_paint_open_fieldset( SP()->primitives->admin_text( 'RSS Feeds' ), true, 'rss-feeds' );
		spa_paint_input( SP()->primitives->admin_text( 'Number of recent posts to feed' ), 'sfrsscount', $sfoptions['sfrsscount'] );
		spa_paint_checkbox( SP()->primitives->admin_text( 'Limit feeds to topic names (no post content)' ), 'sfrsstopicname', $sfoptions['sfrsstopicname'] );
		spa_paint_input( SP()->primitives->admin_text( 'Limit to number of words if showing content (0 = all)' ), 'sfrsswords', $sfoptions['sfrsswords'] );
		spa_paint_checkbox( SP()->primitives->admin_text( 'Enable feedkeys for private RSS feeds' ), 'sfrssfeedkey', $sfoptions['sfrssfeedkey'] );
		spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_tab_right_cell();

		spa_paint_open_panel();
		spa_paint_open_fieldset( SP()->primitives->admin_text( 'CSS/JS Combined Caching' ), true, 'combined-caches' );
		spa_paint_checkbox( SP()->primitives->admin_text( 'Enable combining and caching of forum CSS files' ), 'combinecss', $sfoptions['combinecss'] );
		spa_paint_checkbox( SP()->primitives->admin_text( 'Enable combining and caching of forum script (JS) files' ), 'combinejs', $sfoptions['combinejs'] );
		spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_open_panel();
		spa_paint_open_fieldset( SP()->primitives->admin_text( 'Post Editing' ), true, 'post-editing' );
		?>

        <style>
            .sf-option-global-label{
                padding-bottom: 10px;
            }
        </style>
        <div class="sf-form-row">
                <label><?php SP()->primitives->admin_etext( 'Select Default Editor' ); ?></label>
            <ul>
			<?php
			if ( defined( 'RICHTEXT' ) ) {
				$checked = ( $sfoptions['defeditor'] == 1 ) ? 'checked="checked"' : '';
				?>
                <li class="sf-option-global-label">
                    <input type="radio" name="editor" id="sfradio-editor1" tabindex="<?php echo $tab;
					$tab ++; ?>" value="1" <?php echo $checked; ?> />
                    <label for="sfradio-editor1"><?php echo SP()->primitives->admin_text( 'Rich text' ) . ' (' . RICHTEXTNAME . ')'; ?></label>
                </li>
				<?php
			}
			if ( defined( 'HTML' ) ) {
				$checked = ( $sfoptions['defeditor'] == 2 ) ? 'checked="checked"' : '';
				?>
                <li class="sf-option-global-label">
                    <input type="radio" name="editor" id="sfradio-editor2" tabindex="<?php echo $tab;
					$tab ++; ?>" value="2" <?php echo $checked; ?> />
                    <label for="sfradio-editor2"><?php echo SP()->primitives->admin_text( 'HTML' ) . ' (' . HTMLNAME . ')'; ?></label>
                </li>
				<?php
			}
			if ( defined( 'BBCODE' ) ) {
				$checked = ( $sfoptions['defeditor'] == 3 ) ? 'checked="checked"' : '';
				?>
                <li class="sf-option-global-label">
                    <input type="radio" name="editor" id="sfradio-editor3" tabindex="<?php echo $tab;
					$tab ++; ?>" value="3" <?php echo $checked; ?> />
                    <label for="sfradio-editor3"><?php echo SP()->primitives->admin_text( 'bbCode' ) . ' (' . BBCODENAME . ')'; ?></label>
                </li>
				<?php
			}
			$checked = ( $sfoptions['defeditor'] == 4 ) ? 'checked="checked"' : '';
			?>
            <li class="sf-option-global-label">
                <input type="radio" name="editor" id="sfradio-editor4" tabindex="<?php echo $tab;
				$tab ++; ?>" value="4" <?php echo $checked; ?> />
                <label for="sfradio-editor4"><?php echo SP()->primitives->admin_text( 'Plain text' ) . ' (' . PLAINTEXTNAME . ')'; ?></label>
            </li>

                </ul>
        </div>
		<?php
		spa_paint_input( SP()->primitives->admin_text( '# of days a post can be edited (if user has permission)' ), 'editpostdays', $sfoptions['editpostdays'] );
		spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_open_panel();
		spa_paint_open_fieldset( SP()->primitives->admin_text( 'Error Logging' ), true, 'error-log' );
		spa_paint_checkbox( SP()->primitives->admin_text( 'Disable Error Logging' ), 'errorlog', $sfoptions['errorlog'] );
		spa_paint_checkbox( SP()->primitives->admin_text( 'Disable logging simple Notices only' ), 'notices', $sfoptions['notices'] );
		spa_paint_close_fieldset();
		spa_paint_close_panel();

		do_action( 'sph_options_global_right_panel' );

		spa_paint_close_container();
		?>
        <div class="sf-form-submit-bar">
            <input type="submit" class="sf-button-primary" id="saveit" name="saveit" value="<?php SP()->primitives->admin_etext( 'Update Global Options' ); ?>"/>
        </div>
		<?php
		spa_paint_close_tab();
		?>
    </form>
	<?php
}
