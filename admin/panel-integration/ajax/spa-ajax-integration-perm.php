<?php
/*
Simple:Press Admin
Ajax call for permalink update/integration
$LastChangedDate: 2018-10-17 15:14:27 -0500 (Wed, 17 Oct 2018) $
$Rev: 15755 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

spa_admin_ajax_support();

if (!sp_nonce('integration-perm')) die();

# ----------------------------------
# Check Whether User Can Manage Integration
if (!SP()->auths->current_user_can('SPF Manage Integration')) die();

if (isset($_GET['item'])) {
	$item = SP()->filters->str($_GET['item']);
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
