<?php
/*
Simple:Press
Desc: Database schema data
$LastChangedDate: 2014-05-24 09:12:47 +0100 (Sat, 24 May 2014) $
$Rev: 11461 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# --------------------------------------
# Data current as at version 5.5.0
# For use by version 5.5.1 upgrade
# ---------------------------------------

global $wpdb, $tables;
$p = $wpdb->prefix;

$tables = array(
	$p.'sfadversaries'		=> array('ALTER TABLE '.$p.'sfadversaries ADD KEY user_id_idx (user_id)',
									 'ALTER TABLE '.$p.'sfadversaries ADD KEY adversary_id_idx (adversary_id)'
								),
	$p.'sfauthcats'			=> array('ALTER TABLE '.$p.'sfauthcats CHANGE authcat_id authcat_id TINYINT(4) AUTO_INCREMENT',
									 'ALTER TABLE '.$p.'sfauthcats CHANGE authcat_desc authcat_desc TINYTEXT',
									 'ALTER TABLE '.$p.'sfauthcats CHANGE authcat_slug authcat_slug VARCHAR(50)',
									 'ALTER TABLE '.$p.'sfauthcats ADD KEY authcat_slug_idx (authcat_slug)'
								),
	$p.'sfauths'			=> array('ALTER TABLE '.$p.'sfauths ADD KEY auth_name_idx (auth_name)'
								),
	$p.'sfbuddies'			=> array('ALTER TABLE '.$p.'sfbuddies ADD KEY user_id_idx (user_id)',
									 'ALTER TABLE '.$p.'sfbuddies ADD KEY buddy_id_idx (buddy_id)'
								),
	$p.'sfcache'			=> array(),
	$p.'sfdefpermissions'	=> array('ALTER TABLE '.$p."sfdefpermissions CHANGE group_id group_id BIGINT(20) NOT NULL DEFAULT '0'",
									 'ALTER TABLE '.$p."sfdefpermissions CHANGE usergroup_id usergroup_id BIGINT(20) NOT NULL DEFAULT '0'",
									 'ALTER TABLE '.$p."sfdefpermissions CHANGE permission_role permission_role BIGINT(20) NOT NULL DEFAULT '0'",
									 'ALTER TABLE '.$p.'sfdefpermissions ADD KEY group_id_idx (group_id)',
									 'ALTER TABLE '.$p.'sfdefpermissions ADD KEY usergroup_id_idx (usergroup_id)',
									 'ALTER TABLE '.$p.'sfdefpermissions ADD KEY permission_role_idx (permission_role)'
								),
	$p.'sfdigest'			=> array('ALTER TABLE '.$p.'sfdigest ADD KEY forum_id_idx (forum_id)',
									 'ALTER TABLE '.$p.'sfdigest ADD KEY topic_id_idx (topic_id)',
									 'ALTER TABLE '.$p.'sfdigest ADD KEY post_id_idx (post_id)'
								),
	$p.'sferrorlog'			=> array(),
	$p.'sfforums'			=> array('ALTER TABLE '.$p.'sfforums CHANGE forum_slug forum_slug VARCHAR(200) NOT NULL',
									 'ALTER TABLE '.$p.'sfforums ADD KEY group_id_idx (group_id)',
									 'ALTER TABLE '.$p.'sfforums ADD KEY forum_slug_idx (forum_slug)',
									 'ALTER TABLE '.$p.'sfforums ADD KEY post_id_idx (post_id)'
								),
	$p.'sfgroups'			=> array(),
	$p.'sflinks'			=> array('ALTER TABLE '.$p.'sflinks ADD KEY post_id_idx (post_id)',
									 'ALTER TABLE '.$p.'sflinks ADD KEY forum_id_idx (forum_id)',
									 'ALTER TABLE '.$p.'sflinks ADD KEY topic_id_idx (topic_id)'
								),
	$p.'sflog'				=> array(),
	$p.'sflogmeta'			=> array(),
	$p.'sfmaillog'			=> array(),
	$p.'sfmembers'			=> array('ALTER TABLE '.$p.'sfmembers ADD KEY admin_idx (admin)',
									 'ALTER TABLE '.$p.'sfmembers ADD KEY moderator_idx (moderator)'
								),
	$p.'sfmemberships'		=> array('ALTER TABLE '.$p."sfmemberships CHANGE usergroup_id usergroup_id BIGINT(20) NOT NULL DEFAULT '0'",
									 'ALTER TABLE '.$p."sfmemberships CHANGE usergroup_id usergroup_id BIGINT(20) NOT NULL DEFAULT '0'",
									 'ALTER TABLE '.$p.'sfmemberships ADD KEY user_id_idx (user_id)',
									 'ALTER TABLE '.$p.'sfmemberships ADD KEY usergroup_id_idx (usergroup_id)'
								),
	$p.'sfmeta'				=> array('ALTER TABLE '.$p."sfmeta CHANGE autoload autoload TINYINT(2) NOT NULL DEFAULT '0';",
									 'ALTER TABLE '.$p.'sfmeta ADD KEY meta_type_idx (meta_type)',
									 'ALTER TABLE '.$p.'sfmeta ADD KEY autoload_idx (autoload)'
								),
	$p.'sfnotices'			=> array('ALTER TABLE '.$p.'sfnotices ADD KEY user_id_idx (user_id)'
								),
	$p.'sfoptions'			=> array('ALTER TABLE '.$p.'sfoptions ADD KEY option_id_idx (option_id)',
									 'ALTER TABLE '.$p.'sfoptions DROP INDEX option_id'
								),
	$p.'sfpermissions'		=> array('ALTER TABLE '.$p."sfpermissions CHANGE forum_id forum_id BIGINT(20) NOT NULL DEFAULT '0'",
									 'ALTER TABLE '.$p."sfpermissions CHANGE usergroup_id usergroup_id BIGINT(20) NOT NULL DEFAULT '0'",
									 'ALTER TABLE '.$p."sfpermissions CHANGE permission_role permission_role BIGINT(20) NOT NULL DEFAULT '0'",
									 'ALTER TABLE '.$p.'sfpermissions ADD KEY forum_id_idx (forum_id)',
									 'ALTER TABLE '.$p.'sfpermissions ADD KEY usergroup_id_idx (usergroup_id)',
									 'ALTER TABLE '.$p.'sfpermissions ADD KEY permission_role_idx (permission_role)'
								),
	$p.'sfpmattachments'	=> array(),
	$p.'sfpmmessages'		=> array('ALTER TABLE '.$p.'sfpmmessages ADD KEY thread_id_idx (thread_id)',
									 'ALTER TABLE '.$p.'sfpmmessages ADD KEY user_id_idx (user_id)'
								),
	$p.'sfpmrecipients'		=> array('ALTER TABLE '.$p.'sfpmrecipients ADD KEY thread_id_idx (thread_id)',
									 'ALTER TABLE '.$p.'sfpmrecipients ADD KEY message_id_idx (message_id)',
									 'ALTER TABLE '.$p.'sfpmrecipients ADD KEY user_id_idx (user_id)'
								),
	$p.'sfpmthreads'		=> array('ALTER TABLE '.$p.'sfpmthreads CHANGE thread_slug thread_slug VARCHAR(200) NOT NULL',
									 'ALTER TABLE '.$p.'sfpmthreads ADD KEY  thread_slug_idx (thread_slug)'
								),
	$p.'sfpolls'			=> array('ALTER TABLE '.$p.'sfpolls ADD KEY user_id_idx (user_id)',
									 'ALTER TABLE '.$p.'sfpolls ADD KEY poll_active_idx (poll_active)'
								),
	$p.'sfpollsanswers'		=> array('ALTER TABLE '.$p.'sfpollsanswers ADD KEY poll_id_idx (poll_id)'
								),
	$p.'sfpollsvoters'		=> array(),
	$p.'sfpostratings'		=> array('ALTER TABLE '.$p.'sfpostratings ADD KEY post_id_idx (post_id)'
								),
	$p.'sfposts'			=> array('ALTER TABLE '.$p.'sfposts ADD KEY `topic_id_idx` (`topic_id`)',
									 'ALTER TABLE '.$p.'sfposts ADD KEY `forum_id_idx` (`forum_id`)',
									 'ALTER TABLE '.$p.'sfposts ADD KEY `user_id_idx` (`user_id`)',
									 'ALTER TABLE '.$p.'sfposts ADD KEY `guest_name_idx` (`guest_name`)',
									 'ALTER TABLE '.$p.'sfposts ADD KEY `comment_id_idx` (`comment_id`)',
									 'ALTER TABLE '.$p.'sfposts ADD KEY `post_date_idx` (`post_date`)'
								),
	$p.'sfroles'			=> array('ALTER TABLE '.$p.'sfroles CHANGE role_id role_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT',
									 'ALTER TABLE '.$p.'sfroles CHANGE role_auths role_auths LONGTEXT NOT NULL'
								),
	$p.'sfspecialranks'		=> array('ALTER TABLE '.$p.'sfspecialranks ADD KEY `user_id_idx` (`user_id`)',
									 'ALTER TABLE '.$p.'sfspecialranks ADD KEY `special_rank_idx` (`special_rank`)'
								),
	$p.'sftagmeta'			=> array('ALTER TABLE '.$p.'sftagmeta ADD KEY tag_id_idx (tag_id)',
									 'ALTER TABLE '.$p.'sftagmeta ADD KEY topic_id_idx (topic_id)',
									 'ALTER TABLE '.$p.'sftagmeta ADD KEY forum_id_idx (forum_id)'
								),
	$p.'sftags'				=> array('ALTER TABLE '.$p.'sftags ADD KEY tag_slug_idx (tag_slug)'
								),
	$p.'sftopics'			=> array('ALTER TABLE '.$p.'sftopics CHANGE topic_name topic_name VARCHAR(200) NOT NULL',
									 'ALTER TABLE '.$p.'sftopics CHANGE topic_slug topic_slug VARCHAR(200) NOT NULL',
									 'ALTER TABLE '.$p.'sftopics ADD KEY `forum_id_idx` (`forum_id`)',
									 'ALTER TABLE '.$p.'sftopics ADD KEY `topic_slug_idx` (`topic_slug`)',
									 'ALTER TABLE '.$p.'sftopics ADD KEY `user_id_idx` (`user_id`)',
									 'ALTER TABLE '.$p.'sftopics ADD KEY `post_id_idx` (`post_id`)'
								),
	$p.'sftrack'			=> array('ALTER TABLE '.$p."sftrack CHANGE device device CHAR(1) DEFAULT 'D'",
									 'ALTER TABLE '.$p.'sftrack ADD KEY `trackuserid_idx` (`trackuserid`)',
									 'ALTER TABLE '.$p.'sftrack ADD KEY `forum_id_idx` (`forum_id`)',
									 'ALTER TABLE '.$p.'sftrack ADD KEY `topic_id_idx` (`topic_id`)'
								),
	$p.'sfuseractivity'		=> array('ALTER TABLE '.$p.'sfuseractivity ADD KEY `type_id_idx` (`type_id`)',
									 'ALTER TABLE '.$p.'sfuseractivity ADD KEY `user_id_idx` (`user_id`)'
								),
	$p.'sfusergroups'		=> array(),
	$p.'sfwaiting'			=> array(),
	$p.'sfwarnings'			=> array('ALTER TABLE '.$p.'sfwarnings ADD KEY user_id_idx (user_id)',
									 'ALTER TABLE '.$p.'sfwarnings ADD KEY warn_type_idx (warn_type)'
								)
);

# Rebuild the installed_table option record
function sp_rebuild_table_list() {
	global $tables;
	sp_delete_option('installed_tables');
	$t = array();
	foreach ($tables as $table => $data) {
		$found = spdb_select('var', "SHOW TABLES LIKE '$table'");
		if ($found) $t[] = $table;
	}
	sp_add_option('installed_tables', $t);
}

function sp_rebuild_schema($start, $end) {
	global $tables, $wpdb;

	# prepare the one exception!
	$opTable = $wpdb->prefix . 'sfoptions';
	$opKey = 'option_id';

	$t = sp_get_option('installed_tables');
	for($x=$start; $x<$end+1; $x++) {
		if ($t[$x]) {
			# get list of indexes
			$data = $wpdb->get_results("SHOW INDEXES FROM $t[$x]");
			if ($data) {
				foreach ($data as $item) {
					if (($item->Key_name != 'PRIMARY') && ($t[$x] != $opTable && $item->Key_name != $opKey)) {
						# remove current index
						spdb_query('ALTER TABLE '.$t[$x]." DROP INDEX $item->Key_name");
					}
				}
			}
			# grab list of table actions
			$actions = $tables[$t[$x]];
			if ($actions) {
				foreach ($actions as $sql) {
					# add back index
					spdb_query($sql);
				}
			}
		}
	}
}
?>