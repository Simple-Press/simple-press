<?php

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) {
    die('Access denied - you cannot directly call this file');
}

function spa_toolbox_changelog_form() {
	spa_paint_open_tab(SP()->primitives->admin_text('Change Log'), true);
		spa_paint_open_panel();
            spa_paint_open_fieldset(SP()->primitives->admin_text('Change Log'));

            echo '<div class="sf-form-row">';
                # Display current change log if available or passed log
                if (isset($_POST['clselect'])) {
                    $current = SP()->filters->integer($_POST['clselect']);
                    $cFile = 'change-log-'.$current.'.txt';
                } else {
                    $cFile = 'change-log-'.SPBUILD.'.txt';
                    $current = 0;
                }

                echo '<div id="sp-changelog-data">';
                    $c = wp_remote_get('https://simple-press.com/downloads/simple-press/changelogs/'.$cFile);
                    if (is_wp_error($c) || wp_remote_retrieve_response_code($c) != 200) {
                        $b = substr($cFile, 11);
                        $b = str_replace('.txt', '', $b);
                        echo '<p>'.sprintf(SP()->primitives->admin_text('No change log file found for build %s'), $b).'</p>';
                    } else {
                        echo wpautop($c['body']);
                    }
                echo '</div>';

            echo '</div>';

    		spa_paint_close_fieldset();
		spa_paint_close_panel();

		$c = wp_remote_get('https://simple-press.com/downloads/simple-press/changelogs/log-index.xml');
        if (is_wp_error($c) || wp_remote_retrieve_response_code($c) != 200) {
   			echo '<p>'.SP()->primitives->admin_text('Unable to communicate with Simple Press server').'</p>';
        } else {
			$l = new SimpleXMLElement($c['body']);
			if (!empty($l)) {
        		spa_paint_open_panel();
                    spa_paint_open_fieldset(SP()->primitives->admin_text('Review Change Logs'));
                    echo '<div id="sp-changelog-list" class="sf-form-row">';
    				echo '<form name="loadchangelog" method="post" action="admin.php?page='.SP_FOLDER_NAME.'/admin/panel-toolbox/spa-toolbox.php&amp;tab=changelog">';
                        echo '<div class="sf-select-wrap">';
                            echo '<select name="clselect" class="wp-core-ui">';
                                foreach ($l->log as $x) {
                                    if ($x->build == $current ? $s = " selected='selected' " : $s = '');
                                    echo "<option class='wp-core-ui' value='".$x->build."' $s>".$x->build.' ('.$x->version.') - '.$x->date.'</option>';
                                }
                            echo '</select>';
                            echo '<input type="submit" class="sf-button-secondary" id="gochangelog" name="gochangelog" value="'.SP()->primitives->admin_text('Display Change Log').'" />';
                        echo '</div>';
					echo '</form>';
    				echo '</div>';
    				spa_paint_close_fieldset();
        		spa_paint_close_panel();
			}
		}

		do_action('sph_toolbox_changelog_panel');
		spa_paint_close_container();
	spa_paint_close_tab();
}
