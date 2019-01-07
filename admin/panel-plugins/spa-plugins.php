<?php
/*
Simple:Press
Admin Plugins
$LastChangedDate: 2018-11-02 13:31:02 -0500 (Fri, 02 Nov 2018) $
$Rev: 15796 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# Check Whether User Can Manage Plugins
# dont check for admin panels loaded by plugins - the plugins api will do that
$tab = (isset($_GET['tab'])) ? sanitize_text_field($_GET['tab']) : 'plugin-list';
if ($tab != 'plugin') {
    if (!SP()->auths->current_user_can('SPF Manage Plugins')) die();
}

require_once SP_PLUGIN_DIR.'/admin/panel-plugins/spa-plugins-display.php';
include_once SP_PLUGIN_DIR.'/admin/panel-plugins/support/spa-plugins-prepare.php';
include_once SP_PLUGIN_DIR.'/admin/panel-plugins/support/spa-plugins-save.php';
include_once SP_PLUGIN_DIR.'/admin/library/spa-tab-support.php';

if (SP()->core->status != 'ok') {
    include_once SPLOADINSTALL;
    die();
}

global $adminhelpfile;
$adminhelpfile = 'admin-plugins';
# --------------------------------------------------------------------

# was this individual plugin action or bulk action
# bulk actions use query args action1 and action2 while individual links use action
if (isset($_GET['action1'])) {
    $action = SP()->filters->str($_GET['action1']);
} elseif (isset($_GET['action2'])) {
    $action = SP()->filters->str($_GET['action2']);
} else {
    $action = (isset($_GET['action'])) ? $_GET['action'] : '';
}

$title  = (isset($_GET['title'])) ? urldecode(SP()->filters->str($_GET['title'])) : '';
$plugin = (isset($_GET['plugin'])) ? SP()->filters->str($_GET['plugin']) : '';

if ($action && $action == 'uninstall') {
    $msg = '<h3>'.SP()->primitives->admin_text('Please confirm that you want to uninstall this plugin?');
    $msg.= '</h3><b>'.SP()->primitives->admin_text('Please be aware!');
    $msg.= '</b><br><br>'.SP()->primitives->admin_text('Uninstalling a plugin will also remove ANY components that the plugin created when first activated. This can include folders where items have been stored, database tables, permission settings etc.');
    $msg.= '<br><br><b>'.SP()->primitives->admin_text('If in doubt - deactivate the plugin instead');
    $msg.= '</b>';
    $msg = apply_filters('sph_uninstall_message', $msg, $plugin);
?>
	<div id="dialog"></div>

    <script>
		(function(spj, $, undefined) {
			$(document).ready(function() {
				var execute = function() {
					window.location = '<?php echo SPADMINPLUGINS."&plugin=$plugin&action=uninstall_confirmed&sfnonce=".wp_create_nonce('forum-adminform_plugins'); ?>';
				};
				var cancel = function() {
					window.location = '<?php echo SPADMINPLUGINS."&plugin=$plugin&action=uninstall_cancelled&sfnonce=".wp_create_nonce('forum-adminform_plugins'); ?>';
				};

				$('#dialog').html('<?php echo $msg; ?>');
				$('#dialog').dialog({
					modal: true,
					autoOpen: true,
					show: 'fold',
					hide: 'fold',
					width: '500',
					height: 'auto',
					draggable: false,
					resizable: false,
					title: '<?php echo($plugin); ?>',
					closeText: '',
					buttons: {
						"<?php SP()->primitives->admin_etext('Uninstall'); ?>": execute,
						"<?php SP()->primitives->admin_etext('Cancel'); ?>": cancel
					}
				});
			});
		}(window.spj = window.spj || {}, jQuery));
    </script>
<?php
    die();
}

# was there a plugin action?
if ($action && $action != 'uninstall_cancelled') spa_save_plugin_activation();

spa_panel_header();
spa_render_plugins_panel($tab);
spa_panel_footer();

if ($action) {
    $msg = '';
	if ($action == 'activate') $msg = $title.' '.SP()->primitives->admin_text('Plugin').' <strong>'.SP()->primitives->admin_text('Activated').'</strong>';
	if ($action == 'deactivate') $msg = $title.' '.SP()->primitives->admin_text('Plugin').' <strong>'.SP()->primitives->admin_text('Deactivated').'</strong>';
	if ($action == 'uninstall_confirmed') $msg = $title.' '.SP()->primitives->admin_text('Plugin').' <strong>'.SP()->primitives->admin_text('Deactivated and Uninstalled').'</strong>';
	if ($action == 'uninstall_cancelled') $msg = SP()->primitives->admin_text('Plugin uninstall cancelled');
	if ($action == 'delete') $msg = $title.' '.SP()->primitives->admin_text('Plugin').' <strong>'.SP()->primitives->admin_text('Deleted').'</strong>';
	$msg = apply_filters('sph_plugin_message', $msg);

    if ($action != 'uninstall_cancelled') {
?>
    	<script>
			(function(spj, $, undefined) {
				$(document).ready(function(){
					$("#sfmsgspot").fadeIn("fast");
					$("#sfmsgspot").html("<?php echo $msg; ?>");
					$("#sfmsgspot").fadeOut(8000);
					window.location = '<?php echo SPADMINPLUGINS; ?>';
				});
			}(window.spj = window.spj || {}, jQuery));
    	</script>
<?php
    }
}
