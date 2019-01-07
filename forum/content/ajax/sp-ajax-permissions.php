<?php
/*
Simple:Press
Ajax call for acknowledgements
$LastChangedDate: 2016-06-25 08:14:16 -0500 (Sat, 25 Jun 2016) $
$Rev: 14331 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

sp_forum_ajax_support();

if (!sp_nonce('permissions')) die();

$forumid = sp_esc_int($_GET['forum']);
if (empty($forumid)) die();

$userid = sp_esc_int($_GET['userid']);
if (empty($forumid)) die();

$sql = "SELECT auth_id, auth_name, auth_cat, authcat_name FROM ".SFAUTHS."
		JOIN ".SFAUTHCATS." ON ".SFAUTHS.".auth_cat = ".SFAUTHCATS.".authcat_id
		WHERE active = 1
		ORDER BY auth_cat, auth_id";
$authlist = spdb_select('set', $sql);

global $spGlobals;
$curcol = 1;
$category = '';

foreach ($authlist as $a) {
	$auth_id = $a->auth_id;
	$auth_name = $a->auth_name;

	if ($category != $a->authcat_name) {
		$category = $a->authcat_name;
		$curcol = 1;
		echo '<div class="spAuthCat">'.spa_text($category).'</div>';
	}

	echo '<div class="spColumnSection">';
	if (sp_get_auth($auth_name, $forumid, $userid)) {
		echo sp_paint_icon('', SPTHEMEICONSURL, 'sp_PermissionYes.png').'&nbsp;&nbsp;'.spa_text($spGlobals['auths'][$auth_id]->auth_desc);
	} else {
		echo sp_paint_icon('', SPTHEMEICONSURL, 'sp_PermissionNo.png').'&nbsp;&nbsp;'.spa_text($spGlobals['auths'][$auth_id]->auth_desc);
	}
	echo '</div>';

	$curcol++;
	if ($curcol > 2) $curcol = 1;
}

echo "<p><input type='button' id='spClosePerms$forumid' class='spSubmit spClosePermissions' value='".sp_text('Close')."' data-forumid='$forumid' /></p>";
?>
	<script type="text/javascript">
	jQuery(document).ready(function() {
		baseHeight = Math.max(jQuery("#spProfileData").outerHeight(true) + 10, jQuery("#spProfileMenu").outerHeight(true));
       	jQuery("#spProfileContent").height(baseHeight + jQuery("#spProfileHeader").outerHeight(true));
	})
	</script>
<?php

die();
?>