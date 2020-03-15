<?php
/*
  Simple:Press
  Desc: Database - small subset of sample data
  $LastChangedDate: 2014-05-24 09:12:47 +0100 (Sat, 24 May 2014) $
  $Rev: 11461 $
 */

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# --------------------------------------
# For use by Install Process
# ---------------------------------------

global $current_user;

# sfdefpermissions

$sql = "INSERT INTO ".SPDEFPERMISSIONS." (permission_id, group_id, usergroup_id, permission_role)
VALUES
	(1,1,1,2),
	(2,1,2,4),
	(3,1,3,5),
	(4,2,1,1),
	(5,2,2,4),
	(6,2,3,6);";
SP()->DB->execute($sql);

# sfforums

$sql = "INSERT INTO ".SPFORUMS." (forum_id, forum_name, group_id, forum_seq, forum_desc, forum_status, forum_disabled, forum_slug, permalink_slug, forum_rss, forum_icon, forum_icon_new, forum_icon_locked, topic_icon, topic_icon_new, topic_icon_locked, topic_icon_pinned, topic_icon_pinned_new, feature_image, post_id, post_id_held, topic_count, post_count, post_count_held, forum_rss_private, parent, children, forum_message, keywords)
VALUES
	(1,'My First Forum',1,1,'An example of a main forum - this one allows non-members to create topics',0,0,'my-first-forum', 'my-first-forum', '','','','','','','','','','',1,1,1,1,1,0,0,'','',''),
	(2,'My Second Forum',1,2,'Another example of a main forum - this one with a sub or child forum',0,0,'my-second-forum', 'my-second-forum', NULL,'','','','','','','','','',NULL,NULL,0,0,0,0,0,'a:1:{i:0;s:1:\"3\";}','',''),
	(3,'A Sub-Forum',1,3,'Sub-forums are most useful when you need a lot of forums but do not want to present them in one long list',0,0,'a-sub-forum', 'my-second-forum/a-sub-forum', NULL,'','','','','','','','','',NULL,NULL,0,0,0,0,2,'','',''),
	(4,'A Private Forum for Members',2,1,'Forums can inherit the permissions set up in their parent Forum Group',0,0,'a-private-forum-for-members', 'a-private-forum-for-members', NULL,'','','','','','','','','',NULL,NULL,0,0,0,0,0,'','','');";
SP()->DB->execute($sql);

# sfgroups

$sql = "INSERT INTO ".SPGROUPS." (group_id, group_name, group_seq, group_desc, group_rss, group_icon, group_message, sample)
VALUES
	(1,'My First Forum Group',1,'Forum Groups are a way of organizing your forums into sections helping your users navigate',NULL,'','',1),
	(2,'A Members Only Forum Group',2,'In this group the permissions have been set to allow no access to unregistered users',NULL,'','',1);";
SP()->DB->execute($sql);

# sfpermissions

$sql = "INSERT INTO ".SPPERMISSIONS." (permission_id, forum_id, usergroup_id, permission_role)
VALUES
	(1,1,1,4),
	(2,1,2,4),
	(3,1,3,5),
	(4,2,1,2),
	(5,2,2,4),
	(6,2,3,5),
	(7,3,1,2),
	(8,3,2,4),
	(9,3,3,5),
	(10,4,1,1),
	(11,4,2,4),
	(12,4,3,6);";
SP()->DB->execute($sql);

# sftopics

$sql = "INSERT INTO ".SPTOPICS." (topic_id, topic_name, topic_date, topic_status, forum_id, user_id, topic_pinned, topic_opened, topic_slug, post_id, post_id_held, post_count, post_count_held)
VALUES
	(1,'My First Topic',NOW(),0,1,".$current_user->ID.".,0,0,'my-first-topic',1,1,1,1);";
SP()->DB->execute($sql);

# sfposts

$sql = "INSERT INTO ".SPPOSTS." (post_id, post_content, post_date, topic_id, user_id, forum_id, guest_name, guest_email, post_status, post_pinned, post_index, post_edit, poster_ip, comment_id, source)
VALUES
	(1,'This is a sample post for the first topic of the first forum',NOW(),1,".$current_user->ID.",1,'','',0,0,1,NULL,'0.0.0.0',NULL,0);";
SP()->DB->execute($sql);

require_once SP_PLUGIN_DIR.'/forum/database/sp-db-management.php';
require_once SP_PLUGIN_DIR.'/forum/database/sp-db-statistics.php';

sp_build_post_index(1);
sp_build_forum_index(1);
sp_build_forum_index(2);
sp_build_forum_index(3);
sp_build_forum_index(4);

$counts = sp_get_stats_counts();
SP()->options->update('spForumStats', $counts);
SP()->options->add('spSample', 1);
