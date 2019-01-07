/* ---------------------------------
 Simple:Press - Version 5.0
 Forum Javascript loaded in footer after page loads

 $LastChangedDate: 2017-12-31 06:56:06 -0600 (Sun, 31 Dec 2017) $
 $Rev: 15612 $
 ------------------------------------ */

(function(spj, $, undefined) {
	// private properties

	// public properties

	// public methods
	$(document).ready(function() {
		setupTooltips();
		moveQuickLinks();
		headPadding();
		preLoadImages();
		failedSaveCheck();

		if (sp_platform_vars.autoupdate) {
			spj.autoUpdate(sp_platform_vars.autoupdatelist, sp_platform_vars.autoupdatetime);
		}

		bootstrapFix();
		pageLeaveCheck();
		customizerTest();
	});

	// private methods
	function setupTooltips() {
		if (sp_platform_vars.device == 'desktop' && sp_platform_vars.tooltips == true) {
			$(document).tooltip({
				tooltipClass: 'ttip',
				position: {
					my: sp_platform_vars.tooltipmy,
					at: sp_platform_vars.tooltipat
				},
				track: false,
				content: function() {
					if ($(this).prop('nodeName') == 'IFRAME' || $(this).closest('.mce-container').length) {
						return '';
					} else {
						return $(this).attr('title');
					}
				}
			});
		}
	}

	function moveQuickLinks() {
		if ($('#spQuickLinksTopic').length) {
			/* move the quicklinks html */
			$('#spQuickLinksTopic').append($('#qlContent'));

			/* Quicklinks selects */
			$('#spQuickLinksForumSelect, #spQuickLinksTopicSelect').msDropDown();
			$('#spQuickLinksForum').show();
			$('#spQuickLinksTopic').show();
		}
	}

	function headPadding() {
		var hash = $(location).attr('hash');
		if (hash && sp_platform_vars.headpadding > 0) {
			var h = hash.split("#");
			hash = '#' + h[1];
			$('html, body').animate({scrollTop: (Math.round($(hash).offset().top) - parseInt(sp_platform_vars.headpadding))}, 'fast');
		}
	}

	function preLoadImages() {
		waitImage = new Image(32, 32);
		waitImage.src = sp_platform_vars.waitimage;
		successImage = new Image(32, 32);
		successImage.src = sp_platform_vars.successimage;
		failureImage = new Image(32, 32);
		failureImage.src = sp_platform_vars.failimage;
	}

	function failedSaveCheck() {
		if (sp_platform_vars.pageview == 'topic' || sp_platform_vars.pageview == 'forum') {
			if ($('#spPostNotifications').html() != null) {
				if ($('#spPostNotifications').html() != '') {
					$('#spPostNotifications').show();
					spj.openEditor('spPostForm', 'post');
				}
			}
		}
	}

	function bootstrapFix() {
		try {
			/* fix for Bootstrap stealing button object from $ UI */
			var btn = $.fn.button; // reverts $.fn.button to jqueryui btn
			$.fn.btn = btn; // assigns bootstrap button functionality to $.fn.btn
		} catch (e) {
		}
	}

	function pageLeaveCheck() {
		/* Show message if leaving page during edit */
		if (sp_platform_vars.pageview == 'topic' || sp_platform_vars.pageview == 'forum') {
			var showConfirm = false;
			var edContent = '';

			function confirmExit() {
				if (sp_platform_vars.saveprocess == 0 && $('#spPostForm').css('display') == 'block') {
					if (parseInt(sp_platform_vars.editor) == 1)
						edContent = tinymce.activeEditor.getContent();
					if (showConfirm || edContent != '')
						return sp_forum_vars.lostentry;
				}
			}

			window.onbeforeunload = confirmExit;
			if (parseInt(sp_platform_vars.editor) != 1) {
				$('#postitem').keyup(function() {
					showConfirm = true;
				});
			}
		}
	}

	function customizerTest() {
		if (sp_platform_vars.customizertest) {
			$('a').removeAttr('href');
			$('a').removeAttr('onclick');
		}
	}
}(window.spj = window.spj || {}, jQuery));
