<?php
/*
  Simple:Press
  Upgrade Path Routines - Version 5.0
  $LastChangedDate: 2018-12-16 19:11:16 -0600 (Sun, 16 Dec 2018) $
  $Rev: 15858 $
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

# Start of Upgrade Routines - 6.0.5 ============================================================

sp_bump_build($build, 15827);

$section = 15830;
if ($build < $section) {
	# privacy delete option
	$privacy			= SP()->options->get('spPrivacy');
	$privacy['erase']	= 1;
	$privacy['mess']	= SP()->primitives->admin_text('Post content removed by user request');
	
	SP()->options->update('spPrivacy', $privacy);

	sp_response($section);
}

# Start of Upgrade Routines - 6.0.6 ============================================================

sp_bump_build($build, 15852);

# Start of Upgrade Routines - 6.0.7 ============================================================

sp_bump_build($build, 15857);

# Start of Upgrade Routines - 6.1.0 ============================================================

sp_bump_build($build, 15858);

# Start of Upgrade Routines - 6.2.0 ============================================================

$section = 15861;
if ($build < $section) {
	
	# Install iconsets
	$extract_to = SP()->plugin->add_storage( 'forum-iconsets', 'iconsets' );
	
	# Move and extract zip install archives
	$successCopy1	 = false;
	$successExtract1 = false;
	$zipfile		 = SP_PLUGIN_DIR.'/sp-startup/install/sp-resources-install-part1.zip';
	
	# Copy the zip file
	if (copy($zipfile, $extract_to.'/sp-resources-install-part1.zip')) {
		
		$successCopy1	 = true;
		# Now try and unzip it
		require_once ABSPATH.'wp-admin/includes/class-pclzip.php';
		$zipfile		 = $extract_to.'/sp-resources-install-part1.zip';
		$zipfile		 = str_replace('\\', '/', $zipfile); # sanitize for Win32 installs
		$zipfile		 = preg_replace('|/+|', '/', $zipfile); # remove any duplicate slash
		$extract_to		 = str_replace('\\', '/', $extract_to); # sanitize for Win32 installs
		$extract_to		 = preg_replace('|/+|', '/', $extract_to); # remove any duplicate slash
		$archive		 = new PclZip($zipfile);
		
		$archive->extract( PCLZIP_OPT_PATH, $extract_to, PCLZIP_OPT_BY_NAME, 'forum-iconsets/', PCLZIP_OPT_REMOVE_PATH, 'forum-iconsets/' );
		
		if ($archive->error_code == 0) {
			$successExtract1 = true;
			# Lets try and remove the zip as it seems to have worked
			@unlink($zipfile);
		}
	}
	
	sp_install_iconsets();
	
	sp_response($section);
	
}

# Start of Upgrade Routines - 6.3.0 ============================================================
$section = 15863;
if ($build < $section) {
	// blank upgrade...
	sp_response($section);
}
$section = 15864;
if ($build < $section) {
	// blank upgrade...
	sp_response($section);
}
$section = 15865;
if ($build < $section) {	
	# Add a role to the system...
	$administrator_role = get_role('administrator');

	# Add the new role to users tagged as simple:press admins (some users may not be wp admins!)
	$users = sp_get_admins();
	foreach ($users as $user_id => $display_name) {
		$user = get_user_by('ID', $user_id);
	}
	
	sp_response($section);
	
}
# End of Upgrade Routines - 6.3.0 ============================================================

# Start of Upgrade Routines - 6.4.0 ============================================================
$section = 15866;
if ($build < $section) {
	// blank upgrade...
	sp_response($section);
}

# Start of Upgrade Routines - 6.5.0 ============================================================
$section = 15867;
if ($build < $section) {
	// blank upgrade...
	sp_response($section);
}

# Start of Upgrade Routines - 6.5.1 ============================================================
$section = 15868;
if ($build < $section) {
	// blank upgrade...
	sp_response($section);
}

# Start of Upgrade Routines - 6.6.0 ============================================================
$section = 15869;
if ($build < $section) {
	// blank upgrade...
	sp_response($section);
}

# Start of Upgrade Routines - 6.6.1 ============================================================
$section = 15870;
if ($build < $section) {
	// blank upgrade...
	sp_response($section);
}

# Start of Upgrade Routines - 6.6.2 ============================================================
$section = 15871;
if ($build < $section) {
	// blank upgrade...
	sp_response($section);
}

# Start of Upgrade Routines - 6.6.3 ============================================================
$section = 15872;
if ($build < $section) {
	// blank upgrade...
	sp_response($section);
}

# Start of Upgrade Routines - 6.6.4 ============================================================
$section = 15873;
if ($build < $section) {
	// blank upgrade...
	sp_response($section);
}

# Start of Upgrade Routines - 6.6.5 ============================================================
$section = 15874;
if ($build < $section) {
	// blank upgrade...
	sp_response($section);
}

# Start of Upgrade Routines - 6.6.6 ============================================================
$section = 15875;
if ($build < $section) {
	// blank upgrade...
	sp_response($section);
}

# Start of Upgrade Routines - 6.7.0 ============================================================
$section = 15876;
if ($build < $section) {
	// blank upgrade...
	sp_response($section);
}

# Start of Upgrade Routines - 6.8.0 ============================================================
$section = 15877;
if ($build < $section) {
	// blank upgrade...
	sp_response($section);
}

# Start of Upgrade Routines - 6.8.1 ============================================================
$section = 15878;
if ($build < $section) {
	
	// Add new AUTHS.
	SP()->auths->add('can_use_object_tag', SP()->filters->esc_sql(SP()->primitives->admin_text_noesc('Can use OBJECT and EMBED tags in posts')), 1, 1, 0, 0, 3, SP()->primitives->admin_text('*** WARNING *** The use of the OBJECT and EMBEG tags is dangerous. Allowing users to embed objects enables them to launch a potential security threat against your website. Enabling the OBJECT and EMBED tags requires your trust in your users. Turn on with care.'));
	sp_response($section);
}

# Start of Upgrade Routines - 6.8.2 ============================================================
$section = 15879;
if ($build < $section) {
	sp_response($section);
}

# Start of Upgrade Routines - 6.8.3 ============================================================
$section = 15880;
if ($build < $section) {
	sp_response($section);
}

# Start of Upgrade Routines - 6.8.4 ============================================================
$section = 15881;
if ($build < $section) {
    sp_response($section);
}

# Start of Upgrade Routines - 6.8.5 ============================================================
$section = 15882;
if ($build < $section) {
    sp_response($section);
}

# Start of Upgrade Routines - 6.8.6 ============================================================
$section = 15883;
if ($build < $section) {
    sp_response($section);
}

# Start of Upgrade Routines - 6.8.7 ============================================================
$section = 15884;
if ($build < $section) {
    sp_response($section);
}

# Start of Upgrade Routines - 6.8.8 ============================================================
$section = 15885;
if ($build < $section) {
    sp_response($section);
}

# Start of Upgrade Routines - 6.8.9 ============================================================
$section = 15886;
if ($build < $section) {
    sp_response($section);
}

# Start of Upgrade Routines - 6.8.10 ===========================================================
$section = 15887;
if ($build < $section) {
    sp_response($section);
}

# Start of Upgrade Routines - 6.9.0 ===========================================================
$section = 15888;
if ($build < $section) {
    sp_response($section);
}

# Start of Upgrade Routines - 6.9.1 ===========================================================
$section = 15889;
if ($build < $section) {
    sp_response($section);
}

# Start of Upgrade Routines - 6.10.0 ==========================================================
$section = 15890;
if ($build < $section) {
    sp_response($section);
}

# Start of Upgrade Routines - 6.10.1 ==========================================================
$section = 15891;
if ($build < $section) {
    sp_response($section);
}

# Start of Upgrade Routines - 6.10.2 ==========================================================
$section = 15892;
if ($build < $section) {
    sp_response($section);
}

# Start of Upgrade Routines - 6.10.3 ==========================================================
$section = 15893;
if ($build < $section) {
    sp_response($section);
}

# Start of Upgrade Routines - 6.10.4 ==========================================================
$section = 15894;
if ($build < $section) {
    sp_response($section);
}

# Start of Upgrade Routines - 6.10.5 =========================================================
$section = 15895;
if ($build < $section) {
    sp_response($section);
}

# Start of Upgrade Routines - 6.10.6 =========================================================
$section = 15896;
if ($build < $section) {
    sp_response($section);
}

# Start of Upgrade Routines - 6.10.7 =========================================================
$section = 15897;
if ($build < $section) {
    sp_response($section);
}

# Start of Upgrade Routines - 6.10.8 =========================================================
$section = 15898;
if ($build < $section) {
    sp_response($section);
}

# Start of Upgrade Routines - 6.10.9 =========================================================
$section = 15899;
if ($build < $section) {
    sp_response($section);
}

# Start of Upgrade Routines - 6.10.10 =========================================================
$section = 15900;
if ($build < $section) {
    sp_response($section);
}

# ****** IMPORTANT: THE FINAL $section values MUST be the same as the SPBUILD constant
# ******			for the Upgrade to complete correctly

die();
