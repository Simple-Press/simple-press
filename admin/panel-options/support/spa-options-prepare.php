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
	if($sfauto){
		$sfoptions['sfautoupdate'] = isset($sfauto['sfautoupdate']) ? $sfauto['sfautoupdate'] : false;
		$sfoptions['sfautotime'] = isset($sfauto['sfautotime']) ? $sfauto['sfautotime'] : '' ;
	}
	
	$sfrss = SP()->options->get('sfrss');
	if($sfrss){
		$sfoptions['sfrsscount'] = isset($sfrss['sfrsscount']) ? $sfrss['sfrsscount'] : null ;
		$sfoptions['sfrsswords'] = isset($sfrss['sfrsswords']) ? $sfrss['sfrsswords'] : null;
		$sfoptions['sfrssfeedkey'] = isset($sfrss['sfrssfeedkey']) ? $sfrss['sfrssfeedkey'] : false;
		$sfoptions['sfrsstopicname'] = isset($sfrss['sfrsstopicname']) ? $sfrss['sfrsstopicname'] : false;
	}
	

	$sfblock = SP()->options->get('sfblockadmin');
	if(!empty($sfblock)){
		$sfoptions['blockadmin'] = isset($sfblock['blockadmin']) ? $sfblock['blockadmin'] : false;
		$sfoptions['blockredirect'] = SP()->displayFilters->url(isset($sfblock['blockredirect']) ? $sfblock['blockredirect'] : "");
		$sfoptions['blockprofile'] = isset($sfblock['blockprofile']) ? $sfblock['blockprofile'] : false;
		$sfoptions['blockroles'] = isset($sfblock['blockroles']) ? $sfblock['blockroles'] : null;
	}
	

	$sfoptions['defeditor'] = SP()->options->get('speditor');
	if (!isset($sfoptions['defeditor']) || empty($sfoptions['defeditor'])) $sfoptions['defeditor'] = 4;
	$sfoptions['editpostdays'] = SP()->options->get('editpostdays');

	$sfoptions['combinecss'] = SP()->options->get('combinecss');
	$sfoptions['combinejs'] = SP()->options->get('combinejs');

	$spError = SP()->options->get('spErrorOptions');
	if($spError){
		$sfoptions['errorlog'] = isset($spError['spErrorLogOff']) ? $spError['spErrorLogOff'] : false;
		$sfoptions['notices']  = isset($spError['spNoticesOff']) ? $spError['spNoticesOff'] : false;
	}

	$sfoptions['floodcontrol'] = SP()->options->get('floodcontrol');

	return $sfoptions;
}

function spa_get_display_data() {
	$sfdisplay = SP()->options->get('sfdisplay');
	$sfcontrols = SP()->options->get('sfcontrols');

	# Page title
	$sfoptions['sfnotitle'] = isset($sfdisplay['pagetitle']['notitle']) ? $sfdisplay['pagetitle']['notitle'] : false;
	$sfoptions['sfbanner']  = SP()->displayFilters->url(isset($sfdisplay['pagetitle']['banner']) ? $sfdisplay['pagetitle']['banner']: '');

	# Stats
	if(!empty($sfcontrols)){
		$sfoptions['showtopcount']	= isset($sfcontrols['showtopcount']) ? $sfcontrols['showtopcount'] : null;
		$sfoptions['shownewcount']	= isset($sfcontrols['shownewcount']) ? $sfcontrols['shownewcount'] : null;
		$sfoptions['hidemembers']	= isset($sfcontrols['hidemembers']) ? $sfcontrols['hidemembers'] : false;
	}
	
	$sfoptions['statsinterval']	= SP()->options->get('sp_stats_interval') / 3600; # display in hours

	
	if($sfdisplay){
		$sfoptions['sfsingleforum'] = isset($sfdisplay['forums']['singleforum']) ? $sfdisplay['forums']['singleforum'] : false ;
		
		$sfoptions['sfpagedtopics'] = isset($sfdisplay['topics']['perpage']) ? $sfdisplay['topics']['perpage'] : '';
		$sfoptions['sftopicsort'] 	= isset($sfdisplay['topics']['sortnewtop']) ? $sfdisplay['topics']['sortnewtop'] : false;
	
		$sfoptions['sfpagedposts'] 	= isset($sfdisplay['posts']['perpage']) ? $sfdisplay['posts']['perpage'] : '';
		$sfoptions['sfsortdesc'] 	= isset($sfdisplay['posts']['sortdesc']) ? $sfdisplay['posts']['sortdesc'] : false;
	
		$sfoptions['sftoolbar']		= isset($sfdisplay['editor']['toolbar']) ? $sfdisplay['editor']['toolbar'] : false;
	}
	

	return $sfoptions;
}

function spa_get_content_data() {
	$sfoptions = array();

	# image resizing
	$sfimage = SP()->options->get('sfimage');
	if($sfimage){
		$sfoptions['sfimgenlarge'] = isset($sfimage['enlarge']) ? $sfimage['enlarge'] : false;
		$sfoptions['sfthumbsize'] = isset($sfimage['thumbsize']) ? $sfimage['thumbsize'] : '';
		$sfoptions['style'] = $sfimage['style'];
		$sfoptions['process'] = isset($sfimage['process']) ? $sfimage['process'] : false;
		$sfoptions['constrain'] = isset($sfimage['constrain']) ? $sfimage['constrain'] : false;
		$sfoptions['forceclear'] = isset($sfimage['forceclear']) ? $sfimage['forceclear'] : false;
	}
	

	$sfoptions['sfdates'] = SP()->options->get('sfdates');
	$sfoptions['sftimes'] = SP()->options->get('sftimes');

	if (empty($sfoptions['sfdates'])) $sfoptions['sfdates'] = 'j F Y';
	if (empty($sfoptions['sftimes'])) $sfoptions['sftimes'] = 'g:i a';

	# link filters
	$sffilters = SP()->options->get('sffilters');
	if($sffilters){
		$sfoptions['sfnofollow'] = isset($sffilters['sfnofollow']) ? $sffilters['sfnofollow'] : false;
		$sfoptions['sftarget'] = isset($sffilters['sftarget']) ? $sffilters['sftarget'] : false;
		$sfoptions['sfurlchars'] = isset($sffilters['sfurlchars']) ? $sffilters['sfurlchars'] : '';
		$sfoptions['sffilterpre'] = isset($sffilters['sffilterpre']) ? $sffilters['sffilterpre'] : false;
		$sfoptions['sfmaxlinks'] = isset($sffilters['sfmaxlinks']) ? $sffilters['sfmaxlinks'] : '';
		$sfoptions['sfnolinksmsg'] = SP()->editFilters->text(isset($sffilters['sfnolinksmsg']) ? $sffilters['sfnolinksmsg']:'');
		$sfoptions['sfdupemember'] = isset($sffilters['sfdupemember']) ? $sffilters['sfdupemember'] : false;
		$sfoptions['sfdupeguest'] = isset($sffilters['sfdupeguest']) ? $sffilters['sfdupeguest'] : false;
		$sfoptions['sfmaxsmileys'] = isset($sffilters['sfmaxsmileys']) ? $sffilters['sfmaxsmileys'] : '';
	
	}
	
	# shortcode filtering
	$sfoptions['sffiltershortcodes'] = SP()->options->get('sffiltershortcodes');
	$sfoptions['sfshortcodes'] = SP()->editFilters->text(SP()->options->get('sfshortcodes'));

	return $sfoptions;
}

function spa_get_members_data() {
	$sfoptions = array();

	$sfmemberopts = SP()->options->get('sfmemberopts');
	if($sfmemberopts){
		$sfoptions['sfcheckformember'] = isset($sfmemberopts['sfcheckformember']) ? $sfmemberopts['sfcheckformember'] : false;
		$sfoptions['sfhidestatus'] = isset($sfmemberopts['sfhidestatus']) ? $sfmemberopts['sfhidestatus'] : false;
	}
	

	$sfguests = SP()->options->get('sfguests');
	if($sfguests) {
		$sfoptions['reqemail'] = isset($sfguests['reqemail']) ? $sfguests['reqemail'] : false;
		$sfoptions['storecookie'] = isset($sfguests['storecookie']) ? $sfguests['storecookie'] : false;
	}

	$sfuser = SP()->options->get('sfuserremoval');
	if($sfuser) {
		$sfoptions['sfuserremove'] = isset($sfuser['sfuserremove']) ? $sfuser['sfuserremove'] : false;
		$sfoptions['sfuserperiod'] = isset($sfuser['sfuserperiod']) ? $sfuser['sfuserperiod'] : null;
		$sfoptions['sfuserinactive'] = isset($sfuser['sfuserinactive']) ? $sfuser['sfuserinactive'] : false;
		$sfoptions['sfusernoposts'] = isset($sfuser['sfusernoposts']) ? $sfuser['sfusernoposts'] : false;
	}

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
	if($sfPrivacy){
		$sfoptions['posts'] = isset($sfPrivacy['posts']) ? $sfPrivacy['posts'] : false;
		$sfoptions['number'] = isset($sfPrivacy['number']) ? $sfPrivacy['number'] : '';
		$sfoptions['erase'] = $sfPrivacy['erase'];
		$sfoptions['mess'] = isset($sfPrivacy['mess']) ? $sfPrivacy['mess'] : '';
	}
	

	return $sfoptions;
}

function spa_get_email_data() {
	$sfoptions = array();

	# Load New User Email details
	$sfmail = SP()->options->get('sfnewusermail');
	if($sfmail){
		$sfoptions['sfusespfreg'] = isset($sfmail['sfusespfreg']) ? $sfmail['sfusespfreg']:false;
		$sfoptions['sfnewusersubject'] = SP()->displayFilters->title(isset($sfmail['sfnewusersubject'])?$sfmail['sfnewusersubject']:'');
		$sfoptions['sfnewusertext'] = SP()->displayFilters->title(isset($sfmail['sfnewusertext'])? $sfmail['sfnewusertext'] :'');
	}

	# Load Email Filter Options
	$sfmail = SP()->options->get('sfmail');
	if($sfmail){
		$sfoptions['sfmailsender'] = isset($sfmail['sfmailsender']) ? $sfmail['sfmailsender'] : '';
		$sfoptions['sfmailfrom'] = isset($sfmail['sfmailfrom']) ? $sfmail['sfmailfrom'] : '';
		$sfoptions['sfmaildomain'] = isset($sfmail['sfmaildomain']) ? $sfmail['sfmaildomain'] : '';
		$sfoptions['sfmailuse'] = isset($sfmail['sfmailuse']) ? $sfmail['sfmailuse']:false;
	}

	return $sfoptions;
}

function spa_get_newposts_data() {
	$sfcontrols = SP()->options->get('sfcontrols');
	if($sfcontrols){
		$sfoptions['sfdefunreadposts'] = $sfcontrols['sfdefunreadposts']; // 50 default
		$sfoptions['sfusersunread'] = isset($sfcontrols['sfusersunread']) ? $sfcontrols['sfusersunread'] : false;
		$sfoptions['sfmaxunreadposts'] = $sfcontrols['sfmaxunreadposts']; 

		$sfoptions['flagsuse'] = isset($sfcontrols['flagsuse']) ? $sfcontrols['flagsuse'] : false;
		$sfoptions['flagstext'] = isset($sfcontrols['flagstext']) ? $sfcontrols['flagstext'] : '';
		$sfoptions['flagsbground'] = $sfcontrols['flagsbground'];
		$sfoptions['flagscolor'] = $sfcontrols['flagscolor'];
	}

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
				<span class="sf-item-controls"><?php
				
				$toggle_img = $active ? 'sf-check' : 'sf-no-check';
				$toggle_action = $active ? 'Disable' : 'Enable';
				$toggle_link = esc_url( wp_nonce_url( SPAJAXURL . "options&amp;targetaction=" . strtolower( $toggle_action ) . "iconset&amp;iconset={$id}", 'options' ) );

				printf( '<span class="sf-icon %s spToggleRowReload" title="%s" data-url="%s" data-reload="acciconsets"></span>', $toggle_img, SP()->primitives->admin_text( $toggle_action ), $toggle_link );


				$site = esc_url( wp_nonce_url( SPAJAXURL . "options&amp;targetaction=deliconset&amp;iconset={$id}", 'options' ) );
				printf( '<span title="%s" class="sf-icon sf-delete spDeleteRowReload" data-url="%s" data-reload="acciconsets"></span>', SP()->primitives->admin_text( 'Delete Iconset' ), $site );
						
						
						
				echo '</span>';
				?>
			</td>
		</tr>
			
		<?php
			
		}
	
	
	echo '</table>';
	echo '</div>';

	
}