<?php
/*
Simple:Press
Ajax call for acknowledgements
$LastChangedDate: 2017-12-28 11:37:41 -0600 (Thu, 28 Dec 2017) $
$Rev: 15601 $
*/

if ( ! defined( 'ABSPATH' ) ) {
	die('Access denied - you cannot directly call this file');
}

sp_forum_ajax_support();

if (!sp_nonce('permissions')) die();

$forumid = SP()->filters->integer($_GET['forum']);
if (empty($forumid)) die();

$userid = SP()->filters->integer($_GET['userid']);
if (empty($forumid)) die();

$sql = "SELECT auth_id, auth_name, auth_cat, authcat_name FROM ".SPAUTHS."
		JOIN ".SPAUTHCATS." ON ".SPAUTHS.".auth_cat = ".SPAUTHCATS.".authcat_id
		WHERE active = 1
		ORDER BY auth_cat, auth_id";
$authlist = SP()->DB->select($sql);

$curcol = 1;
$category = '';

foreach ($authlist as $a) {
	$auth_id = $a->auth_id;
	$auth_name = $a->auth_name;

	if ($category != $a->authcat_name) {
		$category = $a->authcat_name;
		$curcol = 1;
		echo '<div class="spAuthCat">'.esc_html(SP()->primitives->admin_text($category)).'</div>';
	}

	echo '<div class="spColumnSection">';
    $icon = SP()->theme->paint_icon('', SPTHEMEICONSURL, SP()->auths->get($auth_name, $forumid, $userid) ? 'sp_PermissionYes.png' : 'sp_PermissionNo.png');
    $desc = SP()->primitives->admin_text(SP()->core->forumData['auths'][$auth_id]->auth_desc);
    echo wp_kses(
        $icon . '&nbsp;' . $desc,
        SP_CORE_ALLOWED_TAGS
    );
	echo '</div>';

	$curcol++;
	if ($curcol > 2) $curcol = 1;
}

echo "<p><input type='button' id='spClosePerms".esc_html($forumid)."' class='spSubmit spClosePermissions' value='".esc_html(SP()->primitives->front_text('Close'))."' data-forumid='".esc_html($forumid)."' /></p>";
?>
	<script>
		(function(spj, $, undefined) {
			$(document).ready(function() {
				baseHeight = Math.max($("#spProfileData").outerHeight(true) + 10, $("#spProfileMenu").outerHeight(true));
				$("#spProfileContent").height(baseHeight + $("#spProfileHeader").outerHeight(true));
			});
		}(window.spj = window.spj || {}, jQuery));
	</script>
<?php

die();
