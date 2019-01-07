/* ---------------------------------
Simple:Press
Admin Javascript
$LastChangedDate: 2010-08-08 14:11:22 -0700 (Sun, 08 Aug 2010) $
$Rev: 4365 $
------------------------------------ */

var sfupload;

/* ----------------------------------*/
/* Admin Form Loader                 */
/* ----------------------------------*/
function spjLoadForm(formID, baseURL, targetDiv, imagePath, id, open, upgradeUrl, admin, save, sform, reload) {
	/* close a dialog (popup help) if one is open */
	if(jQuery().dialog("isOpen")) {
		jQuery().dialog('destroy');
	}

	/* remove any current form unless instructed to leave open */
	if (open === null || open == undefined) {
		for(x=document.forms.length-1;x>=0;x--) {
			if (document.forms[x].id !== '') {
				var tForm = document.getElementById(document.forms[x].id);
				if(tForm !== null) {
					tForm.innerHTML='';
				}
			}
		}
	}

	/* create vars we need */
	var busyDiv = document.getElementById(targetDiv);
	var currentFormBtn = document.getElementById('c'+formID);
	var ajaxURL = baseURL + '&loadform=' + formID;

	/* some sort of ID data? */
	if (id) {
		ajaxURL = ajaxURL + '&id=' + id;
	}

	/* user plugin? */
	if (admin) {
		ajaxURL = ajaxURL + '&admin=' + admin;
	}
	if (save) {
		ajaxURL = ajaxURL + '&save=' + save;
	}
	if (sform) {
		ajaxURL = ajaxURL + '&form=' + sform;
	}
	if (reload) {
		ajaxURL = ajaxURL + '&reload=' + reload;
	}

	/* add random num to GET param to ensure its not cached */
	ajaxURL = ajaxURL + '&rnd=' +  new Date().getTime();

	var spfjform = jQuery.noConflict();
	spfjform(document).ready(function() {
		/* fade out the msg area */
		spfjform('#sfmsgspot').fadeOut();

		/* load the busy graphic */
		busyDiv.innerHTML = '<img src="' + imagePath + 'sp_WaitBox.gif' + '" />';

		/*  now load the form */
		spfjform('#'+targetDiv).load(ajaxURL, function(a, b) {
			if (a == 'Upgrade') {
				spfjform('#'+targetDiv).hide();
				window.location = upgradeUrl;
				return;
			}
            jQuery('#sfmaincontainer').trigger('adminformloaded');
		});
	});
}

/* ----------------------------------*/
/* Setup Ajax Form processing               */
/* ----------------------------------*/
function spjAjaxForm(aForm, reLoad) {
	jQuery(document).ready(function() {
		jQuery('#'+aForm).ajaxForm({
			target: '#sfmsgspot',
			beforeSubmit: function() {
				jQuery('#sfmsgspot').show();
				jQuery('#sfmsgspot').html(sp_platform_vars.pWait);
			},
			success: function() {
				if(reLoad != '') {
					jQuery('#sfmsgspot').hide();
					jQuery('#'+reLoad).click();
				}
				jQuery('#sfmsgspot').fadeIn();
				jQuery('#sfmsgspot').fadeOut(6000);
			}
		});
	});
}

/* ----------------------------------*/
/* Open and Close of hidden divs     */
/* ----------------------------------*/
function spjToggleLayer(whichLayer)
{
	if (document.getElementById) {
		/* this is the way the standards work */
		style2 = document.getElementById(whichLayer).style;
		style2.display = style2.display? "":"block";
	} else if (document.all) {
		/* this is the way old msie versions work */
		style2 = document.all[whichLayer].style;
		style2.display = style2.display? "":"block";
	} else if (document.layers) {
		/* this is the way nn4 works */
		style2 = document.layers[whichLayer].style;
		style2.display = style2.display? "":"block";
	}
	var obj = document.getElementById(whichLayer);
	if (whichLayer == 'spPostForm') {
		obj.scrollIntoView(false);
	}
}

function spjToggleRow(whichRow) {
	jQuery(whichRow).show();
}


/* ----------------------------------*/
/* Admin Option Tools                */
/* ----------------------------------*/
function spjAdminTool(url, target, imageFile) {
	if(imageFile !== '') {
		document.getElementById(target).innerHTML = '<br /><br /><img src="' + imageFile + '" /><br />';
	}
    url = url + '&rnd=' +  new Date().getTime();
	jQuery('#'+target).load(url);
}

/* ----------------------------------*/
/* Admin Show Group Members          */
/* ----------------------------------*/
function spjShowMemberList(url, imageFile, groupID) {
	var memberList = document.getElementById('members-'+groupID);
	var target = 'members-'+groupID;

	/* add random num to GET param to ensure its not cached */
	url = url + '&rnd=' +  new Date().getTime();

	if(memberList.innerHTML === '') {
		if (imageFile !== '') {
			document.getElementById(target).innerHTML = '<img src="' + imageFile + '" />';
		} else {
			document.getElementById(target).innerHTML = '';
		}
		jQuery('#members-'+groupID).load(url);
	} else {
		document.getElementById(target).innerHTML = '';
	}
}

/* ----------------------------------*/
/* Admin Show Multi Select List      */
/* ----------------------------------*/
function spjUpdateMultiSelectList(url, uid) {
	var target = '#mslist-'+uid;

	/* add random num to GET param to ensure its not cached */
	url = url + '&rnd=' +  new Date().getTime();

	jQuery(target).load(url);
}

function spjFilterMultiSelectList(url, uid, imageFile) {
	var target = '#mslist-'+uid;

	document.getElementById('filter-working').innerHTML = '<img src="' + imageFile + '" />';

	filter = document.getElementById('list-filter'+uid);
	url = url + '&filter=' + encodeURIComponent(filter.value);

	/* add random num to GET param to ensure its not cached */
	url = url + '&rnd=' +  new Date().getTime();

	jQuery(target).load(url);
}

function spjTransferSelectList(from, to, msg, exceed, recip) {
	/* can we add more? */
	var newlist = jQuery('#'+from+' option:selected').length;
	var oldlist = jQuery('#'+to+' option').length;
	if((newlist + oldlist) > 400) {
		alert(exceed);
		return false;
	}

	/* remove list empty message */
	jQuery('#'+to+' option[value="-1"]').remove();
	/* move the data from the from box to the to box */
	jQuery('#'+from+' option:selected').remove().appendTo('#'+to);

	jQuery('#selcount').html(jQuery('#'+recip+' option').length);

	/* if the from box is now empty, display message */
	if (!jQuery('#'+from+' option').length)
		jQuery('#'+from).append('<option value="-1">'+msg+'</option>');

	return false;
}

/* delete a row and reload the form */
function spjDelRowReload(url, reload) {
	jQuery('#sfmsgspot').load(url, function() {
		jQuery('#'+reload).click();
	});
}

/* delete a row */
function spjDelRow(url, rowid) {
	jQuery('#'+rowid).css({backgroundColor: '#ffcccc'});
	jQuery('#'+rowid).fadeOut('slow');
	jQuery('#'+rowid).load(url);
}

/* ----------------------------------*/
/* Check/Uncheck box collection      */
/* ----------------------------------*/
function spjCheckAll(container) {
	jQuery(container).find('input[type=checkbox]:not(:checked)').each(function() {
		jQuery('label[for='+jQuery(this).attr('id')+']').trigger('click');
	});
}

/* ----------------------------------*/
/* 							         */
/* ----------------------------------*/
function spjUnCheckAll(container) {
	jQuery(container).find('input[type=checkbox]:checked').each(function() {
		jQuery('label[for='+jQuery(this).attr('id')+']').trigger('click');
	});
}

/* ----------------------------------*/
/* 							         */
/* ----------------------------------*/
function spjSetForumOptions(type) {
	if(type == 'forum') {
		jQuery('#forumselect').hide();
		jQuery('#groupselect').show();
	} else {
		jQuery('#groupselect').hide();
		jQuery('#forumselect').show();
	}
}

/* ----------------------------------*/
/* 							         */
/* ----------------------------------*/
function spjSetForumSequence() {
	jQuery('#block1').show('slow');
	jQuery('#block2').show('slow');
}

/* ----------------------------------*/
/* 							         */
/* ----------------------------------*/
function spjSetForumSlug(title, url, target, slugAction) {
	url+='&targetaction=slug&title='+escape(title.value)+'&slugaction='+slugAction;
	jQuery('#'+target).load(url, function(newslug) {
		document.getElementById(target).value = newslug;
		document.getElementById(target).disabled = false;
	});
}

/* ----------------------------------*/
/* Load the help and troubleshooting */
/* ----------------------------------*/
function spjTroubleshooting(site, targetDiv) {
	jQuery('#'+targetDiv).load(site, function() {
        jQuery('#sfmaincontainer').trigger('troubleshootingformloaded');
    });
}

/* ----------------------------------*/
/* 	Add/Delete members control       */
/* ----------------------------------*/
function spjAddDelMembers(thisFormID, url, target, startMessage, endMessage, startNum, batchNum, source) {
	var totalNum = 0;
	jQuery(source +' option').each(function(i) {
		jQuery(this).prop('selected');
		totalNum++;
	});
	spjBatch(thisFormID, url, target, startMessage, endMessage, startNum, batchNum, totalNum);
	jQuery(source + ' option').remove();
}

/* ---------------------------------- */
/* Set default avatars for no repeats */
/* ---------------------------------- */
function spjCheckAvatarDefaults(newChecked) {
	jQuery("#av-browser").find("input:radio").each(function( index ) {
		if(this.checked && newChecked.value != 'none') {
			if(this.name != newChecked.name && this.value == newChecked.value) {
				var thisId = this.name;
				jQuery('#non-'+thisId).prop("checked", true);
			}
		}
	});
}

/* ------------------------------------- */
/* Load tasklist on admin search keyword */
/* ------------------------------------- */
function spjKeywordSearch(url) {
	var key = jQuery('#keywords').val();
	if (key != '') {
		url+='&keywords='+encodeURIComponent(key);
		jQuery('#codex').load(url);
	}
}

/* ------------------------------------- */
/* Expand/Collapse forum listing control */
/* ------------------------------------- */
function spjExpCollForums(control, target) {
	if (jQuery('#'+target).css('display') == 'block') {
		jQuery('#'+target).slideUp();
		control.text = '+';
	} else {
		jQuery('#'+target).slideDown();
		control.text = String.fromCharCode(8211);
	}
}
