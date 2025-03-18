<?php
/*
Simple:Press Admin
Ajax form loader - themes
*/

if ( ! defined( 'ABSPATH' ) ) {
	die('Access denied - you cannot directly call this file');
}

spa_admin_ajax_support();

if (!sp_nonce('themes-loader')) die();

if (SP()->core->status != 'ok') {
	echo esc_html(SP()->core->status);
	die();
}

require_once SP_PLUGIN_DIR.'/admin/panel-themes/spa-themes-display.php';
include_once SP_PLUGIN_DIR.'/admin/panel-themes/support/spa-themes-prepare.php';
include_once SP_PLUGIN_DIR.'/admin/panel-themes/support/spa-themes-save.php';
include_once SP_PLUGIN_DIR.'/admin/library/spa-tab-support.php';

global $adminhelpfile;
$adminhelpfile = 'admin-themes';
# --------------------------------------------------------------------

# ----------------------------------
# Check Whether User Can Manage Options
if (!SP()->auths->current_user_can('SPF Manage Themes')) die();

if (isset($_GET['loadform'])) {
	spa_render_themes_container(sanitize_text_field($_GET['loadform']));
	die();
}

if (isset($_GET['saveform'])) {
	switch(sanitize_text_field($_GET['saveform'])) {
		case 'theme':
			$msg = spa_save_theme_data();
?>
        	<script>
				(function(spj, $, undefined) {
					$(document).ready(function(){
						$("#sfmsgspot").fadeIn("fast");
						$("#sfmsgspot").html("<?php echo esc_js($msg); ?>");
						$("#sfmsgspot").fadeOut(8000);
					});
				}(window.spj = window.spj || {}, jQuery));
        	</script>
<?php
			break;

		case 'mobile':
			$msg = spa_save_theme_mobile_data();
?>
        	<script>
				(function(spj, $, undefined) {
					$(document).ready(function(){
						$("#sfmsgspot").fadeIn("fast");
						$("#sfmsgspot").html("<?php echo esc_js($msg); ?>");
						$("#sfmsgspot").fadeOut(8000);
	            	});
				}(window.spj = window.spj || {}, jQuery));
            	</script>
<?php
			break;

		case 'tablet':
			$msg = spa_save_theme_tablet_data();
?>
        	<script>
				(function(spj, $, undefined) {
					$(document).ready(function(){
						$("#sfmsgspot").fadeIn("fast");
						$("#sfmsgspot").html("<?php echo esc_js($msg); ?>");
						$("#sfmsgspot").fadeOut(8000);
	            	});
				}(window.spj = window.spj || {}, jQuery));
        	</script>
<?php
			break;

		case 'editor':
			echo wp_kses_post(spa_save_editor_data());
			break;

		case 'css':
			echo wp_kses_post(spa_save_css_data());
			break;
	}
	die();
}

die();
