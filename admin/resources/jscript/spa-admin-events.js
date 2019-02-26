/* ---------------------------------
 Simple:Press - Version 5.0
 Forum Admin Event Handlers Javascript

 $LastChangedDate: 2016-06-22 19:23:07 -0700 (Wed, 22 Jun 2016) $
 $Rev: 14304 $
 ------------------------------------ */

(function(spj, $, undefined) {
	// private properties
	/*****************************
	 general admin event handlers
	 *****************************/

	toggleLayer = {
		init: function() {
			$('.spLayerToggle').off();
			$('.spLayerToggle').click(function() {
				var mydata = $(this).data();
				spj.toggleLayer(mydata.target);
			});
		}
	};

	toggleRow = {
		init: function() {
			$('.spToggleRow').off();
			$('.spToggleRow').click(function() {
				var mydata = $(this).data();
				$(mydata.target).show();
			});
		}
	};

	deleteRow = {
		init: function() {
			$('#sfmaincontainer').off('click', '.spDeleteRow');
			$('#sfmaincontainer').on('click', '.spDeleteRow', function() {
				var mydata = $(this).data();
				if (typeof mydata.msg == 'undefined' || confirm(mydata.msg)) {
					spj.delRow(mydata.url, mydata.target);
				}
			});
		}
	};

	deleteRowReload = {
		init: function() {
			$('#sfmaincontainer').off('click', '.spDeleteRowReload');
			$('#sfmaincontainer').on('click', '.spDeleteRowReload', function() {
				var mydata = $(this).data();
				if (typeof mydata.msg == 'undefined' || confirm(mydata.msg)) {
					spj.delRowReload(mydata.url, mydata.reload);
				}
			});
		}
	};

	loadForm = {
		init: function() {
			$('#sfmaincontainer').off('click', '.spLoadForm');
			$('#sfmaincontainer').on('click', '.spLoadForm', function() {
				var mydata = $(this).data();
				spj.loadForm(mydata.form, mydata.url, mydata.target, mydata.img, mydata.id, mydata.open);
			});
		}
	};

	cancelForm = {
		init: function() {
			$('#sfmaincontainer').off('click', '.spCancelForm');
			$('#sfmaincontainer').on('click', '.spCancelForm', function() {
				var mydata = $(this).data();
				$(mydata.target).html('');
			});
		}
	};

	loadAjax = {
		init: function() {
			$('#sfmaincontainer').off('click', '.spLoadAjax');
			$('#sfmaincontainer').on('click', '.spLoadAjax', function() {
				var mydata = $(this).data();
				spj.loadAjax(mydata.url, mydata.target, mydata.img);
			});
		}
	};

	reloadForm = {
		init: function() {
			$('#sfmaincontainer').off('click', '.spReloadForm');
			$('#sfmaincontainer').on('click', '.spReloadForm', function() {
				var mydata = $(this).data();
				$(mydata.target).click();
			});
		}
	};

	showElement = {
		init: function() {
			$('#sfmaincontainer').off('click', '.spShowElement');
			$('#sfmaincontainer').on('click', '.spShowElement', function() {
				var mydata = $(this).data();
				$(mydata.target).show();
			});
		}
	};

	checkAll = {
		init: function() {
			$('.spPruneCheckAll').off();
			$('.spPruneCheckAll').click(function() {
				var mydata = $(this).data();
				spj.checkAll(mydata.target);
			});
		}
	};

	uncheckAll = {
		init: function() {
			$('.spPruneUncheckAll').off();
			$('.spPruneUncheckAll').click(function() {
				var mydata = $(this).data();
				spj.uncheckAll(mydata.target);
			});
		}
	};

	adminTool = {
		init: function() {
			$('.spAdminTool').off();
			$('.spAdminTool').click(function() {
				var mydata = $(this).data();
				spj.adminTool(mydata.url, mydata.target, mydata.img);
			});
		}
	};

	searchTool = {
		init: function() {
			$('.key-word').off();
			$('.key-word').click(function() {
				var mydata = $(this).data();
				spj.keywordSearch(mydata.url);
			});
		}
	};

	adminHelp = {
		init: function() {
			$('.spHelpLink').off();
			$('.spHelpLink').click(function() {
				var mydata = $(this).data();
				spj.dialogAjax(this, mydata.site, mydata.label, mydata.width, mydata.height, mydata.align);
			});
		}
	};

	spCancelScript = {
		init: function() {
			$('.spCancelScript').click(function(event) {
				event.preventDefault();
				spj.cancelScript();
			});
		}
	};

	openDialog = {
		init: function() {
			$('.spOpenDialog').click(function() {
				var mydata = $(this).data();
				spj.dialogAjax(this, mydata.site, mydata.label, mydata.width, mydata.height, mydata.align);
				$('#dialog, #spMobilePanel').one('opened', function() {
					spCancelScript.init();
				});
			});
		}
	};

	troubleshooting = {
		init: function() {
			$('.spTroubleshoot').off();
			$('.spTroubleshoot').click(function() {
				var mydata = $(this).data();
				spj.troubleshooting(mydata.url, mydata.target);
			});
		}
	};

	accordionLoadForm = {
		init: function() {
			$('#sfadminmenu').off('click', '.spAccordionLoadForm');
			$('#sfadminmenu').on('click', '.spAccordionLoadForm', function() {
				var mydata = $(this).data();
				spj.loadForm(mydata.form, mydata.url, mydata.target, mydata.img, mydata.id, mydata.open, mydata.upgrade, mydata.admin, mydata.save, mydata.sform, mydata.reload);
			});
		}
	};

	/*****************************
	 admin multiselect event handlers
	 *****************************/

	multiselectTransfer = {
		init: function() {
			$('#sfmaincontainer').on('click', '.spTransferList', function() {
				var mydata = $(this).data();
				spj.transferMultiSelectList(mydata.from, mydata.to, mydata.msg, mydata.exceed, mydata.recip);
			});
		}
	};

	multiselectUpdate = {
		init: function() {
			$('#sfmaincontainer').on('click', '.spUpdateList', function() {
				var mydata = $(this).data();
				spj.updateMultiSelectList(mydata.url, mydata.uid);
			});
		}
	};

	multiselectFilter = {
		init: function() {
			$('#sfmaincontainer').on('click', '.spFilterList', function() {
				var mydata = $(this).data();
				spj.filterMultiSelectList(mydata.url, mydata.uid, mydata.image);
			});
		}
	};

	/*****************************
	 admin forums event handlers
	 *****************************/

	forumsExpandCollapseGroup = {
		init: function() {
			$('.spExpandCollapseGroup').click(function() {
				var mydata = $(this).data();
				spj.expandCollapseForums(this, mydata.target);
			});
		}
	};

	/*****************************
	 admin components event handlers
	 *****************************/

	componentsSpecialRankAdd = {
		init: function() {
			$('.spSpecialRankAdd, spSpecialRankDel').click(function() {
				var mydata = $(this).data();
				$(mydata.target).each(function(i) {
					$(this).attr('selected', 'selected');
				});
			});
		}
	};

	componentsSpecialRankCancel = {
		init: function() {
			$('.spSpecialRankCancel').click(function() {
				var mydata = $(this).data();
				spj.toggleLayer(mydata.loc);
				$(mydata.target).html('');
			});
		}
	};

	componentsSpecialRankShow = {
		init: function() {
			$('.spSpecialRankShow').click(function() {
				var mydata = $(this).data();
				spj.toggleRow(mydata.loc);
				spj.showMemberList(mydata.site, mydata.img, mydata.id);
			});
		}
	};

	componentsSpecialRankForm = {
		init: function() {
			$('.spSpecialRankForm').click(function() {
				var mydata = $(this).data();
				$(mydata.loc).show();
				spj.loadForm(mydata.form, mydata.base, mydata.target, mydata.img, mydata.id, 'open');
			});
		}
	};

	/*****************************
	 admin plugins event handlers
	 *****************************/

	pluginsUpload = {
		init: function() {
			$('.spPluginUpload').click(function() {
				var mydata = $(this).data();
				$(mydata.target).attr('disabled', 'disabled');
				document.sfpluginuploadform.submit();
			});
		}
	};

	/*****************************
	 admin profiles event handlers
	 *****************************/

	profileAvatarDefaults = {
		init: function() {
			$('.spCheckAvatarDefaults').click(function() {
				spj.checkAvatarDefaults(this);
			});
		}
	};

	profileAvatarUpdatePriorities = {
		init: function() {
			$('.spProfileAvatarUpdate').change(function() {
				var mydata = $(this).data();
				spj.avatarPriority(mydata.target);
			});
		}
	};

	/*****************************
	 admin themes event handlers
	 *****************************/

	themesDeleteConfirm = {
		init: function() {
			$('.spThemeDeleteConfirm').click(function(event) {
				var mydata = $(this).data();
				if (typeof mydata.msg == 'undefined' || confirm(mydata.msg)) {
					return true;
				} else {
					event.preventDefault();
				}
			});
		}
	};

	themesUpload = {
		init: function() {
			$('.spThemeUpload').click(function() {
				var mydata = $(this).data();
				$(mydata.target).attr('disabled', 'disabled');
				document.sfthemeuploadform.submit();
			});
		}
	};

	/*****************************
	 admin usegroups event handlers
	 *****************************/

	ugShowMembers = {
		init: function() {
			$('.spUsergroupShowMembers').click(function() {
				var mydata = $(this).data();
				spj.showMemberList(mydata.url, mydata.img, mydata.id);
			});
		}
	};

	/*****************************
	 admin forums event handlers
	 *****************************/

	setForumOptions = {
		init: function() {
			$('.spForumSetOptions').change(function() {
				var mydata = $(this).data();
				spj.setForumOptions(mydata.target);
			});
		}
	};

	setForumSequence = {
		init: function() {
			$('.spForumSetSequence').change(function() {
				spj.setForumSequence();
			});
		}
	};

	setForumSlug = {
		init: function() {
			$('#sfmaincontainer').on('change', '.spForumSetSlug', function() {
				var mydata = $(this).data();
				spj.setForumSlug(this, mydata.url, mydata.target, mydata.type);
			});
		}
	};
        
        prepareEditors = {
		init: function() {
                        
                        $( '#sfmaincontainer' ).find('.wp-editor-wrap').each( function () {
                                spj.prepareAjaxEditor( $(this) );
                        });
                        
		}
	};
        
        prepareDatePicker = {
                init : function() {
                        $('.sp-analytics-chart-date').datepicker({
                                beforeShow: function(input, inst) {
                                        $("#ui-datepicker-div").addClass("sp-datepicker");
                                }
                        });
                }
        }
        

	// public properties

	// public methods
	$(document).ready(function() {
		$('#sfmaincontainer').on('adminformloaded', function() {
			toggleLayer.init();
			toggleRow.init();
			deleteRow.init();
			deleteRowReload.init();
			loadForm.init();
			cancelForm.init();
			loadAjax.init();
			reloadForm.init();
			showElement.init();
			checkAll.init();
			uncheckAll.init();
			adminTool.init();
			adminHelp.init();
			openDialog.init();
			troubleshooting.init();
			accordionLoadForm.init();
			multiselectTransfer.init();
			multiselectUpdate.init();
			multiselectFilter.init();
			forumsExpandCollapseGroup.init();
			componentsSpecialRankAdd.init();
			componentsSpecialRankCancel.init();
			componentsSpecialRankShow.init();
			componentsSpecialRankForm.init();
			pluginsUpload.init();
			profileAvatarDefaults.init();
			profileAvatarUpdatePriorities.init();
			themesDeleteConfirm.init();
			themesUpload.init();
			ugShowMembers.init();
			setForumOptions.init();
			setForumSequence.init();
			setForumSlug.init();
                        prepareEditors.init();
                        prepareDatePicker.init();
		});

		$('#sfmaincontainer').on('troubleshootingformloaded', function() {
			searchTool.init();
		});
	});

	// private methods
}(window.spj = window.spj || {}, jQuery));
