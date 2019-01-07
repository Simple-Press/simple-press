<?php
/*
Simple:Press Admin
Ajax call for permalink update/integration
$LastChangedDate: 2017-12-28 11:37:41 -0600 (Thu, 28 Dec 2017) $
$Rev: 15601 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

spa_admin_ajax_support();

if (!sp_nonce('integration-perm')) die();

# ----------------------------------
# Check Whether User Can Manage Integration
if (!SP()->auths->current_user_can('SPF Manage Integration')) die();

if (isset($_GET['item'])) {
	$item = $_GET['item'];
	if ($item == 'upperm') spa_update_permalink_tool();
}

function spa_update_permalink_tool() {
	echo '<strong>&nbsp;'.SP()->spPermalinks->update_permalink(true).'</strong>';
?>
	<script>
		window.location= "<?php echo SPADMININTEGRATION; ?>";
	</script>
<?php
	die();
}

die();
