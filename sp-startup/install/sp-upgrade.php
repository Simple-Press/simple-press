<?php
/*
  Simple:Press
  Upgrade Path Routines - Version 5.0
  $LastChangedDate: 2018-11-11 23:06:59 -0600 (Sun, 11 Nov 2018) $
  $Rev: 15814 $
 */

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

global $current_user;

if (!sp_nonce('upgrade')) die();

ob_start();

$InstallID = get_option('sfInstallID'); # use wp option table
wp_set_current_user($InstallID);

require_once dirname(__file__).'/sp-install-support.php';
require_once dirname(__file__).'/sp-upgrade-support.php';
require_once SP_PLUGIN_DIR.'/admin/library/spa-support.php';

# use WP check here since SPF stuff may not be set up
if (!current_user_can('activate_plugins')) {
	sp_response(0, true, 'error', SP()->primitives->admin_text('Access denied - Only Users who can Activate Plugins may perform this upgrade'));
	die();
}

if (!isset($_GET['start'])) {
	sp_response(0, true, 'error', SP()->primitives->admin_text('Start build number not provided to upgrade script'));
	die();
}

$checkval = SP()->filters->integer($_GET['start']);
$build    = intval($checkval);

# double check that the next build section has not reset for any reason - which it should not
$startUpgrade = SP()->options->get('sfStartUpgrade');
$lastSection  = SP()->options->get('sfbuild');
if ($build < $startUpgrade) $build = $startUpgrade;
if ($build < $lastSection) $build = $lastSection;

# send out json header
header('Content-type: application/json; charset='.SPCHARSET);

# DATABASE SCHEMA CHANGES 5.7.0 through to V6 plus

# Start of Upgrade Routines - 5.7 ============================================================

$section = 14290;
if ($build < $section) {
	# alter autoupdate records
	$recs = SP()->DB->select('SELECT * FROM '.SPMETA.' WHERE meta_type="autoupdate"');
	if ($recs) {
		foreach ($recs as $rec) {
			$key = $rec->meta_key;
			if ($key == 'user') {
				SP()->meta->delete($rec->meta_id);
			} else {
				$data = unserialize($rec->meta_value);
				$data[1] = str_replace('sp_ahah=', '', $data[1]);
				$sql = "UPDATE ".SPMETA." SET meta_value = '".serialize($data)."' WHERE meta_key = '".$key."' AND meta_type='autoupdate'";
				SP()->DB->execute($sql);
			}
		}
	}

	sp_response($section);
}

$section = 14430;
if ($build < $section) {
	# alter profile photos data
	$profile = SP()->options->get('sfprofile');
	unset($profile['photoswidth']);
	unset($profile['photosheight']);
	$profile['photoscols'] = 3;
	SP()->options->update('sfprofile', $profile);

	sp_response($section);
}

# Start of Upgrade Routines - 5.7.1 ============================================================

$section = 14492;
if ($build < $section) {
	SP()->auths->add('can_view_images', SP()->filters->esc_sql(SP()->primitives->admin_text_noesc('Can view images in posts')), 1, 0, 0, 0, 2);
	SP()->auths->add('can_view_media', SP()->filters->esc_sql(SP()->primitives->admin_text_noesc('Can view media in posts')), 1, 0, 0, 0, 2);

	$image = SP()->DB->select("SELECT auth_id FROM ".SPAUTHS." WHERE auth_name = 'can_view_images'", 'var');
	$media = SP()->DB->select("SELECT auth_id FROM ".SPAUTHS." WHERE auth_name = 'can_view_media'", 'var');

	$roles = SP()->DB->table(SPROLES);
	foreach ($roles as $role) {
		$actions = unserialize($role->role_auths);
		$actions[$image] = 1;
		$actions[$media] = 1;
		SP()->DB->execute('UPDATE '.SPROLES." SET role_auths='".serialize($actions)."' WHERE role_id=$role->role_id");
	}

	sp_response($section);
}

# Start of Upgrade Routines - 5.7.2 ============================================================

$section = 14512;
if ($build < $section) {
	# reset users new post arrays
	$list = array();
	$list['topics'] = array();
	$list['forums'] = array();
	$list['post'] = array();

	SP()->DB->execute("UPDATE ".SPMEMBERS." SET newposts='".serialize($list)."';");

	sp_response($section);
}

$section = 14520;
if ($build < $section) {
	# add new posts flag support
	$sfcontrols = SP()->options->get('sfcontrols');
	$sfcontrols['flagsuse'] = true;
	$sfcontrols['flagstext'] = 'new';
	$sfcontrols['flagsbground'] = 'ff0000';
	$sfcontrols['flagscolor'] = 'ffffff';
	SP()->options->update('sfcontrols', $sfcontrols);

	sp_response($section);
}

$section = 14600;
if ($build < $section) {
	# add new login/register options
	$splogin = array();
	$splogin = SP()->options->get('sflogin');
	$splogin['spshowlogin'] = true;
	$splogin['spshowregister'] = true;
	$splogin['spaltloginurl'] = '';
	$splogin['spaltlogouturl'] = '';
	$splogin['spaltregisterurl'] = '';
	SP()->options->update('sflogin', $splogin);

	sp_response($section);
}

# Start of Upgrade Routines - 6.0 ============================================================

$section = 15540;
if ($build < $section) {
	# create new user activity type table
	$sql = '
		CREATE TABLE IF NOT EXISTS '.SPUSERACTIVITYTYPE.' (
			activity_id TINYINT(4) NOT NULL AUTO_INCREMENT,
			activity_name VARCHAR(50) NOT NULL,
			PRIMARY KEY	 (activity_id)
		) '.SP()->DB->charset();
	SP()->DB->execute($sql);

	# add to installed tables
	$tables = SP()->options->get('installed_tables');
	if ($tables) {
		if (!in_array(SPUSERACTIVITYTYPE, $tables)) $tables[] = SPUSERACTIVITYTYPE;
		SP()->options->update('installed_tables', $tables);
	}

	# add core activity types - fixed IDs since these are already hard coded in use
	$sql     = 'INSERT INTO '.SPUSERACTIVITYTYPE." (activity_id, activity_name) VALUES (1, 'watches')";
	$success = SP()->DB->execute($sql);
	$sql     = 'INSERT INTO '.SPUSERACTIVITYTYPE." (activity_id, activity_name) VALUES (2, 'give thanks')";
	$success = SP()->DB->execute($sql);
	$sql     = 'INSERT INTO '.SPUSERACTIVITYTYPE." (activity_id, activity_name) VALUES (3, 'receive thanks')";
	$success = SP()->DB->execute($sql);
	$sql     = 'INSERT INTO '.SPUSERACTIVITYTYPE." (activity_id, activity_name) VALUES (4, 'mentions')";
	$success = SP()->DB->execute($sql);
	$sql     = 'INSERT INTO '.SPUSERACTIVITYTYPE." (activity_id, activity_name) VALUES (5, 'posts rated')";
	$success = SP()->DB->execute($sql);
	$sql     = 'INSERT INTO '.SPUSERACTIVITYTYPE." (activity_id, activity_name) VALUES (6, 'topic subscriptions')";
	$success = SP()->DB->execute($sql);
	$sql     = 'INSERT INTO '.SPUSERACTIVITYTYPE." (activity_id, activity_name) VALUES (7, 'forum subscriptions')";
	$success = SP()->DB->execute($sql);
	$sql     = 'INSERT INTO '.SPUSERACTIVITYTYPE." (activity_id, activity_name) VALUES (8, 'reputation')";
	$success = SP()->DB->execute($sql);
	$sql     = 'INSERT INTO '.SPUSERACTIVITYTYPE." (activity_id, activity_name) VALUES (9, 'reserved')";
	$success = SP()->DB->execute($sql);
	$sql     = 'INSERT INTO '.SPUSERACTIVITYTYPE." (activity_id, activity_name) VALUES (10, 'anonymous poster')";
	$success = SP()->DB->execute($sql);

	sp_response($section);
}

$section = 15550;
if ($build < $section) {
	# change primary key on sfmeta and ensure integrity iof entries
	# 1 - save off current table
	SP()->DB->execute('RENAME TABLE '.SPMETA.' TO '.SP_PREFIX.'sfmetatemp');

	# 2 - create new version with different primary keys
	$sql = '
	  CREATE TABLE '.SPMETA." (
	  meta_id BIGINT(20) NOT NULL AUTO_INCREMENT,
	  meta_type VARCHAR(20) NOT NULL,
	  meta_key VARCHAR(100) NOT NULL,
	  meta_value LONGTEXT,
	  PRIMARY KEY (meta_type, meta_key),
	  KEY meta_idx (meta_id)
	  ) ".SP()->DB->charset();
	SP()->DB->execute($sql);

	# 3 - select reords from temp table
	$query          = new stdClass();
	$query->table   = SP_PREFIX.'sfmetatemp';
	$query->fields  = 'meta_id, meta_type, meta_key, meta_value';
	$query->orderby = 'meta_id ASC';
	$meta           = SP()->DB->select($query);

	# 4 - insert back into new table - no dupes
	if ($meta) {
		foreach ($meta as $m) {
			$query                = new stdClass();
			$query->table         = SPMETA;
			$query->fields        = array('meta_type',
			                              'meta_key',
			                              'meta_value');
			$query->data          = array($m->meta_type,
			                              $m->meta_key,
			                              $m->meta_value);
			$query->duplicate_key = true;
			SP()->DB->insert($query);
		}
	}

	# 5 - drop temp table
	SP()->DB->execute('DROP TABLE '.SP_PREFIX.'sfmetatemp');

	sp_response($section);
}

$section = 15560;
if ($build < $section) {
	# db change to allow subforums to have permalinks
	SP()->DB->execute('ALTER TABLE '.SP_PREFIX.'sfforums ADD (permalink_slug VARCHAR(1000) NOT NULL)');

	spa_build_forum_permalink_slugs();

	sp_response($section);
}

$section = 15570;
if ($build < $section) {
	# drop slug indices to ensure innoDB amd mb4 will work ok
	$sql = 'DROP INDEX forum_slug_idx ON '.SPFORUMS;
	$success = SP()->DB->execute($sql);
	$sql = 'DROP INDEX topic_slug_idx ON '.SPTOPICS;
	$success = SP()->DB->execute($sql);

	sp_response($section);
}

$section = 15600;
if ($build < $section) {
	# deactivate all active plugins to keep from blowing up WP
	$list = '';
	$plugins = SP()->options->get('sp_active_plugins', array());
	if ($plugins) {
		if (empty($list)) $list = esc_js(SP()->primitives->admin_text('The following plugins were automatically deactivated and should be activated only after updating to their latest version:')).'<br />';
		foreach ($plugins as $plugin) {
			SP()->plugin->deactivate($plugin, true);
			$list .= strtok($plugin, '/').'<br />';
		}
	}

	sp_response($section, true, 'success', '', $list);
}

sp_bump_build($build, 15746);

# Start of Upgrade Routines - 6.0.1 ============================================================

sp_bump_build($build, 15766);

# Start of Upgrade Routines - 6.0.2 ============================================================

sp_bump_build($build, 15768);

# Start of Upgrade Routines - 6.0.3 ============================================================

sp_bump_build($build, 15802);

$section = 15810;
if ($build < $section) {
	# privacy export options
	$privacy			= array();
	$privacy['posts']	= false;
	$privacy['number']	= 200;
	SP()->options->add('spPrivacy', $privacy);

	sp_response($section);
}

# Start of Upgrade Routines - 6.0.4 ============================================================

sp_bump_build($build, 15814);


# ****** IMPORTANT: THE FINAL $section values MUST be the same as the SPBUILD constant
# ******			for the Upgrade to complete correctly

die();
