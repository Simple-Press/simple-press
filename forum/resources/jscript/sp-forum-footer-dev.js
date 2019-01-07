/* ---------------------------------
Simple:Press - Version 5.0
Forum Javascript loaded in footer after page loads

$LastChangedDate: 2017-04-15 09:51:19 -0500 (Sat, 15 Apr 2017) $
$Rev: 15346 $
------------------------------------ */

jQuery(document).ready(function() {
    /* set up tooltips */
	if (sp_platform_vars.device == 'desktop' && sp_platform_vars.tooltips == true) {
		jQuery(document).tooltip( {
			tooltipClass: 'ttip',
			position: {
				my: sp_platform_vars.tooltipmy,
				at: sp_platform_vars.tooltipat
			},
			track: false,
			content: function() {
                if (jQuery(this).prop('nodeName') == 'IFRAME' || jQuery(this).closest('.mce-container').length) {
					return '';
				} else {
					return jQuery(this).attr('title');
				}
			}
		});
	}

	if (jQuery('#spQuickLinksTopic').length) {
		/* move the quicklinks html */
        jQuery('#spQuickLinksTopic').append(jQuery('#qlContent'));

		/* Quicklinks selects */
		jQuery('#spQuickLinksForumSelect, #spQuickLinksTopicSelect').msDropDown();
		jQuery('#spQuickLinksForum').show();
		jQuery('#spQuickLinksTopic').show();
	}

    /* if fragment postID and head padding add padding */
	var hash = jQuery(location).attr('hash');
	if (hash && sp_platform_vars.headpadding > 0) {
        var h = hash.split("#");
        hash = '#' + h[1];
		jQuery('html, body').animate({scrollTop: (Math.round(jQuery(hash).offset().top) - parseInt(sp_platform_vars.headpadding))}, 'fast');
	}

    /* pre-load 'wait' imag */
	waitImage = new Image(32,32);
	waitImage.src = sp_platform_vars.waitimage;
	successImage = new Image(32,32);
	successImage.src = sp_platform_vars.successimage;
	failureImage = new Image(32,32);
	failureImage.src = sp_platform_vars.failimage;

    /* check if this is a redirect from a failed save */
	if (sp_platform_vars.pageview == 'topic' || sp_platform_vars.pageview == 'forum') {
		if (jQuery('#spPostNotifications').html() != null) {
			if (jQuery('#spPostNotifications').html() != '') {
				jQuery('#spPostNotifications').show();
				spjOpenEditor('spPostForm', 'post');
			}
		}
	}

	if (sp_platform_vars.autoupdate) spjAutoUpdate(sp_platform_vars.autoupdatelist, sp_platform_vars.autoupdatetime);

	try {
        /* fix for Bootstrap stealing button object from jQuery UI */
		var btn = jQuery.fn.button.noConflict(); // reverts $.fn.button to jqueryui btn
		jQuery.fn.btn = btn; // assigns bootstrap button functionality to $.fn.btn
	} catch (e) { }

    /* Show message if leaving page during edit */
	if (sp_platform_vars.pageview == 'topic' || sp_platform_vars.pageview == 'forum') {
		var showConfirm = false;
		var edContent = '';

        function confirmExit() {
        	if (sp_platform_vars.saveprocess == 0 && jQuery('#spPostForm').css('display') == 'block') {
        		if (parseInt(sp_platform_vars.editor) == 1) edContent = tinymce.activeEditor.getContent();
        		if (showConfirm || edContent != '') return sp_forum_vars.lostentry;
        	}
        }

		window.onbeforeunload = confirmExit;
		if (parseInt(sp_platform_vars.editor) != 1) {
			jQuery('#postitem').keyup(function() {
				showConfirm = true;
			});
		}
    }

    /* handle theme customizer test mode */
    if (sp_platform_vars.customizertest) {
    	jQuery('a').removeAttr('href');
    	jQuery('a').removeAttr('onclick');
    }

});
