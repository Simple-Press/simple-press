<?php
/*
Simple:Press
Desc: Database - admin search data (core)
$LastChangedDate: 2014-05-24 09:12:47 +0100 (Sat, 24 May 2014) $
$Rev: 11461 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# --------------------------------------
# For use by version 5.6.2 upgrade
# ---------------------------------------

# table sfadminkeywords
# ------------------------------------------------------------

$sql = "DROP TABLE IF EXISTS ".SPADMINKEYWORDS;
SP()->DB->execute($sql);

$sql = "CREATE TABLE ".SPADMINKEYWORDS." (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `keyword` varchar(25) NOT NULL DEFAULT '',
  `plugin` varchar(25) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ".SP()->DB->charset();
SP()->DB->execute($sql);

$sql = "INSERT INTO ".SPADMINKEYWORDS." (`id`, `keyword`)
VALUES
	(1,'Forum Groups'),
	(2,'Forums'),
	(3,'Topics'),
	(4,'Posts'),
	(5,'RSS'),
	(6,'Social Networks'),
	(7,'WordPress Admin'),
	(8,'Caching'),
	(9,'Error Logging'),
	(10,'Display'),
	(11,'Images'),
	(12,'Date and Time'),
	(13,'Links'),
	(14,'Short Codes'),
	(15,'Users - Members'),
	(16,'Users - Guests'),
	(17,'User Names'),
	(18,'Emails'),
	(19,'Smileys'),
	(20,'Custom Forum Icons'),
	(21,'Registration and Login'),
	(22,'SEO'),
	(23,'Meta Tags'),
	(24,'Open Graph'),
	(25,'User Ranking'),
	(26,'Custom Messages'),
	(27,'User Groups'),
	(28,'Permissions'),
	(29,'WordPress Theme'),
	(30,'Permalinks'),
	(31,'Folders - Server'),
	(32,'Translations'),
	(33,'User Profiles'),
	(34,'Photos - Personal'),
	(35,'Signatures'),
	(36,'Avatars'),
	(37,'Admins and Moderators'),
	(38,'Plugins'),
	(39,'Themes'),
	(40,'CSS'),
	(41,'Tools - Admin'),
	(42,'Uninstall'),
	(43,'Editing'),
	(44,'Unread Posts'),
	(45,'Statistics'),
	(46,'Page Title'),
	(47,'Badges'),
	(48,'Sneak Peek'),
	(49,'WordPress Roles'),
	(50,'WordPress Integration'),
	(51,'Localisation'),
	(52,'Version'),
	(53,'Housekeeping'),
	(54,'Development'),
	(55,'Cron');";
SP()->DB->execute($sql);

# table sfadmintasks
# ------------------------------------------------------------

$sql = "DROP TABLE IF EXISTS ".SPADMINTASKS;
SP()->DB->execute($sql);

$sql = "CREATE TABLE ".SPADMINTASKS." (
  `id` int(4) unsigned NOT NULL AUTO_INCREMENT,
  `keyword_id` int(4) NOT NULL,
  `task` varchar(80) NOT NULL DEFAULT '',
  `url` varchar(150) NOT NULL DEFAULT '',
  `plugin` varchar(25) DEFAULT NULL,
   PRIMARY KEY (`id`),
   KEY `keyword` (`keyword_id`)
)  ".SP()->DB->charset();
SP()->DB->execute($sql);

$sql = "INSERT INTO ".SPADMINTASKS." (`id`, `keyword_id`, `task`, `url`)
VALUES
	(1,2,'Create a new forum','panel-forums/spa-forums.php&tab=createforum'),
	(2,2,'Edit an existing forum','panel-forums/spa-forums.php'),
	(3,2,'Check and edit forum permissions','panel-forums/spa-forums.php'),
	(4,2,'Change the display order of forums','panel-forums/spa-forums.php'),
	(5,2,'Delete a forum','panel-forums/spa-forums.php'),
	(6,2,'Disable a forum','panel-forums/spa-forums.php'),
	(7,2,'Upload custom forum icons','panel-forums/spa-forums.php&tab=customicons'),
	(8,2,'Upload an open graph forum featured image','panel-forums/spa-forums.php&tab=featuredimages'),
	(9,2,'Merge Forums','panel-forums/spa-forums.php&tab=mergeforums'),
	(10,1,'Create a new forum group','panel-forums/spa-forums.php&tab=creategroup'),
	(11,1,'Edit an existing forum group','panel-forums/spa-forums.php'),
	(12,1,'Change the display order of forum groups','panel-forums/spa-forums.php&tab=ordering'),
	(13,2,'Add or change permission for all forums in a group','panel-forums/spa-forums.php'),
	(14,1,'Delete a forum group','panel-forums/spa-forums.php'),
	(15,1,'Upload custom forum group icons','panel-forums/spa-forums.php&tab=customicons'),
	(16,2,'Lock all forums','panel-options/spa-options.php'),
	(17,7,'Block WP Admin Page Access','panel-options/spa-options.php'),
	(18,8,'Cached list of new posts','panel-options/spa-options.php'),
	(19,4,'Flood Control','panel-options/spa-options.php'),
	(20,5,'RSS Options','panel-options/spa-options.php')";
SP()->DB->execute($sql);

$sql = "INSERT INTO ".SPADMINTASKS." (`id`, `keyword_id`, `task`, `url`)
VALUES
	(21,8,'Cached CSS and JavaScript files','panel-options/spa-options.php'),
	(22,43,'Set the default editor and editing limits','panel-options/spa-options.php'),
	(23,9,'Error logging options','panel-options/spa-options.php'),
	(24,5,'Global enabling/disabling of RSS feeds','panel-forums/spa-forums.php&tab=globalrss'),
	(25,28,'Check and edit forum permissions','panel-forums/spa-forums.php'),
	(26,28,'Add or change permission for all forums in a group','panel-forums/spa-forums.php'),
	(27,2,'Add a global permission set to all forums','panel-forums/spa-forums.php&tab=globalperm'),
	(28,28,'Add a global permission set to all forums','panel-forums/spa-forums.php&tab=globalperm'),
	(29,2,'Delete all forum permission set assignments','panel-forums/spa-forums.php&tab=removeperms'),
	(30,10,'Remove page title from forum display','panel-options/spa-options.php&tab=display'),
	(31,10,'Set number of topics per page and sort order','panel-options/spa-options.php&tab=display'),
	(32,3,'Set number of topics per page and sort order','panel-options/spa-options.php&tab=display'),
	(33,10,'Set number of posts per page and sort order','panel-options/spa-options.php&tab=display'),
	(34,4,'Set number of posts per page and sort order','panel-options/spa-options.php&tab=display'),
	(35,43,'Set the display preference of the forum editor options toolbar','panel-options/spa-options.php&tab=display'),
	(36,4,'Set users unread post count and options','panel-options/spa-options.php&tab=display'),
	(37,15,'Set users unread post count and options','panel-options/spa-options.php&tab=display'),
	(38,44,'Set users unread post count and options','panel-options/spa-options.php&tab=display'),
	(39,2,'Single forum site option','panel-options/spa-options.php&tab=display'),
	(40,10,'Control statistics display','panel-options/spa-options.php&tab=display')";
SP()->DB->execute($sql);

$sql = "INSERT INTO ".SPADMINTASKS." (`id`, `keyword_id`, `task`, `url`)
VALUES
	(41,45,'Control statistics display','panel-options/spa-options.php&tab=display'),
	(42,28,'Delete all forum permission set assignments','panel-forums/spa-forums.php&tab=removeperms'),
	(43,10,'Set date and time display format','panel-options/spa-options.php&tab=content'),
	(44,12,'Set date and time display format','panel-options/spa-options.php&tab=content'),
	(45,10,'Image display options','panel-options/spa-options.php&tab=content'),
	(46,11,'Image display options','panel-options/spa-options.php&tab=content'),
	(47,10,'Set smiley options','panel-options/spa-options.php&tab=content'),
	(48,19,'Set smiley options','panel-options/spa-options.php&tab=content'),
	(49,10,'Spam post controls','panel-options/spa-options.php&tab=content'),
	(50,4,'Spam post controls','panel-options/spa-options.php&tab=content'),
	(51,10,'Links in posts controls','panel-options/spa-options.php&tab=content'),
	(52,4,'Links in posts controls','panel-options/spa-options.php&tab=content'),
	(53,13,'Links in posts controls','panel-options/spa-options.php&tab=content'),
	(54,10,'Using WordPress shortcodes','panel-options/spa-options.php&tab=content'),
	(55,4,'Using WordPress shortcodes','panel-options/spa-options.php&tab=content'),
	(56,14,'Using WordPress shortcodes','panel-options/spa-options.php&tab=content'),
	(57,15,'Members online settings','panel-options/spa-options.php&tab=members'),
	(58,15,'Members - links on their name','panel-options/spa-options.php&tab=members'),
	(59,13,'Members - links on their name','panel-options/spa-options.php&tab=members'),
	(60,16,'Guest user settings','panel-options/spa-options.php&tab=members')";
SP()->DB->execute($sql);

$sql = "INSERT INTO ".SPADMINTASKS." (`id`, `keyword_id`, `task`, `url`)
VALUES
	(61,15,'Members account removal','panel-options/spa-options.php&tab=members'),
	(62,15,'Member post count control','panel-options/spa-options.php&tab=members'),
	(63,4,'Member post count control','panel-options/spa-options.php&tab=members'),
	(64,15,'Account name blacklists','panel-options/spa-options.php&tab=members'),
	(65,16,'Account name blacklists','panel-options/spa-options.php&tab=members'),
	(66,17,'Account name blacklists','panel-options/spa-options.php&tab=members'),
	(67,15,'New user registration email','panel-options/spa-options.php&tab=email'),
	(68,18,'New user registration email','panel-options/spa-options.php&tab=email'),
	(69,18,'Email address settings','panel-options/spa-options.php&tab=email'),
	(70,19,'Upload smileys','panel-components/spa-components.php'),
	(71,19,'Control smiley display','panel-components/spa-components.php'),
	(72,21,'User registration settings','panel-components/spa-components.php&tab=login'),
	(73,21,'Login and registration redirects','panel-components/spa-components.php&tab=login'),
	(74,21,'Login using social network credentials','panel-components/spa-components.php&tab=login'),
	(75,45,'User online timeout','panel-components/spa-components.php&tab=login'),
	(76,22,'SEO - forum settings','panel-components/spa-components.php&tab=seo'),
	(77,22,'Meta Tags - forum settings','panel-components/spa-components.php&tab=seo'),
	(78,22,'Open Graph - forum settings','panel-components/spa-components.php&tab=seo'),
	(79,23,'SEO - forum settings','panel-components/spa-components.php&tab=seo'),
	(80,23,'Meta Tags - forum settings','panel-components/spa-components.php&tab=seo')";
SP()->DB->execute($sql);

$sql = "INSERT INTO ".SPADMINTASKS." (`id`, `keyword_id`, `task`, `url`)
VALUES
	(81,23,'Open Graph - forum settings','panel-components/spa-components.php&tab=seo'),
	(82,24,'SEO - forum settings','panel-components/spa-components.php&tab=seo'),
	(83,24,'Meta Tags - forum settings','panel-components/spa-components.php&tab=seo'),
	(84,24,'Open Graph - forum settings','panel-components/spa-components.php&tab=seo'),
	(85,46,'Page title settings (seo)','panel-components/spa-components.php&tab=seo'),
	(86,46,'Remove page title from forum display','panel-options/spa-options.php&tab=display'),
	(87,20,'Upload custom icons','panel-forums/spa-forums.php&tab=customicons'),
	(88,25,'Setup forum ranks using post count','panel-forums/spa-forums.php&tab=forumranks'),
	(89,25,'Create special forum ranks','panel-forums/spa-forums.php&tab=forumranks'),
	(90,25,'Add and remove members to special forum ranks','panel-forums/spa-forums.php&tab=forumranks'),
	(91,25,'Upload special forum rank badges','panel-forums/spa-forums.php&tab=forumranks'),
	(92,47,'Upload special forum rank badges','panel-forums/spa-forums.php&tab=forumranks'),
	(93,26,'Custom messages above and within editor','panel-forums/spa-forums.php&tab=messages'),
	(94,43,'Custom messages above and within editor','panel-forums/spa-forums.php&tab=messages'),
	(95,48,'Create sneak peek message and redirect','panel-forums/spa-forums.php&tab=messages'),
	(96,26,'Special admin and user view messages','panel-forums/spa-forums.php&tab=messages'),
	(97,27,'Create a new user group','panel-usergroups/spa-usergroups.php&tab=createusergroup'),
	(98,27,'Edit a user group','panel-usergroups/spa-usergroups.php'),
	(99,27,'Add, move or delete members from a user group','panel-usergroups/spa-usergroups.php'),
	(100,27,'Display user group members','panel-usergroups/spa-usergroups.php')";
SP()->DB->execute($sql);

$sql = "INSERT INTO ".SPADMINTASKS." (`id`, `keyword_id`, `task`, `url`)
VALUES
	(101,15,'Display user group members','panel-usergroups/spa-usergroups.php'),
	(102,27,'Map WordPress user roles to forum user groups','panel-usergroups/spa-usergroups.php&tab=mapusers'),
	(103,49,'Map WordPress user roles to forum user groups','panel-usergroups/spa-usergroups.php&tab=mapusers'),
	(104,27,'Rebuild roles/user group mappings','panel-usergroups/spa-usergroups.php&tab=mapusers'),
	(105,49,'Rebuild roles/user group mappings','panel-usergroups/spa-usergroups.php&tab=mapusers'),
	(106,28,'Create a new permission set','panel-permissions/spa-permissions.php&tab=createperm'),
	(107,28,'Edit a permission set','panel-permissions/spa-permissions.php'),
	(108,28,'Review permission set usages','panel-permissions/spa-permissions.php'),
	(109,28,'Reset all permissions back to install state','panel-permissions/spa-permissions.php&tab=resetperms'),
	(110,2,'Reset all permissions back to install state','panel-permissions/spa-permissions.php&tab=resetperms'),
	(111,28,'Create a custom authorisation','panel-permissions/spa-permissions.php&tab=newauth'),
	(112,50,'Select WordPress page for displaying the forum','panel-integration/spa-integration.php'),
	(113,50,'Update the forum permalinks','panel-integration/spa-integration.php'),
	(114,50,'WordPress display integration options','panel-integration/spa-integration.php'),
	(115,50,'WordPress theme display options','panel-integration/spa-integration.php'),
	(116,30,'Update the forum permalinks','panel-integration/spa-integration.php'),
	(117,29,'WordPress display integration options','panel-integration/spa-integration.php'),
	(118,29,'WordPress theme display options','panel-integration/spa-integration.php'),
	(119,31,'Create, move and check all forum storage folders','panel-integration/spa-integration.php&tab=storage'),
	(120,32,'Download and install translations (language packs)','panel-integration/spa-integration.php&tab=language')";
SP()->DB->execute($sql);

$sql = "INSERT INTO ".SPADMINTASKS." (`id`, `keyword_id`, `task`, `url`)
VALUES
	(121,51,'Download and install translations (language packs)','panel-integration/spa-integration.php&tab=language'),
	(122,33,'Selecting member display name format','panel-profiles/spa-profiles.php'),
	(123,17,'Selecting member display name format','panel-profiles/spa-profiles.php'),
	(124,33,'Profile display options','panel-profiles/spa-profiles.php'),
	(125,33,'Personal profile photos','panel-profiles/spa-profiles.php'),
	(126,34,'Personal profile photos','panel-profiles/spa-profiles.php'),
	(127,33,'Member signatures settings','panel-profiles/spa-profiles.php'),
	(128,35,'Member signatures settings','panel-profiles/spa-profiles.php'),
	(129,15,'Personal profile photos','panel-profiles/spa-profiles.php'),
	(130,15,'Member signatures settings','panel-profiles/spa-profiles.php'),
	(131,33,'Select profile edit form options','panel-profiles/spa-profiles.php&tab=tabsmenus'),
	(132,36,'Setup avatar display and upload options','panel-profiles/spa-profiles.php&tab=avatars'),
	(133,36,'Control the sources of avatars','panel-profiles/spa-profiles.php&tab=avatars'),
	(134,36,'Upload and control default forum avatars','panel-profiles/spa-profiles.php&tab=avatars'),
	(135,36,'Create and upload an avatar pool','panel-profiles/spa-profiles.php&tab=pool'),
	(136,37,'Set your own personal admin/moderator settings','panel-admins/spa-admins.php'),
	(137,37,'Grant your moderators their personal settings','panel-admins/spa-admins.php'),
	(138,37,'Set global admin/moderator settings','panel-admins/spa-admins.php&tab=globaladmin'),
	(139,37,'Manage admin/moderator access capabilities','panel-admins/spa-admins.php&tab=manageadmin'),
	(140,37,'Add a new forum admin','panel-admins/spa-admins.php&tab=manageadmin')";
SP()->DB->execute($sql);

$sql = "INSERT INTO ".SPADMINTASKS." (`id`, `keyword_id`, `task`, `url`)
VALUES
	(141,15,'Complete members listing','panel-users/spa-users.php'),
	(142,38,'Activate, deactivate and delete Simple:Press plugins','panel-plugins/spa-plugins.php'),
	(143,38,'Upload a Simple:Press plugin','panel-plugins/spa-plugins.php&tab=plugin-upload'),
	(144,39,'Select and activate a Simple:Press theme (desktop or global)','panel-themes/spa-themes.php'),
	(145,39,'Enable and select a different theme for mobile devices','panel-themes/spa-themes.php&tab=mobile'),
	(146,39,'Enable and select a different theme for tablet devices','panel-themes/spa-themes.php&tab=tablet'),
	(147,39,'Make edits to Simple:Press theme templates','panel-themes/spa-themes.php&tab=editor'),
	(148,39,'Define special override CSS rules for your theme','panel-themes/spa-themes.php&tab=css'),
	(149,40,'Define special override CSS rules for your theme','panel-themes/spa-themes.php&tab=css'),
	(150,39,'Upload a Simple:Press theme','panel-themes/spa-themes.php&tab=theme-upload'),
	(151,52,'Check installed Simple:Press version and build','panel-toolbox/spa-toolbox.php'),
	(152,52,'Force re-upgrade if requested by support','panel-toolbox/spa-toolbox.php'),
	(153,41,'Force re-upgrade if requested by support','panel-toolbox/spa-toolbox.php'),
	(154,41,'Suite of housekeeping and reset tools','panel-toolbox/spa-toolbox.php&tab=housekeeping'),
	(155,53,'Suite of housekeeping and reset tools','panel-toolbox/spa-toolbox.php&tab=housekeeping'),
	(156,41,'Development data inspector toolkit','panel-toolbox/spa-toolbox.php&tab=inspector'),
	(157,54,'Development data inspector toolkit','panel-toolbox/spa-toolbox.php&tab=inspector'),
	(158,41,'Cron task inspector and scheduler','panel-toolbox/spa-toolbox.php&tab=cron'),
	(159,55,'Cron task inspector and scheduler','panel-toolbox/spa-toolbox.php&tab=cron'),
	(160,41,'The forum error log','panel-toolbox/spa-toolbox.php&tab=errorlog')";
SP()->DB->execute($sql);

$sql = "INSERT INTO ".SPADMINTASKS." (`id`, `keyword_id`, `task`, `url`)
VALUES
	(161,9,'The forum error log','panel-toolbox/spa-toolbox.php&tab=errorlog'),
	(162,41,'Details of your forum and website environment','panel-toolbox/spa-toolbox.php&tab=environment'),
	(163,41,'History of your forum install and upgrades','panel-toolbox/spa-toolbox.php&tab=log'),
	(164,41,'The Simple:Press upgrade change log','panel-toolbox/spa-toolbox.php&tab=changelog'),
	(165,41,'Uninstall Simple:Press ands remove','panel-toolbox/spa-toolbox.php&tab=uninstall'),
	(166,42,'Uninstall Simple:Press ands remove','panel-toolbox/spa-toolbox.php&tab=uninstall'),
	(167,6,'Login using social network credentials','panel-components/spa-components.php&tab=login')";
SP()->DB->execute($sql);
