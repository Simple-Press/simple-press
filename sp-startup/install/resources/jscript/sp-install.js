/* Simple:Press Version 6.0 Install/Upgrade */

/* ---------------------------------
 Simple:Press - Version 6.0
 Forum Javascript loaded in footer after page loads

 $LastChangedDate: 2016-11-21 09:37:50 -0800 (Mon, 21 Nov 2016) $
 $Rev: 14735 $
 ------------------------------------ */

(function(spj, $, undefined) {
	// private properties
	var messageStrings;
	var installProgress;

	// public properties

	// public methods
	spj.performInstall = function(phpUrl, phaseCount, currentPhase, subPhaseCount, currentSubPhase, image, messages, folder) {
		try {
			var phaseTotal = (parseInt(phaseCount) + parseInt(subPhaseCount));
                        
			currentPhase = parseInt(currentPhase);

			/* If first time in - load up message strings and initialize progress */
			if (currentPhase == 0) {
				var installtext = new String(messages);
				messageStrings = installtext.split("@");

				/* display installing message and set up progress bar */
				//$('#imagezone').html('<p><br /><img src="' + image + '" /><br />' + messageStrings[1] + '<br /></p>');
				//$('#imagezone').fadeIn('slow');
				$("#progressbar").progressbar({value: 0});
				installProgress = 0;
			} else {
				installProgress++;
			}

			/* update progress bar */
			var currentProgress = ((installProgress / phaseTotal) * 100);
			$("#progressbar").progressbar('option', 'value', currentProgress);

			/* do next phase/build section */
			var thisUrl = phpUrl + '&phase=' + currentPhase;
			//var target = "#zone" + currentPhase;
			if (currentPhase == 8 && currentSubPhase < (subPhaseCount + 1)) {
				thisUrl = thisUrl + '&subphase=' + currentSubPhase;
			}

			$("#zone" + currentPhase).addClass('sf-processing')
				.find('.sf-icon').removeClass('sf-waiting').addClass('sf-working');

			$("#zone0").load(thisUrl, function(a, b) {
				/* check for errors first */
				var retVal = a.substr(0, 13);

				if (retVal == 'Install Error') {
					$('#errorzone').html('<p>' + messageStrings[3] + '</p>');
					return;
				}

				$("#zone" + currentPhase).removeClass('sf-processing').addClass('sf-ready')
					.find('.sf-icon').removeClass('sf-working').addClass('sf-check');

				if (currentPhase == 8) {
					currentSubPhase++;
					if (currentSubPhase > subPhaseCount) {
						currentPhase++;
					}
				} else {
					currentPhase++;
				}

				/* are we finished yet */
				if (currentPhase > phaseCount) {
					$("#progressbar").progressbar('option', 'value', 100);
					$("#sfmaincontainer [type=submit]").removeAttr('disabled');

					$("#installation-finished").toggleClass('sfhidden');
					$("#installation-header").html(messageStrings[2])
					return;
				} else {
					spj.performInstall(phpUrl, phaseCount, currentPhase, subPhaseCount, currentSubPhase, image, messages, folder);
				}
			});
		} catch (e) {
			//var iZone = document.getElementById('imagezone');
			var eZone = document.getElementById('errorzone');
			//iZone.innerHTML = '<p>PROBLEM - The Install can not be completed</p>';
			var abortMsg = "<p>There is a problem with the JavaScript being loaded on this page which is stopping the upgrade from being completed.<br />";
			abortMsg += "The error being reported is: " + e.message + '</p>';
			eZone.innerHTML = abortMsg;
			//iZone.style.display = "block";
			eZone.style.display = "block";
		}
	};

	spj.performUpgrade = function(phpUrl, startBuild, endBuild, currentBuild, image, messages, homeUrl, folder) {
		try {
			var currentProgress = 0;
			var buildSpan = (endBuild - startBuild);

			/* If first time in - load up message strings and initialize progress */
			if (messageStrings == null) {
				var installtext = new String(messages);
				messageStrings = installtext.split("@");

				/* display upgrading message and progressbar */
				// $('#imagezone').html('<p><br /><img src="' + image + '" /><br />' + messageStrings[1] + '<br /></p>');
				// $('#imagezone').fadeIn('slow');
				$("#progressbar").progressbar({value: 0});
			} else {
				/* calculate progress so far */
				cValue = (buildSpan - (endBuild - currentBuild));
				currentProgress = ((cValue / buildSpan) * 100);
			}

			/* update progress bar */
			$("#progressbar").progressbar('option', 'value', currentProgress);

			/* do next phase/build section */
			var thisUrl = phpUrl + '&start=' + currentBuild;
			$('#errorzone').load(thisUrl, function(a, b) {
				try {
					var stripped = a.split('%%%marker%%%');
					var response = $.parseJSON(stripped[1]);
					/* valid response if we get here - if was invalid, will go to catch */
					if (response.status == 'success') { /* check for success or error */
						/* see if done or more upgrades to do */
						returnVal = response.section; /* get completed section */
						if (returnVal == endBuild) {
							/* last section complete - finish up */
							$('#errorzone').empty();
							$('#finishzone').html('<h3>' + messageStrings[2] + '</h3>' + '<p>' + endUpgrade(messageStrings[0], messageStrings[4], homeUrl, folder) + '</p>');
							$("#progressbar").progressbar('option', 'value', 100);

							/* any special messages? */
							if (response.message != '') {
								$('#imagezone').append('<p>' + response.message + '</p>');
							}
							return;
						} else {
							/* run next upgrade section */
							spj.performUpgrade(phpUrl, startBuild, endBuild, returnVal, image, messages, homeUrl, folder);
						}
					} else {
						/* output our error message */
						$('#errorzone').html('<p>' + messageStrings[3] + '<br />current build: ' + currentBuild + '<br />error: ' + response.error + '</p><div style="clear:both"></div>');
						$('#errorzone').fadeIn('slow');
						return;
					}
				} catch (e) {
					/* a valid json response was not issued so error */
					$('#errorzone').html('<p>' + messageStrings[3] + '<br />current build: ' + currentBuild + '<br />' + a + '</p><div style="clear:both"></div>');
					$('#errorzone').fadeIn('slow');
					return;
				}
			});
		} catch (e) {
			var iZone = document.getElementById('imagezone');
			var eZone = document.getElementById('errorzone');
			iZone.innerHTML = '<p>PROBLEM - The Upgrade can not be completed</p>';
			var abortMsg = "<p>There is a problem with the JavaScript being loaded on this page which is stopping the upgrade from being completed.<br />";
			abortMsg += "The error being reported is: " + e.message + '</p>';
			eZone.innerHTML = abortMsg;
			iZone.style.display = "block";
			eZone.style.display = "block";
		}
	};

	// private methods
	function endInstall(messagetext, folder) {
		return '<form name="sfinstalldone" method="post" action="admin.php?page=' + folder + '/admin/panel-forums/spa-forums.php"><br /><input type="hidden" name="install" value="1" /><input type="submit" class="sf-button-primary" name="goforuminstall" value="' + messagetext + '" /></form>';
	}

	function endUpgrade(admintext, forumtext, homeUrl, folder) {
		return '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="admin.php?page=' + folder + '/admin/panel-toolbox/spa-toolbox.php&tab=changelog"><input type="submit" class="sf-button-primary" name="goforumupgrade" value="' + admintext + '" /></a>&nbsp;&nbsp;<a href="' + homeUrl + '"><input type="submit" class="sf-button-primary" name="goforumupgrade" value="' + forumtext + '" /></a>';
	}

}(window.spj = window.spj || {}, jQuery));
