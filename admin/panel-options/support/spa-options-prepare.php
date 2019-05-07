<?php
/*
Simple:Press
Admin Options General Support Functions
$LastChangedDate: 2018-12-03 11:05:54 -0600 (Mon, 03 Dec 2018) $
$Rev: 15840 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_get_global_data() {
	$sfoptions = array();
	$sfoptions['sflockdown'] = SP()->options->get('sflockdown');

	# auto update
	$sfauto = SP()->options->get('sfauto');
	$sfoptions['sfautoupdate'] = $sfauto['sfautoupdate'];
	$sfoptions['sfautotime'] = $sfauto['sfautotime'];

	$sfrss = SP()->options->get('sfrss');
	$sfoptions['sfrsscount'] = $sfrss['sfrsscount'];
	$sfoptions['sfrsswords'] = $sfrss['sfrsswords'];
	$sfoptions['sfrssfeedkey'] = $sfrss['sfrssfeedkey'];
	$sfoptions['sfrsstopicname'] = $sfrss['sfrsstopicname'];

	$sfblock = SP()->options->get('sfblockadmin');
	$sfoptions['blockadmin'] = $sfblock['blockadmin'];
	$sfoptions['blockredirect'] = SP()->displayFilters->url($sfblock['blockredirect']);
	$sfoptions['blockprofile'] = $sfblock['blockprofile'];
    $sfoptions['blockroles'] = $sfblock['blockroles'];

	$sfoptions['defeditor'] = SP()->options->get('speditor');
	if (!isset($sfoptions['defeditor']) || empty($sfoptions['defeditor'])) $sfoptions['defeditor'] = 4;
	$sfoptions['editpostdays'] = SP()->options->get('editpostdays');

	$sfoptions['combinecss'] = SP()->options->get('combinecss');
	$sfoptions['combinejs'] = SP()->options->get('combinejs');

	$spError = SP()->options->get('spErrorOptions');
	$sfoptions['errorlog'] = $spError['spErrorLogOff'];
	$sfoptions['notices']  = $spError['spNoticesOff'];

	$sfoptions['floodcontrol'] = SP()->options->get('floodcontrol');

	return $sfoptions;
}

function spa_get_display_data() {
	$sfdisplay = SP()->options->get('sfdisplay');
	$sfcontrols = SP()->options->get('sfcontrols');

	# Page title
	$sfoptions['sfnotitle'] = $sfdisplay['pagetitle']['notitle'];
	$sfoptions['sfbanner']  = SP()->displayFilters->url($sfdisplay['pagetitle']['banner']);

	# Stats
	$sfoptions['showtopcount']	= $sfcontrols['showtopcount'];
	$sfoptions['shownewcount']	= $sfcontrols['shownewcount'];
	$sfoptions['hidemembers']	= $sfcontrols['hidemembers'];
	$sfoptions['statsinterval']	= SP()->options->get('sp_stats_interval') / 3600; # display in hours

	$sfoptions['sfsingleforum'] = $sfdisplay['forums']['singleforum'];

	$sfoptions['sfpagedtopics'] = $sfdisplay['topics']['perpage'];
	$sfoptions['sftopicsort'] 	= $sfdisplay['topics']['sortnewtop'];

	$sfoptions['sfpagedposts'] 	= $sfdisplay['posts']['perpage'];
	$sfoptions['sfsortdesc'] 	= $sfdisplay['posts']['sortdesc'];

	$sfoptions['sftoolbar']		= $sfdisplay['editor']['toolbar'];

	return $sfoptions;
}

function spa_get_content_data() {
	$sfoptions = array();

	# image resizing
	$sfimage = SP()->options->get('sfimage');
	$sfoptions['sfimgenlarge'] = $sfimage['enlarge'];
	$sfoptions['sfthumbsize'] = $sfimage['thumbsize'];
	$sfoptions['style'] = $sfimage['style'];
	$sfoptions['process'] = $sfimage['process'];
	$sfoptions['constrain'] = $sfimage['constrain'];
	$sfoptions['forceclear'] = $sfimage['forceclear'];

	$sfoptions['sfdates'] = SP()->options->get('sfdates');
	$sfoptions['sftimes'] = SP()->options->get('sftimes');

	if (empty($sfoptions['sfdates'])) $sfoptions['sfdates'] = 'j F Y';
	if (empty($sfoptions['sftimes'])) $sfoptions['sftimes'] = 'g:i a';

	# link filters
	$sffilters = SP()->options->get('sffilters');
	$sfoptions['sfnofollow'] = $sffilters['sfnofollow'];
	$sfoptions['sftarget'] = $sffilters['sftarget'];
	$sfoptions['sfurlchars'] = $sffilters['sfurlchars'];
	$sfoptions['sffilterpre'] = $sffilters['sffilterpre'];
	$sfoptions['sfmaxlinks'] = $sffilters['sfmaxlinks'];
	$sfoptions['sfnolinksmsg'] = SP()->editFilters->text($sffilters['sfnolinksmsg']);
	$sfoptions['sfdupemember'] = $sffilters['sfdupemember'];
	$sfoptions['sfdupeguest'] = $sffilters['sfdupeguest'];
	$sfoptions['sfmaxsmileys'] = $sffilters['sfmaxsmileys'];

	# shortcode filtering
	$sfoptions['sffiltershortcodes'] = SP()->options->get('sffiltershortcodes');
	$sfoptions['sfshortcodes'] = SP()->editFilters->text(SP()->options->get('sfshortcodes'));

	return $sfoptions;
}

function spa_get_members_data() {
	$sfoptions = array();

	$sfmemberopts = SP()->options->get('sfmemberopts');
	$sfoptions['sfcheckformember'] = $sfmemberopts['sfcheckformember'];
	$sfoptions['sfhidestatus'] = $sfmemberopts['sfhidestatus'];

	$sfguests = SP()->options->get('sfguests');
	$sfoptions['reqemail'] = $sfguests['reqemail'];
	$sfoptions['storecookie'] = $sfguests['storecookie'];

	$sfuser = SP()->options->get('sfuserremoval');
	$sfoptions['sfuserremove'] = $sfuser['sfuserremove'];
	$sfoptions['sfuserperiod'] = $sfuser['sfuserperiod'];
	$sfoptions['sfuserinactive'] = $sfuser['sfuserinactive'];
	$sfoptions['sfusernoposts'] = $sfuser['sfusernoposts'];

	$sfoptions['account-name'] = SP()->options->get('account-name');
	$sfoptions['display-name'] = SP()->options->get('display-name');
	$sfoptions['guest-name'] = SP()->options->get('guest-name');

	# cron scheduled?
	$sfoptions['sched'] = wp_get_schedule('sph_cron_user');

	$sfoptions['post_count_delete'] = SP()->options->get('post_count_delete');
	
	$sfoptions['display_deprecated_identities'] = SP()->options->get('display_deprecated_identities');

	$sfprofile = SP()->options->get('sfprofile');
	$sfoptions['namelink'] = $sfprofile['namelink'];
	
	$sfPrivacy = SP()->options->get('spPrivacy');
	$sfoptions['posts'] = $sfPrivacy['posts'];
	$sfoptions['number'] = $sfPrivacy['number'];
	$sfoptions['erase'] = $sfPrivacy['erase'];
	$sfoptions['mess'] = $sfPrivacy['mess'];

	return $sfoptions;
}

function spa_get_email_data() {
	$sfoptions = array();

	# Load New User Email details
	$sfmail = SP()->options->get('sfnewusermail');
	$sfoptions['sfusespfreg'] = $sfmail['sfusespfreg'];
	$sfoptions['sfnewusersubject'] = SP()->displayFilters->title($sfmail['sfnewusersubject']);
	$sfoptions['sfnewusertext'] = SP()->displayFilters->title($sfmail['sfnewusertext']);

	# Load Email Filter Options
	$sfmail = SP()->options->get('sfmail');
	$sfoptions['sfmailsender'] = $sfmail['sfmailsender'];
	$sfoptions['sfmailfrom'] = $sfmail['sfmailfrom'];
	$sfoptions['sfmaildomain'] = $sfmail['sfmaildomain'];
	$sfoptions['sfmailuse'] = $sfmail['sfmailuse'];

	return $sfoptions;
}

function spa_get_newposts_data() {
	$sfcontrols = SP()->options->get('sfcontrols');

	$sfoptions['sfdefunreadposts'] = $sfcontrols['sfdefunreadposts'];
	$sfoptions['sfusersunread'] = $sfcontrols['sfusersunread'];
	$sfoptions['sfmaxunreadposts'] = $sfcontrols['sfmaxunreadposts'];

	$sfoptions['flagsuse'] = $sfcontrols['flagsuse'];
	$sfoptions['flagstext'] = $sfcontrols['flagstext'];
	$sfoptions['flagsbground'] = $sfcontrols['flagsbground'];
	$sfoptions['flagscolor'] = $sfcontrols['flagscolor'];

	$sfoptions['topiccache'] = SP()->options->get('topic_cache');

	return $sfoptions;
}

/**
 * Iconsets listing table
 */
function spa_paint_iconsets_table() {
	

	$iconsets = spa_get_all_iconsets();
	
	
	# start the table display
	echo '<div>';
	echo '<table id="spIconsetsList" class="widefat fixed striped spMobileTable1280">';

	foreach ($iconsets as $id => $iconset_options ) {
			
		$active = $iconset_options['active'];
			
		?>

		<tr>
			<td><?php echo $iconset_options['name']; ?></td>
			<td>
				<span class="item-controls"><?php
				
				$toggle_img = SPADMINIMAGES. 'sp_' . ( $active ? 'Yes' : 'No' ) . '.png';
				$toggle_action = $active ? 'Disable' : 'Enable';
				$toggle_link = esc_url( wp_nonce_url( SPAJAXURL . "options&amp;targetaction=" . strtolower( $toggle_action ) . "iconset&amp;iconset={$id}", 'options' ) );

				printf( '<img src="%s" title="%s" alt="" style="vertical-align: middle;cursor:pointer;" class="spToggleRowReload" data-url="%s" data-reload="acciconsets" />&nbsp;&nbsp;', $toggle_img, SP()->primitives->admin_text( $toggle_action ), $toggle_link );


				$site = esc_url( wp_nonce_url( SPAJAXURL . "options&amp;targetaction=deliconset&amp;iconset={$id}", 'options' ) );
				printf( '<img src="%s" title="%s" alt="" style="vertical-align: middle;cursor:pointer;" class="spDeleteRowReload" data-url="%s" data-reload="acciconsets" />&nbsp;&nbsp;', SPCOMMONIMAGES.'delete.png', SP()->primitives->admin_text( 'Delete Iconset' ), $site );
						
						
						
				echo '</span>';
				?>
			</td>
		</tr>
			
		<?php
			
		}
	
	
	echo '</table>';
	echo '</div>';

	
}