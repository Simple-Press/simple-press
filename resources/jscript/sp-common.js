/*
 $LastChangedDate: 2017-12-31 06:56:06 -0600 (Sun, 31 Dec 2017) $
 $Rev: 15612 $
 */

(function(spj, $, undefined) {
	// private properties

	// public properties

	// private methods
	function dialogPopup(e, title, width, height, position, dClass, content) {
		// calc the available height
		var winH = window.innerHeight;
		winH = (winH - sp_platform_vars.headpadding - 60);
		if (height > winH)
			height = winH;

		// set width and height values
		var h = (height == 0) ? 'auto' : height;
		var w = (width == 0) ? 'auto' : width;

		$('#dialog').html(content);
		$('#dialog').dialog({
			modal: true,
			zindex: 100000,
			autoOpen: false,
			show: 'fold',
			hide: 'fold',
			width: w,
			height: h,
			maxHeight: winH,
			draggable: true,
			resizable: true,
			title: title,
			closeText: '',
			dialogClass: dClass,
			close: function(event, ui) {
				$('#postitem').trigger('closed');
			},
			focus: function(event, ui) {
				$('#dialog').trigger('opened');
			}
		});

		if (position === 0) {
			$('#dialog').dialog("option", "position", {my: "right top", at: "left bottom", of: e});
		}

		if (width > 0 && sp_platform_vars.device == 'desktop') {
			$('#dialog').dialog("option", "width", width);
		}
		if (height > 0) {
			$('#dialog').dialog("option", "height", height);
		}

		// check top position for a minus
		var t = $('.ui-dialog').css('top');
		if (t.charAt(0) == '-') {
			var c = Math.round(parseInt(t) - parseInt(sp_platform_vars.headpadding));
			$('#dialog').dialog("option", "position", {my: "right top" + c, at: "left bottom", of: e});
		}
		// and z-index
		$('.ui-dialog').css('z-index', '999999');

		$('#dialog').dialog('open');

		// hide any initial tooltip that wants to open
		if (sp_platform_vars.tooltips) {
			$('.ui-tooltip').hide();
		}
	}

	// public methods

	spj.loadAjax = function(url, target, image) {
		if (image !== '') {
			document.getElementById(target).innerHTML = '<img src="' + image + '" />';
		}
		url = url + '&rnd=' + new Date().getTime();
		$('#' + target).show();
		$('#' + target).load(url);
	};

	spj.batch = function(thisFormID, url, target, startMessage, endMessage, startNum, batchNum, totalNum) {
		if (startNum == 0) {
			url += '&target=' + target + '&totalNum=' + totalNum + '&' + $('#' + thisFormID).serialize();
			$('#' + target).show();
			$('#' + target).html(startMessage);
			$("#progressbar").progressbar({value: 0});
		} else {
			var currentProgress = ((startNum / totalNum) * 100);
			$("#progressbar").progressbar('option', 'value', currentProgress);
		}

		var thisUrl = url + '&startNum=' + startNum + '&batchNum=' + batchNum;

		$('#onFinish').load(thisUrl, function(a, b) {
			startNum = (startNum + batchNum);
			if (startNum < totalNum) {
				spj.batch(thisFormID, url, target, startMessage, endMessage, startNum, batchNum, totalNum);
			} else {
				$("#progressbar").hide();
				$('#' + target).show();
				$('#' + target).html(endMessage);
				$('#' + target).fadeOut(6000);
			}
		});

		return false;
	};

	spj.dialogAjax = function(e, url, title, width, height, position, dClass, mobileScroll) {
		if (!dClass)
			dClass = 'spDialogDefault';
		if ((sp_platform_vars.device != 'mobile' && sp_platform_vars.focus == 'forum') || (sp_platform_vars.focus == 'admin') || (sp_platform_vars.mobiletheme == false) || mobileScroll) {
			// close and remove any existing dialog. remove hdden div and recreate it */
			if ($().dialog("isOpen")) {
				$().dialog('close');
			}
			$('#dialog').remove();
			$("#dialogcontainer").append("<div id='dialog'></div>");
			$('#dialog').load(url, function(ajaxContent) {
				dialogPopup(e, title, width, height, position, dClass, ajaxContent);
			});
		} else {
			var panel = $('#spMobilePanel');
			// grab new position and set up the top
			if (panel.css('display') == 'block') {
				panel.hide('slide', {direction: 'right'}, 'down', function() {
					panel.css('display', 'none');
					panel.css('right', '-1px');
				});
			}
			spj.dialogPanel(e, url, dClass);
		}
	};

	spj.dialogHtml = function(e, content, title, width, height, position, dClass) {
		if (!dClass)
			dClass = 'spDialogDefault';
		// close and remove any existing dialog. remove hdden div and recreate it */
		if ($().dialog("isOpen")) {
			$().dialog('close');
		}
		$('#dialog').remove();
		$("#dialogcontainer").append("<div id='dialog'></div>");
		dialogPopup(e, title, width, height, position, dClass, content);
	};

	spj.dialogPanel = function(e, url, dClass) {
		var panel = $('#spMobilePanel');
		panel.load(url, function() {
			panel.removeClass();
			panel.addClass(dClass);
			panel.show('slide', {direction: 'down'}, 'slow', function() {
				panel.append("<span id='spPanelClose'></span>");
				$('#spMobilePanel').trigger('opened');
			});
		});
		// bind the 'mousedown' event to the document so we can close panel
		$('body').on('mousedown', function() {
			panel.hide('slide', {direction: 'down'}, 'slow');
		});
		// don't close panel when clicking inside it
		panel.on('mousedown', function(e) {
			e.stopPropagation();
		});
	};

	spj.dialogPanelHtml = function(e, source) {
		var panel = $('#spMobilePanel');
		var content = $(source).html();
		panel.html(content);
		panel.show('slide', {direction: 'down'}, 'slow');
		panel.append("<span id='spPanelClose'></span>");
		// bind the 'mousedown' event to the document so we can close panel
		$('body').on('mousedown', function() {
			panel.hide('slide', {direction: 'down'}, 'slow');
		});
		// don't close panel when clicking inside it
		panel.on('mousedown', function(e) {
			e.stopPropagation();
		});
	};

	spj.cookie = function(name, value, options) {
		if (typeof value != 'undefined') { // name and value given, set cookie
			options = options || {};
			if (value === null) {
				value = '';
				options.expires = -1;
			}
			var expires = '';
			if (options.expires && (typeof options.expires == 'number' || options.expires.toUTCString)) {
				var date;
				if (typeof options.expires == 'number') {
					date = new Date();
					date.setTime(date.getTime() + (options.expires * 24 * 60 * 60 * 1000));
				} else {
					date = options.expires;
				}
				expires = '; expires=' + date.toUTCString(); // use expires attribute, max-age is not supported by IE
			}
			// CAUTION: Needed to parenthesize options.path and options.domain
			// in the following expressions, otherwise they evaluate to undefined
			// in the packed version for some reason...
			var path = options.path ? '; path=' + (options.path) : '';
			var domain = options.domain ? '; domain=' + (options.domain) : '';
			var secure = options.secure ? '; secure' : '';
			document.cookie = [name, '=', encodeURIComponent(value), expires, path, domain, secure].join('');
		} else { // only name given, get cookie
			var cookieValue = null;
			if (document.cookie && document.cookie !== '') {
				var cookies = document.cookie.split(';');
				for (var i = 0; i < cookies.length; i++) {
					var cookie = $.trim(cookies[i]);
					// Does this cookie string begin with the name we want?
					if (cookie.substring(0, name.length + 1) == (name + '=')) {
						cookieValue = decodeURIComponent(cookie.substring(name.length + 1));
						break;
					}
				}
			}
			return cookieValue;
		}
	};
        
        spj.dialogOpen = function(e, url, title, width, height, position, dClass, mobileScroll, ajaxContent){
            
            console.log(ajaxContent);
            if (!dClass)
			dClass = 'spDialogDefault';
		if ((sp_platform_vars.device != 'mobile' && sp_platform_vars.focus == 'forum') || (sp_platform_vars.focus == 'admin') || (sp_platform_vars.mobiletheme == false) || mobileScroll) {
			// close and remove any existing dialog. remove hdden div and recreate it */
			if ($().dialog("isOpen")) {
				$().dialog('close');
			}
			$('#dialog').remove();
			$("#dialogcontainer").append("<div id='dialog'></div>");
			dialogPopup(e, title, width, height, position, dClass, ajaxContent);
		} else {
			var panel = $('#spMobilePanel');
			// grab new position and set up the top
			if (panel.css('display') == 'block') {
				panel.hide('slide', {direction: 'right'}, 'down', function() {
					panel.css('display', 'none');
					panel.css('right', '-1px');
				});
			}
			spj.dialogPanel(e, url, dClass);
		}
        };
}(window.spj = window.spj || {}, jQuery));