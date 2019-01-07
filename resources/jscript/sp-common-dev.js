/*
$LastChangedDate: 2016-06-25 06:08:56 -0500 (Sat, 25 Jun 2016) $
$Rev: 14323 $
*/
/*--------------------------------------------------------------
spjLoadAjax: Generic ajax call hander
	url:	the url of the ajax php file
	target:	the target element id for displaying the results
	image:	the src url of an optional image file like a spinner
*/

function spjLoadAjax(url, target, image) {
	if (image !== '') {
		document.getElementById(target).innerHTML = '<img src="' + image + '" />';
	}
    url = url + '&rnd=' +  new Date().getTime();
    jQuery('#'+target).show();
	jQuery('#'+target).load(url);
}

/*--------------------------------------------------------------
spjBatch: Generic batch processor
	thisFormID:		id of the form making the call
	url:			url of the php ajax code file
	target:			target dic for final message
	message:		message to show on completion
	startNum:		starting number - usually 0
	batchNum:		how many to process in each batch
	totalNum:		how many in total to be processed
*/

function spjBatch(thisFormID, url, target, startMessage, endMessage, startNum, batchNum, totalNum) {
	if (startNum == 0) {
		url += '&target='+target+'&totalNum='+totalNum+'&'+jQuery('#'+thisFormID).serialize();
		jQuery('#'+target).show();
		jQuery('#'+target).html(startMessage);
		jQuery("#progressbar").progressbar({ value: 0 });
	} else {
		var currentProgress  = ((startNum / totalNum) * 100);
		jQuery("#progressbar").progressbar('option', 'value', currentProgress);
	}

	var thisUrl = url + '&startNum='+startNum+'&batchNum='+batchNum;

	jQuery('#onFinish').load(thisUrl, function(a, b) {
		startNum = (startNum + batchNum);
		if (startNum < totalNum) {
			spjBatch(thisFormID, url, target, startMessage, endMessage, startNum, batchNum, totalNum);
		} else {
			jQuery("#progressbar").hide();
			jQuery('#'+target).show();
			jQuery('#'+target).html(endMessage);
			jQuery('#'+target).fadeOut(6000);
		}
	});

	return false;
}

/*--------------------------------------------------------------
spjDialogAjax: Opens a jQuery UI Dialog popup filled by Ajax
	e:			The button/link object making the call
	url:		The url to the ajax file to populate dialog
	title:		text for the popup title bar
	width:		Width of the popup or 0 fot auto
	height:		Height of popup or 0 for auto
	position:	Set to zero to calculate. Or 'center'
	dClass:		Optional class to apply to overall dialog container (ui-dialog)
	mobileScroll: if set true do NOT hand mobile over to the sliding panel
*/

function spjDialogAjax(e, url, title, width, height, position, dClass, mobileScroll) {
	if (!dClass) dClass = 'spDialogDefault';
	if ((sp_platform_vars.device != 'mobile' && sp_platform_vars.focus == 'forum') || (sp_platform_vars.focus == 'admin') || (sp_platform_vars.mobiletheme == false) || mobileScroll) {
		// close and remove any existing dialog. remove hdden div and recreate it */
		if (jQuery().dialog("isOpen")) {
			jQuery().dialog('close');
		}
		jQuery('#dialog').remove();
		jQuery("#dialogcontainer").append("<div id='dialog'></div>");
		jQuery('#dialog').load(url, function(ajaxContent) {
			spjDialogPopUp(e, title, width, height, position, dClass, ajaxContent);
		});
	} else {
		var panel = jQuery('#spMobilePanel');
		// grab new position and set up the top
		if (panel.css('display') == 'block') {
			panel.hide('slide', {direction: 'right'}, 'down', function() {
				panel.css('display', 'none');
				panel.css('right', '-1px');
			});
		}
		spjDialogPanel(e, url, dClass);
	}
}

/*--------------------------------------------------------------
spjDialogHtml:	Opens a jQuery UI Dialog popup filled by content
	e:			The button/link object making the call
	content:	the formatted content to be displayed
	title:		text for the popup title bar
	width:		Width of the popup or 0 fot auto
	height:		Height of popup or 0 for auto
	position:	Set to zero to calculate. Or 'center'
	dClass:		Optional class to apply to overall dialog container (ui-dialog)
*/

function spjDialogHtml(e, content, title, width, height, position, dClass) {
	if (!dClass) dClass = 'spDialogDefault';
	// close and remove any existing dialog. remove hdden div and recreate it */
	if (jQuery().dialog("isOpen")) {
		jQuery().dialog('close');
	}
	jQuery('#dialog').remove();
	jQuery("#dialogcontainer").append("<div id='dialog'></div>");
	spjDialogPopUp(e, title, width, height, position, dClass, content);
}

/*--------------------------------------------------------------
spjDialogPopUp: Opens a jQuery UI Dialog popup
	e:			The button/link object making the call
	title:		text for the popup title bar
	width:		Width of the popup or 0 fot auto
	height:		Height of the popup or 0 for auto
	position:	Set to zero to calculate. Or 'center'
	dClass:		Optional class to apply to overall dialog container (ui-dialog)
	content:	The cntent to be dsplayed
*/

function spjDialogPopUp(e, title, width, height, position, dClass, content) {
	// calc the available height
	var winH = window.innerHeight;
	winH = (winH-sp_platform_vars.headpadding-60);
	if (height > winH) height = winH;

	// set width and height values
	var h = (height==0) ? 'auto' : height;
	var w = (width==0) ? 'auto' : width;

	jQuery('#dialog').html(content);
	jQuery('#dialog').dialog({
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
        close: function( event, ui ) {jQuery('#postitem').trigger('closed');},
        focus: function( event, ui ) {jQuery('#dialog').trigger('opened');}
	});

	if (position === 0) {
		jQuery('#dialog').dialog("option", "position", { my: "right top", at: "left bottom", of: e });
	}

	if (width > 0 && sp_platform_vars.device == 'desktop') {
		jQuery('#dialog').dialog("option", "width", width);
	}
	if (height > 0) {
		jQuery('#dialog').dialog("option", "height", height);
	}

	// check top position for a minus
	var t = jQuery('.ui-dialog').css('top');
	if (t.charAt(0) == '-') {
		var c = Math.round(parseInt(t) - parseInt(sp_platform_vars.headpadding));
		jQuery('#dialog').dialog("option", "position", { my: "right top"+c, at: "left bottom", of: e });
	}
	// and z-index
	jQuery('.ui-dialog').css('z-index', '999999');

	jQuery('#dialog').dialog('open');

	// hide any initial tooltip that wants to open
	if (sp_platform_vars.tooltips) {
		jQuery('.ui-tooltip').hide();
	}
}

/*--------------------------------------------------------------
spjDialogPanel: Opens a sliding panel filled by Ajax
	e:			The button/link object making the call
	url:		The url to the ajax file to populate dialog
*/

function spjDialogPanel(e, url, dClass) {
	var panel = jQuery('#spMobilePanel');
	panel.load(url, function() {
		panel.removeClass();
		panel.addClass(dClass);
		panel.show('slide', {direction: 'down'}, 'slow', function() {
    		panel.append("<span id='spPanelClose'></span>");
            jQuery('#spMobilePanel').trigger('opened');
        });
	});
	// bind the 'mousedown' event to the document so we can close panel
	jQuery('body').on('mousedown', function() {
		panel.hide('slide', {direction: 'down'}, 'slow');
	});
	// don't close panel when clicking inside it
	panel.on('mousedown', function(e) {
		e.stopPropagation();
	});
}

function spjDialogPanelHTML(e, source) {
	var panel = jQuery('#spMobilePanel');
	var content = jQuery(source).html();
	panel.html(content);
	panel.show('slide', {direction: 'down'}, 'slow');
	panel.append("<span id='spPanelClose'></span>");
	// bind the 'mousedown' event to the document so we can close panel
	jQuery('body').on('mousedown', function() {
		panel.hide('slide', {direction: 'down'}, 'slow');
	});
	// don't close panel when clicking inside it
	panel.on('mousedown', function(e) {
		e.stopPropagation();
	});
}

/*--------------------------------------------------------------
jcookie:  Set/Get a cookie
*/
jQuery.cookie = function(name, value, options) {
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
                var cookie = jQuery.trim(cookies[i]);
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