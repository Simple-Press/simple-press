/* ---------------------------------
 Simple:Press
 Admin Javascript
 $LastChangedDate: 2010-08-08 14:11:22 -0700 (Sun, 08 Aug 2010) $
 $Rev: 4365 $
 ------------------------------------ */

(function(spj, $, undefined) {
	// private properties

	// public properties

	// public methods
	spj.loadForm_OLD = function(formID, baseURL, targetDiv, imagePath, id, open, upgradeUrl, admin, save, sform, reload) {
		/* close a dialog (popup help) if one is open */
		if ($().dialog("isOpen")) {
			$().dialog('destroy');
		}

		/* remove any current form unless instructed to leave open */
		if (open === null || open == undefined) {
			for (x = document.forms.length - 1; x >= 0; x--) {
				if (document.forms[x].id !== '') {
					var tForm = document.getElementById(document.forms[x].id);
					if (tForm !== null) {
						tForm.innerHTML = '';
					}
				}
			}
		}

		/* create vars we need */
		var busyDiv = document.getElementById(targetDiv);
		var currentFormBtn = document.getElementById('c' + formID);
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
		ajaxURL = ajaxURL + '&rnd=' + new Date().getTime();

		$(document).ready(function() {
			/* fade out the msg area */
			$('#sfmsgspot').fadeOut();

			/* load the busy graphic */
			busyDiv.innerHTML = '<img src="' + imagePath + 'sp_WaitBox.gif' + '" />';

			/*  now load the form */
			$('#' + targetDiv).load(ajaxURL, function(a, b) {
				if (a == 'Upgrade') {
					$('#' + targetDiv).hide();
					window.location = upgradeUrl;
					return;
				}
				$('#sfmaincontainer').trigger('adminformloaded');
			});
		});
	};

        spj.loadForm = function(formID, baseURL, targetDiv, imagePath, id, open, upgradeUrl, admin, save, sform, reload) {
		/* close a dialog (popup help) if one is open */
		if ($().dialog("isOpen")) {
			$().dialog('destroy');
		}

                var $target = $(/^\s*[a-z]/i.test(targetDiv) ? '#' + targetDiv : targetDiv);

		/* remove any current form unless instructed to leave open */
		if (open === null || open == undefined) {
			$target.html('');
		}

		/* create vars we need */
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
		ajaxURL = ajaxURL + '&rnd=' + new Date().getTime();

		$(document).ready(function() {
			/* fade out the msg area */
			$('#sfmsgspot').fadeOut();

			/* load the busy graphic */
			$target.html('<img src="' + imagePath + 'sp_WaitBox.gif' + '" />');

			/*  now load the form */
			$target.load(ajaxURL, function(a, b) {
				if (a == 'Upgrade') {
					$target.hide();
					window.location = upgradeUrl;
					return;
				}
				$('#sfmaincontainer').trigger('adminformloaded');
			});
		});
	};


	spj.loadAjaxForm = function(aForm, reLoad) {
		$(document).ready(function() {
			$('#' + aForm).ajaxForm({
				//target: '#sfmsgspot',
                                
                                beforeSerialize : function() {
                                        if( typeof tinymce !== 'undefined' ) {
                                                tinymce.triggerSave();
                                        }
                                },
				beforeSubmit: function() {
					$('#sfmsgspot').show();
					$('#sfmsgspot').html(sp_platform_vars.pWait);
				},
				success: function( msg ) {
                                        
                                        $('#sfformmsgspot').prepend('<div class="updated notice is-dismissible">\
                                                <p>'+msg+'</p>\
                                                <button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>\
                                        </div>');
                                        
					if (reLoad != '') {
						$('#' + reLoad).click();
					}
					$('#sfmsgspot').fadeOut(6000);
				}
			});
		});
	};

	spj.toggleLayer = function(whichLayer) {
		if (document.getElementById) {
			/* this is the way the standards work */
			style2 = document.getElementById(whichLayer).style;
			style2.display = style2.display ? "" : "block";
		} else if (document.all) {
			/* this is the way old msie versions work */
			style2 = document.all[whichLayer].style;
			style2.display = style2.display ? "" : "block";
		} else if (document.layers) {
			/* this is the way nn4 works */
			style2 = document.layers[whichLayer].style;
			style2.display = style2.display ? "" : "block";
		}
		var obj = document.getElementById(whichLayer);
		if (whichLayer == 'spPostForm') {
			obj.scrollIntoView(false);
		}
	};

	spj.toggleRow = function(whichRow) {
		$(whichRow).show();
	};

	spj.delRowReload = function(url, reload) {
		$('#sfmsgspot').load(url, function() {
			$('#' + reload).click();
		});
	};

	spj.delRow = function(url, rowid) {
		$('#' + rowid).css({backgroundColor: '#ffcccc'});
		$('#' + rowid).fadeOut('slow');
		$('#' + rowid).load(url);
	};

	spj.adminTool = function(url, target, imageFile) {
		if (imageFile !== '') {
			document.getElementById(target).innerHTML = '<br /><br /><img src="' + imageFile + '" /><br />';
		}
		url = url + '&rnd=' + new Date().getTime();
		$('#' + target).load(url);
	};

	spj.showMemberList_OLD = function(url, imageFile, groupID) {
		var memberList = document.getElementById('members-' + groupID);
		var target = 'members-' + groupID;

		/* add random num to GET param to ensure its not cached */
		url = url + '&rnd=' + new Date().getTime();

		if (memberList.innerHTML === '') {
			if (imageFile !== '') {
				document.getElementById(target).innerHTML = '<img src="' + imageFile + '" />';
			} else {
				document.getElementById(target).innerHTML = '';
			}
			$('#members-' + groupID).load(url);
		} else {
			document.getElementById(target).innerHTML = '';
		}
	};
        
        spj.showMemberList = function(url, imageFile, groupID, target) {
		var $el = target ? $(target) : $('#members-' + groupID);
                if (imageFile) {
                    $el.html('<img src="' + imageFile + '" />');
                } else {
                    $el.html('');
                }
                $el.load(url);
	};

	spj.updateMultiSelectList = function(url, uid) {
		var target = '#mslist-' + uid;

		/* add random num to GET param to ensure its not cached */
		url = url + '&rnd=' + new Date().getTime();

		$(target).load(url);
	};

	spj.filterMultiSelectList = function(url, uid, imageFile) {
		var target = '#mslist-' + uid;

		document.getElementById('filter-working').innerHTML = '<img src="' + imageFile + '" />';

		filter = document.getElementById('list-filter' + uid);
		url = url + '&filter=' + encodeURIComponent(filter.value);

		/* add random num to GET param to ensure its not cached */
		url = url + '&rnd=' + new Date().getTime();

		$(target).load(url);
	};

	spj.transferMultiSelectList = function(from, to, msg, exceed, recip) {
		/* can we add more? */
		var newlist = $('#' + from + ' option:selected').length;
		var oldlist = $('#' + to + ' option').length;
		if ((newlist + oldlist) > 400) {
			alert(exceed);
			return false;
		}

		/* remove list empty message */
		$('#' + to + ' option[value="-1"]').remove();
		/* move the data from the from box to the to box */
		$('#' + from + ' option:selected').remove().appendTo('#' + to);

		$('#selcount').html($('#' + recip + ' option').length);

		/* if the from box is now empty, display message */
		if (!$('#' + from + ' option').length)
			$('#' + from).append('<option value="-1">' + msg + '</option>');

		return false;
	};

	spj.checkAll = function(container) {
		$(container).find('input[type=checkbox]:not(:checked)').each(function() {
			$('label[for=' + $(this).attr('id') + ']').trigger('click');
		});
	};

	spj.uncheckAll = function(container) {
		$(container).find('input[type=checkbox]:checked').each(function() {
			$('label[for=' + $(this).attr('id') + ']').trigger('click');
		});
	};

	spj.setForumOptions = function(type) {
		if (type == 'forum') {
			$('#forumselect').hide();
			$('#groupselect').show();
		} else {
			$('#groupselect').hide();
			$('#forumselect').show();
		}
	};

	spj.setForumSequence = function() {
		$('#block1').show('slow');
		$('#block2').show('slow');
	};

	spj.setForumSlug = function(title, url, target, slugAction) {
		url += '&targetaction=slug&title=' + escape(title.value) + '&slugaction=' + slugAction;
		$('#' + target).load(url, function(newslug) {
			document.getElementById(target).value = newslug;
			document.getElementById(target).disabled = false;
		});
	};

	spj.troubleshooting = function(site, targetDiv) {
		$('#' + targetDiv).load(site, function() {
			$('#sfmaincontainer').trigger('troubleshootingformloaded');
		});
	};

	spj.addDelMembers = function(thisFormID, url, target, startMessage, endMessage, startNum, batchNum, source, type ) {
		var totalNum = 0;
		$(source + ' option').each(function(i) {
			$(this).prop('selected');
			totalNum++;
		});
                
                var groupid = '';
                
                if( $( '#' + thisFormID + ' input[name=usergroupid]').length === 1 ) {
                        groupid = $( '#' + thisFormID + ' input[name=usergroupid]').val();
                } else {
                        groupid = $( '#' + thisFormID + ' input[name=usergroup_id]').val();
                }
                
                var btn = $('input#'+type+groupid)
                var image = btn.data('img');
                
                if( image ) {
                        var load_img = $('<img src="' + image + 'sp_WaitBox.gif' + '" />');
                        $( btn.data('target') ).prepend(load_img);
                }
                
                spj[thisFormID+'_Def'] = $.Deferred();
                
                
                $.when( spj[thisFormID+'_Def'] )
                .then( function(a, b) {
                        
                        var mydata = btn.data();
                        var ajaxURL = mydata.url + '&loadform=' + mydata.form + '&id=' + mydata.id;
                        
                        $(mydata.target).load(ajaxURL, function(c, d) {
				$('#sfmaincontainer').trigger('adminformloaded');
			});
                } );
                
                
                spj.batch(thisFormID, url, target, startMessage, endMessage, startNum, batchNum, totalNum);
                
		$(source + ' option').remove();
	};

	spj.checkAvatarDefaults = function(newChecked) {
		$("#av-browser").find("input:radio").each(function(index) {
			if (this.checked && newChecked.value != 'none') {
				if (this.name != newChecked.name && this.value == newChecked.value) {
					var thisId = this.name;
					$('#non-' + thisId).prop("checked", true);
				}
			}
		});
	};

	spj.keywordSearch = function(url) {
		var key = $('#keywords').val();
		if (key != '') {
			url += '&keywords=' + encodeURIComponent(key);
			$('#codex').load(url);
		}
	};

	spj.expandCollapseForums = function(control, target) {
		if ($('#' + target).css('display') == 'block') {
			$('#' + target).slideUp();
			control.text = '+';
		} else {
			$('#' + target).slideDown();
			control.text = String.fromCharCode(8211);
		}
	};
        
        
        spj.prepareAjaxEditor = function( editor ) {
                
                var id = editor.find('textarea.wp-editor-area').attr('id');
                
                if( tinyMCE.get( id ) ) {
                        tinyMCE.get( id ).destroy();
                }
                
                
                var init = null;

                if( tinyMCEPreInit.mceInit.hasOwnProperty( id ) ) {
                        init = tinyMCEPreInit.mceInit[ id ];
                } else {

                        var mce_init = tinyMCEPreInit.mceInit[Object.keys(tinyMCEPreInit.mceInit)[0]];
                        init = $.extend({}, mce_init, { selector : "#" + id });
                        tinyMCEPreInit.mceInit[ id ] = init;
                }
                
                var $wrap = tinymce.$( '#wp-' + id + '-wrap' );
                var is_mce = $wrap.hasClass( 'tmce-active' ) ? true : false;


                new QTags( id );
                QTags._buttonsInit();
                switchEditors.go( id, 'html' );

                if( is_mce ) {

                        setTimeout( function(){
                                switchEditors.go( id, 'tmce' );
                        }, 200 );

                }
                
        }
        
        
        spj.UpdateFontIcon = function( id, clear_color ) {
                
                var color = '';
                
                if( !clear_color ) {
                        $.each( $('#'+id).closest('.sf-icon-picker-row').find('.font-color-container .font-style-color').data(), function() {
                                if( this.hasOwnProperty('_color') ) {
                                        color = this._color.toString();
                                }
                        });
                }
                        
                var val = {
                        icon : $('#'+id).val(),
                        color : color,
                        size : $('#'+id).closest('.sf-icon-picker-row').find('.font-style-size').val(),
                        size_type : $('#'+id).closest('.sf-icon-picker-row').find('.font-style-size_type').val()
		};
                
                $('#'+id).closest('.sf-icon-picker-row').find('.icon_value').val( JSON.stringify( val ) );
                
                $('#'+id).closest('.sf-icon-picker-row').find('.selected-icon i').css({color: color});
        };
        

	// private methods
}(window.spj = window.spj || {}, jQuery));
