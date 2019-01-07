/* Simple:Press Version 5.0 Install/Upgrade */

var messageStrings;
var installProgress;

/* ======================================== */
/*											*/
/*				SP INSTALLER				*/
/*			  Simple:Press 5.0.0			*/
/*											*/
/* ======================================== */
function spjPerformInstall(phpUrl, phaseCount, currentPhase, subPhaseCount, currentSubPhase, image, messages) {
	try {
		var phaseTotal = (parseInt(phaseCount) + parseInt(subPhaseCount));

		/* If first time in - load up message strings and initialize progress */
		if (currentPhase == 0) {
			var installtext = new String(messages);
			messageStrings = installtext.split("@");

			/* display installing message and set up progress bar */
			jQuery('#imagezone').html('<p><br /><img src="' + image + '" /><br />' + messageStrings[1] + '<br /></p>');
			jQuery('#imagezone').fadeIn('slow');
			jQuery("#progressbar").progressbar({ value: 0 });
			installProgress = 0;
		} else {
			installProgress++;
		}

		/* update progress bar */
		var currentProgress  = ((installProgress / phaseTotal) * 100);
		jQuery("#progressbar").progressbar('option', 'value', currentProgress);

		/* do next phase/build section */
		var thisUrl = phpUrl + '&phase=' + currentPhase;
		var target = "#zone" + currentPhase;
		if (currentPhase == 8 && currentSubPhase < (subPhaseCount+1)) {
			thisUrl = thisUrl + '&subphase=' + currentSubPhase;
		}

		jQuery(target).load(thisUrl, function(a, b) {
			/* check for errors first */
			var retVal = a.substr(0,13);

			jQuery(target).fadeIn('slow');

			if (retVal == 'Install Error') {
				jQuery('#imagezone').html('<p>' + messageStrings[3] + '</p>');
				return;
			}

			if (currentPhase == 8) {
				currentSubPhase++;
				if (currentSubPhase > subPhaseCount)  { currentPhase++; }
			} else {
				currentPhase++;
			}

			/* are we finished yet */
			if(currentPhase > phaseCount) {
				jQuery("#progressbar").progressbar('option', 'value', 100);
				jQuery('#finishzone').html('<p>' + spjEndInstall(messageStrings[0]) + '</p>');
				jQuery('#imagezone').html('<p>' + messageStrings[2] + '</p>');
				return;
			} else {
				spjPerformInstall(phpUrl, phaseCount, currentPhase, subPhaseCount, currentSubPhase, image, messages);
			}
		});
	}

	catch(e) {
		var iZone = document.getElementById('imagezone');
		var eZone = document.getElementById('errorzone');
		iZone.innerHTML = '<p>PROBLEM - The Install can not be completed</p>';
		var abortMsg = "<p>There is a problem with the JavaScript being loaded on this page which is stopping the upgrade from being completed.<br />";
		abortMsg += "The error being reported is: " + e.message + '</p>';
		eZone.innerHTML = abortMsg;
		iZone.style.display="block";
		eZone.style.display="block";
	}
}

/* ======================================== */
/*											*/
/*				SP UPGRADER				*/
/*			  Simple:Press 5.0.0			*/
/*											*/
/* ======================================== */
function spjPerformUpgrade(phpUrl, startBuild, endBuild, currentBuild, image, messages, homeUrl) {
	try {
		var currentProgress = 0;
		var buildSpan = (endBuild - startBuild);

		/* If first time in - load up message strings and initialize progress */
		if (messageStrings == null) {
			var installtext = new String(messages);
			messageStrings = installtext.split("@");

			/* display upgrading message and progressbar */
			jQuery('#imagezone').html('<p><br /><img src="' + image + '" /><br />' + messageStrings[1] + '<br /></p>');
			jQuery('#imagezone').fadeIn('slow');
			jQuery("#progressbar").progressbar({ value: 0 });
		} else {
			/* calculate progress so far */
			cValue = (buildSpan - (endBuild - currentBuild));
			currentProgress  = ((cValue / buildSpan) * 100);
		}

		/* update progress bar */
		jQuery("#progressbar").progressbar('option', 'value', currentProgress);

		/* do next phase/build section */
		var thisUrl = phpUrl + '&start=' + currentBuild;
		jQuery('#errorzone').load(thisUrl, function(a, b) {
    		try {
                var stripped = a.split('%%%marker%%%');
    			var response = jQuery.parseJSON(stripped[1]);
                /* valid response if we get here - if was invalid, will go to catch */
                if (response.status == 'success') { /* check for success or error */
                    returnVal = response.section; /* get completed section */
        			if (returnVal == endBuild) {
                        /* last section complete - finish up */
        				jQuery('#finishzone').html('<p>' + spjEndUpgrade(messageStrings[0], messageStrings[4], homeUrl) + '</p>');
        				jQuery('#imagezone').html('<p>' + messageStrings[2] + '</p>');
        				jQuery("#progressbar").progressbar('option', 'value', 100);
        				return;
        			} else {
        			     /* run next upgrade section */
      					spjPerformUpgrade(phpUrl, startBuild, endBuild, returnVal, image, messages, homeUrl);
        			}
                } else {
                    /* output our error message */
    				jQuery('#errorzone').html('<p>' + messageStrings[3] + '<br />current build: ' + currentBuild + '<br />error: ' + response.error + '</p><div style="clear:both"></div>');
    				jQuery('#errorzone').fadeIn('slow');
                    return;
                }
			}
			catch (e) {
                /* a valid json response was not issued so error */
				jQuery('#errorzone').html('<p>' + messageStrings[3] + '<br />current build: ' + currentBuild + '<br />' + a + '</p><div style="clear:both"></div>');
				jQuery('#errorzone').fadeIn('slow');
				return;
			}
		});
	}

	catch(e) {
		var iZone = document.getElementById('imagezone');
		var eZone = document.getElementById('errorzone');
		iZone.innerHTML = '<p>PROBLEM - The Upgrade can not be completed</p>';
		var abortMsg = "<p>There is a problem with the JavaScript being loaded on this page which is stopping the upgrade from being completed.<br />";
		abortMsg += "The error being reported is: " + e.message + '</p>';
		eZone.innerHTML = abortMsg;
		iZone.style.display="block";
		eZone.style.display="block";
	}
}

function spjEndInstall(messagetext) {
	return '<form name="sfinstalldone" method="post" action="admin.php?page=simple-press/admin/panel-forums/spa-forums.php"><br /><input type="hidden" name="install" value="1" /><input type="submit" class="button-primary" name="goforuminstall" value="' + messagetext + '" /></form>';
}

function spjEndUpgrade(admintext, forumtext, homeUrl) {
	return '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="admin.php?page=simple-press/admin/panel-toolbox/spa-toolbox.php&tab=changelog"><input type="submit" class="button-primary" name="goforumupgrade" value="' + admintext + '" /></a>&nbsp;&nbsp;<a href="' + homeUrl + '"><input type="submit" class="button-primary" name="goforumupgrade" value="' + forumtext + '" /></a>';
}