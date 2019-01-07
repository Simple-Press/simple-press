<?php
/*
Simple:Press
Admin themes editor
$LastChangedDate: 2016-06-25 05:55:17 -0500 (Sat, 25 Jun 2016) $
$Rev: 14322 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_themes_editor_form() {
	# get current theme
	$curTheme = sp_get_option('sp_current_theme');

	$themedir = SPTHEMEBASEDIR.$curTheme['theme'];
	$file = (isset($_GET['file'])) ? sp_esc_str($_GET['file']) : '';
	$type = (isset($_GET['type'])) ? sp_esc_str($_GET['type']) : 'style';
	if (empty($file)) {
	    $file = $themedir.'/styles/'.$curTheme['style'];
		$filename = $curTheme['style'];
 	} else {
		$filename = stripslashes($file);
 		if ($type == 'template') {
			$file = $themedir.'/templates/'.stripslashes($file);
		} else if ($type == 'style') {
			$file = $themedir.'/styles/'.stripslashes($file);
		} else {
			$file = $themedir.'/styles/overlays/'.stripslashes($file);
		}
 	}

	$content = '';
	if (is_file($file)) {
		if (filesize($file) > 0) {
			$f = fopen($file, 'r');
			$content = fread($f, filesize($file));
			$content = esc_textarea($content);
		}
	}
?>
<script type="text/javascript">
jQuery(document).ready(function() {
	spjAjaxForm('spedittheme', '');
});
</script>
<?php
    $ajaxURL = wp_nonce_url(SPAJAXURL.'themes-loader&amp;saveform=editor', 'themes-loader');
?>
	<form action="<?php echo $ajaxURL; ?>" method="post" id="spedittheme" name="spedittheme">
	<?php echo sp_create_nonce('forum-adminform_theme-editor'); ?>
<?php
	spa_paint_options_init();
	spa_paint_open_tab(spa_text('SP Theme Editor').' - '.spa_text('Edit Simple:Press Themes'), true);
	spa_paint_open_panel();
	spa_paint_open_fieldset(spa_text('SP Theme Editor'), true, 'theme-editor');

	echo '<div id="sfeditside">';

	# list the template files
	echo '<h3>'.spa_text('Template Files').'</h3>';
    $templates = sp_themes_read_templates($themedir.'/templates');
	if ($templates) {
		echo '<ul>';
		foreach ($templates as $template) {
			echo '<li>';
			if ($template == $filename) echo '<span class="highlight">';
			echo '<a href="'.admin_url('admin.php?page=simple-press/admin/panel-themes/spa-themes.php&amp;tab=editor&amp;file='.esc_attr($template).'&amp;type=template').'">'.$template.'</a>';
			if ($template == $filename) echo '</span>';
			echo '</li>';
		}
		echo '</ul>';
	}

	# list the stylesheets files
	echo '<h3>'.spa_text('Stylesheets').'</h3>';
	$stylesheets = array();
	$stylesheets_dir = @opendir($themedir.'/styles');
	if ($stylesheets_dir) {
		while (($subfile = readdir($stylesheets_dir)) !== false) {
			if (substr($subfile, 0, 1) == '.') continue;
			if (substr($subfile, -4) == '.php' || substr($subfile, -4) == '.css' || substr($subfile, -6) == '.spcss') $stylesheets[] = $subfile;
		}
	}
	@closedir($stylesheets_dir);

	if ($stylesheets) {
		echo '<ul>';
		foreach ($stylesheets as $style) {
			echo '<li>';
			if ($style == $filename) echo '<span class="highlight">';
			echo '<a href="'.admin_url('admin.php?page=simple-press/admin/panel-themes/spa-themes.php&amp;tab=editor&amp;file='.esc_attr($style).'&amp;type=style').'">'.$style.'</a>';
			if ($style == $filename) echo '</span>';
			echo '</li>';
		}
		echo '</ul>';
	}

	# list the overlay files
    if (file_exists($themedir.'/styles/overlays')) { # make sure theme has overlays
    	echo '<h3>'.spa_text('Overlays').'</h3>';
    	$overlays = array();
    	$overlays_dir = @opendir($themedir.'/styles/overlays');
    	if ($overlays_dir) {
    		while (($subfile = readdir($overlays_dir)) !== false) {
    			if (substr($subfile, 0, 1) == '.') continue;
    			if (substr($subfile, -4) == '.php' || substr($subfile, -4) == '.css') $overlays[] = $subfile;
    		}
    	}
    	@closedir($overlays_dir);

    	if ($overlays) {
    		echo '<ul>';
    		foreach ($overlays as $overlay) {
    			echo '<li>';
    			if ($overlay == $filename) echo '<span class="highlight">';
    			echo '<a href="'.admin_url('admin.php?page=simple-press/admin/panel-themes/spa-themes.php&amp;tab=editor&amp;file='.esc_attr($overlay).'&amp;type=overlay').'">'.$overlay.'</a>';
    			if ($overlay == $filename) echo '</span>';
    			echo '</li>';
    		}
    		echo '</ul>';
    	}
    }

    # main div
	echo '</div>';

	echo '<div id="sfeditwindow">';
	echo '<h3>'.spa_text('Editing Theme File').': '.$filename.'</h3>';
	echo '<textarea rows="25" name="spnewcontent" id="spnewcontent" tabindex="1">'.$content.'</textarea>';
	echo '<input type="hidden" name="file" value="'.esc_attr($file).'" />';
	echo '</div>';

	spa_paint_close_fieldset();
	spa_paint_close_panel();
	spa_paint_close_container();
	if (is_writeable($file)) {
?>
    	<div class="sfform-submit-bar">
    	   <input type="submit" class="button-primary" id="saveit" name="saveit" value="<?php spa_etext('Update File'); ?>" />
    	</div>
<?php
	} else {
		echo '<p><em>'.spa_text('You need to make this file writable before you can save your changes. See the <a href="http://codex.wordpress.org/Changing_File_Permissions">WP Codex</a> for more information').'</em></p>';
	}
	spa_paint_close_tab();
	echo '</form>';
}

function sp_themes_read_templates($dir, $base='') {
	$files = array();
	if ($handle = opendir($dir)) {
        while (($file = readdir($handle)) !== false) {
    		if ($file == '.' || $file == '..') continue;
    		$path = $dir.'/'.$file;
    		if (is_file($path)) {
               	if (substr($file, -4) == '.php') {
               	    if (!empty($base)) $file = $base.'/'.$file;
                    $files[] = $file;
                }
            } else if (is_dir($path)) {
                $base = $base.'/'.$file;
    			$subs = sp_themes_read_templates($path, $base);
    			if (!empty($subs)) $files = array_merge($files, $subs);
                $base = '';
            }
        }
    }
	@closedir($handle);
    return $files;
}

?>